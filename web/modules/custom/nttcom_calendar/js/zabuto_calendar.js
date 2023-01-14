(function ($, Drupal, drupalSettings) {

  "use strict";

  Drupal.behaviors.initGuideCalendar = {
    attach: function (context, settings) {
      var $context = $(context);
      var calendarEvents = JSON.parse(drupalSettings.zabutoCalendar);
      $context.find("#js-drupal-zabutocalendar").zabuto_calendar({
        navigation_prev: false,
        navigation_next: false,
        events: calendarEvents
      });
      var $guide_calendar = $context.find("#guide_zabutocalendar").zabuto_calendar({
        navigation_prev: false,
        navigation_next: false,
      });

      $guide_calendar.on('zabuto:calendar:day', function (e) {
        openDialog();
      });

      function openDialog(){
        var ajaxSettings = {
          url: Drupal.url('my-calendar/add'),
          dialogType: 'modal',
          dialog: { width: 400 },
        };
        var myAjaxObject = Drupal.ajax(ajaxSettings);
        myAjaxObject.execute();
      }
    }
  };

})(jQuery, Drupal, drupalSettings)
