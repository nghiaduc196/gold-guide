(function ($, Drupal, drupalSettings) {
  if (drupalSettings.fullCalendarView !== undefined) {
    for (var i = 0; i < (Object.keys(drupalSettings.fullCalendarView).length); i++) {
      if (drupalSettings.fullCalendarView[i]['calendar_options'] !== undefined) {
        let calendarOptions = JSON.parse(drupalSettings.fullCalendarView[i]['calendar_options']);
        //disable header
        calendarOptions['headerToolbar'] = false;
        calendarOptions['displayEventTime'] = false;

        calendarOptions['weekends'] = true;
        calendarOptions['allDaySlot'] = false;
        calendarOptions['contentHeight'] = 'auto';
        // limit calendar time
        calendarOptions['minTime'] = '06:00:00';
        calendarOptions['maxTime'] = '23:00:00';
        // change language & date format to en-gb
        // calendarOptions['locale'] = 'en-gb';
        drupalSettings.fullCalendarView[i]['calendar_options'] = JSON.stringify(calendarOptions);
      }
    }
  }
})(jQuery, Drupal, drupalSettings);
