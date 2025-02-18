<?php

namespace Drupal\group_contentenabler\Plugin\Group\Relation;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\group\Plugin\Group\Relation\GroupRelationTypeInterface;

/**
 * Assist class name.
 */
class AssetsEntityDeriver extends DeriverBase {

  /**
   * Get assets plugin.
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    assert($base_plugin_definition instanceof GroupRelationTypeInterface);
    $this->derivatives = [];
    $name = 'assets';
    $label = "assets";
    $this->derivatives[$name] = clone $base_plugin_definition;
    $this->derivatives[$name]->set('entity_bundle', $name);
    $this->derivatives[$name]->set('label', t('Group entity (@type)', ['@type' => $label]));
    $this->derivatives[$name]->set('description', t('Adds %type content to groups both publicly and privately.', ['%type' => $label]));
    return $this->derivatives;
  }

}
