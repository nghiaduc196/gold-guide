<?php

namespace Drupal\nttcom_chat\Controller;

use Drupal\user\Entity\User;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Factory;
use Symfony\Component\HttpFoundation\JsonResponse;

class ChatAjaxController extends \Drupal\Core\Controller\ControllerBase
{
  protected $auth;

  protected $firebase;

  public function __construct()
  {
    $factory = (new Factory())->withServiceAccount($this->getConfigServiceAccount());
    $this->auth = $factory->createAuth();
    $this->firebase = $factory->createDatabase();
  }

  public function content() {
    $configFirebase = $this->config('nttcom_chat.configuration')->get();
    $build['#theme'] = 'nttcom_chat_blank';
    $build['#attached']['library'][] = 'nttcom_chat/nttcom_chat';
    $build['#attached']['drupalSettings']['configFirebase'] = $configFirebase;
    return $build;
  }

  /**
   * Sync user from database to firebase firebase
   *
   * @return JsonResponse
   * @throws AuthException
   * @throws FirebaseException
   */
  public function syncUserFirebase()
  {
    $user = \Drupal::currentUser()->getAccount();
    $userId = User::load($user->id());
    $uuid = $userId->uuid();
    $infoUser = [
      'email' => $user->getEmail(),
      'username' => $user->getAccountName()
    ];
    $customToken = $this->auth->createCustomToken($uuid, $infoUser);
    $token = $customToken->toString();
    $arrData = [
      'user_uuid' => $uuid,
      'token' => $token
    ];
    return new JsonResponse([
      'status' => 200,
      'data' => $arrData
    ]);
  }

  public function getConfigServiceAccount()
  {
    $path = \Drupal::service('extension.list.module')->getPath('nttcom_chat');
    return $path . '/data/nttchat-firebase-adminsdk-xh73g-4c7ec06ea9.json';
  }

  public function getUsers()
  {
    $currentUser = \Drupal::currentUser();
    $idCurrentUser = $currentUser->getAccount()->id();
    $query = \Drupal::entityQuery('user')->condition('uid', $idCurrentUser, '!=')->execute();
    $data = User::loadMultiple($query);
    $users = [];
    foreach ($data as $value) {
      $users[] = [
        'uid' => $value->get('uid')->value,
        'uuid' => $value->get('uuid')->value,
        'name' => $value->get('name')->value
      ];
    }
    return new JsonResponse([
      'status' => 200,
      'data' => $users,
    ]);
  }

  public function connectUser()
  {
    $requestAll = \Drupal::request()->request;
    $user_1_uuid = $requestAll->get('user_1');
    $user_2_uuid = $requestAll->get('user_2');
    if (empty($user_1_uuid) || empty($user_2_uuid)) {
      return new JsonResponse([
        'status' => 303,
        'message' => 'Invalid detail'
      ]);
    }

    $database = \Drupal::database();
    $query = $database->select('nttcom_chat', 'ntt_cr');
    $query->fields('ntt_cr', ['chat_uuid']);

    $queryGroup1 = $query->andConditionGroup()
      ->condition('user_1_uuid', $user_1_uuid)
      ->condition('user_2_uuid', $user_2_uuid);

    $queryGroup2 = $query->andConditionGroup()
      ->condition('user_1_uuid', $user_2_uuid)
      ->condition('user_2_uuid', $user_1_uuid);

    $queryGroup3 = $query->orConditionGroup()
      ->condition($queryGroup1)
      ->condition($queryGroup2);

    $query->condition($queryGroup3);
    $result = $query->execute();
    $uuidChatRecord = $result->fetchAssoc();
    $arr = [
      'chat_uuid' => $uuidChatRecord['chat_uuid'],
      'user_1_uuid' => $user_1_uuid,
      'user_2_uuid' => $user_2_uuid,
    ];
    if ($uuidChatRecord['chat_uuid']) {
      return new JsonResponse([
        'status' => 200,
        'data' => $arr
      ]);
    } else {
      $uuidService = \Drupal::service('uuid');
      $uuid = $uuidService->generate();
      $queryInsert = $database->insert('nttcom_chat')
        ->fields([
          'chat_uuid' => $uuid,
          'user_1_uuid' => $user_1_uuid,
          'user_2_uuid' => $user_2_uuid
        ])->execute();

      $arrNew = [
        'chat_uuid' => $uuid,
        'user_1_uuid' => $user_1_uuid,
        'user_2_uuid' => $user_2_uuid
      ];

      return new JsonResponse([
        'status' => 200,
        'data' => $arrNew
      ]);
    }
  }
}
