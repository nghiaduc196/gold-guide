<?php

namespace Drupal\nttcom_user;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use ZipStream\Exception;

/**
 * Service description.
 */
class UserService implements UserServiceInterface {

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs an UserService object.
   *
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityFieldManagerInterface $entity_field_manager,
                              EntityTypeManagerInterface $entity_type_manager,
                              AccountProxyInterface $account_proxy) {
    $this->entityFieldManager = $entity_field_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $account_proxy;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldSetting($type = 'node', $bundle = '', $field = '') {
    $manager = $this->entityFieldManager->getFieldDefinitions($type, $bundle);
    $facilities = $manager[$field]->getFieldStorageDefinition()
      ->toArray();
    $allowed_values = $facilities['settings']['allowed_values'];
    return $allowed_values;
  }

  /**
   * {@inheritdoc}
   */
  public function getOptionTerms($vid = 'areas') {
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')
      ->loadByProperties(
        [
          'vid' => $vid,
        ]
      );

    $data = [];
    foreach ($terms as $term) {
      $data[$term->id()] = $term->getName();
    }
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function updateGuideEntry(array $data) {
    $paragraph_entity = $this->entityTypeManager->getStorage('paragraph');
    $user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
    try {
      if($user->get('field_addination_info')->isEmpty()){
        $paragraph = $paragraph_entity->create(['type' => 'addination_info']);
      }else{
        $paragraph = $paragraph_entity->load($user->field_addination_info->target_id);
      }
      $paragraph->set('field_national_license', $data['field_national_license']);
      $paragraph->set('field_region_license', $data['field_region_license']);
      $paragraph->set('field_operating_day', $data['field_operating_day']);
      $paragraph->set('field_working_day', $data['field_working_day']);
      $paragraph->set('field_other_qualification', $data['field_other_qualification']);
      $paragraph->save();

      $user->field_addination_info[] = [
        'target_id' => $paragraph->id(),
        'target_revision_id' => $paragraph->getRevisionId(),
      ];
      $user->set('field_first_name', $data['field_first_name']);
      $user->set('field_last_name', $data['field_last_name']);
      $user->set('field_phone_number', $data['field_phone_number']);
      $user->set('field_approval_status', 'waiting_approval');
      $user->save();
      return TRUE;
    } catch (Exception $e) {
      return FALSE;
    }
  }

}
