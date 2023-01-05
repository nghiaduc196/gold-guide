(function ($, Drupal, drupalSettings) {

  "use strict";

  var chat_data = {}, user_uuid, chatHTML = '', chat_uuid = "", userList = [], user_2_name = "", classMess = "", message = "", classShowDeleteBtn = "";
  var  newMessage = '';

  var getConfig = drupalSettings.configFirebase;
  var config = {
    apiKey: getConfig.api_key,
    authDomain: getConfig.auth_domain,
    databaseURL: getConfig.database_url,
    projectId: getConfig.project_id,
    storageBucket: getConfig.storage_bucket,
    messagingSenderId: getConfig.messaging_sender_id,
    appId: getConfig.app_id,
    measurementId: getConfig.measurement_id
  }

  firebase.initializeApp(config);

  // Initialize Cloud Firestore through Firebase
  var db = firebase.firestore();
  console.log('Main: ' + db)
  // Disable deprecated features
  db.settings({
    timestampsInSnapshots: true,
  });

  function syncUser() {
    $.ajax({
      url: Drupal.url('sync-user-firebase'),
      method: 'GET',
      success: function (response) {
        if (response.status === 200) {
          let token = response.data.token;

          firebase.auth().signInWithCustomToken(token).catch(function (error) {
            let errorMessage = error.message;
          }).then(function (data) {
            // console.log(data.user.uid)
          })
        }
      }
    })
  }

  syncUser()

  firebase.auth().onAuthStateChanged(function (user) {
    if (user) {
      // console.log(user);
      user_uuid = user.uid;

      getUsers();
      // console.log(user_uuid);

    } else {
      console.log("Not sign in");
    }
  });

  function getUsers() {
    $.ajax({
      url: Drupal.url('get-user'),
      method: 'GET',
      success: function (response) {
        if (response.status === 200) {
          let users = response.data;
          let usersHtml = '';
          $.each(users, function (index, value) {
            if (user_uuid !== value.uuid) {

              usersHtml += '<div class="user" uuid="' + value.uuid + '">' +
                '<div class="user-image fa fa-user-circle"></div>' +
                '<div class="user-details">' +
                '<span><strong class="list-name-user">' + value.name + '<span class="count"></span></strong></span>' +
                '</div>' +
                '</div>';

              userList.push({user_uuid: value.uuid, username: value.name})
            } else {
              console.log(response.message);
            }
          });

          $('.users').html(usersHtml);
        }
      }
    });
  }
  Drupal.behaviors.message = {}
  Drupal.behaviors.message = {
    deleteM: function (elem) {
      var id = $(elem).attr("data-id");
      let field_id = "message";
      let updates = {};
      updates[field_id] = "";
      // define document location (Collection Name > Document Name > Collection Name >)
      db.collection("chat").doc(id)
        .update(updates)
        .then(function () {
          var getElem = $(elem).parent().children().first();
          console.log(getElem)
          getElem.removeClass( "message" ).addClass( "messageDeleted");
          getElem.text('This message is deleted.');
        }) // Document deleted
        .catch((error) => console.error("Error deleting document", error));
    }
  }

  $(document.body).on('click', '.user', function () {
    let user_1 = user_uuid;
    let user_2 = $(this).attr('uuid')
    $('.message-container').html('Connecting...!');
    user_2_name = $(this).text();
    $('.name').html(user_2_name)

    $.ajax({
      url: Drupal.url('connect-user'),
      method: 'POST',
      data: {user_1: user_1, user_2: user_2},
      success: function (response) {

        if (response.status === 200) {
          chat_data = {
            chat_uuid: response.data.chat_uuid,
            user_1_uuid: response.data.user_1_uuid,
            user_2_uuid: response.data.user_2_uuid,
            user_1_name: '',
            user_2_name: name
          };
          $('.message-container').html('Say Hi to ' + name);
          db.collection('chat').where('chat_uuid', '==', chat_data.chat_uuid)
            .orderBy("time")
            .get().then(function (querySnapshot) {
            chatHTML = '';
            querySnapshot.forEach(function (doc) {
              var time = doc.data().time;
              const fireBaseTime = new Date(
                time.seconds * 1000 + time.nanoseconds / 1000000,
              );
              const dateTime = fireBaseTime.toDateString() + ' ' + fireBaseTime.toLocaleTimeString();
              if(doc.data().message !== "") {
                classMess = "message";
                message = doc.data().message;
                // classShowDeleteBtn = "display: block";
              } else {
                classMess = "messageDeleted";
                message = "This message is deleted.";
                // classShowDeleteBtn = "display: none";
              }
              if (doc.data().user_1_uuid === user_uuid) {
                chatHTML += '<div class="message-block">' +
                  '<div class="user-icon fa fa-user-circle"></div>' +
                  '<div>' +
                  '<div class="'+classMess+'">' + message + '</div>' +
                  '<span class="btn-delete-mess" onclick="return confirm(\'Are you sure delete message?\') ? Drupal.behaviors.message.deleteM(this):\'\';" data-id="'+doc.id+'">delete</span>' +
                  '<div class="mess-date">'+dateTime+'</div>' +
                  '</div>' +
                  '</div>';

              } else {
                chatHTML += '<div class="message-block received-message">' +
                  '<div class="user-icon fa fa-user-circle"></div>' +
                  '<div class="user-message-block">' +
                  '<div class="name-user">'+ user_2_name +'</div>' +
                  '<div class="message message-custom">' + message + '</div>' +
                  '</div>' +
                  '</div>';
              }
            });
            $(".message-container").html(chatHTML);
            if (chat_uuid === "") {
              chat_uuid = chat_data.chat_uuid;
              realTime();
            }
          });
        }
      },
    })
  })

  function realTime() {
    db.collection('chat').where('chat_uuid', '==', chat_data.chat_uuid)
      .orderBy('time')
      .onSnapshot(function (snapshot) {
        newMessage = '';
        snapshot.docChanges().forEach(function (change) {
          var time = change.doc.data().time;
          const fireBaseTime = new Date(
            time.seconds * 1000 + time.nanoseconds / 1000000,
          );
          const dateTime = fireBaseTime.toDateString() + ' ' + fireBaseTime.toLocaleTimeString();
          let id = change.doc.id;

          if(change.doc.data().message !== "") {
            classMess = "message";
            message = change.doc.data().message;
            classShowDeleteBtn = "display: block";
          } else {
            classMess = "messageDeleted";
            message = "This message is deleted.";
            classShowDeleteBtn = "display: none";
          }

          if (change.type === "added") {
            // console.log(change.doc.data());
            if (change.doc.data().user_1_uuid === user_uuid) {
              newMessage += '<div class="message-block">' +
                '<div class="user-icon fa fa-user-circle"></div>' +
                '<div>' +
                '<div class="'+classMess+'">' + message + '</div>' + '<span class="btn-delete-mess" onclick="return confirm(\'Are you sure delete message?\') ? Drupal.behaviors.message.deleteM(this):\'\';" data-id="'+id+'">delete</span>' +
                // '<span onclick="edit_message(this)" data-id="'+id+'" data-message="'+message+'"> edit</span>';
                '<div class="mess-date">'+dateTime+'</div>' +
                '</div>'+
                '</div>';

            } else {
              newMessage += '<div class="message-block received-message">' +
                '<div class="user-icon fa fa-user-circle"></div>' +
                '<div class="user-message-block">' +
                '<div class="name-user">'+ user_2_name +'</div>' +
                '<div class="message message-custom">' + message + '</div>' +
                '</div>' +
                '</div>';
            }
          }
          if (change.type === "modified") {
            console.log("Modified message: ", change.doc.data());
          }
          if (change.type === "removed") {
            console.log("Removed message: ", change.doc.data());
          }
        });

        if (chatHTML !== newMessage) {
          $('.message-container').append(newMessage);
        }

        $(".chats").scrollTop($(".chats")[0].scrollHeight);

      });
  }


  $(".send-btn").on('click', function () {
  var message = $(".message-input").val();
  if (message !== "") {
    db.collection("chat").add({
      message: message,
      user_1_uuid: user_uuid,
      user_2_uuid: chat_data.user_2_uuid,
      chat_uuid: chat_data.chat_uuid,
      user_1_isView: 0,
      user_2_isView: 0,
      time: new Date(),
    })
      .then(function (docRef) {
        $(".message-input").val("");
        console.log("Document written with ID: ", docRef.id);
      })
      .catch(function (error) {
        console.error("Error adding document: ", error);
      });
  }
});

})(jQuery, Drupal, drupalSettings)
