<?php

namespace Drupal\group_contentenabler\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\GroupMembership;
use Drupal\user\Entity\User;

/**
 * Returns responses for Group Custom entity routes.
 */
class GroupLgaMembers extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {
    $uid = \Drupal::request()->get('uid');
    // If the uid is not provided, set it to a default value (e.g., null).
    if (empty($uid)) {
      $user = \Drupal::currentUser();
      $data = \Drupal::service('user.data')->get('group_contentenabler', $user->id(), 'lga');
      return [
        '#markup' => $this->t('
          <div class="empty-icon-wrapper mt-64">
            <img class="empty-icon mb-16" src="/themes/custom/assets_theme/icons/empty-illustration.png">
              <div class="empty-content">
                <h5 class="mb-0">Select a user</h5>
              </div>
              <p class="title-block__body text-center mb-16">or add a new user by selecting the ‘Add <br> new member’ button below.</p>
              <a href="/admin/people/create/add_new_member?gid=@data&type=member" class="use-ajax button link--primary link-decoration-none button--secondary" data-dialog-type="modal" data-dialog-options="{&quot;title&quot;:&quot;Add Upload file or add URL&quot;, &quot;width&quot;:480, &quot;dialogClass&quot;: &quot;popup-modal file-button&quot;}" >
              <img src="/themes/custom/assets_theme/icons/add.svg"> Add new member
              </a>
          </div>',
          ['@data' => $data]
        ),
        '#cache' => [
          'contexts' => ['user'],
        ]
      ];
    }
    $uid = (!empty($uid)) ? $uid : 1;
    $user = User::load($uid);
    // Build the user edit form using the specified form mode.
    $form = \Drupal::service('entity.form_builder')->getForm($user, 'lga_member');
    $form['#action'] = "/user/{$user->id()}/edit/lga_member?destination=/lga-members?uid=$uid";
    return $form;
  }

  /**
   * Check Access.
   */
  public function access(AccountInterface $account) {
    $roles = $account->getRoles();
    if (in_array('asset_engine_admin', $roles) || in_array('administrator', $roles)) {
      return AccessResult::allowed();
    }
    // Check if the user is a member of the group with the ID 'my_group'.
    $group_memberships = GroupMembership::loadByUser($account);
    foreach ($group_memberships as $group_membership) {
      // Get the group entity.
      $group = $group_membership->getGroup();
      // Check if the group entity exists and is valid.
      if ($group) {
        if ($group->hasPermission("administer members", $account)) {
          return AccessResult::allowed();
        }
      }
    }
    return AccessResult::forbidden();
  }

}
