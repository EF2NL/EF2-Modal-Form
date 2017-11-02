<?php

namespace Drupal\ef2_modal_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;


use Drupal\webform\Entity\Webform;
use Drupal\webform\WebformSubmissionForm;

/**
 * ModalForm class.
 */
class ModalForm extends FormBase {
    
    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'ef2_modal_form_modal_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {
        $form['#prefix'] = '<div id="modal_form">';
        $form['#suffix'] = '</div>';
        
        //dsm($options);

        // The status messages that will contain any form errors.
        $form['status_messages'] = [
            '#type' => 'status_messages',
            '#weight' => -10,
        ];
        
        $form['start'] = [
            '#type' => 'markup',
            '#markup' => '<p>' . $this->t('Om het bestand te kunnen downloaden dient u de onderstaande velden in te vullen en op bevestigen te klikken. ') . '</p>'
        ];
        
        $form['file'] = [
            '#type' => 'hidden',
            '#value' => $options
        ];
        
        
        $form['name'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Uw naam'),
            '#attributes' => [
                'class' => ['form-control'],
                'placeholder' => $this->t('Vul hier uw naam in')
            ],
            '#required' => TRUE
        ];
        
        $form['company'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Uw bedrijfsnaam'),
            '#attributes' => [
                'class' => ['form-control'],
                'placeholder' => $this->t('Vul hier uw bedrijfsnaam in')
            ],
            '#required' => FALSE
        ];
        
        $form['email'] = array(
            '#type' => 'email',
            '#title' => t('E-mailadres'),
            '#attributes' => [
                'class' => ['form-control'],
                'placeholder' => $this->t('Vul hier uw e-mailadres in')
            ],
            '#required' => true
        );
        
        $form['actions'] = array('#type' => 'actions');
        $form['actions']['send'] = [
            '#type' => 'submit',
            '#value' => $this->t('Direct downloaden'),
            '#attributes' => [
                'class' => [
                    'use-ajax',
                ],
            ],
            '#ajax' => [
                'callback' => [$this, 'submitModalFormAjax'],
                'event' => 'click',
            ],
        ];

        $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

        return $form;
    }

    /**
     * AJAX callback handler that displays any errors or a success message.
     */
    public function submitModalFormAjax(array $form, FormStateInterface $form_state) {
        $response = new AjaxResponse();

        // If there are any form errors, re-display the form.
        if ($form_state->hasAnyErrors()) {
            $response->addCommand(new ReplaceCommand('#modal_form', $form));
        } else {
            $file = $form_state->getValue('file');
            $this->appendSubmission($form_state);
            
            if(is_numeric($file)){
                $file_entity = \Drupal\file\Entity\File::load($file);
                $link_text = $file_entity->getFilename();
                $url = file_create_url($file_entity->getFileUri());
            }
            $response->addCommand(new OpenModalDialogCommand("Bedankt", '<p>Bedankt voor uw aanmelding. '
                    . 'U kunt het bestand hier downloaden: <a target="_blank" class="cta-clean" href="' . $url . '">' .$link_text. '</a></p>', ['width' => 685]));
        }

        return $response;
    }
    
    
    private function appendSubmission ($form_state) {
        $values = [
            'webform_id' => EF2_MODAL_FORM_WEBFORM_ID,
            'in_draft' => FALSE,
            'uri' => '/webform/my_webform/api',
            'data' => [
                'name' => $form_state->getValue('name'),
                'company' => $form_state->getValue('company'),
                'email' => $form_state->getValue('email'),
            ],
        ];
        
        $webform = Webform::load($values['webform_id']);
        $is_open = WebformSubmissionForm::isOpen($webform);
        
        if ($is_open === TRUE) {
            $errors = WebformSubmissionForm::validateValues($values);
            if (!empty($errors)) {
                return print_r($errors, TRUE);
            } else {
                $webform_submission = WebformSubmissionForm::submitValues($values);
                return TRUE; //$webform_submission->id();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {
        
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        
    }

    /**
     * Gets the configuration names that will be editable.
     *
     * @return array
     *   An array of configuration object names that are editable if called in
     *   conjunction with the trait's config() method.
     */
    protected function getEditableConfigNames() {
        return ['config.ef2_modal_form_modal_form'];
    }

}
