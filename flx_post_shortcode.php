<?php

/**
 * Plugin Name:       FiloxPoster
 * Description:       Handle post submission
 * Version:           1.0.0
 * Requires at least: 5.4.2
 * Requires PHP:      7.3.5
 * Author:            George Margaritis
 * Author URI:        https://github.com/gmargaritis
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

// Block direct access
defined( 'ABSPATH' ) or die( 'Access denied' );
require_once ABSPATH . '/wp-admin/includes/post.php';


// Report all PHP errors (see changelog)
error_reporting(E_ALL);

// Report all PHP errors
error_reporting(-1);

// Defines the functionality for the flx_post_shortcode
class flx_post_shortcode{

  public function __construct(){
    add_action('init', array($this, 'register_flx_post_shortcode'));
    add_action('rest_api_init', array($this, 'register_routes'));
    add_action('wp_enqueue_scripts', array($this,'scripts'));
  }

  public function scripts(){
    wp_register_script('main_js', plugins_url('/js/main.js',  __FILE__ ));
    $base_url = get_site_url();

    wp_enqueue_script('main_js');

    wp_localize_script('main_js', 'base_url', $base_url);
  }

  public function register_flx_post_shortcode(){
    add_shortcode('flx_post', array($this,'flx_post_shortcode_output'));
  }

  public function flx_post_shortcode_output(){
    if (is_user_logged_in()){
      readfile(__DIR__ . '/form.php');
    } else {
      $loginURL = admin_url();
      ?>
      <p>
        <a href="<?php echo $loginURL ?>">Log in to Wordpress</a>
      </p>
      <?php
    }
  }

  public function register_routes() {
    register_rest_route( 'filox-poster/v1', 'validation', array(
                         'methods' => 'POST',
                         'callback' => array($this, 'validateTitle'),
                         //'permission_callback' => function() {
                         //   return current_user_can( 'edit_posts' );
                         // }
                         )
                       );
    register_rest_route( 'filox-poster/v1', 'post-creation', array(
                        'methods' => 'POST',
                        'callback' => array($this, 'createPost'),
                        //'permission_callback' => function() {
                        //   return current_user_can( 'edit_posts' );
                        // }
                        )
                      );
  }

  // Checks if the title already exists
  public function validateTitle(WP_REST_Request $request) {
    $post_title = sanitize_title ($request['title']);

    if(post_exists($post_title)){
      return true;
    } else {
      return false;
    }
  }

  // Creates a new post
  public function createPost(WP_REST_Request $request){

      $post_title = sanitize_title ($request['title']);
      $post_content = $request['content'];

      if (empty($post_title) or empty($post_content)){ 
        return true;
      } 
      else {

        $new_post = array(
          'post_title' => $post_title,
          'post_content' => $post_content,
          'post_status' => 'publish',
          'post_date' => date('Y-m-d H:i:s'),
          'post_author' => '',
          'post_type' => 'post',
        );

        wp_insert_post($new_post);

        $my_post = get_page_by_title( $post_title, OBJECT, 'post' );
        $url = get_permalink($my_post);

        $data = ['url' => $url];
        header('Content-type: application/json');
        echo json_encode($data);
      }
    }
}

$flx_post_shortcode = new flx_post_shortcode;
