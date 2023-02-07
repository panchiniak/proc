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
use Drupal\proc\ProcInterface;


/**
 * Defines the Proc entity.
 *
 * @ingroup proc
 * 
 * @ContentEntityType(
 *   id = "proc",
 *   label = @Translation("Protected Content"),
 *   base_table = "proc",
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\proc\Entity\Controller\ContactListBuilder",
 *     "form" = {
 *       "default" = "Drupal\proc\Form\ProcForm",
 *       "delete" = "Drupal\proc\Form\ProcDeleteForm",
 *     },
 *     "access" = "Drupal\proc\ProcAccessControlHandler",
 *   },
 *   list_cache_contexts = { "user" },
 *   base_table = "contact",
 *   admin_permission = "administer contact entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/content_entity_example_contact/{content_entity_example_contact}",
 *     "edit-form" = "/content_entity_example_contact/{content_entity_example_contact}/edit",
 *     "delete-form" = "/contact/{content_entity_example_contact}/delete",
 *     "collection" = "/content_entity_example_contact/list"
 *   },
 *   field_ui_base_route = "content_entity_example.contact_settings",
 * )
 * 
 * 
 * 
 * 
 */
class Proc extends ContentEntityBase implements ProcInterface {
  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   *
   * When a new entity instance is added, set the user_id entity reference to
   * the current user as the creator of the instance.
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }
  
}



// namespace Drupal\proc_entity;

// use Drupal\Core\Entity\ContentEntityBase;
// use Drupal\Core\Entity\EntityTypeInterface;
// use Drupal\Core\Field\BaseFieldDefinition;

// /**
//  * Defines the Proc entity.
//  *
//  * @ContentEntityType(
//  *   id = "proc",
//  *   label = @Translation("Proc"),
//  *   handlers = {
//  *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
//  *     "list_builder" = "Drupal\proc_entity\ProcListBuilder",
//  *     "views_data" = "Drupal\views\EntityViewsData",
//  *     "form" = {
//  *       "default" = "Drupal\proc_entity\Form\ProcForm",
//  *       "add" = "Drupal\proc_entity\Form\ProcForm",
//  *       "edit" = "Drupal\proc_entity\Form\ProcForm",
//  *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
//  *     },
//  *     "access" = "Drupal\Core\Entity\EntityAccessControlHandler",
//  *     "route_provider" = {
//  *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
//  *     },
//  *   },
//  *   base_table = "proc",
//  *   admin_permission = "administer proc entity",
//  *   entity_keys = {
//  *     "id" = "id",
//  *     "label" = "label",
//  *     "uuid" = "uuid",
//  *   },
//  *   links = {
//  *     "canonical" = "/proc/{proc}",
//  *     "add-form" = "/proc/add",
//  *     "edit-form" = "/proc/{proc}/edit",
//  *     "delete-form" = "/proc/{proc}/delete",
//  *     "collection" = "/proc",
//  *   },
//  * )
//  */
// class Proc extends ContentEntityBase {

//   /**
//    * {@inheritdoc}
//    */
//   public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
//     $fields = parent::baseFieldDefinitions($entity_type);
//     $fields['ciphertext'] = BaseFieldDefinition::create('string')
//       ->setLabel(t('Ciphertext'))
//       ->setDescription(t('The ciphertext of the Proc entity.'))
//       ->setRequired(TRUE)
//       ->setDisplayOptions('view', [
//         'label' => 'above',
//         'type' => 'type',
//       ];
//   }
// } 


// namespace Drupal\my_module;

// use Drupal\Core\Entity\ContentEntityBase;
// use Drupal\Core\Entity\EntityTypeInterface;
// use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the MyCustomEntity entity.
 *
 * @ContentEntityType(
 *   id = "my_custom_entity",
 *   label = @Translation("My custom entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\my_module\MyCustomEntityListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\my_module\Form\MyCustomEntityForm",
 *       "add" = "Drupal\my_module\Form\MyCustomEntityForm",
 *       "edit" = "Drupal\my_module\Form\MyCustomEntityForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "access" = "Drupal\Core\Entity\EntityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "my_custom_entity",
 *   admin_permission = "administer my custom entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "canonical" = "/my_custom_entity/{my_custom_entity}",
 *     "add-form" = "/my_custom_entity/add",
 *     "edit-form" = "/my_custom_entity/{my_custom_entity}/edit",
 *     "delete-form" = "/my_custom_entity/{my_custom_entity}/delete",
 *     "collection" = "/my_custom_entity",
 *   },
 * )
 */
// class MyCustomEntity extends ContentEntityBase {

//   /**
//    * {@inheritdoc}
//    */
//   public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
//     $fields = parent::baseFieldDefinitions($entity_type);
//     $fields['ciphertext'] = BaseFieldDefinition::create('string')
//       ->setLabel(t('Ciphertext'));
//   }
// }
     
