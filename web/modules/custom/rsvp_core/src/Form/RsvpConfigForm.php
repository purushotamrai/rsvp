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
    $form['rsvp_core_allowed_radius'] = [
			'#type' => 'number',
			'#title' => $this->t('RSVP Allowed Radius'),
			'#description' => $this->t('Only allow users within a 20 miles radius from the Event Location to RSVP'),
			'#default_value' => $config->get('rsvp_core_allowed_radius'),
		];
		$form['rsvp_core_join_text'] = [
			'#type' => 'textfield',
			'#title' => $this->t('RSVP Join Button text'),
			'#description' => $this->t('RSVP display text'),
			'#default_value' => $config->get('rsvp_core_join_text'),
		];
		$form['rsvp_core_cannot_join_text'] = [
			'#type' => 'textfield',
			'#title' => $this->t('RSVP Cannot Join Button text'),
			'#description' => $this->t('RSVP - Not allowed display text'),
			'#default_value' => $config->get('rsvp_core_cannot_join_text'),
		];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('rsvp_core.config')
      ->set('rsvp_core_allowed_radius', $form_state->getValue('rsvp_core_allowed_radius'))
			->set('rsvp_core_join_text', $form_state->getValue('rsvp_core_join_text'))
			->set('rsvp_core_cannot_join_text', $form_state->getValue('rsvp_core_cannot_join_text'))
      ->save();
  }

}
