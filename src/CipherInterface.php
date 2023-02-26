<?php

namespace Drupal\proc;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a cipher entity type.
 */
interface CipherInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
