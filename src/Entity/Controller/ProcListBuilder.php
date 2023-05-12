<?php

namespace Drupal\proc\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list controller for proc entity.
 *
 * @ingroup proc
 */
class ProcListBuilder extends EntityListBuilder {

  /**
   * The url generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('url_generator')
    );
  }

  /**
   * Constructs a new ProcListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The url generator.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, UrlGeneratorInterface $url_generator) {
    parent::__construct($entity_type, $storage);
    $this->urlGenerator = $url_generator;
  }

  /**
   * {@inheritdoc}
   *
   * We override ::render() so that we can add our own content above the table.
   * parent::render() is where EntityListBuilder creates the table using our
   * buildHeader() and buildRow() implementations.
   */
  // public function render() {
  //   $build['description'] = [
  //     '#markup' => $this->t('Content Entity Example implements a Contacts model. These contacts are fieldable entities. You can manage the fields on the <a href="@adminlink">Contacts admin page</a>.', [
  //       '@adminlink' => $this->urlGenerator->generateFromRoute('content_entity_example.contact_settings'),
  //     ]),
  //   ];
  //   $build['table'] = parent::render();
  //   return $build;
  // }

  /**
   * {@inheritdoc}
   *
   * Building the header and content lines for the contact list.
   *
   * Calling the parent::buildHeader() adds a column for the possible actions
   * and inserts the 'edit' and 'delete' links as defined for the entity type.
   */
  public function buildHeader() {
    $header['id'] = $this->t('Proc ID');
    $header['label'] = $this->t('Label');
    $header['owner'] = $this->t('Owner');
    $header['type'] = $this->t('Type');
    $header['status'] = $this->t('Status');
    $header['meta'] = $this->t('Meta');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\content_entity_example\Entity\Contact */
//    ksm($entity->get('status')->value());
//    ksm($entity->getOwner()->getAccountName());
//    ksm($entity);
//    ksm($entity->get('meta')->getValue());
//    ksm($entity->getEntityType());
    $row['id'] = $entity->id();
    $row['label'] = $entity->label();
    $row['owner'] = $entity->getOwner()->getAccountName();
    $row['type'] = $entity->getType();
    $row['status'] = $entity->getStatus();
    $row['meta'] = $entity->getMeta();

    // $row['name'] = $entity->toLink()->toString();
    // $row['first_name'] = $entity->first_name->value;
    // $row['role'] = $entity->role->value;
    return $row + parent::buildRow($entity);
  }

}
