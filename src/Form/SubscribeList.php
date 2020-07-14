<?php

namespace Drupal\subscribe\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormBuilder;

/**
 * Implements the SimpleForm form controller.
 *
 * This example demonstrates a simple form with a single text input element. We
 * extend FormBase which is the simplest form base class used in Drupal.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class SubscribeList extends FormBase {

  protected $tempStoreFactory;

  protected $formBuilder;

  // Pass the dependency to the object constructor
  public function __construct(PrivateTempStoreFactory $temp_store_factory, FormBuilder $formBuilder) {
    $this->tempStoreFactory = $temp_store_factory->get('subscribe');
    $this->formBuilder = $formBuilder;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
      $container->get('form_builder')
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

    // Load data from database
    $database = \Drupal::database();
    $query = $database->select('subscribe_list', 's');
    $query->fields('s', ['uid','email']);
    $results = $query->execute()->fetchAll();


    // Initialize an empty array
    $data = array();
    // Next, loop through the $results array
    foreach ($results as $result) {
      if (!empty($result->email)) {
        $data[$result->uid] = [
          'uid' => $result->uid,     // 'uid' was the key used in the header
          'email' => $result->email,    // 'email' was the key used in the header
        ];
      }
    }

    $header = [
      'uid' => $this->t('UID'),
      'email' => $this->t('Email Address'),
    ];

    // Add the core AJAX library.
    $form['#attached']['library'][] = 'core/drupal.ajax';
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    $form['table'] = [
      '#type' => 'tableselect',
      '#title' => $this->t('List of email subscribers'),
      '#header' => $header,
      '#options' => $data,
      '#empty' => $this->t('No users found'),
      '#ajax' => [
        'callback' => '::promptCallback',
        'progress' => [
          'message' => '',
        ],
      ],
    ];

    // Load DialogForm as a modal
    $form['open_modal1'] = array(
      '#type' => 'link',
      '#title' => t('Unsubscribe'),
      '#url' => Url::fromRoute('subscribe.dialog_form'),
      '#attributes' => array(
        //'class' => array('button', 'use-ajax'),
        'class' => array('button', 'use-ajax'),
        'data-dialog-type' => 'modal',
        'data-dialog-options' => '{"width": 400, "title": "Delete"}'
      ),
    );


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
    return 'subscribe_list_form';
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

  }

  public function promptCallback(array &$form, FormStateInterface $form_state) {

    // Get selected table value
    $tvalues = $form_state->getValue('table');
    // Store value in temp storage
    $this->tempStoreFactory->set('table_values', $tvalues);

  }
}

