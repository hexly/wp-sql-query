<?php
/*
Plugin Name: wp-sql-query
Plugin URI:
Version: 1.0.0
Author: Hexly
Author URI: http://www.hexly.cloud/
*/

define('HEXLY_SQL_QUERY_TYPE', 'hexly_sql_query_post');

add_action( 'plugins_loaded', [ 'HexlySqlQuery', 'get_instance' ] );
register_activation_hook( __FILE__, ['HexlySqlQuery', 'on_activate_hook'] );
register_deactivation_hook( __FILE__, ['HexlySqlQuery', 'on_deactivate_hook'] );
register_uninstall_hook( __FILE__, ['HexlySqlQuery', 'on_uninstall_hook'] );

class HexlySqlQuery {
  private static $instance;
  public static function get_instance() {
    if ( null == self::$instance ) {
      self::$instance = new HexlySqlQuery();
    }
    return self::$instance;
  }

  public static $statuses = [
    ['id' => 'hexsqlquery-sent',   'name' => 'Active'],
    ['id' => 'hexsqlquery-unsent', 'name' => 'Inactive'],
    ['id' => 'hexsqlquery-needs-attention', 'name' => 'Needs Attention'],
  ];

  public function __construct() {
    add_action( 'init', ['HexlySqlQuery', 'create_sql_query_post_type'] );
    add_action( 'add_meta_boxes', [$this, 'custom_editor_metabox'] );
    add_action( 'save_post', [$this, 'save_wp_editor_fields'] );
    add_action( 'admin_post_hexly_run_sql_query', [ $this, 'run_sql_query' ]); // NOT WORKING. FIND REPLACEMENT
  }

  public function on_activate_hook() {
    error_log('Activated!');
   }

  public function on_deactivate_hook() {
    error_log('Dectivated!');
  }

  public function on_uninstall_hook() {
    error_log('Uninstalled!');
  }

  function create_sql_query_post_type() {
    $labels = [
        'name' => __( 'Hexly SQL Queries' ),
        'singular_name' => 'Hexly SQL Query' ,
        'menu_name'           => 'Hexly SQL Queries',
        'all_items'           => __( 'All SQL Queries', 'hexly' ),
        'view_item'           => __( 'View SQL Query', 'hexly' )
    ];
    
    $args = [
        'labels'              => $labels,
        'has_archive'         => true,
        'rewrite'             => array('slug' => 'sync queries'),
        'label'               => __( 'Hexly SQL Queries', 'hexly' ),
        'description'         => 'Hexly SQL Queries',
        'supports'            => array( 'title' ),
        'taxonomies'          => array( 'genres' ),
        'hierarchical'        => false, // this can get slow at even just hundreds
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 6,
        'can_export'          => false,
        'has_archive'         => false,
        'exclude_from_search' => true,
        'publicly_queryable'  => false,
        'capability_type'     => 'page'
    ];

    $register_res = register_post_type(HEXLY_SQL_QUERY_TYPE, $args);
    
    foreach (HexlySqlQuery::$statuses as $status) {
      register_post_status( $status['id'], array(
        'label'                     => _x( $status['name'], HEXLY_SQL_QUERY_TYPE ),
        'public'                    => true,
        'post_type'                 => HEXLY_SQL_QUERY_TYPE,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'show_in_metabox_dropdown'  => true,
        'show_in_inline_dropdown'   => true,
        'dashicon'                  => 'dashicons-yes',
        'label_count'               => _n_noop( 
          $status['name'] . ' <span class="count">(%s)</span>',
          $status['name'] . ' <span class="count">(%s)</span>'
        ),
      ));
    }
  }

  function custom_editor_metabox() {
    global $post;
    $post_type = $post->post_type;
    $post_content = $post->post_content;

    if ($post_type != HEXLY_SQL_QUERY_TYPE) {
      return;
    }
    add_meta_box( 'custom_editor', 'SQL Editor', [$this, 'render_custom_editor_callback'], HEXLY_SQL_QUERY_TYPE, 'normal', 'high', $post_content);
    
  }

  function render_custom_editor_callback($post, $cb_args) {
    $post_vars = $_POST;
    
    $args = $cb_args['args'];

    wp_editor( $args, 'hexly_sql_editor', ['tinymce' => false] );

    echo '<input id="run-sql-btn" type="submit" value="Run SQL" class="button button-primary">';
  }

  function save_wp_editor_fields($post_id) {
    $content = $_POST['hexly_sql_editor'];

    remove_action( 'save_post', [$this, 'save_wp_editor_fields'] );
    wp_update_post( ['ID' => $post_id, 'post_content' => wp_kses_post($content)] );
  }

  function run_sql_query() {
    error_log(print_r('run_sql_query()', true));
    $content = $_POST['hexly_sql_editor'];
    error_log(print_r(['$content' => $content], true));
  }
}