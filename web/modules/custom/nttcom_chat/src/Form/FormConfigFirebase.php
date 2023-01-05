<?php

namespace Drupal\nttcom_chat\Form;

use Drupal\Core\Form\FormStateInterface;

class FormConfigFirebase extends \Drupal\Core\Form\ConfigFormBase
{

  /**
   * @inheritDoc
   */
  protected function getEditableConfigNames()
  {
    return [
      'nttcom_chat.configuration'
    ];
  }

  /**
   * Get id of form
   *
   * @inheritDoc
   */
  public function getFormId()
  {
    return 'nttcom_chat.configuration';
  }

  /**
   * Build form setting configuration for chat
   *
   * @param array $form
   * @param FormStateInterface $form_state
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $config = $this->config('nttcom_chat.configuration');
    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key'),
      '#default_value' => $config->get('api_key'),
    ];
    $form['auth_domain'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Auth Domain'),
      '#default_value' => $config->get('auth_domain')
    ];
    $form['database_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Database URL'),
      '#default_value' => $config->get('database_url')
    ];
    $form['project_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Project ID'),
      '#default_value' => $config->get('project_id')
    ];
    $form['storage_bucket'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Storage Bucket'),
      '#default_value' => $config->get('storage_bucket')
    ];
    $form['messaging_sender_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Messaging Sender ID'),
      '#default_value' => $config->get('messaging_sender_id')
    ];
    $form['app_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('App ID'),
      '#default_value' => $config->get('app_id'),
    ];
    $form['measurement_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Measurement ID'),
      '#default_value' => $config->get('measurement_id')
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * Get values to form set
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    foreach ($form_state->getValues() as $key => $value) {
      $this->config('nttcom_chat.configuration')
        ->set($key, $value)
        ->save();
    }
    parent::submitForm($form, $form_state);
  }
}
