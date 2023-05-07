<?php

namespace Drupal\proc;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of proc types.
 */
class ProcTypeListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Label');
    $header['id'] = $this->t('Machine name');
    $header['status'] = $this->t('Status');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\proc\ProcTypeInterface $entity */
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    $row['status'] = $entity->status() ? $this->t('Enabled') : $this->t('Disabled');
    return $row + parent::buildRow($entity);
  }

}



// namespace Drupal\proc;

// use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
// use Drupal\Core\Entity\EntityInterface;
// use Drupal\Core\Url;

// /**
// * Defines a class to build a listing of proc type entities.
// *
// * @see \Drupal\proc\Entity\procType
// */
// class procTypeListBuilder extends ConfigEntityListBuilder {

//   /**
//   * {@inheritdoc}
//   */
//   public function buildHeader() {
//     $header['title'] = $this->t('Label');

//     return $header + parent::buildHeader();
//   }

//   /**
//   * {@inheritdoc}
//   */
//   public function buildRow(EntityInterface $entity) {
//     $row['title'] = [
//       'data' => $entity->label(),
//       'class' => ['menu-label'],
//     ];

//     return $row + parent::buildRow($entity);
//   }

//   /**
//   * {@inheritdoc}
//   */
//   public function render() {
//     $build = parent::render();

//     $build['table']['#empty'] = $this->t(
//       'No proc types available. <a href=":link">Add proc type</a>.',
//       [':link' => Url::fromRoute('entity.proc_type.add_form')->toString()]
//     );

//     return $build;
//   }

// }
