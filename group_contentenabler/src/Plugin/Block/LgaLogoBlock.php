<?php

namespace Drupal\group_contentenabler\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\Group;

/**
 * Provides a lga logo block.
 *
 * @Block(
 *   id = "group_contentenabler_lga_logo",
 *   admin_label = @Translation("LGA Logo"),
 *   category = @Translation("Custom")
 * )
 */
class LgaLogoBlock extends BlockBase {

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

    // Get the current login user id.
    $userId = \Drupal::currentUser()->id();
    $selectedGroupId = \Drupal::service('user.data')->get('group_contentenabler', $userId, 'lga');
    if ($selectedGroupId) {
      $group = Group::load($selectedGroupId);
      $image = '';
      if ($group->get('field_custom_logo')->getValue()) {
        $customLogoObj = $group->get('field_custom_logo')->referencedEntities()[0];
        if ($customLogoObj) {
          $logoURL = \Drupal::service('file_url_generator')->generateAbsoluteString($customLogoObj->getFileUri());
          if ($logoURL) {
            $image = '<span class="lga-logo"><img src="' . $logoURL . '" /></span>';
          }
        }
      }
      // '<div class="lga-logo-block-wrapper"><span class="lga-label">' . $group->label() . '</span>' . $image . '</div>';
      return [
        '#markup' => '<div class="lga-logo-block-wrapper">' . $image . '</div>',
        '#cache' => [
          'max-age' => 0,
        ],
      ];
    }
  }

}
