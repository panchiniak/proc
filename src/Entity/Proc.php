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
 *     "views_data" = "Drupal\proc\ProcViewsData"
 *   },
 *   list_cache_contexts = { "user" },
 *   base_table = "proc",
 *   admin_permission = "administer proc entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/proc/{proc}",
 *     "edit-form" = "/proc/{proc}/edit",
 *     "delete-form" = "/proc/{proc}/delete",
 *     "collection" = "/proc/list"
 *   },
 *   field_ui_base_route = "proc.contact_settings",
 * )
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

  /**
   * Get public key cipher
   */
  public function getPubkey() {
    return $this->get('armored')->target_id;
  }


  
  /**
   * {@inheritdoc}
   *
   * Define the field properties here.
   *
   * Field name, type and size determine the table structure.
   *
   * In addition, we can define how the field and its content can be manipulated
   * in the GUI. The behaviour of the widgets used can be determined here.
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    // Standard field, used as unique if primary index.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Proc entity.'))
      ->setReadOnly(TRUE);

    // Standard field, unique outside of the scope of the current project.
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Proc entity.'))
      ->setReadOnly(TRUE);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Label'))
      ->setDescription(t('The label of the Proc entity.'))
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      // Set no default value.
      ->setDefaultValue(NULL)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -6,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Proc entity is published.'))
      ->setDefaultValue(TRUE)
      ->setSettings(['on_label' => 'Published', 'off_label' => 'Unpublished'])
      ->setDisplayOptions('view', [
        'label' => 'visible',
        'type' => 'boolean',
        'weight' => 2,
      ])
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

      $fields['type'] = BaseFieldDefinition::create('string')
        ->setLabel(t('Type'))
        ->setDescription(t('The type of the Proc entity (keyring or ciphertext).'))
        ->setSettings([
          'max_length' => 255,
          'text_processing' => 0,
        ])
        // Set no default value.
        ->setDefaultValue('keyring')
        ->setDisplayOptions('view', [
          'label' => 'above',
          'type' => 'string',
          'weight' => -6,
        ])
        ->setDisplayOptions('form', [
          'type' => 'string_textfield',
          'weight' => -6,
        ])
        ->setDisplayConfigurable('form', TRUE)
        ->setDisplayConfigurable('view', TRUE)
        ->setReadOnly(TRUE);

      $fields['type'] = BaseFieldDefinition::create('list_string')
        ->setLabel(t('Type of the proc'))
        ->setDescription(t('Defines a proc as keyring or ciphertext.'))
        ->setDefaultValue('keyring')
        ->setSettings([
          'allowed_values' => [
            'keyring' => 'Keyring',
            'cipher' => 'Cipher Text',
          ],
        ])
        ->setDisplayOptions('view', [
          'label' => 'visible',
          'type' => 'list_default',
          'weight' => 6,
        ])
        ->setDisplayOptions('form', [
          'type' => 'options_select',
          'weight' => 6,
        ])
        ->setDisplayConfigurable('view', TRUE)
        ->setDisplayConfigurable('form', TRUE);

      $fields['meta'] = BaseFieldDefinition::create('map')
        ->setLabel((t('Metadata')))
        ->setDescription(t('Metadata for the proc'));

      $fields['created'] = BaseFieldDefinition::create('created')
        ->setLabel(t('Created'))
        ->setDescription(t('The time that the entity was created.'));
  
      $fields['changed'] = BaseFieldDefinition::create('changed')
        ->setLabel(t('Changed'))
        ->setDescription(t('The time that the entity was last edited.'))
        ->setDefaultValue('browser_figerprint');
  
      $fields['armored'] = BaseFieldDefinition::create('map')
        ->setLabel(t('Armored'))
        ->setDescription(t('The armored value of the Proc entity.'))
        // Set no default value.
        ->setDefaultValue(NULL)
        ->setDisplayOptions('view', [
          'label' => 'above',
          'type' => 'string',
          'weight' => -6,
        ])
        ->setDisplayOptions('form', [
          'type' => 'string_textfield',
          'weight' => -6,
        ])
        ->setDisplayConfigurable('form', TRUE)
        ->setDisplayConfigurable('view', TRUE);
  
    // Owner field of the contact.
    // Entity reference field, holds the reference to the user object.
    // The view shows the user name field of the user.
    // The form presents a auto complete field for the user name.
    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User Name'))
      ->setDescription(t('The Name of the associated user.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'author',
        'weight' => -3,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'match_limit' => 10,
          'size' => 60,
          'placeholder' => '',
        ],
        'weight' => -3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code of Proc entity.'));

    return $fields;
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
