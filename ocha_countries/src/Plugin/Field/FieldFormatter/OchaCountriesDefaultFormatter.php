<?php

namespace Drupal\ocha_countries\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldFilteredMarkup;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\OptGroup;
use Drupal\ocha_integrations\Plugin\Field\FieldFormatter\OchaIntegrationsDefaultFormatter;

/**
 * Plugin implementation of the 'ocha_countries' formatter.
 *
 * @FieldFormatter (
 *   id = "ocha_countries_default",
 *   label = @Translation("OCHA country formatter"),
 *   field_types = {
 *     "ocha_countries"
 *   }
 * )
 */
class OchaCountriesDefaultFormatter extends OchaIntegrationsDefaultFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'output' => 'label',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['output'] = [
      '#title' => $this->t('Output'),
      '#type' => 'select',
      '#options' => [
        'label' => $this->t('Label'),
        'label_translated' => $this->t('Label translated'),
        'iso2' => $this->t('ISO2 code'),
        'iso3' => $this->t('ISO3 code'),
      ],
      '#default_value' => $this->getSetting('output'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    if ($items->count()) {
      $provider = $items->getFieldDefinition()
        ->getFieldStorageDefinition()
        ->getOptionsProvider('value', $items->getEntity());

      $options = OptGroup::flattenOptions($provider->getPossibleOptions());

      foreach ($items as $delta => $item) {
        $value = $item->value;

        $output = isset($options[$value]) ? $options[$value] : $value;
        if ($this->getSetting('output') != 'label') {
          // Get item data.
          $data = ocha_countries_get_item($value, $langcode);
          switch ($this->getSetting('output')) {
            case 'label_translated':
              $output = $data->label->translated;
              break;

            case 'iso2':
              $output = $data->iso2;
              break;

            case 'iso3':
              $output = $data->iso3;
              break;

          }
        }

        $elements[$delta] = [
          '#markup' => $output,
          '#allowed_tags' => FieldFilteredMarkup::allowedTags(),
        ];
      }
    }

    return $elements;
  }

}
