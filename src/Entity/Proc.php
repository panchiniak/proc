<?php
/**
 * @file
 * Contains \Drupal\proc\Entity\Proc.
 */

namespace Drupal\proc\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;
// use Drupal\proc\ProcInterface;
use Drupal\user\UserInterface;
use Drupal\Core\Entity\EntityChangedTrait;

/**
 * Defines the Proc entity.
 *
 * @ingroup proc
 *
 * @ContentEntityType(
 *   id = "proc",
 *   label = @Translation("Protected Content"),
 *   base_table = "advertiser",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *   },
 * )
 */
class Proc extends ContentEntityBase implements ContentEntityInterface {
  
}
