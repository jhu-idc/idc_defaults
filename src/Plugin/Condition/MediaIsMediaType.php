<?php

namespace Drupal\idc_defaults\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Provides a 'Media Type' condition.
 *
 * @Condition(
 *   id = "media_mediatype",
 *   label = @Translation("Media is a certain media type"),
 *   context_definitions = {
 *     "media" = @ContextDefinition("entity:media", required = TRUE, label = @Translation("Media"))
 *   }
 * )
 */
class MediaIsMediaType extends ConditionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    $options = [];
    $bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo('media');
    foreach ($bundles as $bundle => $bundle_properties) {
      $options[$bundle] = $this->t('@bundle (media)', [
        '@bundle' => $bundle_properties['label']
      ]);
    }

    $form['bundles'] = [
      '#title' => $this->t('Bundles'),
      '#type' => 'checkboxes',
      '#options' => $options,
      '#default_value' => $this->configuration['bundles'],
    ];

    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['bundles'] = array_filter($form_state->getValue('bundles'));
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    if (empty($this->configuration['bundles'])) {
      return $this->t('No bundles are selected.');
    }

    return $this->t(
      'Entity bundle in the list: @bundles',
      [
        '@bundles' => implode(', ', $this->configuration['field']),
      ]
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array_merge(
      ['bundles' => []],
      parent::defaultConfiguration()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    if (empty($this->configuration['bundles'])) {
      return TRUE;
    }

    $media = $this->getContextValue('media');
    if ($media) {
      //\Drupal::logger("media test")->info("bundle type is: " . $media->bundle());
      if (!empty($this->configuration['bundles'][$media->bundle()])) {
        return TRUE;
      }
    }
    return FALSE;
  }
}
