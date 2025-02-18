<?php

namespace Drupal\group_contentenabler\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a lga switcher block.
 *
 * @Block(
 *   id = "group_contentenabler_lga_switcher",
 *   admin_label = @Translation("lga switcher"),
 *   category = @Translation("Custom")
 * )
 */
class LgaSwitcherBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    // @DCG Evaluate the access condition here.
    $condition = TRUE;
    return AccessResult::allowedIf($condition);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\group_contentenabler\Form\LgaSwitcherForm');
    return $form;
  }

}
