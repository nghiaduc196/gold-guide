<?php

namespace Drupal\nttcom_user\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Random;

class NttcomUserVerificationMailExistForm extends FormBase {

  public function getFormId () {
   return 'nttcom_user_verification_mail_exist_form';
  }

  public function buildForm (array $form, FormStateInterface $form_state) {
//    $form['#attached']['library'][] = 'nttcom_user/verification_email';

    $form['email_verification'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email'),
      '#default_value' => $form_state->getValue('email_verification', '')
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['next'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Next'),
      // Custom submission handler for page 1.
      '#submit' => ['::sendCodeVerification'],
      // Custom validation handler for page 1.
      '#validate' => ['::mailValidate'],
    ];
    if ($form_state->has('page') && $form_state->get('page') == 2) {
      $config = \Drupal::service('config.factory')->getEditable('captcha.captcha_point.nttcom_user_verification_mail_exist_form');
      $config->set('status', FALSE)->save();
      return $this->stepBuildFormVerificationCode($form, $form_state);
    }
    if (!$form_state->has('page')) {
      $form_state->set('page', 1);
    }
    if ($form_state->has('page') && $form_state->get('page') == 1) {
      $config = \Drupal::service('config.factory')->getEditable('captcha.captcha_point.nttcom_user_verification_mail_exist_form');
      $config->set('status', TRUE)->save();
    }
    $form['#prefix'] = $this->getFormPrefix(1);

    return $form;
  }

  public function mailValidate (array &$form, FormStateInterface $form_state) {
    $email_regist = $form_state->getValue('email_verification');
    if (!\Drupal::service('email.validator')->isValid($email_regist)
      || !filter_var($email_regist, FILTER_VALIDATE_EMAIL)) {
      $form_state->setErrorByName(
        'email_verification',
        t('The email address %mail is not valid.', array('%mail' => $email_regist)));
    }
    $ids = \Drupal::entityQuery('user')
      ->condition('mail', $email_regist)
      ->execute();
    if (!empty($ids)) {
      $form_state->setErrorByName(
        'email_verification',
        t('This email address %mail already exists', array('%mail' => $email_regist)));
    }
  }

  public function submitForm (array &$form, FormStateInterface $form_state) {
    // TODO: Implement submitForm() method.
  }

  public function sendCodeVerification(array &$form, FormStateInterface $form_state) {
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $random = new Random();
    $code_generation = "NTT-".$random->name(6, TRUE);

    //Add send mail code verification
    $mailManager = \Drupal::service('plugin.manager.mail');
    $message_body = $this->t('Code verification: ',[],[$langcode])->render().$code_generation;
    $module = 'nttcom_user';
    $key = 'code_verification_exist_mail';
    $to = $form_state->getValue('email_verification');
    $params['message'] = $message_body;
    $params['mail'] = $form_state->getValue('email_verification');

    $send = TRUE;
    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    if ($result['send'] === TRUE) {
      $message = t('Code has been sent to your email. Please check your email: @email ', ['@email' => $to]);
      \Drupal::messenger()->addMessage($message);
      \Drupal::logger('nttcom-user-mail')->info($message);
      $form_state
        ->set('page_values', [
          // Keep only first step values to minimize stored data.
          'email_verification' => $form_state->getValue('email_verification'),
          'code_generation' => $code_generation
        ])
        ->set('page', 2)
        // Since we have logic in our buildForm() method, we have to tell the form
        // builder to rebuild the form. Otherwise, even though we set 'page'
        // to 2, the AJAX-rendered form will still show page 1.
        ->setRebuild(TRUE);
    }
  }

  public function stepBuildFormVerificationCode(array &$form, FormStateInterface $form_state) {
//    $form['#attached']['library'][] = 'nttcom_user/verification_email';
    $form['email_verification']['#access'] = FALSE;
    $form['actions']['#access'] = FALSE;
    $form['code_verification'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Verification Code'),
      '#required' => TRUE,
      '#default_value' => $form_state->getValue('code_verification', ''),
    ];

    $form['back'] = [
      '#type' => 'submit',
      '#value' => $this->t('Back'),
      // Custom submission handler for 'Back' button.
      '#submit' => ['::backStepEnterMail'],
      // We won't bother validating the required 'color' field, since they
      // have to come back to this page to submit anyway.
      '#limit_validation_errors' => [],
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Next'),
      '#submit' => ['::nextStepRegisterUser'],
      '#validate' => ['::codeVerificationValidate'],
    ];

    $form['#prefix'] = $this->getFormPrefix(2);

    return $form;
  }
  public function codeVerificationValidate(array &$form, FormStateInterface $form_state) {
    $code_gen = $form_state->get('page_values')['code_generation'];
    $code_ver = $form_state->getValue('code_verification');
    if (strcmp($code_gen,$code_ver) !== 0) {
      $form_state->setErrorByName(
        'code_verification',
        t('This code: "%code" do not match.
        This code is case sensitive. Please re-enter code', ['%code' => $code_ver]));
    }
  }
  public function nextStepRegisterUser(array &$form, FormStateInterface $form_state) {
    $value_verification = $form_state->get('page_values');
    $router = 'user.register';
    $form_state->setRedirect($router, $value_verification);
  }

  public function backStepEnterMail(array &$form, FormStateInterface $form_state) {
    $form_state
      // Restore values for the first step.
      ->setValues($form_state->get('page_values'))
      ->set('page', 1)
      // Since we have logic in our buildForm() method, we have to tell the form
      // builder to rebuild the form. Otherwise, even though we set 'page'
      // to 1, the AJAX-rendered form will still show page 2.
      ->setRebuild(TRUE);

  }

  public function getFormPrefix($step) {
    switch ($step) {
      case 1:
        return '<div class="my-form-wrapper">
             <ul id="progressbar">
               <li class="active" id="mail"><span><strong>Enter Mail</strong></span></li>
               <li id="code"><span><strong>Verification Email</strong></span></li>
             </ul>
         </div>';
        break;
      case 2:
        return '<div class="my-form-wrapper">
           <ul id="progressbar">
               <li id="mail"><span><strong>Enter Mail</strong></span></li>
               <li class="active" id="code"><span><strong>Verification Email</strong></span></li>
         </ul>
         </div>';
        break;
      default:
        return '';
    }
  }
}
