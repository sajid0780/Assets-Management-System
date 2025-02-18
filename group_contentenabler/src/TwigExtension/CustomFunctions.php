<?php

namespace Drupal\group_contentenabler\TwigExtension;

use Drupal\group\Entity\GroupMembership;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Custom twig functions.
 */
class CustomFunctions extends AbstractExtension {

  /**
   * Declare your custom twig function here.
   *
   * @return \Twig\TwigFunction[]
   *   TwigFunction array.
   */
  public function getFunctions() {
    return [
      'checkaccess' => new TwigFunction('checkaccess',
       ['Drupal\group_contentenabler\TwigExtension\CustomFunctions', 'checkAccess']),
    ];
  }

  /**
   * Checks whether a path is accessible to the current user.
   */
  public static function checkAccess($permission) {
    $account = \Drupal::currentUser();
    $roles = $account->getRoles();
    if (in_array('asset_engine_admin', $roles) || in_array('administrator', $roles)) {
      return TRUE;
    }
    $group_memberships = GroupMembership::loadByUser($account);
    foreach ($group_memberships as $group_membership) {
      // Get the group entity.
      $group = $group_membership->getGroup();
      // Check if the group entity exists and is valid.
      if ($group) {
        if ($group->hasPermission($permission, $account)) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

}
