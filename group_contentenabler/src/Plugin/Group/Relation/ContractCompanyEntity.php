<?php

namespace Drupal\group_contentenabler\Plugin\Group\Relation;

use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Plugin\Group\Relation\GroupRelationBase;

/**
 * Provides a content enabler for user entities.
 *
 * @GroupRelationType(
 *   id = "group_contracting_companies",
 *   label = @Translation("contracting_companies"),
 *   description = @Translation("Adds contracting_companies to groups as pure entities."),
 *   entity_type_id = "contracting_companies",
 *   entity_access = TRUE,
 *   pretty_path_key = "contracting_companies",
 *   reference_label = @Translation("title"),
 *   reference_description = @Translation("The name of the contracting_companies (entity) to add to the group"),
 *   deriver = "Drupal\group_contentenabler\Plugin\Group\Relation\ContractCompanyEntityDeriver",
 * )
 */
class ContractCompanyEntity extends GroupRelationBase {

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
    return ['module' => ['contracting_companies']];
  }

}
