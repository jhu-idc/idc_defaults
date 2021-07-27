<?php

namespace Drupal\idc_defaults\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\UuidItem;

/**
 * Defines the 'idc_unique' entity field type.
 *
 * The field uses a newly generated UUID as default value.
 *
 * @FieldType(
 *   id = "idc_unique",
 *   label = @Translation("iDC Unique Field"),
 *   description = @Translation("An entity field containing a unique value used within iDC."),
 *   default_widget = "string_textfield",
 *   default_formatter = "string"
 * )
 */
class IdcUniqueItem extends UuidItem {

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraints = parent::getConstraints();
    $constraint_manager = \Drupal::typedDataManager()->getValidationConstraintManager();
    $constraints[] = $constraint_manager->create('IdcUniqueItem', []);
    return $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);
    // UUID get applied to revisions as well by default. Given that this field
    // is only concerned about targeting entities as a whole remove that unique
    // constraint at the database level. The custom constraint will handle
    // the entity level collision.
    unset($schema['unique keys']);
    return $schema;
  }

}
