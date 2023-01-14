<?php

namespace Drupal\nttcom_calendar\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\nttcom_calendar\CalendarServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a guide calendar block block.
 *
 * @Block(
 *   id = "nttcom_calendar_guide_calendar_block",
 *   admin_label = @Translation("NTT Guide Calendar Block"),
 *   category = @Translation("Custom")
 * )
 */
class GuideCalendarBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager
   *
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current user.
   *
   * @var AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The calendar service.
   *
   * @var CalendarServiceInterface
   */
  protected $caleandar;

  /**
   * {@inheritdoc}
   */
  public function __construct(array                      $configuration, $plugin_id, $plugin_definition,
                              EntityTypeManagerInterface $entity_type_manager,
                              AccountProxyInterface      $account_proxy,
                              CalendarServiceInterface   $calendar_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $account_proxy;
    $this->caleandar = $calendar_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('nttcom_calendar')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['#theme'] = 'nttcom_guide_calendar_block';
    $build['#attached']['drupalSettings']['zabutoCalendar'] = $this->caleandar->getTimeSlots();
    $build['#attached']['library'][] = 'nttcom_calendar/nttcom.zabuto_calendar';
    $build['#cache']['max-age'] = 0;
    $build['#cache']['contexts'] = ['user'];
    return $build;
  }
}
