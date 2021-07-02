<?php

namespace Drupal\idc_defaults\Plugin\Validation\Constraint;

use Drupal\node\NodeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates that the user has access to the entity.
 */
class WorkbenchAccessValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    foreach ($items as $item) {
      if (!isset($item->entity) || !$item->entity instanceof NodeInterface) {
        $this->context->addViolation($constraint->badType);
      }
      else {
        // Use the "update" op as it applies to an already existing entity and
        // not a new entity being created.
        if (!$item->entity->access('update')) {
          $this->context->addViolation($constraint->noAccess);
        }
      }
    }
  }

}
