<?php

namespace Drupal\nttcom_calendar;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Service description.
 */
class CalendarService implements CalendarServiceInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The calendar entity storage
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $calendarStorage;

  /**
   * The logger factory service
   *
   * @var LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * Constructs a CalendarService object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   */
  public function __construct(EntityTypeManagerInterface    $entity_type_manager,
                              AccountInterface              $account,
                              LoggerChannelFactoryInterface $drupal_logger) {
    $this->entityTypeManager = $entity_type_manager;
    $this->account = $account;
    $this->logger = $drupal_logger;
    $this->calendarStorage = $entity_type_manager->getStorage('calendar');
  }

  /**
   * {@inheritdoc}
   */
  public function checkTimeSlot($start_date, $end_date) {
    $start = $this->convertDate($start_date, 'Y-m-d\T00:00:00');
    $end = $this->convertDate($end_date, 'Y-m-d\T23:59:59');
    $start_time = $this->convertDate($start_date, 'H:i:s');
    $end_time = $this->convertDate($end_date, 'H:i:s');
    $query = $this->calendarStorage->getQuery()
      ->condition('uid', $this->account->id());
    $group = $query->orConditionGroup()
      ->condition('field_start_date', [$start, $end], 'BETWEEN')
      ->condition('field_end_date', [$start, $end], 'BETWEEN');
    $query->condition($group);
    $ids = $query->execute();
    $invalid = false;
    if (!empty($ids)) {
      $calendars = $this->calendarStorage->loadMultiple($ids);
      foreach ($calendars as $calendar) {
        if (!$calendar instanceof CalendarInterface) {
          continue;
        }
        if (!$calendar->get('field_start_date')->isEmpty() && !$calendar->get('field_start_date')->isEmpty()) {
          $calendar_start_time = $calendar->field_start_date->date->format('H:i:s');
          $calendar_end_time = $calendar->field_end_date->date->format('H:i:s');
          if (strtotime($start_time) < strtotime($calendar_end_time) || strtotime($start_time) < strtotime($calendar_start_time)) {
            $invalid = true;
          }
        }
      }
    }
    return $invalid;
  }

  /**
   * {@inheritdoc}
   */
  public function createCalendar($data) {
    try {
      $begin = new \DateTime($data['field_start_date']);
      $end = new \DateTime($data['field_end_date']);
      $interval = \DateInterval::createFromDateString('1 day');
      $period = new \DatePeriod($begin, $interval, $end);
      foreach ($period as $dt) {
        $data['field_start_date'] = $dt->format('Y-m-d\TH:i:s');
        $data['field_end_date'] = $dt->format('Y-m-d') . $end->format('\TH:i:s');
        $this->calendarStorage->create($data)->save();
      }
      return true;
    } catch (\Exception $e) {
      $this->logger->get('Calendar')->error($e->getMessage());
      return false;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function convertDate($date, $format = 'Y-m-d\TH:i:s') {
    $timestamp = strtotime($date);
    $formatted = \Drupal::service('date.formatter')->format(
      $timestamp, 'custom', $format
    );
    return $formatted;
  }

  /**
   * {@inheritdoc}
   */
  public function getTimeSlots() {
    $module_path = \Drupal::service('extension.list.module')->getPath('nttcom_calendar');
    $query = $this->calendarStorage->getQuery()
      ->condition('uid', $this->account->id());
    $ids = $query->execute();

    if (empty($ids)) {
      return null;
    }
    $data = [];
    $calendars = $this->calendarStorage->loadMultiple($ids);
    foreach ($calendars as $calendar) {
      if ($calendar instanceof CalendarInterface) {
        if ($calendar->get('field_start_date')->isEmpty()) {
          continue;
        }
        $data[$calendar->field_start_date->date->format('Y-m-d')][] = [
          'status' => $calendar->get('status')->value,
          'date' => $calendar->field_start_date->date->format('Y-m-d')
        ];
      }
    }

    $events = [];
    foreach ($data as $date => $items) {
      $icon_type = $this->renderCalendarIcon($items);
      $icon = "<div>[day]<br/><img src='/{$module_path}/images/icons/icon-{$icon_type}.svg'/></div>";
      $events[] = [
        'date' => $date,
        'classname' => '',
        'markup' => $icon
      ];
    }
    return Json::encode($events);
  }

  /**
   * {@inheritdoc}
   */
  protected function renderCalendarIcon($items) {
    $group = [];
    foreach ($items as $item) {
      $group[] = $item['status'];
    }
    if (in_array(1, $group) && in_array(0, $group)) {
      return 'triangle';
    }
    if (in_array(1, $group)) {
      return 'circle';
    }
    if (in_array(0, $group)) {
      return 'close';
    }
  }

}
