<?php

namespace Drupal\proc;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a Proc entity.
 *
 * We have this interface so we can join the other interfaces it extends.
 *
 * @ingroup proc
 */
interface ProcInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {
}
