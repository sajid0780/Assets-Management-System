<?php

namespace Drupal\group_contentenabler\Plugin\views\argument_default;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\group\GroupMembershipLoaderInterface;
use Drupal\views\Plugin\views\argument_default\ArgumentDefaultPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Default argument plugin to get current user group ids.
 *
 * @ingroup views_argument_default_plugins
 *
 * @ViewsArgumentDefault(
 *   id = "current_groups_id",
 *   title = @Translation("Current group id")
 * )
 */
class CurrentGroupIds extends ArgumentDefaultPluginBase implements CacheableDependencyInterface {

  /**
   * Current user object.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Service to get user groups.
   *
   * @var \Drupal\group\GroupMembershipLoaderInterface
   */
  protected $groupMembership;

  /**
   * Constructs a new CurrentUserGroup instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   The account object representing the current user.
   * @param \Drupal\group\GroupMembershipLoaderInterface $groupMembershipLoader
   *   The group membership loader service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountProxyInterface $account, GroupMembershipLoaderInterface $groupMembershipLoader) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->currentUser = $account;
    $this->groupMembership = $groupMembershipLoader;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user'),
      $container->get('group.membership_loader')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getArgument() {
    // If the user can bypass group access - associate the user with all groups.
    $user = \Drupal::currentUser();
    $data = \Drupal::service('user.data')->get('group_contentenabler', $user->id(), 'lga');
    return implode('+', [$data]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return ['user'];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return Cache::PERMANENT;
  }

}
