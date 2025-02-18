<?php

namespace Drupal\group_contentenabler\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\GroupMembership;

/**
 * Controller group based access.
 *
 * @package Drupal\group_contentenabler\Access
 */
class CustomGroupAccessCheck implements AccessInterface {

  /**
   * A custom access check.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for the logged in user.
   */
  public function access(AccountInterface $account) {
    // Check if the user is a member of the group with the ID 'my_group'.
    $group_membership = GroupMembership::loadByUser($account);
    // Replace 'my_group' with the actual group ID.
    $group_id = \Drupal::service('user.data')->get('group_contentenabler', $account->id(), 'lga');
    if ($group_membership && isset($group_membership[$group_id])) {
      if ($group_membership[$group_id]->getGroup()->getMemberPermission('access settings page')->isAllowed()) {
        return AccessResult::allowed();
      }
    }
    return AccessResult::forbidden();
  }

}
