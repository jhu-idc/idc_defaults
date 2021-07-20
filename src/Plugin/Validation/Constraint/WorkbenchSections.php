<?php

namespace Drupal\idc_defaults\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the user has access to an entity.
 *
 * @Constraint(
 *   id = "WorkbenchSections",
 *   label = @Translation("Workbench Sections", context = "Validation"),
 *   type = "string"
 * )
 */
class WorkbenchSections extends Constraint {

  /**
   * Entity passed in does not exist or isn't a taxonomy term.
   *
   * @var string
   */
  public $badType = 'The entity does not exist or is not a taxonomy term.';

  /**
   * User does not have access to the term (collection) section via workbench.
   *
   * @var string
   */
  public $noAccess = 'The user does not have access to ingest into the collection: @collection.';

}
