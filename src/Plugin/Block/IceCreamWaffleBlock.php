<?php
namespace Drupal\ice_cream_waffle\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormInterface;


/**
 * Defines a ice cream waffle block.
 *
 * @Block(
 *  id = "ice_cream_waffle_block",
 *  admin_label = @Translation("Ice cream waffle"),
 * )
 */
class IceCreamWaffleBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return \Drupal::formBuilder()->getForm('Drupal\ice_cream_waffle\Form\IceCreamWaffleApplyForm');
  }
}