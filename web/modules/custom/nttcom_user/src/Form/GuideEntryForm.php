<?php

namespace Drupal\nttcom_user\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\nttcom_user\UserServiceInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\user\Entity\User;
use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Nttcom guide update info form.
 */
class GuideEntryForm extends FormBase {


  /**
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * @var \Drupal\nttcom_user\UserServiceInterface
   */
  protected $userService;

  /**
   *{@inheritdoc}
   */
  public function __construct(AccountProxyInterface $account_proxy, UserServiceInterface $user_service) {
    $this->currentUser = $account_proxy;
    $this->userService = $user_service;
  }

  /**
   *{@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('nttcom_user')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'nttcom_guide_entry_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $user = User::load($this->currentUser->id());
    $paragraph = NULL;
    if(!$user->get('field_addination_info')->isEmpty()){
      /**
       * @var \Drupal\paragraphs\ParagraphInterface $paragraph
       */
      $paragraph = $user->field_addination_info->entity;
    }

    if($user->get('field_approval_status')->value =='waiting_approval'){
      $form['announcement'] =[
        '#type'=>'markup',
        '#markup'=> $this->t('Your account is begin review. Please come back next time!')
      ];
    }
    $form['field_first_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('姓'),
      '#required' => TRUE,
      '#default_value'=> $user->get('field_first_name')->value
    ];
    $form['field_last_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('名'),
      '#required' => TRUE,
      '#default_value'=> $user->get('field_last_name')->value
    ];
    $form['field_area'] = [
      '#type' => 'select',
      '#title' => $this->t('お住まいのところ'),
      '#required' => TRUE,
      '#options' => $this->userService->getOptionTerms(),
      '#default_value'=>''
    ];
    $form['field_national_license'] = [
      '#type' => 'select',
      '#title' => $this->t('全国通訳案内士資格'),
      '#required' => TRUE,
      '#options' => [
        1 => $this->t('有'),
        0 => $this->t('無'),
      ],
      '#default_value'=> $this->prepareParagraphData($paragraph, 'field_national_license')
    ];
    $form['field_region_license'] = [
      '#type' => 'select',
      '#title' => $this->t('地域通訳案内士資格'),
      '#required' => TRUE,
      '#options' => [
        1 => $this->t('有'),
        0 => $this->t('無'),
      ],
      '#default_value'=> $this->prepareParagraphData($paragraph, 'field_region_license')
    ];
    $form['field_operating_day'] = [
      '#type' => 'select',
      '#title' => $this->t('稼働日数（2019年）'),
      '#required' => TRUE,
      '#options' => $this->userService->getFieldSetting('paragraph', 'addination_info', 'field_operating_day'),
      '#default_value'=> $this->prepareParagraphData($paragraph, 'field_operating_day')
    ];
    $form['field_working_day'] = [
      '#type' => 'select',
      '#title' => $this->t('稼働日数（資格取得後通算）'),
      '#required' => TRUE,
      '#options' => $this->userService->getFieldSetting('paragraph', 'addination_info', 'field_working_day'),
      '#default_value'=> $this->prepareParagraphData($paragraph, 'field_working_day')
    ];
    $form['field_other_qualification'] = [
      '#type' => 'textfield',
      '#title' => $this->t('その他資格'),
      '#required' => TRUE,
      '#default_value'=> $this->prepareParagraphData($paragraph, 'field_other_qualification')
    ];
    $form['email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email'),
      '#required' => TRUE,
      '#default_value' => $this->currentUser()->getEmail(),
    ];
    $form['field_phone_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Phone number'),
      '#required' => TRUE,
      '#default_value'=> $user->get('field_phone_number')->value
    ];
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('送信'),
    ];
    $form['actions']['cancel'] = [
      '#type' => 'submit',
      '#value' => $this->t('キャンセル'),
      '#submit' => ['::cancelForm'],
    ];
    $form['#theme'] = 'nttcom_guide_entry_form';
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
    $values = $form_state->getValues();
    $res = $this->userService->updateGuideEntry($values);
    $msg = $this->t('Your request has been sent.');
    $route = '<front>';
    if (!$res) {
      $msg = $this->t("Opp! Something went wrong");
      $route = '<current>';
    }
    $this->messenger()->addStatus($msg);
    $form_state->setRedirect($route);
  }

  /**
   * {@inheritdoc}
   */
  public function cancelForm(array &$form, FormStateInterface $form_state) {
    user_logout();
    $form_state->setRedirect('<front>');
  }

  protected function prepareParagraphData($paragraph = NULL, $field= NULL){
    $value = NULL;
    if($paragraph instanceof ParagraphInterface){
      return $paragraph->{$field}->value;
    }
    return  $value;
  }

}
