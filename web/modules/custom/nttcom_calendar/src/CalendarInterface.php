<?php

namespace Drupal\nttcom_calendar;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a calendar entity type.
 */
interface CalendarInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
