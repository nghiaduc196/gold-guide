(function ($, Drupal) {
    Drupal.behaviors.power = {
        attach: function (context, settings) {
            // Your custom JavaScript goes inside this function...
        }
    };
})
(jQuery, Drupal);


$(document).ready(function(){
  $("body").on('click', "a[data-toggle='dropdown1']", function (event) {
    $(this).next().toggleClass('show');
    event.preventDefault();
  });

  $('.owl-carousel').owlCarousel({
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


});
