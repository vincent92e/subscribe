<?php

namespace Drupal\subscribe\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements the SimpleForm form controller.
 *
 * This example demonstrates a simple form with a single text input element. We
 * extend FormBase which is the simplest form base class used in Drupal.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class SubscribeForm extends FormBase {

  /**
   * Build the simple form.
   *
   * A build form method constructs an array that defines how markup and
   * other form elements are included in an HTML form.
   *
   * @param array $form
   *   Default form array structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   *
   * @return array
   *   The render array defining the elements of the form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Add the core AJAX library.
    $form['#attached']['library'][] = 'core/drupal.ajax';
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    $form['#prefix'] = '<div id="form-subscribe" class="form-subscribe"><div class="container"><div class="row"><div class="col col-lg-3 title mb-2">'.t('Sign up for a newsletter').'</div><div class="col-sm-12 col-lg-6">';

    // display field with custom html
    $form['subscribe'] = [
      '#type' => 'item',
      '#markup' => '<input type="text" name="subscribe" class="form-control" id="subscribe" placeholder="Your valid email address">',
      '#allowed_tags' => ['input',],
      '#theme_wrappers' => [],
    ];

    $form['message'] = [
      '#type' => 'item',
      '#markup' => '<div id="form-message"></div>',
      '#theme_wrappers' => [],
    ];

    $form['actions'] = [
      '#type' => 'actions',
      '#theme_wrappers' => [],
    ];

    // Add a submit button that handles the submission of the form.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#ajax' => [
        'callback' => '::displayMessage',
        'wrapper' => 'form-subscribe',
        'progress' => [
          'message' => '',
        ],
      ],
      '#attributes' => [
        'class' => ['button', 'btn', 'btn-primary', 'mb-2', 'col-3', 'col-sm-2'],
      ],
    ];

    $form['#suffix'] = '</div></div></div></div>';

    return $form;
  }

  /**
   * Getter method for Form ID.
   *
   * The form ID is used in implementations of hook_form_alter() to allow other
   * modules to alter the render array built by this form controller. It must be
   * unique site wide. It normally starts with the providing module's name.
   *
   * @return string
   *   The unique ID of the form defined by this class.
   */
  public function getFormId() {
    return 'subscribe_form';
  }

  /**
   * Implements form validation.
   *
   * The validateForm method is the default method called to validate input on
   * a form.
   *
   * @param array $form
   *   The render array of the currently built form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object describing the current state of the form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * Implements a form submit handler.
   *
   * The submitForm method is the default method called for any submit elements.
   *
   * @param array $form
   *   The render array of the currently built form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object describing the current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValues('subscribe');

    $conn = \Drupal::database();
    $conn->insert('subscribe_list')->fields(
      array(
        'email' => $values['subscribe'],
      )
    )->execute();

  }

  public function displayMessage(array &$form, FormStateInterface $form_state) {

    // Update form with message and return
    $element = $form;
    $element['message']['#markup'] = '<div id="form-message" class="form-message">Thank you! You are now subscribed.<i class="fa fa-times"></i></div>';

    return $element;

  }

}
