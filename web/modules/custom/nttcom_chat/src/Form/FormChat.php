<?php

namespace Drupal\nttcom_chat\Form;

use Drupal\Core\Entity\EntityBase;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\user\UserData;

class FormChat extends FormBase
{
  public function getFormId()
  {
    return 'form_chat';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $config = $this->getConfigFirebase();
    $form['#attached']['library'] = 'nttcom_chat/nttcom_chat';
    $form['#attached']['drupalSettings']['configFirebase'] = $config;
//    $form['#theme'] = 'nttcom_chat_blank';
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    parent::validateForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    // TODO: Implement submitForm() method.
  }

  public function getConfigFirebase()
  {
    return $this->config('nttcom_chat.configuration')->get();
  }
}
