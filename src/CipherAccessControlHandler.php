<?php

namespace Drupal\proc;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the cipher entity type.
 */
class CipherAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view cipher');

      case 'update':
        return AccessResult::allowedIfHasPermissions(
          $account,
          ['edit cipher', 'administer cipher'],
          'OR',
        );

      case 'delete':
        return AccessResult::allowedIfHasPermissions(
          $account,
          ['delete cipher', 'administer cipher'],
          'OR',
        );

      default:
        // No opinion.
        return AccessResult::neutral();
    }

  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermissions(
      $account,
      ['create cipher', 'administer cipher'],
      'OR',
    );
  }

}
