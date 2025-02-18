<?php

namespace Drupal\group_contentenabler;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\user\Entity\User;

/**
 * Service description.
 */
class GroupcontentenablerGroupMembershipService {

  /**
   * Method description.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;

  }

  /**
   * Add member to group.
   */
  public function addMemberToGroup($uid, $groupId, $role) {
    $user = $this->entityTypeManager->getStorage('user')->load($uid);
    $group = $this->entityTypeManager->getStorage('group')->load($groupId);
    if ($user && $group && $role) {
      $group->addMember($user, ['group_roles' => [$role]]);
      $group->save();
    }
  }

  /**
   * Load group type.
   */
  public function loadGroupTypeRoles() {
    // Load the group type.
    $group_type = \Drupal::entityTypeManager()->getStorage('group_type')->load('lga');
    // Load the group roles associated with the group type.
    $group_roles = \Drupal::entityTypeManager()->getStorage('group_role')->loadByProperties(['group_type' => $group_type->id()]);
    $roles = [];
    if (!empty($group_roles)) {
      foreach ($group_roles as $role_id => $group_role) {
        if (!in_array($role_id, ['lga-contractor_member', 'lga-administrator', 'lga-contractor_admin', 'lga-asset_engine_admin', 'lga-super_admin'])) {
          $roles[$role_id] = $group_role->label();
        }
      }
    }
    return $roles;
  }

  /**
   * Load group type.
   */
  public function loadGroupType() {
    $load_group = $this->entityTypeManager->getStorage('group_type')->load('lga');
    $group_name = [];
    if (!empty($load_group)) {
      $groups = $this->entityTypeManager->getStorage('group')->loadByProperties([
        'type' => $load_group->id(),
      ]);
      foreach ($groups as $group) {
        $group_name[$group->id()] = $group->label();
      }
      return $group_name;
    }
  }

  /**
   * Load member group.
   */
  public function loadMemberGroup() {
    // Load the current user.
    $current_user = User::load(\Drupal::currentUser()->id());
    // Load all groups that the user is a member of.
    $groups = \Drupal::service('group.membership_loader')->loadByUser($current_user);
    if ($current_user->hasRole("administrator") || $current_user->hasRole("asset_engine_admin")) {
      $groups = \Drupal::entityTypeManager()->getStorage('group')->loadMultiple();
    }
    $group_names = [];
    foreach ($groups as $group_members) {
      /** @var \Drupal\group\Entity\GroupInterface $group */
      if ($current_user->hasRole("administrator") || $current_user->hasRole("asset_engine_admin")) {
        if ($group_members instanceof GroupInterface) {
          $group_names[$group_members->id()] = $group_members->label();
        }
      }
      else {
        $group = $group_members->getGroup();
        if ($group instanceof GroupInterface) {
          $group_names[$group->id()] = $group->label();
        }
      }
    }

    return $group_names;
  }

  /**
   * Add content to group.
   */
  public function addContentToGroup($group, $entity, $pluginId) {
    if ($group) {
      $relationships = $group->getRelationshipsByEntity($entity, $pluginId);
      if (!$relationships) {
        $group->addRelationship($entity, $pluginId);
      }
    }
    else {
      // Handle the case where the group could not be loaded.
      // For example, log an error or display a message to the user.
      \Drupal::logger('group_contentenabler')->error('Failed to load group with ID: @group_id', ['@group_id' => $group_id]);
    }
  }
  /**
   * Add content to group.
   */
  public function removeContentFromGroup($group, $entity,$type) {
    $isRemoved = false;
    if ($group) {
      $groupContentSearch = $group->getRelationshipsByEntity($entity,$type);
      if (!empty($groupContentSearch)) {
        // $group_content = reset($groupContentSearch);
        foreach ($groupContentSearch as $groupContent) {
            $groupContent->delete();
            $isRemoved = true;
        }
      }
    }
    return $isRemoved;
  }

  /**
   * Set user data.
   */
  public function setUserData($id, $group_id) {
    // Load the current user.
    // $user = User::load($id);
    $data = \Drupal::service('user.data')->get('group_contentenabler', $id, 'lga');
    if (!empty($data) || $data != NULL) {
      \Drupal::service('user.data')->delete('group_contentenabler', $id, 'lga');
    }
    \Drupal::service('user.data')->set('group_contentenabler', $id, 'lga', $group_id);
  }

}
