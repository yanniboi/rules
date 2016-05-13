<?php

namespace Drupal\Tests\rules\Kernel;

use Drupal\Core\Language\LanguageInterface;

/**
 * Performs tests on the pluggable mailing framework.
 *
 * @group Mail
 */
class RulesMailTest extends RulesDrupalTestBase {

  /**
   * The action manager used to instantiate the action plugin.
   *
   * @var \Drupal\rules\Core\RulesActionManagerInterface
   */
  protected $actionManager;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['rules'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    // Use the state system collector mail backend.
    $this->container->get('config.factory')->getEditable('system.mail')
      ->set('interface.default', 'test_mail_collector')
      ->save();

    // Reset the state variable that holds sent messages.
    \Drupal::state()->set('system.test_mail_collector', []);

    $this->actionManager = $this->container->get('plugin.manager.rules_action');
  }

  /**
   * Checks the From: and Reply-to: headers.
   */
  public function testSubjectAndBody() {
    // Create action and add context values.
    $action = $this->actionManager->createInstance('rules_send_email');

    $to = ['mail@example.com'];
    $action->setContextValue('to', $to)
      ->setContextValue('subject', 'subject')
      ->setContextValue('message', 'hello');

    $params = [
      'subject' => 'subject',
      'message' => 'hello',
    ];

    // Send email.
    \Drupal::service('plugin.manager.mail')->mail(
      'rules', 'rules_action_mail_' . $action->getPluginId(),
      $to[0],
      LanguageInterface::LANGCODE_SITE_DEFAULT,
      $params,
      NULL
    );

    // Retrieve sent message.
    $captured_emails = \Drupal::state()->get('system.test_mail_collector');
    $sent_message = end($captured_emails);

    // Check to make sure that our subject and body are as expected.
    $this->assertEquals($sent_message['to'], $to[0]);
    $this->assertEquals($sent_message['subject'], $params['subject']);
    // Need to trim the email body to get rid of newline at end.
    $this->assertEquals(trim($sent_message['body']), $params['message']);
  }

}
