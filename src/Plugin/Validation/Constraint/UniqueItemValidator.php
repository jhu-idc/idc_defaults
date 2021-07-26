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
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    foreach ($items as $item) {
      $field_name = $items->getFieldDefinition()->getName();
      $field_map = $this->entityFieldManager->getFieldMap();

      foreach ($field_map as $entity_type => $fields) {
        if (isset($fields[$field_name])) {
          $query = $this->entityTypeManager->getStorage($entity_type)->getQuery();
          $value_taken = (bool) $query
            ->condition($field_name, $item->getValue())
            ->range(0, 1)
            ->count()
            ->execute();
          if ($value_taken) {
            $this->context->addViolation($constraint->alreadyExists, ['@id' => $item->getValue()]);
          }
        }
      }
    }

  }

}
