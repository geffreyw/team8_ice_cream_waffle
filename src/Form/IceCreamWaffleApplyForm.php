<?php

namespace Drupal\ice_cream_waffle\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class IceCreamWaffleApplyForm extends FormBase {

  protected $database;

  protected $state;

  protected $entity;

  /**
   * IceCreamWaffleApplyForm constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   * @param \Drupal\Core\State\StateInterface $state
   * @param \Drupal\Core\Entity\EntityInterface $entity
   */
  public function __construct(Connection $database, StateInterface $state) {
    $this->database = $database;
    $this->state = $state;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return \Drupal\Core\Form\FormBase|\Drupal\ice_cream_waffle\Form\IceCreamWaffleApplyForm
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('state')
    );
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'thomas_more_social_media_settings_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['keuze'] = [
      '#type' => 'radios',
      '#title' => 'Maak een keuze',
      '#default_value' => 'ice_cream',
      '#options' => [
        'ice_cream' => 'Ice cream',
        'waffle' => 'Waffle',
      ],
    ];

    $form['smaak'] = [
      '#type' => 'select',
      '#states' => [
        'visible' => [
          ':input[name="keuze"]' => [
            'value' => 'ice_cream',
          ],
        ],
      ],
      '#title' => 'Kies een smaak',
      '#options' => [
        'vanille' => 'Vanille',
        'chocolade' => 'Chocolade',
        'aardbei' => 'Aardbei',
      ],
    ];

    $form['topping'] = [
      '#type' => 'checkboxes',
      '#states' => [
        'visible' => [
          ':input[name="keuze"]' => [
            'value' => 'waffle',
          ],
        ],
      ],
      '#title' => 'Kies een topping',
      '#options' => [
        'slagroom' => 'Slagroom',
        'chocolade' => 'Chocolade',
        'caramel' => 'Caramel',
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Opslaan',
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @throws \Exception
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('keuze') == 'ice_cream') {
      if ($this->getWeather() > 20) {
        $this->database->insert('ice_cream_waffle_orders')
          ->fields([
            'keuze' => $form_state->getValue('keuze'),
            'smaak' => $form_state->getValue('smaak'),
            'status' => 'nieuw',
          ])->execute();
        drupal_set_message('Uw ' . $form_state->getValue('smaak') . '-ijsje is besteld.');

        $aantalIjsjes = $this->countOrders('ice_cream');
        if ($aantalIjsjes >= $this->state->get('ice_cream_waffle.threshold_ice_cream')) {
          $this->stuurMail('ice_cream');
          $this->database->update('ice_cream_waffle_orders')
            ->fields([
              'status' => 'besteld',
            ])
            ->condition('keuze', 'ice_cream')
            ->execute();
          drupal_set_message('Er werden ' . $aantalIjsjes . ' ijsjes besteld.');
        }
        else {
          drupal_set_message('Er zijn nog niet genoeg ijsjes besteld! Bestel ' . $this->state->get('ice_cream_waffle.threshold_ice_cream') . ' ijsjes!');
        }
      } else{
        drupal_set_message('Het is te koud voor een ijsje neem eventueel een wafel.');
      }
    }
    else {
      $toppings = NULL;
      foreach ($form_state->getValue('topping') as $topping) {
        if (!empty($topping)) {
          $toppings .= $topping . ",";
        }
      }
      $toppings = rtrim($toppings, ",");
      $this->database->insert('ice_cream_waffle_orders')
        ->fields([
          'keuze' => $form_state->getValue('keuze'),
          'topping' => $toppings,
          'status' => 'nieuw',
        ])->execute();
      drupal_set_message('Uw wafel is besteld met volgende toppings: ' . $toppings);

      $aantalWafels = $this->countOrders('waffle');
      if ($aantalWafels >= $this->state->get('ice_cream_waffle.threshold_waffle')) {
        $this->stuurMail('waffle');
        $this->database->update('ice_cream_waffle_orders')
          ->fields([
            'status' => 'besteld',
          ])
          ->condition('keuze', 'waffle')
          ->execute();
        drupal_set_message('Er werden ' . $aantalWafels . ' wafels besteld.');
      }
      else {
        drupal_set_message('Er zijn nog niet genoeg wafels besteld! Bestel ' . $this->state->get('ice_cream_waffle.threshold_waffle') . ' wafels!');
      }
    }
  }

  /**
   * @param \Drupal\ice_cream_waffle\Form\string $keuze
   *
   * @return int
   */
  public function countOrders(string $keuze) {
    return (int) $this->database->select('ice_cream_waffle_orders')
      ->condition('keuze', $keuze)
      ->condition('status', 'nieuw')
      ->countQuery()->execute()->fetchField();
  }

  /**
   * @param \Drupal\ice_cream_waffle\Form\string $keuze
   *
   * @return int
   */
  public function getOrders(string $keuze) {
    return $this->database->select('ice_cream_waffle_orders', 't')
      ->fields('t')
      ->condition('keuze', $keuze, '=')
      ->condition('status', 'nieuw')
      ->execute()
      ->fetchAll();
  }

  public function stuurMail(string $keuze) {
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'ice_cream_waffle';
    $key = 'node_insert'; // Replace with Your key
    $to = "r0663037@student.thomasmore.be";
    $params['message'] = "Er werden volgende producten besteld: \n";
    foreach ($this->getOrders($keuze) as $order){
      $params['message'] .= '- '.$order->keuze . ' ' . $order->topping . $order->smaak."\n";
    }
    $params['title'] = 'Bestelling van ' . $keuze;
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = true;

    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    if ($result['result'] != true) {
      $message = t('There was a problem sending your email notification to @email.', array('@email' => $to));
      drupal_set_message($message, 'error');
      \Drupal::logger('mail-log')->error($message);
      return;
    }

    $message = t('An email notification has been sent to @email ', array('@email' => $to));
    drupal_set_message($message);
    \Drupal::logger('mail-log')->notice($message);
  }

  public function getWeather()
  {
    //units=For temperature in Celsius use units=metric
    //2797779 is new york ID

    $url = "http://api.openweathermap.org/data/2.5/weather?id=2797779&lang=en&units=metric&APPID=cf31cb557d3485a9dde6a7a98ce5da6b";

    $contents = file_get_contents($url);
    $clima=json_decode($contents);

    $temp_max=$clima->main->temp_max;

    return $temp_max;

  }
}