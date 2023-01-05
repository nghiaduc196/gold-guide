<?php

namespace Drupal\dashboard\Controller;

use Drupal\core\Url;
use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for the Dashboard module.
 */
class DashboardController extends ControllerBase {

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function getDashboard() {
    $dashboard_menu = dashboard_get('dashboard_menu', []);
    foreach ($dashboard_menu as $key => $value) {
      if (empty($value['url']) || !\Drupal::service('path.validator')->isValid(substr($value['url'], 1))) {
        unset($dashboard_menu[$key]);
      }
      unset($dashboard_menu['id']);
      unset($dashboard_menu['provider']);
    }

    $dashboard = dashboard_get('dashboard', []);
    $groups = dashboard_get('dashboard_menu_groups', ['System']);
    $menu_group = [];
    foreach ($groups as $group) {
      $menu_group[$group] = [];
    }

    $legacy_icons_map = _dashboard_legacy_icon_map();

    foreach ($dashboard_menu as $menu) {
      $group = isset($menu['group']) ? $menu['group'] : 'System';
      // Replace the legacy icons with fa-icons.
      if (isset($legacy_icons_map[$menu['icon']])) {
        $menu['icon'] = $legacy_icons_map[$menu['icon']];
      }
      //Handle full domain uri
      /*if(isset($menu['uri'])){
        $menu['uri'] = (Url::fromUri($menu['uri']))->setAbsolute(true);
      }*/
      // Handle domain prefixes.
      $menu['url'] = (Url::fromRoute($menu['url']))->getRouteName();

      $menu_group[$group][] = $menu;
    }
    foreach ($menu_group as $group => $menu) {
      if (empty($menu)) {
        unset($menu_group[$group]);
      }
    }
    return [
      '#theme' => 'dashboard',
      '#title' => $dashboard['title'],
      '#dashboard_menu' => $menu_group,
    ];
  }

}
