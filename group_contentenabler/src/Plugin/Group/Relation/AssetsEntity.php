<?php

namespace Drupal\group_contentenabler\Plugin\Group\Relation;

use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Plugin\Group\Relation\GroupRelationBase;

/**
 * Provides a content enabler for user entities.
 *
 * @GroupRelationType(
 *   id = "group_assets",
 *   label = @Translation("assets"),
 *   description = @Translation("Adds assets to groups as pure entities."),
 *   entity_type_id = "assets",
 *   entity_access = TRUE,
 *   pretty_path_key = "assets",
 *   reference_label = @Translation("title"),
 *   reference_description = @Translation("The name of the assets (entity) to add to the group"),
 *   deriver = "Drupal\group_contentenabler\Plugin\Group\Relation\AssetsEntityDeriver",
 * )
 */
class AssetsEntity extends GroupRelationBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $config = parent::defaultConfiguration();
    $config['entity_cardinality'] = 1;
    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    // Disable the entity cardinality field as the functionality of this module
    // relies on a cardinality of 1. We don't just hide it, though, to keep a UI
    // that's consistent with other content enabler plugins.
    $info = $this->t("This field has been disabled by the plugin to guarantee the functionality that's expected of it.");
    $form['entity_cardinality']['#disabled'] = TRUE;
    $form['entity_cardinality']['#description'] .= '<br /><em>' . $info . '</em>';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return ['module' => ['assets_management']];
  }

}
