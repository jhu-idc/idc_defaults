<?php

namespace Drupal\idc_defaults\Plugin\Condition;

use Drupal\islandora\Plugin\Condition\NodeHasTerm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Sees if a certain Media Use term is present
 *
 * @Condition(
 *   id = "media_use_is_present",
 *   label = @Translation("Media has a certain Media Use term applied to it"),
 *   context_definitions = {
 *     "media" = @ContextDefinition("entity:media", required = TRUE , label = @Translation("media"))
 *   }
 * )
 */
class MediaUseIsPresent extends NodeHasTerm {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $config = parent::buildConfigurationForm($form, $form_state);

    if (isset($config['term']['#selection_settings']) && isset($config['term']['#selection_settings']['target_bundles'])) {
      if (!in_array('islandora_media_use', $config['term']['#selection_settings']['target_bundles'])) {
        $config['term']['#selection_settings']['target_bundles'][] = 'islandora_media_use';
      }
    } else {
        $config['term']['#selection_settings']['target_bundles'] = array('islandora_media_use');
    }

    return $config;
  }

    /**
   * {@inheritdoc}
   */
  public function evaluate() {
    if (empty($this->configuration['uri']) && !$this->isNegated()) {
      return TRUE;
    }

    $media = $this->getContextValue('media');
    if (!$media) {
      return FALSE;
    }
    $val = $this->evaluateEntity($media);
    //$valStr = $val ? "true" : "false";
    //\Drupal::logger("Media Condition")->info("Evaluate: $valStr (negate: " . $this->isNegated() . ")");
    return  $val;
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    if (!empty($this->configuration['negate'])) {
      return $this->t('The media is not associated with taxonomy term with uri @uri.', ['@uri' => $this->configuration['uri']]);
    }
    else {
      return $this->t('The media is associated with taxonomy term with uri @uri.', ['@uri' => $this->configuration['uri']]);
    }
  }
}
