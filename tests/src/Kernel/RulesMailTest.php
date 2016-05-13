<?php

namespace Drupal\Tests\rules\Kernel;

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
    $this->container->get('state')->set('system.test_mail_collector', []);

    $this->actionManager = $this->container->get('plugin.manager.rules_action');
  }

  /**
   * Checks the From: and Reply-to: headers.
   */
  public function testSubjectAndBody() {
    // Create action to send email.
    $action = $this->actionManager->createInstance('rules_send_email');

    $params = [
      'to' => ['mail@example.com'],
      'subject' => 'subject',
      'message' => 'hello',
    ];

    // Add context values to action.
    $action->setContextValue('to', $params['to'])
      ->setContextValue('subject', $params['subject'])
      ->setContextValue('message', $params['message']);

    // Send email.
    $action->execute();

    // Retrieve sent message.
    $captured_emails = $this->container->get('state')->get('system.test_mail_collector');
    $sent_message = end($captured_emails);

    // Check to make sure that our subject and body are as expected.
    $this->assertEquals($sent_message['to'], $params['to'][0]);
    $this->assertEquals($sent_message['subject'], $params['subject']);
    // Need to trim the email body to get rid of newline at end.
    $this->assertEquals(trim($sent_message['body']), $params['message']);
  }

}
