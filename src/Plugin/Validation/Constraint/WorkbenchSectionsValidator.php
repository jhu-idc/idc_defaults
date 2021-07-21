<?php

namespace Drupal\idc_defaults\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\workbench_access\UserSectionStorageInterface;
use Drupal\workbench_access\WorkbenchAccessManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates that the user has access to the entity.
 */
class WorkbenchSectionsValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * User section storage.
   *
   * @var \Drupal\workbench_access\UserSectionStorageInterface
   */
  protected $userSectionStorage;

  /**
   * Validator construct.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   The current user making the request.
   * @param \Drupal\workbench_access\UserSectionStorageInterface $userSectionStorage
   *   User section storage.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, AccountInterface $currentUser, UserSectionStorageInterface $userSectionStorage) {
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $currentUser;
    $this->userSectionStorage = $userSectionStorage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('workbench_access.user_section_storage')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    foreach ($items as $item) {
      if (!isset($item->entity) || !$item->entity instanceof TermInterface) {
        $this->context->addViolation($constraint->badType);
      }
      else {
        if (!$this->currentUser->hasPermission('bypass workbench access')) {
          // Ensure that an access scheme applies for this entity, bundle and
          // field.
          foreach ($this->entityTypeManager->getStorage('access_scheme')->loadMultiple() as $access_scheme) {
            $scheme = $access_scheme->getAccessScheme();
            if (!$scheme->applies($item->getEntity()->getEntityTypeId(), $item->getEntity()->bundle())) {
              continue;
            }
            $fields = $scheme->getApplicableFields($item->getEntity()->getEntityTypeId(), $item->getEntity()->bundle());
            foreach ($fields as $field) {
              if ($field['field'] !== $item->getFieldDefinition()->getName()) {
                continue;
              }
              // Ensure that the entity specified falls within the user's
              // allowed entities.
              if (!WorkbenchAccessManager::checkTree($access_scheme, [$item->entity->id()], $this->userSectionStorage->getUserSections($access_scheme))) {
                $this->context->addViolation($constraint->noAccess, ['@collection' => $item->entity->label()]);
              }
            }
          }
        }
      }
    }
  }

}
