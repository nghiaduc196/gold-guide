<?php

namespace Drupal\nttcom_user\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

/**
 * Provides a Nttcom User form.
 */
class CancelAccountForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'nttcom_user_cancel_account';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['announcement'] =[
      '#type'=>'markup',
      '#markup'=>'<h3>Do you want to cancel your account?</h3>'
    ];
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];
    $form['actions']['cancel'] = [
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
      '#submit' => ['::cancelForm'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->messenger()->addStatus($this->t('You has been cancel your account!'));
    $user = User::load($this->currentUser()->id());
    if(in_array(ROLE_GUEST, $user->getRoles())){
      $user->delete();
    }
    if(in_array(ROLE_GUIDE, $user->getRoles())){
      $user->block();
      $user->save();
    }
    user_logout();
    $form_state->setRedirect('<front>');
  }

  /**
   * {@inheritdoc}
   */
  public function cancelForm(array &$form, FormStateInterface $form_state){
    $form_state->setRedirect('user.page');
  }

}
