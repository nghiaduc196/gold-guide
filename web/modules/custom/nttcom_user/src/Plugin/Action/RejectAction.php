<?php
namespace Drupal\nttcom_user\Plugin\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * create custom action
 *
 * @Action(
 *   id = "user_reject_action",
 *   label = @Translation("Reject Guide"),
 *   confirm = TRUE,
 *   type = "user"
 * )
 */
class RejectAction extends ActionBase {

  /**
   * {@inheritDoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    $result = AccessResult::allowed();
    return $return_as_object ? $result : $result->isAllowed();
  }

  public function execute($entity = NULL) {
      if($entity->hasField('field_approval_status')){
        $entity->set('field_approval_status','reject');
        $entity->save();
      }
  }

}
