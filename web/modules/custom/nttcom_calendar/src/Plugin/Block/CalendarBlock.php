<?php

namespace Drupal\nttcom_calendar\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a calendar block.
 *
 * @Block(
 *   id = "nttcom_calendar_calendar",
 *   admin_label = @Translation("NTT Calendar"),
 *   category = @Translation("Custom")
 * )
 */
class CalendarBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var FormBuilder
   */
  protected $formBuider;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilder $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuider = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [
      '#theme' => 'nttcom_block_calendar',
      '#attached' => [
        'library' => [
          'core/drupal',
          'core/drupal.form',
          'core/drupal.ajax'
        ]
      ]
    ];
    $build['#attached']['library'][] ='core/jquery';
    $build['#attached']['library'][] ='core/drupal.ajax';
    $build['#attached']['library'][] ='core/drupal.dialog.ajax';
    $build['#attached']['library'][] ='core/jquery.form';
    return $build;
  }
}
