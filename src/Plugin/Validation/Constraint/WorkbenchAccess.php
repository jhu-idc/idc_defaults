<?php

namespace Drupal\idc_defaults\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the user has access to an entity.
 *
 * @Constraint(
 *   id = "WorkbenchAccess",
 *   label = @Translation("Workbench Access", context = "Validation"),
 *   type = "string"
 * )
 */
class WorkbenchAccess extends Constraint {

  /**
   * Entity passed in does not exist or isn't a node.
   *
   * @var string
   */
  public $badType = 'The entity does not exist or is not a node.';

  /**
   * User does not have access to the entity referenced via workbench.
   *
   * @var string
   */
  public $noAccess = 'The user does not have access to ingest into this object.';

}
