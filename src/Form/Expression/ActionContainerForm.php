<?php

namespace Drupal\rules\Form\Expression;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\rules\Ui\RulesUiHandlerTrait;
use Drupal\rules\Engine\ActionExpressionContainerInterface;

/**
 * Form handler for action containers.
 */
class ActionContainerForm implements ExpressionFormInterface {

  use StringTranslationTrait;
  use RulesUiHandlerTrait;
  use ExpressionFormTrait;

  /**
   * The rule expression object this form is for.
   *
   * @var \Drupal\rules\Engine\ActionExpressionContainerInterface
   */
  protected $actionSet;

  /**
   * Creates a new object of this class.
   */
  public function __construct(ActionExpressionContainerInterface $action_set) {
    $this->actionSet = $action_set;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form['action_table'] = [
      '#type' => 'container',
    ];

    $form['action_table']['table'] = [
      '#type' => 'table',
      '#theme' => 'table',
      '#header' => [
        $this->t('Elements'),
        $this->t('Weight'),
        [
          'data' => $this->t('Operations'),
          'colspan' => 3,
        ],
      ],
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'action-weight',
          'limit' => 1,
        ],
      ],
    ];

    $form['action_table']['table']['#empty'] = $this->t('None');
    foreach ($this->actionSet as $action) {
      $uuid = $action->getUuid();
      $form['action_table']['table'][$uuid]['#item'] = $action;

      // TableDrag: Mark the table row as draggable.
      $form['action_table']['table'][$uuid]['#attributes']['class'][] = 'draggable';

      // TableDrag: Sort the table row according to its existing/configured weight.
      $form['action_table']['table'][$uuid]['#weight'] = $action->getWeight();
      $form['action_table']['table'][$uuid]['title'] = ['#markup' => $action->getLabel()];

      $form['action_table']['table'][$uuid]['weight'] = [
        '#type' => 'weight',
        '#delta' => 50,
        '#default_value' => 0,
        '#attributes' => ['class' => ['action-weight']]
      ];

      // Operations (dropbutton) column.
      $form['action_table']['table'][$uuid]['operations'] = [
        'data' => [
          '#type' => 'dropbutton',
          '#links' => [
            'edit' => [
              'title' => $this->t('Edit'),
              'url' => $this->getRulesUiHandler()->getUrlFromRoute('expression.edit', [
                'uuid' => $uuid,
              ]),
            ],
            'delete' => [
              'title' => $this->t('Delete'),
              'url' => $this->getRulesUiHandler()->getUrlFromRoute('expression.delete', [
                'uuid' => $uuid,
              ]),
            ],
          ],
        ],
      ];

      $form['action_table']['table'][$uuid]['id'] = [
        '#type' => 'hidden',
        '#value' => $uuid,
        '#attributes' => ['class' => ['action-id']]
      ];
    }

    // @todo Put this into the table as last row and style it like it was in
    // Drupal 7 Rules.
    $form['add_action'] = [
      '#theme' => 'menu_local_action',
      '#link' => [
        'title' => $this->t('Add action'),
        'url' => $this->getRulesUiHandler()->getUrlFromRoute('expression.add', [
          'expression_id' => 'rules_action',
        ]),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValue('table');
    $component = $this->getRulesUiHandler()->getComponent();
    /* @var $rule_expression \Drupal\rules\Plugin\RulesExpression\Rule */
    $rule_expression = $component->getExpression();

    foreach ($values as $uuid => $expression) {
      $action = $rule_expression->getExpression($uuid);
      $action->setWeight($expression['weight']);
      $action->setConfiguration($action->getConfiguration());
    }

    $this->getRulesUiHandler()->updateComponent($component);
  }

}
