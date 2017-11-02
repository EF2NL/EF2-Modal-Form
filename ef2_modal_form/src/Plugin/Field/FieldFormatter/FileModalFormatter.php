<?php

namespace Drupal\ef2_modal_form\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\file\Plugin\Field\FieldFormatter\FileFormatterBase;

/**
 * Plugin implementation of the 'Random_default' formatter.
 *
 * @FieldFormatter(
 *   id = "File_modal",
 *   label = @Translation("File modal"),
 *   field_types = {
 *     "file"
 *   }
 * )
 */
class FileModalFormatter extends FileFormatterBase {

    /**
     * {@inheritdoc}
     */
    public function settingsSummary() {
        $summary = [];
        $settings = $this->getSettings();

        $summary[] = t('Print a button to the modal form');

        return $summary;
    }

    /**
     * {@inheritdoc}
     */
    public function viewElements(FieldItemListInterface $items, $langcode) {
        $element = [];
        
        $element['#attached']['library'][] = 'ef2_modal_form/global-css';
        $element['#attached']['library'][] = 'ef2_modal_form/global-js';
        
        foreach ($this->getEntitiesToView($items, $langcode) as $delta => $file) {
            $item = $file->_referringItem;
            $options = array();
            $file_entity = ($file instanceof \Drupal\file\Entity\File) ? $file : \Drupal\file\Entity\File::load($file->fid);
            
            $mime_type = $file->getMimeType();
            $options['attributes']['type'] = $mime_type . '; length=' . $file->getSize();
            $options['attributes']['class'] = ['use-ajax', 'button', 'cta-clean', 'popup-button'];
            
            if (empty($item->description)) {
                $link_text = $file_entity->getFilename();
            } else {
                $link_text = $item->description;
                $options['attributes']['title'] = $file_entity->getFilename();
            }
            
            // Render each element as markup.
            $element[$delta] = [
                '#type' => 'markup',
                '#markup' => \Drupal::l($link_text, \Drupal\core\Url::fromRoute('ef2_modal_form.open_modal_form', array(
                    'fileId' => $file_entity->id(),
                    'fileName' => base64_encode($link_text)
                ), $options))
            ];
        }

        return $element;
    }

}
