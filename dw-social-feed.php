<?php
/**
  * Plugin Name: DW Social Feed
  * Plugin URI: http://designwall.com/plugins/dw-social-feed/
  * Description: Plugin import content automatically from popular Social media sites
  * Version: 1.0.6
  * Author: the DesignWall team
  * Author URI: http://designwall.com
  * License: GPLv2
  */ 

if( ! defined('DWSF_PATH') ) {
    define( 'DWSF_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
} 

if( ! defined('DWSF_URL') ) {
    define( 'DWSF_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
} 

require_once DWSF_PATH  . 'inc/settings.php';
require_once DWSF_PATH 	. 'inc/class-feed.php';
require_once DWSF_PATH 	. 'inc/class-facebook.php';
require_once DWSF_PATH  . 'inc/class-twitter.php';
require_once DWSF_PATH  . 'inc/class-youtube.php';
require_once DWSF_PATH  . 'inc/class-flickr.php';
require_once DWSF_PATH  . 'inc/class-instagram.php';
require_once DWSF_PATH  . 'inc/class-vimeo.php';
require_once DWSF_PATH  . 'inc/class-rss-feed.php';

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

// When active plugin
function dwsf_activation(){
    global $dw_social_feed;
    $options = $dw_social_feed->get_options();
    update_option('wall_social_feed_options', $options );
}
register_activation_hook(__FILE__, 'dwsf_activation');

class DW_Social_Feed {

 	public function __construct() {
        global $wall_social_feed_options;
        $wall_social_feed_options = $this->get_options();

 		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts') );
		add_filter( 'cron_schedules', array( $this, 'add_more_schedules') );
 	}

    public function get_options(){
        $options = get_option('wall_social_feed_options');
        $options = wp_parse_args( 
            $options, 
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
        return $options;
    }

 	public function enqueue_scripts() {
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

 	public function add_more_schedules( $schedules ) {

 		// we has hourly, twicedaily, daily
 		// need to add more 60, 300, 600, 1800, 10800, 14400, 21600, 43200
 		$schedules['every_minute'] = array(
	        'interval' => 60, // seconds
	        'display'  => __( 'Every minute', 'dwsf' )
	    );
	    $schedules['every_5minutes'] = array(
	        'interval' => 300, // seconds
	        'display'  => __( 'Every 5 minutes', 'dwsf' )
	    );
	    $schedules['every_10minutes'] = array(
	        'interval' => 600, // seconds
	        'display'  => __( 'Every 10 minutes', 'dwsf' )
	    );
	    $schedules['every_30minutes'] = array(
	        'interval' => 1800, // seconds
	        'display'  => __( 'Every 30 minutes', 'dwsf' )
	    );
	    //hourly : exists
	    $schedules['every_3hours'] = array(
	        'interval' => 10800, // seconds
	        'display'  => __( 'Every 3 hours', 'dwsf' )
	    );
	    $schedules['every_4hours'] = array(
	        'interval' => 14400, // seconds
	        'display'  => __( 'Every 4 hours', 'dwsf' )
	    );
	    $schedules['every_6hours'] = array(
	        'interval' => 21600, // seconds
	        'display'  => __( 'Every 6 hours', 'dwsf' )
	    );
	    $schedules['every_12hours'] = array(
	        'interval' => 43200, // seconds
	        'display'  => __( 'Every 12 hours', 'dwsf' )
	    );
	    //daily : exists
	    return $schedules;
 	}

 }

$GLOBALS['dw_social_feed'] = new DW_Social_Feed();