<?php

namespace Drupal\subscribe\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements the SimpleForm form controller.
 *
 * This example demonstrates a simple form with a single text input element. We
 * extend FormBase which is the simplest form base class used in Drupal.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class DialogForm extends FormBase {

  /**
   * The temp store factory.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  // Pass the dependency to the object constructor
  public function __construct(PrivateTempStoreFactory $temp_store_factory) {
    $this->tempStoreFactory = $temp_store_factory->get('subscribe');
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private')
    );
  }

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

    $form['#attached']['library'][] = 'core/drupal.ajax';
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    $form['container'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'my-container'],
    ];

    $form['container']['description'] = [
      '#type' => 'item',
      '#markup' => $this->t('Are you sure you want to delete this item/s?'),
    ];

    $form['container']['results'] = [
      '#type' => 'item',
      '#markup' => $this->t('Results will be displayed here'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
      '#theme_wrappers' => array(),
    ];

    // Add a submit button that handles the submission of the form.
    $form['actions']['submit1'] = [
      '#type' => 'submit',
      '#value' => $this->t('Yes'),
    ];

    $form['actions']['submit2'] = [
      '#type' => 'submit',
      '#value' => $this->t('No'),
      '#submit' => ['::cancelDelete'],
    ];

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
    return 'dialog_form';
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

    // Get temp storage value
    $submitted_values = $this->tempStoreFactory->get('table_values');


    //Delete data from database
    foreach ($submitted_values as $key => $submitted_value) {
      if (isset($submitted_values[$key]) == TRUE) {
        $uid = intval($submitted_value);
        $query = \Drupal::database();
        $query->delete('subscribe_list')
          ->condition('uid', $uid)
          ->execute();
      }
    }

    // Redirect to same page
    $url = Url::fromRoute('subscribe.subscribe_list');
    $form_state->setRedirectUrl($url);

  }

  public function cancelDelete(array &$form, FormStateInterface $form_state) {

    // Redirect to same page if "No" button is clicked.
    $url = Url::fromRoute('subscribe.subscribe_list');
    $form_state->setRedirectUrl($url);

  }
}
