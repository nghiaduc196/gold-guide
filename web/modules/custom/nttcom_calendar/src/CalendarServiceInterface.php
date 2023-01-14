<?php

namespace Drupal\nttcom_calendar;

/**
 * Provides an interface defining a calendar service.
 */
interface CalendarServiceInterface {


  /**
   * Check the time slot when create a new record.
   */
  public function checkTimeSlot($start_date, $end_date);

  /**
   *  Create new a new record entity calendar
   */
  public function createCalendar($data);

  /**
   * Convert date to format.
   */
  public function convertDate($date, $format = 'Y-m-d\TH:i:s');

  /**
   * Get time slots for guide calendar
   */
  public function getTimeSlots();


}
