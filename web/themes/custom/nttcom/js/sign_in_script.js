function actionpwd(){
    var pwd = document.getElementById('edit-pass');
    if(pwd.getAttribute('type') == 'password'){
        pwd.setAttribute('type','text');
    } else {
        pwd.setAttribute('type','password');
    }
}

(function ($, Drupal) {
  Drupal.behaviors.guideDetail = {
    attach: function (context, settings) {
      if(screen.width <= 768){
        $('.btn-sign-in').css({
          'width' : $('.user-input').outerWidth() + 'px'
        });
      }
      $( window ).resize(function() {
        if(screen.width <= 768){
          $('.btn-sign-in').css({
            'width' : $('.user-input').outerWidth() + 'px'
          });
        }
      });
    }
  };
})
(jQuery, Drupal);
