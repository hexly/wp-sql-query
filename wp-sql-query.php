<?php
/*
Plugin Name: wp-sql-query
Plugin URI:
Version: 1.0.0
Author: Hexly
Author URI: http://www.hexly.cloud/
*/

add_action( 'plugins_loaded', [ 'HexlySqlQuery', 'get_instance' ] );
register_activation_hook( __FILE__, ['HexlySqlQuery', 'on_activate_hook'] );
register_deactivation_hook( __FILE__, ['HexlySqlQuery', 'on_deactivate_hook'] );
register_uninstall_hook( __FILE__, ['HexlySqlQuery', 'on_uninstall_hook'] );

class HexlySqlQuery {
  private static $instance;
  public static function get_instance() {
    if ( null == self::$instance ) {
      self::$instance = new HexlyPlugin();
    } 
    return self::$instance;
  }

  public function __construct() {
  }

  public function on_activate_hook() {
    error_log('Activated!');
    // echo $this->plugin_message('Congrats!');
  }

  public function on_deactivate_hook() {
    error_log('Dectivated!');
    // echo $this->plugin_message('Congrats!');
  }

  public function on_uninstall_hook() {
    error_log('Uninstalled!');
    // echo $this->plugin_message('Congrats!');
  }
}