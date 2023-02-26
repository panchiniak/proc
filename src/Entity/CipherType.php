<?php

namespace Drupal\proc\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Cipher type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "cipher_type",
 *   label = @Translation("Cipher type"),
 *   label_collection = @Translation("Cipher types"),
 *   label_singular = @Translation("cipher type"),
 *   label_plural = @Translation("ciphers types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count ciphers type",
 *     plural = "@count ciphers types",
 *   ),
 *   handlers = {
 *     "form" = {
 *       "add" = "Drupal\proc\Form\CipherTypeForm",
 *       "edit" = "Drupal\proc\Form\CipherTypeForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *     "list_builder" = "Drupal\proc\CipherTypeListBuilder",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   admin_permission = "administer cipher types",
 *   bundle_of = "cipher",
 *   config_prefix = "cipher_type",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/cipher_types/add",
 *     "edit-form" = "/admin/structure/cipher_types/manage/{cipher_type}",
 *     "delete-form" = "/admin/structure/cipher_types/manage/{cipher_type}/delete",
 *     "collection" = "/admin/structure/cipher_types"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid",
 *   }
 * )
 */
class CipherType extends ConfigEntityBundleBase {

  /**
   * The machine name of this cipher type.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of the cipher type.
   *
   * @var string
   */
  protected $label;

}
