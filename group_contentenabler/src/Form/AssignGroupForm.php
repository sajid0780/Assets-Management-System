<?php

namespace Drupal\group_contentenabler\Form;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\GroupMembership;
use Drupal\user\Entity\User;

/**
 * Form class for assigning groups.
 */
class AssignGroupForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'assign_group_form_selectlist';
  }

  /**
   * Load all groups.
   */
  public function getListOfGroups() {
    $groupMembershipService = \Drupal::service('group_contentenabler.group_membership_service');
    return $groupMembershipService->loadGroupType();
  }

  /**
   * Get a list of all defined group roles.
   */
  public function getGroupRoles() {
    $groupMembershipService = \Drupal::service('group_contentenabler.group_membership_service');
    return $groupMembershipService->loadGroupTypeRoles();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {
    $form['group_name'] = [
      '#type' => 'select',
      '#title' => $this->t('Select LGA'),
      '#required' => TRUE,
      '#placeholder' => $this->t('Select group'),
      '#options' => $this->getListOfGroups(),
    ];

    $form['group_role'] = [
      '#type' => 'select',
      '#title' => $this->t('Assign role'),
      '#required' => TRUE,
      '#options' => $this->getGroupRoles(),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    // Add a submit button.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validation logic can be added here if needed.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $groupId = $values['group_name'];
    $role = $values['group_role'];
    $uid = \Drupal::request()->query->get('uid');
    $uid = (!empty($uid)) ? $uid : 1;
    if (!empty($uid) && !empty($groupId) && !empty($role)) {
      $user = User::load($uid);
      $roles = $user->getRoles();
      if (!in_array('administrator', $roles) && !in_array('asset_engine_admin', $roles)) {
        // Inject the service.
        $groupMembershipService = \Drupal::service('group_contentenabler.group_membership_service');
        // Call the method to add a member to a group.
        $groupMembershipService->addMemberToGroup($uid, $groupId, $role);
        if ($role == "lga-admin") {
          // Set the role for the user.
          $user->addRole('lga_admin');
        }
        elseif ($role == "lga-member") {
          // Set the role for the user.
          $user->addRole('lga_member');
        }
        // Save the user.
        $user->save();
      }
    }
  }

  /**
   * Group access controller.
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
        if ($group->hasPermission("access assign group form", $account)) {
          return AccessResult::allowed();
        }
      }
    }
    return AccessResult::forbidden();
  }

}
