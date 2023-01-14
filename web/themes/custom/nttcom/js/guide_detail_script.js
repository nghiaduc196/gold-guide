
(function ($, Drupal) {
  Drupal.behaviors.guideDetail = {
    attach: function (context, settings) {
      if(screen.width > 920){
        $('.guide-img-mobile').css({
          'height' : screen.width - 180 + 'px'
        });
      } else if(screen.width > 640) {
        $('.guide-img-mobile').css({
          'height' : screen.width - 150 + 'px'
        });
      } else {
        $('.guide-img-mobile').css({
          'height' : screen.width - 100 + 'px'
        });
      }
      $( window ).resize(function() {
        if(screen.width > 920){
          $('.guide-img-mobile').css({
            'height' : screen.width - 180 + 'px'
          });
        } else if(screen.width > 640) {
          $('.guide-img-mobile').css({
            'height' : screen.width - 150 + 'px'
          });
        } else {
          $('.guide-img-mobile').css({
            'height' : screen.width - 100 + 'px'
          });
        }
      });
    }
  };
})
(jQuery, Drupal);
