<?php

namespace Drupal\idc_defaults\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the user has access to an entity.
 *
 * @Constraint(
 *   id = "IdcUniqueItem",
 *   label = @Translation("iDC Unique Item", context = "Validation"),
 *   type = "string"
 * )
 */
class UniqueItem extends Constraint {

  /**
   * ID passed in already exists.
   *
   * @var string
   */
  public $alreadyExists = 'The ID (@id) is already in use, please choose another one.';

}
