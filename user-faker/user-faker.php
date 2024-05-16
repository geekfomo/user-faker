<?php
/*
 * Plugin Name: User-Faker
 * Plugin URI: https://geekfomo.com/user-faker-for-wordpress/
 * Description: Switch users easily
 * Author: Kobi
 * Author URI: https://geekfomo.com
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */
namespace gfoUserFaker;
const pluginDir = __DIR__;
require_once pluginDir . '/lib/UserFaker.php';


class Plugin {
  /** Register Hooks */
  public function register() : void {
    $faker = new UserFaker();
    $faker->register();
  }
  /** @var Plugin The main instance */
  public static Plugin $main;

}

Plugin::$main = new Plugin();
Plugin::$main->register();
