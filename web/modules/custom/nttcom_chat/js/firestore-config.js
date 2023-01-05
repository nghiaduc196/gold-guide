// (function ($, Drupal, drupalSettings) {
//   "use strict";
//   Drupal.behaviors.getConfigfirebase = {};
//   Drupal.behaviors.getConfigfirebase = {
//     attach: function() {
//
//     },
//     getConfigFirebase: function() {
//       var getConfig = drupalSettings.configFirebase;
//
//       var config = {
//         apiKey: "AIzaSyBtqVgy6QQ8KmCwxy-kliSF2OO17yN9mZI",
//         authDomain: "nttchatdemo.firebaseapp.com",
//         projectId: "nttchatdemo",
//         databaseURL: "https://nttchatdemo-default-rtdb.firebaseio.com/",
//         storageBucket: "nttchatdemo.appspot.com",
//         messagingSenderId: "417602333678",
//         appId: "1:417602333678:web:0ed968c15e7f50ec1f7cff",
//         measurementId: "G-ER86YQ4MMP"
//       }
//
//       firebase.initializeApp(config);
//
//       // Initialize Cloud Firestore through Firebase
//       var db = firebase.firestore();
//       // Disable deprecated features
//       db.settings({
//       	timestampsInSnapshots: true
//       });
//     }
//   }
//
// })(jQuery, Drupal, drupalSettings)
//
// jQuery(document).ready(function($){
//   // var getConfig = drupalSettings.configFirebase;
//
//   var config = {
//     apiKey: "AIzaSyBtqVgy6QQ8KmCwxy-kliSF2OO17yN9mZI",
//     authDomain: "nttchatdemo.firebaseapp.com",
//     projectId: "nttchatdemo",
//     databaseURL: "https://nttchatdemo-default-rtdb.firebaseio.com/",
//     storageBucket: "nttchatdemo.appspot.com",
//     messagingSenderId: "417602333678",
//     appId: "1:417602333678:web:0ed968c15e7f50ec1f7cff",
//     measurementId: "G-ER86YQ4MMP"
//   }
//
//   firebase.initializeApp(config);
//
//   // Initialize Cloud Firestore through Firebase
//   var db = firebase.firestore();
//   // Disable deprecated features
//   db.settings({
//     timestampsInSnapshots: true
//   });
// });
