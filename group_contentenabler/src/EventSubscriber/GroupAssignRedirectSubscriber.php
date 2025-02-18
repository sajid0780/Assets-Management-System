<?php

namespace Drupal\group_contentenabler\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\group\GroupMembershipLoaderInterface;
use Drupal\user\UserDataInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Redirects to the homepage when the user has the "non_grata" role.
 */
class GroupAssignRedirectSubscriber implements EventSubscriberInterface {

  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * User tempstore data.
   *
   * @var \Drupal\user\UserDataInterface
   */
  protected $userData;

  /**
   * Group membership.
   *
   * @var \Drupal\group\GroupMembershipLoaderInterface
   */
  protected $groupMembershipLoader;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * GroupAssignRedirectSubscriber constructor.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user.
   * @param \Drupal\user\UserDataInterface $userData
   *   The user data service.
   * @param \Drupal\group\GroupMembershipLoaderInterface $groupMembershipLoader
   *   The group membership loader service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(AccountProxyInterface $currentUser, UserDataInterface $userData, GroupMembershipLoaderInterface $groupMembershipLoader, EntityTypeManagerInterface $entityTypeManager) {
    $this->currentUser = $currentUser;
    $this->userData = $userData;
    $this->groupMembershipLoader = $groupMembershipLoader;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events['kernel.request'][] = ['onRequest', 0];
    return $events;
  }

  /**
   * Handler for the kernel request event.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The request event.
   */
  public function onRequest(RequestEvent $event) {
    $account = $this->currentUser;
    $data = $this->userData->get('group_contentenabler', $account->id(), 'lga');
    $assignGroups = $this->getCurrentUserGroups($account);
    if (!in_array($data, $assignGroups)) {
      $groupId = !empty($assignGroups[0]) ? $assignGroups[0] : NULL;
      \Drupal::service('group_contentenabler.group_membership_service')->setUserData($account->id(), $groupId);
    }
  }

  /**
   * Gets the current user groups.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   The current user account.
   *
   * @return array
   *   An array of group IDs.
   */
  protected function getCurrentUserGroups($account) {
    // Load all groups that the user is a member of.
    $groups = $this->groupMembershipLoader->loadByUser($account);

    if ($account->hasRole('administrator') || $account->hasRole('asset_engine_admin')) {
      $groups = $this->entityTypeManager->getStorage('group')->loadMultiple();
    }

    $groupIds = [];
    foreach ($groups as $groupMembership) {
      if ($groupMembership instanceof GroupInterface) {
        $groupIds[] = $groupMembership->id();
      }
      else {
        $group = $groupMembership->getGroup();
        if ($group instanceof GroupInterface) {
          $groupIds[] = $group->id();
        }
      }
    }

    return $groupIds;
  }

  /**
   * Factory method for creating the subscriber.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   *
   * @return static
   *   The event subscriber.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('user.data'),
      $container->get('group.membership_loader'),
      $container->get('entity_type.manager')
    );
  }

}
