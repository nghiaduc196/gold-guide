(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.setupCalendar = {
    attach: function (context, settings) {
      if (drupalSettings.fullCalendarView !== undefined) {
        for (var i = 0; i < (Object.keys(drupalSettings.fullCalendarView).length); i++) {
          if (drupalSettings.fullCalendarView[i]['calendar_options'] !== undefined) {
            let calendarOptions = JSON.parse(drupalSettings.fullCalendarView[i]['calendar_options']);
            //disable header
            calendarOptions['headerToolbar'] = true;
            calendarOptions['displayEventTime'] = false;
            calendarOptions['disableDragging'] = false;
            calendarOptions['eventStartEditable'] = false;
            calendarOptions['editable'] = false;

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
    }
  };

  Drupal.behaviors.initCalendar = {
    attach: function (context, settings) {
      var startDate = new Date();
      $('.start-date-datetimepicker').datepicker({
        uiLibrary: 'bootstrap4',
        isRTL: false,
        format: 'yyyy/mm/dd',
        autoclose: true,
        language: 'ja',
        orientation: "bottom auto",
        startDate: startDate
      });

      $('.end-date-datetimepicker').datepicker({
        uiLibrary: 'bootstrap4',
        isRTL: false,
        format: 'yyyy/mm/dd',
        autoclose: true,
        language: 'ja',
        orientation: "bottom auto",
        startDate: startDate
      });
    }
  };

  Drupal.behaviors.initTimer = {
    attach: function (context, settings){
      $('.start_timepicker').timepicker({
        timeFormat: 'HH:mm',
        interval: 60,
        minTime: '06:00am',
        maxTime: '23:00pm',
        dynamic: false,
        dropdown: true,
        scrollbar: true
      });
      $('.end_timepicker').timepicker({
        timeFormat: 'HH:mm',
        interval: 60,
        minTime: '06:00am',
        maxTime: '23:00pm',
        dynamic: false,
        dropdown: true,
        scrollbar: true
      });
    }
  }
})(jQuery, Drupal, drupalSettings);
