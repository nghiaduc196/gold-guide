(function ($, Drupal) {
    Drupal.behaviors.power = {
        attach: function (context, settings) {
            // Your custom JavaScript goes inside this function...
        }
    };

    Drupal.behaviors.initOwlCarousel = {
      attach: function (context, settings) {
        $('.owl-carousel-responsive').owlCarousel({
          loop:true,
          margin: 10,
          responsiveClass:true,
          nav: false,
          responsive:{
            0:{
              items:2,
              margin: 24
            },
            600:{
              items:3,
              margin: 30
            },
            1000:{
              items:4,
              margin: 40
            }
          }
        });

        $('.owl-carousel-custom').owlCarousel({
          loop:true,
          margin: 10,
          responsiveClass:true,
          nav: false,
          dots: true,
          items: 1
        });

        $('.owl-carousel-custom-one').owlCarousel({
          loop:true,
          margin: 10,
          responsiveClass:true,
          nav: true,
          dots: false,
          responsive:{
            0:{
              items:1,
              margin: 24
            },
            600:{
              items:3,
              margin: 30
            },
            1000:{
              items:4,
              margin: 40
            }
          }
        });

        $('.owl-carousel-custom-two').owlCarousel({
          loop:true,
          margin: 10,
          responsiveClass:true,
          nav: true,
          responsive:{
            0:{
              items:2,
              margin: 24
            },
            600:{
              items:3,
              margin: 30
            },
            1000:{
              items:4,
              margin: 40
            }
          }
        });

        $('.owl-carousel-custom-three').owlCarousel({
          loop:true,
          margin: 10,
          responsiveClass:true,
          nav: true,
          dots: false,
          responsive:{
            0:{
              items:1,
              margin: 24
            },
            600:{
              items:3,
              margin: 30
            }
          }
        });


      }
    };
})
(jQuery, Drupal);

