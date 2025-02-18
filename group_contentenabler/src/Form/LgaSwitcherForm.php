<?php

namespace Drupal\group_contentenabler\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Group Custom entity settings for this site.
 */
class LgaSwitcherForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'group_contentenabler_lga_switcher_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['group_contentenabler.lga_switcher_settings'];
  }

  /**
   * Load current group.
   */
  public function loadGroup() {
    $loadGroup = \Drupal::service('group_contentenabler.group_membership_service');
    $options = ['All' => 'Select Shire'] + $loadGroup->loadMemberGroup();
    return $options;

  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    $data = !empty($user) ? \Drupal::service('user.data')->get('group_contentenabler', $user->id(), 'lga') : NULL;
    $form['lga_list'] = [
      '#type' => 'select',
      '#options' => $this->loadGroup(),
      '#default_value' => $data,
    ];
    // Add a submit button.
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#attributes' => [
        'class' => ['d-none'],
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $value = $form_state->getValue('lga_list');
    if (!empty($value)) {
      $user = \Drupal::currentUser();
      $data = \Drupal::service('user.data')->get('group_contentenabler', $user->id(), 'lga');
      if (!empty($data) || $data != NULL) {
        \Drupal::service('user.data')->delete('group_contentenabler', $user->id(), 'lga');
      }
      \Drupal::service('user.data')->set('group_contentenabler', $user->id(), 'lga', $value);
      Cache::invalidateTags(['group_token_' . $user->id()]);
      // For temporary invalidate the render cache.
      \Drupal::service('cache.render')->invalidateAll();
    }
    parent::submitForm($form, $form_state);
  }

}
