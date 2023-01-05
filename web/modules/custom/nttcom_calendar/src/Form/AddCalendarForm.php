<?php

namespace Drupal\nttcom_calendar\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a Nttcom calendar form.
 */
class AddCalendarForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'nttcom_calendar_add_calendar';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['start_date'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Start date'),
      '#required' => TRUE,
    ];
    $form['end_date'] = [
      '#type' => 'textfield',
      '#title' => $this->t('End date'),
      '#required' => TRUE,
    ];
    $form['start_time'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Start time'),
      '#required' => TRUE,
    ];
    $form['end_time'] = [
      '#type' => 'textfield',
      '#title' => $this->t('End time'),
      '#required' => TRUE,
    ];
    $form['selectbox'] = [
      '#type' => 'select',
      '#title' => $this->t('Select box'),
      '#required' => TRUE,
      '#options'=>[
        'a'=> $this->t('Option A'),
        'b'=> $this->t('Option B'),
        'c'=> $this->t('Option C')
      ]
    ];
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send'),
    ];
    $form['#theme'] = 'nttcom_block_calendar_form';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (mb_strlen($form_state->getValue('message')) < 10) {
      $form_state->setErrorByName('message', $this->t('Message should be at least 10 characters.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->messenger()->addStatus($this->t('The message has been sent.'));
    $form_state->setRedirect('<front>');
  }

}
