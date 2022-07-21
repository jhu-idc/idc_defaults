<?php

namespace Drupal\idc_defaults\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates that the ID is unique.
 */
class UniqueItemValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Validator construct.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   The entity field manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, EntityFieldManagerInterface $entityFieldManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityFieldManager = $entityFieldManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * Validates that the ID is unique.
   * @param mixed $items
   *  The value of the field.
   * @param \Symfony\Component\Validator\Constraint $constraint
   * The constraint for the field.
   */
  public function validate($items, Constraint $constraint) {

    $field_name = $items->getFieldDefinition()->getName();
    $field_map = $this->entityFieldManager->getFieldMap();
    $item_id = $items->getEntity()->id();
    $item_type_id = $items->getEntity()->getEntityTypeId();
    $id_str = 'nid';

    if ($item_type_id == 'taxonomy_term') {
      $id_str = 'tid';
    } else if ($item_type_id == 'media') {
      $id_str = 'mid';
    }

    foreach ($items as $item) {
      foreach ($field_map as $entity_type => $fields) {
        if (isset($fields[$field_name])) {
          $query = $this->entityTypeManager->getStorage($entity_type)->getQuery()
            ->condition($field_name, $item->getValue())
            ->range(0, 1)
            ->count();
          // If the object we're working with already has an id, than this is an update.
          // We need to make sure that a user can update the same object using the same exact
          // unique_id.  If we don't put this here, than validation fails as the object itself
          // will match its own id and trigger a violation.
          if ($entity_type == $item_type_id && $item_id) {
            $query->condition($id_str, $item_id, "!=");
          }
          $value_taken = (bool)$query->execute();
          if ($value_taken) {
            $this->context->addViolation($constraint->alreadyExists, ['@id' => $item->getValue()]);
          }
        }
      }
    }
  }
}
