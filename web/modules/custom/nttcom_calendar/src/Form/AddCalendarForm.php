<?php

namespace Drupal\nttcom_calendar\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\nttcom_calendar\CalendarServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Provides a Nttcom calendar form.
 */
class AddCalendarForm extends FormBase {


  /**
   * The calendar service
   *
   * @var CalendarServiceInterface
   */
  protected $calendar;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->calendar = $container->get('nttcom_calendar');
    return $instance;
  }

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
    $form['#attributes'] += ['novalidate' => TRUE];
    $form['start_date'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Start date'),
      '#required' => TRUE,
      '#attributes' => [
        'class' => [
          'start-date',
          'start-date-datetimepicker'
        ],
        'data-date-format' => 'yyyy/mm/dd'
      ],
    ];
    $form['end_date'] = [
      '#type' => 'textfield',
      '#title' => $this->t('End date'),
      '#required' => TRUE,
      '#attributes' => [
        'class' => [
          'end-date',
          'end-date-datetimepicker'
        ],
        'data-date-format' => 'yyyy/mm/dd'
      ],
    ];
    $form['start_time'] = [
      '#type' => 'textfield',
      '#title' => $this->t('開始時間'),
      '#required' => TRUE,
      '#attributes' => [
        'class' => [
          'start-time',
          'start_timepicker'
        ],
      ],
    ];
    $form['end_time'] = [
      '#type' => 'textfield',
      '#title' => $this->t('終了時間'),
      '#required' => TRUE,
      '#attributes' => [
        'class' => [
          'end-time',
          'end_timepicker'
        ],
      ],
    ];
    $form['status'] = [
      '#type' => 'select',
      '#title' => $this->t('ステータス'),
      '#required' => TRUE,
      '#options' => [
        '予約受付' => $this->t('予約受付'),
        '受付NG' => $this->t('受付NG'),
      ],
    ];
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send'),
      '#ajax' => [
        'callback' => '::ajaxCalendarSubmit'
      ]
    ];
    $form['#theme'] = 'nttcom_block_calendar_form';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    if (!$values['status']) {
      $form_state->setErrorByName('status', $this->t('Please choice a value'));
    }
    if (!$values['start_date']) {
      $form_state->setErrorByName('start_date', $this->t('This field is require'));
    }
    if (!$values['end_date']) {
      $form_state->setErrorByName('end_date', $this->t('This field is require'));
    }
    if (!$values['start_time']) {
      $form_state->setErrorByName('start_time', $this->t('This field is require'));
    }
    if (!$values['end_time']) {
      $form_state->setErrorByName('end_time', $this->t('This field is require'));
    }
    if($values['start_date'] && $values['end_date'] && $values['start_time'] && $values['end_time']){
      $date = $this->prepareDate($values);
      $start_date = $this->calendar->convertDate($date['start_date']);
      $end_date = $this->calendar->convertDate($date['end_date']);
      $start_time = $this->calendar->convertDate($start_date, 'H:i:s');
      $end_time = $this->calendar->convertDate($end_date, 'H:i:s');
      if(strtotime($end_date) < strtotime($start_date)){
        $form_state->setErrorByName('start_date', $this->t('Start date is not greater than End date'));
      }
      if (strtotime($start_time) >= strtotime($end_time)) {
        $form_state->setErrorByName('end_time', $this->t('Start time is not greater than End time'));
      }
      $invalid = $this->calendar->checkTimeSlot($start_date, $end_date);
      if ($invalid) {
        $form_state->setErrorByName('start_date', $this->t('Time slot is not available'));
      }
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->messenger()->addStatus($this->t('The calendar has been create successfully!.'));
  }

  /**
   * {@inheritdoc}
   */
  public function ajaxCalendarSubmit(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    if ($form_state->getErrors()) {
      $this->messenger()->deleteAll();
      //showing form errors.
      $response->addCommand(new HtmlCommand('#drupal-modal', $form));
    } else {
      $values = $form_state->getValues();
      $date = $this->prepareDate($values);
      $start_date = $this->calendar->convertDate($date['start_date']);
      $end_date = $this->calendar->convertDate($date['end_date']);
      $data = [
        'label' => $form_state->getValue('status'),
        'field_start_date' => $start_date,
        'field_end_date' => $end_date,
        'status' => ($form_state->getValue('status') == '予約受付') ? 1 : 0
      ];
      //creating a new calendar.
      $this->calendar->createCalendar($data);

      //redirect current page.
      $url = Url::fromRoute('view.my_calendar.page_my_calendar');
      $command = new RedirectCommand($url->toString());
      $response->addCommand($command);
    }
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareDate($values) {
    return [
      'start_date' => $values['start_date'] . ' ' . $values['start_time'] . ':00',
      'end_date' => $values['end_date'] . ' ' . $values['end_time'] . ':00'
    ];
  }


}
