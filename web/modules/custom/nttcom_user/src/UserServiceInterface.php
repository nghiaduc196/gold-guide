<?php

namespace Drupal\nttcom_user;

/**
 * UserServiceInterface.
 */
interface UserServiceInterface {

  /**
   * Get field settings.
   */
  public function getFieldSetting($type = 'node', $bundle = '', $field = '');

  /**
   * Get term convert to array option. key,value
   */
  public function getOptionTerms($vid = 'areas');

  /**
   * Update guide entry.
   */
  public function updateGuideEntry(array $data);

}
