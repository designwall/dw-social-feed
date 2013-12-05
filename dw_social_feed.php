<?php
/**
  * Plugin Name: DW Social Feed
  * Plugin URI: http://designwall.com/plugins/dw-social-feed/
  * Description: Plugin import content automatically from popular Social media sites
  * Version: 1.0.5
  * Author: the DesignWall team
  * Author URI: http://designwall.com
  * License: GPLv2
  */ 

if( ! defined('DWSF_PATH') ) {
    define( 'DWSF_PATH', plugin_dir_path( __FILE__ ) );
} 

if( ! defined('DWSF_URL') ) {
    define( 'DWSF_URL', plugin_dir_url( __FILE__ ) );
} 

require_once DWSF_PATH  . 'inc/settings.php';
require_once DWSF_PATH  . 'inc/class/dwsf_twitter.php';
require_once DWSF_PATH  . 'inc/class/dw_social_feed_class.php';
global $wall_social_feed_options, $dwsf_events;

$dwsf_events = array(
    'glb' => array(
            'label' => __( 'Global', 'dwsf' ),
            'event' => 'dwsf_global_event'
        ),
    'fb' => array(
            'label' => __( 'Facebook', 'dwsf' ),
            'event' => 'dwsf_facebook_event'
        ),
    'tw' => array(
            'label' => __( 'Twitter', 'dwsf' ),
            'event' => 'dwsf_twitter_event'
        ),
    'yb' => array(
            'label' => __( 'Youtube', 'dwsf' ),
            'event' => 'dwsf_youtube_event'
        ),
    'vm' => array(
            'label' => __( 'Vimeo', 'dwsf' ),
            'event' => 'dwsf_vimeo_event'
        ),
    'igm' => array(
            'label' => __( 'Instagram', 'dwsf' ),
            'event' => 'dwsf_instagram_event'
        ),
    'fkr' => array(
            'label' => __( 'Flickr', 'dwsf' ),
            'event' => 'dwsf_flickr_event'
        ),
    'custom' => array(
            'label' => __( 'RSS Feed', 'dwsf' ),
            'event' => 'dwsf_rsscustom_event'
        ),
);

$wall_social_feed_options = dwsf_get_options();
// When active plugin
function dwsf_activation(){
    update_option('wall_social_feed_options', dwsf_get_options() );
}
register_activation_hook(__FILE__, 'dwsf_activation');

/**
 *  Get settings of plugin
 *  @return array Plugin options
 */
function dwsf_get_options(){
    $wall_social_feed_options = get_option('wall_social_feed_options');
    $wall_social_feed_options = wp_parse_args( 
        $wall_social_feed_options, 
        array(
            'general'       =>  array(
                'converter_server'          =>  'untiny.com',
                'cron_times'                =>  3600,
                'use_custom_time'           =>  0,
                'fb_cron_time'              =>  3600,
                'tw_cron_time'              =>  3600,
                'yb_cron_time'              =>  3600,
                'vm_cron_time'              =>  3600,
                'igm_cron_time'             =>  3600,
                'fkr_cron_time'             =>  3600,
                'custom_cron_time'          =>  3600,
                'facebook_status'           =>  0,
                'twitter_status'            =>  0,
                'twitter_consumer_key'      => '',
                'twitter_consumer_secret'   => '',
                'youtube_status'            =>  0,
                'vimeo_status'              =>  0,
                'instagram_status'          =>  0,
                'flickr_status'             =>  0,
                'custom_status'             =>  0
            ),
            'facebook'      => array(),
            'twitter'       => array(),
            'youtube'       => array(),
            'vimeo'         => array(),
            'instagram'     => array(),
            'flickr'        => array(),
            'custom'        => array(),
        ) 
    );
    return $wall_social_feed_options;
}

function dwsf_deactivation() {
    delete_option('wall_social_feed_options');

    remove_action('dwsf_global_event', 'dwsf_do_this_global_time');
    remove_action('dwsf_facebook_event', 'dwsf_do_this_facebook_time');
    remove_action('dwsf_twitter_event', 'dwsf_do_this_twitter_time');
    remove_action('dwsf_youtube_event', 'dwsf_do_this_youtube_time');
    remove_action('dwsf_vimeo_event', 'dwsf_do_this_vimeo_time');
    remove_action('dwsf_instagram_event', 'dwsf_do_this_instagram_time');
    remove_action('dwsf_flickr_event', 'dwsf_do_this_flickr_time');
    remove_action('dwsf_rsscustom_event', 'dwsf_do_this_custom_time');

    wp_clear_scheduled_hook('dwsf_global_event');
    wp_clear_scheduled_hook('dwsf_facebook_event');
    wp_clear_scheduled_hook('dwsf_youtube_event');
    wp_clear_scheduled_hook('dwsf_vimeo_event');
    wp_clear_scheduled_hook('dwsf_twitter_event');
    wp_clear_scheduled_hook('dwsf_instagram_event');
    wp_clear_scheduled_hook('dwsf_flickr_event');
    wp_clear_scheduled_hook('dwsf_rsscustom_event');
    wp_clear_scheduled_hook('dwsf_global_event');

}
register_deactivation_hook(__FILE__, 'dwsf_deactivation');


function dwsf_active() {
    $wall_social_feed_options = get_option('wall_social_feed_options');

    $use_custom_time = isset( $wall_social_feed_options['general']['use_custom_time'] ) ? $wall_social_feed_options['general']['use_custom_time'] : 0;

    $time = isset($wall_social_feed_options['general']['cron_times']) ? $wall_social_feed_options['general']['cron_times'] : 3600;

    $time_fb = isset($wall_social_feed_options['general']['fb_cron_time']) ? $wall_social_feed_options['general']['fb_cron_time'] : 3600;

    $time_tw = isset($wall_social_feed_options['general']['tw_cron_time']) ? $wall_social_feed_options['general']['tw_cron_time'] : 3600;

    $time_yb = isset($wall_social_feed_options['general']['yb_cron_time']) ? $wall_social_feed_options['general']['yb_cron_time'] : 3600;

    $time_vm = isset($wall_social_feed_options['general']['vm_cron_time']) ? $wall_social_feed_options['general']['vm_cron_time'] : 3600;

    $time_igm = isset($wall_social_feed_options['general']['igm_cron_time']) ? $wall_social_feed_options['general']['igm_cron_time'] : 3600;

    $time_fkr = isset($wall_social_feed_options['general']['fkr_cron_time']) ? $wall_social_feed_options['general']['fkr_cron_time'] : 3600;

    $time_custom = isset($wall_social_feed_options['general']['custom_cron_time']) ? $wall_social_feed_options['general']['custom_cron_time'] : 3600;


    
    wp_clear_scheduled_hook( 'dwsf_facebook_event' );
    wp_clear_scheduled_hook( 'dwsf_twitter_event' );
    wp_clear_scheduled_hook( 'dwsf_youtube_event' );
    wp_clear_scheduled_hook( 'dwsf_vimeo_event' );
    wp_clear_scheduled_hook( 'dwsf_instagram_event' );
    wp_clear_scheduled_hook( 'dwsf_flickr_event' );
    wp_clear_scheduled_hook( 'dwsf_rsscustom_event' );
    wp_clear_scheduled_hook( 'dwsf_global_event');

    if( $use_custom_time ){


        if(  $wall_social_feed_options['general']['facebook_status'] == 1 ){
            wp_schedule_event( time() + $time_fb, 'dwsf_custom_time_fb', 'dwsf_facebook_event' );
        }
        if( $wall_social_feed_options['general']['twitter_status'] == 1 ){
            wp_schedule_event( time() + $time_tw, 'dwsf_custom_time_tw', 'dwsf_twitter_event' );
        }
        if( $wall_social_feed_options['general']['youtube_status'] == 1 ){
            wp_schedule_event( time() + $time_yb, 'dwsf_custom_time_yb', 'dwsf_youtube_event' );
        }
        if(  $wall_social_feed_options['general']['vimeo_status'] == 1 ){
                wp_schedule_event( time() + $time_vm, 'dwsf_custom_time_vm', 'dwsf_vimeo_event' );}
        if( $wall_social_feed_options['general']['instagram_status'] == 1 ){
                wp_schedule_event( time() + $time_igm, 'dwsf_custom_time_igm', 'dwsf_instagram_event' );}
        if(  $wall_social_feed_options['general']['flickr_status'] == 1 ){
                wp_schedule_event( time() + $time_fkr, 'dwsf_custom_time_fkr', 'dwsf_flickr_event' );}
        if(  $wall_social_feed_options['general']['custom_status'] == 1 ){
                wp_schedule_event( time() + $time_custom, 'dwsf_custom_time_rss', 'dwsf_rsscustom_event' );}

        
    }else{
        if( !wp_next_scheduled('dwsf_global_event') ){
            wp_schedule_event( time() + $time, 'dwsf_custom_time', 'dwsf_global_event' );
        }
    }
    /*
        dwsf_flickr_event
    */
}
/**
 *  Fucntion to setup for cron job
 */
global $wall_social_feed;
$wall_social_feed = new dwsf_social_feed();

function dwsf_do_this_global_time() {
    global $wall_social_feed;
    $wall_social_feed->dwsf_get_feed_data();
}
add_action('dwsf_global_event', 'dwsf_do_this_global_time');

function dwsf_do_this_facebook_time() {
    global $wall_social_feed;
    $wall_social_feed->dwsf_get_feed_data_facebook();
}
add_action('dwsf_facebook_event', 'dwsf_do_this_facebook_time');

function dwsf_do_this_twitter_time() {
    global $wall_social_feed;
    $wall_social_feed->dwsf_get_feed_data_twitter();
}
add_action('dwsf_twitter_event', 'dwsf_do_this_twitter_time');

function dwsf_do_this_youtube_time() {
    global $wall_social_feed;
    $wall_social_feed->dwsf_get_feed_data_youtube();
}
add_action('dwsf_youtube_event', 'dwsf_do_this_youtube_time');

function dwsf_do_this_vimeo_time() {
    global $wall_social_feed;
    $wall_social_feed->dwsf_get_feed_data_vimeo();
}
add_action('dwsf_vimeo_event', 'dwsf_do_this_vimeo_time');

function dwsf_do_this_instagram_time() {
    global $wall_social_feed;
    $wall_social_feed->dwsf_get_feed_data_instagram();
}
add_action('dwsf_instagram_event', 'dwsf_do_this_instagram_time');

function dwsf_do_this_flickr_time() {
    global $wall_social_feed;
    $wall_social_feed->dwsf_get_feed_data_flickr();
}
add_action('dwsf_flickr_event', 'dwsf_do_this_flickr_time');
function dwsf_do_this_custom_time() {
    global $wall_social_feed;
    $wall_social_feed->dwsf_get_custom_feeds_cron_job();
}
add_action('dwsf_rsscustom_event', 'dwsf_do_this_custom_time');
/**/

// add custom time to cron
function dwsf_cron_schedules( $schedules  ) {
$wall_social_feed_options = get_option('wall_social_feed_options');

    $time = isset($wall_social_feed_options['general']['cron_times']) ? $wall_social_feed_options['general']['cron_times'] : 3600;
    $time_fb = isset($wall_social_feed_options['general']['fb_cron_time']) ? $wall_social_feed_options['general']['fb_cron_time'] : 3600;
    $time_tw = isset($wall_social_feed_options['general']['tw_cron_time']) ? $wall_social_feed_options['general']['tw_cron_time'] : 3600;
    $time_yb = isset($wall_social_feed_options['general']['yb_cron_time']) ? $wall_social_feed_options['general']['yb_cron_time'] : 3600;
    $time_vm = isset($wall_social_feed_options['general']['vm_cron_time']) ? $wall_social_feed_options['general']['vm_cron_time'] : 3600;
    $time_igm = isset($wall_social_feed_options['general']['igm_cron_time']) ? $wall_social_feed_options['general']['igm_cron_time'] : 3600;
    $time_fkr = isset($wall_social_feed_options['general']['fkr_cron_time']) ? $wall_social_feed_options['general']['fkr_cron_time'] : 3600;
    $time_custom = isset($wall_social_feed_options['general']['custom_cron_time']) ? $wall_social_feed_options['general']['custom_cron_time'] : 3600;

    $schedules['dwsf_custom_time'] = array(
        'interval' => $time, // seconds
        'display'  => __( 'Custome time' )
    );

    $schedules['dwsf_custom_time_fb'] = array(
        'interval' => $time_fb, // seconds
        'display'  => __( 'Facebook Custome time' )
    );
    $schedules['dwsf_custom_time_tw'] = array(
        'interval' => $time_tw, // seconds
        'display'  => __( 'Twitter Custome time' )
    );
    $schedules['dwsf_custom_time_yb'] = array(
        'interval' => $time_yb, // seconds
        'display'  => __( 'Youtube Custome time' )
    );
    $schedules['dwsf_custom_time_vm'] = array(
        'interval' => $time_vm, // seconds
        'display'  => __( 'Vimeo Custome time' )
    );
    $schedules['dwsf_custom_time_igm'] = array(
        'interval' => $time_igm, // seconds
        'display'  => __( 'Instagram Custome time' )
    );
    $schedules['dwsf_custom_time_fkr'] = array(
        'interval' => $time_fkr, // seconds
        'display'  => __( 'Flickr Custome time' )
    );
    $schedules['dwsf_custom_time_rss'] = array(
        'interval' => $time_custom, // seconds
        'display'  => __( 'Rss Custome time' )
    );
    return $schedules;
}
add_filter( 'cron_schedules', 'dwsf_cron_schedules' );

 /*
    Add theme post-format support

    video for youtube feed, image for instagram, status for twitter, aside for facebook

 */
add_theme_support('post-format', array( 'aside', 'image', 'video', 'status') );
add_theme_support('post-thumbnails');
 

 /**
  *     Create option setting for this plugin
  */

function dwsf_settings(){
    add_menu_page('DW Social Feed', 'DW Social Feed', 'manage_options', 'dwsf-social-feed', 'dwsf_settings_template');
    add_submenu_page('wall-social-feed', 'Run feed cron', 'Run feed cron', 'manage_options', 'dwsf-social-feed-cron', 'dwsf_cron_run');
 }
 add_action('admin_menu','dwsf_settings');

function dwsf_cron_run(){
    global $wall_social_feed;
    $wall_social_feed_options = get_option('wall_social_feed_options');

    $type = isset($_GET['type']) ? $_GET['type'] : '';
    $id = isset($_GET['id']) ? $_GET['id'] : '';

    if( $type && $id ){
        $socials = array( 'facebook', 'twitter', 'youtube', 'flickr', 'vimeo', 'instagram', 'custom');
        if( in_array($type, $socials) ){
            if( array_key_exists($id, $wall_social_feed_options[$type]) ){
                $wall_social_feed->dwsf_get_feed_foreach($type,$id);
            }
        }
    }else{
        $wall_social_feed->dwsf_get_feed_data();
    }

    wp_safe_redirect( site_url() );
}

/**
 *  Get wall social options
 */

function dwsf_get_profile(){
    $type = isset($_GET['type']) ? $_GET['type'] : '';
    $item = isset($_GET['select_item']) ? $_GET['select_item'] : '';
    if( !$type || !$item ){
        wp_send_json_error();
    }
    $wall_social_feed_options = dwsf_get_options();
    $select_item = $wall_social_feed_options[$type][$item];
    wp_send_json_success( array(
        'item'  =>  $select_item
    ) );
}
add_action( 'wp_ajax_dwsf-get-profile', 'dwsf_get_profile' );

/*Ajax for create new item */
function dwsf_create_item(){
    $result = new stdClass();

    $wp_nonce = isset($_POST['wp_nonce']) ? $_POST['wp_nonce'] : '';
    $type = isset($_POST['type']) ? $_POST['type'] : '';
    global $wall_social_feed_options;

    if( $wp_nonce && $type && wp_verify_nonce($wp_nonce, $type.'_create_item') ){

        $username = isset( $_POST['username'] ) ? $_POST['username'] : '';
        if( $username  ){
            $label = $username;
            $username = sanitize_title($username);
            $user_lower_case = strtolower($username);
            $array_lower_case = array_change_key_case( $wall_social_feed_options[$type],CASE_LOWER );

            if( !array_key_exists( $user_lower_case, $array_lower_case ) ){
                
                $cat = get_term_by('name', $type, 'category');
                 
                if( $cat ){ 
                    $catID=$cat->term_id; 
                }else{
                    $catID = 0;
                }
                if( in_array($type, array( 'instagram','flickr' )) ){
                    $use_image = 1;
                }else{
                    $use_image = 0;
                }
                if( $type == 'custom' ){
                    $source_text = 'Rss';
                }elseif($type == 'youtube'){
                    $source_text = 'YouTube';
                }else{
                    $source_text = ucfirst($type);
                }
                $wall_social_feed_options[$type][$username] = array(
                    'label' => $label,
                    'status' => 'enable',
                    'query' => '',
                    'vtype' => ( isset($_POST['vtype']) ) ? $_POST['vtype'] : '',
                    'retweet' => ( isset($_POST['retweet']) ) ? $_POST['retweet'] : 0,
                    'category' => $catID,
                    'update_post' => 0,
                    'limit' => 20,
                    'use_image' => $use_image,
                    'width_image_limit' => 200,
                    'height_image_limit' => 200,
                    'posttype' => 'post',
                    'author' => 1,
                    'video_embed_width' => 200,
                    'video_embed_height'    => 200,
                    'video_loop' => 0,
                    'video_autoplay' => 0,
                    'source_text' => '( From '.$source_text.' )'
                );

                update_option('wall_social_feed_options', $wall_social_feed_options);
                $result->status = 'success';
                $result->msg = $username;
            }else{
                $result->status = 'error';
                $result->msg = 'This profile name is existing.';
            }
        }else{ 
            $result->status = 'error'; 
            $result->msg = 'Please enter '.$type;
        }
    }else{ 
        $result->status = 'error';
        $result->msg = 'Cheating huh? 2';
    }

    echo json_encode($result);
    exit(0);
}
add_action('wp_ajax_wall_social_feed_create_item','dwsf_create_item');


function dwsf_custom_setting($value){
    if( in_array($value, array( 60, 300, 600, 1800, 3600, 10800, 14400, 21600, 43200, 86400) ) ){
        return true;
    }
    return false;
}

/**
 * Enqueue script for DW Social Feed Backend 
 * @return void
 */
function dwsf_admin_enqueue_scripts(){
    wp_enqueue_style( 'wall_social_feed_css', DWSF_URL . 'assets/css/style.css' );

    wp_enqueue_script( 'jquery' );
    wp_enqueue_script('jquery-tooltip', DWSF_URL .'assets/js/jquery-tooltip.js', array('jquery') );
    wp_enqueue_script('wall_social_feed_javascript', DWSF_URL .'assets/js/javascript.js', array('jquery') );

    wp_localize_script( 'wall_social_feed_javascript', 'dwsf', array(
        'optionPage'    => admin_url( '?page=dwsf-social-feed' ),
        'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
        'pluginUrl'     => DWSF_URL
    ) );
}
add_action( 'admin_enqueue_scripts', 'dwsf_admin_enqueue_scripts' );

/* Function ajax to get fb id for feed query */
function dwsf_facebook_id(){
    $result = new stdClass();

    $query = isset($_GET['query']) ? $_GET['query'] : '';
    if( $query )
    { 
        $result->facebook_id = dwsf_social_feed::fetch_id_facebook( $query  );
    }else{
        $result->error = true;
    }
    echo json_encode( $result );
    exit(0);
}
add_action('wp_ajax_get_facebook_id','dwsf_facebook_id');


?>