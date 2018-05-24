<?php

namespace Drupal\ice_cream_waffle\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class IceCreamWaffleSettingsForm extends FormBase {

  protected $state;

  /**
   * IceCreamWaffleSettingsForm constructor.
   *
   * @param \Drupal\Core\State\StateInterface $state
   */
  public function __construct(StateInterface $state) {
    $this->state = $state;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return \Drupal\Core\Form\FormBase|\Drupal\ice_cream_waffle\Form\IceCreamWaffleSettingsForm
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('state')
    );
  }

  /**
   * @return string
   */
  public function getFormId() {
    return 'ice_cream_waffle_settings_form';
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['threshold_ice_cream'] = [
      '#type' => 'textfield',
      '#title' => 'threshold ijsjes instellen',
      '#default_value' => \Drupal::state()
        ->get('ice_cream_waffle.threshold_ice_cream'),
    ];
    $form['threshold_waffle'] = [
      '#type' => 'textfield',
      '#title' => 'threshold wafels instellen',
      '#default_value' => \Drupal::state()
        ->get('ice_cream_waffle.threshold_waffle'),
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Instellen',
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->state
      ->set('ice_cream_waffle.threshold_ice_cream', $form_state->getValue('threshold_ice_cream'));
    $this->state
      ->set('ice_cream_waffle.threshold_waffle', $form_state->getValue('threshold_waffle'));
    drupal_set_message('succesvol ingesteld');
  }

}