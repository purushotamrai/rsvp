<?php

namespace Drupal\rsvp_core\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class RsvpConfigForm.
 */
class RsvpConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'rsvp_core.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rsvp_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('rsvp_core.config');
    $form['rsvp_allowed_radius'] = [
      '#type' => 'number',
      '#title' => $this->t('RSVP Allowed Radius'),
      '#description' => $this->t('Only allow users within a 20 miles radius from the Event Location to RSVP'),
      '#default_value' => $config->get('rsvp_allowed_radius'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('rsvp_core.config')
      ->set('rsvp_allowed_radius', $form_state->getValue('rsvp_allowed_radius'))
      ->save();
  }

}
