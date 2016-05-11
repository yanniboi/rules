<?php

namespace Drupal\rules\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides route controllers for Reaction Rules.
 */
class RulesReactionController extends ControllerBase {

  /**
   * Enables or disables a Rule.
   *
   * @param \Drupal\Core\Config\Entity\ConfigEntityInterface $rules_reaction_rule
   *   The rule entity.
   * @param string $op
   *   The operation to perform, usually 'enable' or 'disable'.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect back to the rules list page.
   */
  public function performReactionRuleOperation(ConfigEntityInterface $rules_reaction_rule, $op) {
    $rules_reaction_rule->$op()->save();

    if ($op == 'enable') {
      drupal_set_message($this->t('The %label rule has been enabled.', ['%label' => $rules_reaction_rule->label()]));
    }
    elseif ($op == 'disable') {
      drupal_set_message($this->t('The %label rule has been disabled.', ['%label' => $rules_reaction_rule->label()]));
    }

    return $this->redirect('entity.rules_reaction_rule.collection');
  }

}
