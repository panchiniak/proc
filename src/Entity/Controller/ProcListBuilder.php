<?php

namespace Drupal\proc\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Routing\RedirectDestinationInterface;

/**
 * Provides a list controller for proc entity.
 *
 * @ingroup proc
 */
class ProcListBuilder extends EntityListBuilder {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Constructs a new ProcListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Routing\RedirectDestinationInterface $redirect_destination
   *   The redirect destination service.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, DateFormatterInterface $date_formatter, RedirectDestinationInterface $redirect_destination) {
    parent::__construct($entity_type, $storage);

    $this->dateFormatter = $date_formatter;
    $this->redirectDestination = $redirect_destination;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('date.formatter'),
      $container->get('redirect.destination')
    );
  }

  /**
   * {@inheritdoc}
   *
   * Building the header and content lines for the contact list.
   *
   * Calling the parent::buildHeader() adds a column for the possible actions
   * and inserts the 'edit' and 'delete' links as defined for the entity type.
   */
  public function buildHeader() {
    $header = [
      'id' => $this->t('Proc ID'),
      'label' => [
        'data' => $this->t('Label'),
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      'owner' => [
        'data' => $this->t('Owner'),
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
      'type' => $this->t('Type'),
      'status' => [
        'data' => $this->t('Status'),
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
      'created' => [
        'data' => $this->t('Created'),
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
      'changed' => [
        'data' => $this->t('Changed'),
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
      'size' => [
        'data' => $this->t('Size'),
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
    ];

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    //    ksm($entity->getMeta());

    $flag = '';
    // Proc entities less than 30 days old are considered new:
    if ($entity->getCreated() > HISTORY_READ_LIMIT) {
      $flag = $this->t('New');
    }
    $row['id'] = $entity->id();
    $row['title']['data'] = [
      '#type' => 'link',
      '#title' => $entity->label(),
      '#suffix' => ' ' . $flag,
      '#url' => $entity->toUrl(),
    ];
    $row['owner'] = $entity->getOwner()->getAccountName();
    $row['type'] = $entity->getType();
    $row['status'] = $entity->getStatus() ? $this->t('published') : $this->t('not published');
    $row['created'] = $this->dateFormatter->format($entity->getCreated(), 'short');
    $row['changed'] = $entity->getChangedTime() ? $this->dateFormatter->format($entity->getChangedTime(), 'short') : $this->t('not changed');
    $row['size'] = $entity->getType() == 'cipher' ? format_size(intval($entity->getMeta()[0]['source_file_size'])) : format_size(intval($entity->getMeta()[0]['key_size']));

    return $row + parent::buildRow($entity);
  }

}
