<?php

namespace Drupal\proc\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the proc type entity type.
 *
 * @ConfigEntityType(
 *   id = "proc_type",
 *   label = @Translation("Proc Type"),
 *   label_collection = @Translation("Proc Types"),
 *   label_singular = @Translation("proc type"),
 *   label_plural = @Translation("proc types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count proc type",
 *     plural = "@count proc types",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\proc\ProcTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\proc\Form\ProcTypeForm",
 *       "edit" = "Drupal\proc\Form\ProcTypeForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider"
 *     }
 *   },
 *   bundle_of = "proc",
 *   config_prefix = "proc_type",
 *   admin_permission = "administer proc_type",
 *   links = {
 *     "collection" = "/admin/structure/proc-type",
 *     "add-form" = "/admin/structure/proc-type/add",
 *     "edit-form" = "/admin/structure/proc-type/{proc_type}",
 *     "delete-form" = "/admin/structure/proc-type/{proc_type}/delete"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label"
 *   }
 * )
 */
class ProcType extends ConfigEntityBundleBase {

  /**
   * The machine name of this Proc type.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of the Proc type.
   *
   * @var string
   */
  protected $label;

}
