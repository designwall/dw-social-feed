<?php  

function dwsf_custom_setting($value){
    if( in_array($value, array( 60, 300, 600, 1800, 3600, 10800, 14400, 21600, 43200, 86400) ) ){
        return true;
    }
    return false;
}


class DWSF_Settings {
    public function __construct() {
        add_action( 'admin_menu', array($this, 'menus') );
        add_action( 'admin_init', array( $this, 'update' ) );
        add_action( 'wp_ajax_wall_social_feed_create_item', array( $this, 'create_profile') );
        add_action( 'admin_init', array( $this, 'save_general_settings') );
    }
    public function update(){
        global $wall_social_feed_options;

        if( isset( $_POST ) && isset( $_POST['item_type'] ) ){
            $item_type = $_POST['item_type'];
            $item_types = array( 'facebook', 'twitter', 'vimeo', 'flickr', 'youtube', 'instagram', 'custom' );

            if( in_array( $item_type, $item_types ) ){
                /*Remove profile*/
                if( isset( $_POST['remove_'.$item_type.'_item_nonce'] ) ) :
                    if( wp_verify_nonce($_POST['remove_'.$item_type.'_item_nonce'], 'remove_'.$item_type.'_item') ){
                        if( isset( $_POST[$item_type.'_item_remove'] ) ){

                            $item_remove = $_POST[ $item_type.'_item_remove' ];
                            unset ( $wall_social_feed_options[$item_type][ $item_remove ] );
                            update_option('wall_social_feed_options',$wall_social_feed_options);
                        }
                    }
                endif;

                /*Update profile*/
                if( isset( $_POST[$item_type.'_item_update_nonce'] ) ){
                    if( wp_verify_nonce( $_POST[$item_type.'_item_update_nonce'], $item_type.'_item_update') ){

                        $item_id = isset( $_POST[$item_type.'_item_id'] ) ? $_POST[$item_type.'_item_id'] : '';

                        $item_category = isset( $_POST[$item_type.'_item_category'] ) ? $_POST[$item_type.'_item_category'] : 1;

                        $item_limit = isset( $_POST[$item_type.'_item_limit'] ) && is_numeric( $_POST[$item_type.'_item_limit'] ) ? $_POST[$item_type.'_item_limit'] : 20;

                        $item_useimage = isset( $_POST[$item_type.'_item_useimage'])  ? $_POST[$item_type.'_item_useimage'] : 0;

                        $item_status = isset( $_POST[$item_type.'_item_status'] )  ? $_POST[$item_type.'_item_status'] : 0;

                        $item_update = isset( $_POST[$item_type.'_item_update_status'] )  ? $_POST[$item_type.'_item_update_status'] : 0;

                        $item_width_image_limit = isset( $_POST[$item_type.'_item_image_width_limit']) ? $_POST[$item_type.'_item_image_width_limit'] : 0;

                        $item_height_image_limit = isset( $_POST[$item_type.'_item_image_height_limit']) ? $_POST[$item_type.'_item_image_height_limit'] : 0;

                        $item_retweet = isset($_POST[$item_type.'_item_retweet']) ? $_POST[$item_type.'_item_retweet'] : 0;

                        $item_vtype = isset($_POST[$item_type.'_item_vtype']) ? $_POST[$item_type.'_item_vtype'] : '';

                        $item_query = isset($_POST[$item_type.'_item_query']) ? $_POST[$item_type.'_item_query'] :  '';

                        $item_posttype = isset($_POST[$item_type.'_item_posttype']) ? $_POST[$item_type.'_item_posttype'] : 'post';

                        $item_postauthor = isset($_POST[$item_type.'_item_postauthor']) ? $_POST[$item_type.'_item_postauthor'] : 1;

                        $item_video_embed_width = isset($_POST[$item_type.'_item_video_embed_width']) ? $_POST[$item_type.'_item_video_embed_width'] : 200;
                        $item_video_embed_height = isset($_POST[$item_type.'_item_video_embed_height']) ? $_POST[$item_type.'_item_video_embed_height'] : 200;

                        $item_video_loop = isset($_POST[$item_type.'_item_video_loop']) ? $_POST[$item_type.'_item_video_loop'] : 0;
                        $item_video_autoplay = isset($_POST[$item_type.'_item_video_autoplay']) ? $_POST[$item_type.'_item_video_autoplay'] : 0;

                        $item_source_text = isset($_POST[$item_type.'_item_source_text']) ? $_POST[$item_type.'_item_source_text'] : '';


                        if( !$item_id ) return false;
                        $label = $wall_social_feed_options[$item_type][$item_id]['label'];

                        $wall_social_feed_options[$item_type][$item_id] = array(
                            'label' => $label,
                            'status' => $item_status,
                            'query' => $item_query,
                            'update_post' => $item_update,
                            'category' => $item_category,
                            'limit' => $item_limit,
                            'use_image' => $item_useimage,
                            'width_image_limit' => $item_width_image_limit,
                            'height_image_limit' => $item_height_image_limit,
                            'retweet' => $item_retweet,
                            'vtype' => $item_vtype,
                            'posttype' => $item_posttype,
                            'author' => $item_postauthor,
                            'video_loop' => $item_video_loop,
                            'video_autoplay' => $item_video_autoplay,
                            'video_embed_width' => $item_video_embed_width,
                            'video_embed_height' => $item_video_embed_height,
                            'source_text' => $item_source_text
                        );
                        update_option('wall_social_feed_options', $wall_social_feed_options);
                    }
                }
                // END
            }
        } //#END SUBMIT 
    }
    public function menus(){
        add_menu_page(
            __('DW Social Feed','dwsf'), 
            __('DW Social Feed','dwsf'), 
            'manage_options', 
            'dwsf-social-feed', 
            array( $this, 'display' )
        );
        add_submenu_page(
            'wall-social-feed', 
            __('Run feed cron', 'dwsf'),
            __('Run feed cron', 'dwsf'), 
            'manage_options', 
            'dwsf-social-feed-cron', 
            'dwsf_cron_run'
        );
    }

    public function display() {
        global $wall_social_feed_options;
        include_once DWSF_PATH . 'templates/setting-template.php';
    }

    /*Ajax for create new item */
    public function create_profile(){
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

    public function save_general_settings(){

        if( ! isset( $_POST['cron-run'] ) ){
            return false;
        }

        if( ! isset($_POST['cron_wpnonce']) || ! wp_verify_nonce($_POST['cron_wpnonce'], 'general_setting') ){
            return false;
        }

        $_POST = wp_parse_args( $_POST, array(
            'converter-server'      => 'untiny.com',
            'cron-times'            => 3600,
            'active-facebook'       => false,
            'active-twitter'        => false,
            'active-youtube'        => false,
            'active-vimeo'          => false,
            'active-instagram'      => false,
            'active-flickr'         => false,
            'active-custom'         => false,
            'use_custom_time'       => 0
        ) );

        $_POST = wp_parse_args( $_POST, array(
            'fb_cron_time_value'        => $_POST['cron-times'],
            'tw_cron_time_value'        => $_POST['cron-times'],
            'yb_cron_time_value'        => $_POST['cron-times'],
            'igm_cron_time_value'       => $_POST['cron-times'], 
            'vm_cron_time_value'        => $_POST['cron-times'], 
            'fkr_cron_time_value'       => $_POST['cron-times'],
            'custom_cron_time_value'    => $_POST['cron-times']
        ) );

        $general = array( 
            'converter_server'              =>  $_POST['converter-server'],
            'cron_times'                    =>  $_POST['cron-times'],
            'use_custom_time'               =>  $_POST['use_custom_time'],
            'fb_cron_time'                  =>  $_POST['fb_cron_time_value'],
            'tw_cron_time'                  =>  $_POST['tw_cron_time_value'],
            'yb_cron_time'                  =>  $_POST['yb_cron_time_value'],
            'vm_cron_time'                  =>  $_POST['vm_cron_time_value'],
            'igm_cron_time'                 =>  $_POST['igm_cron_time_value'],
            'fkr_cron_time'                 =>  $_POST['fkr_cron_time_value'],
            'custom_cron_time'              =>  $_POST['custom_cron_time_value'],
            'facebook_status'               =>  $_POST['active-facebook'],
            'twitter_status'                =>  $_POST['active-twitter'],
            'twitter_consumer_key'          =>  $_POST['twitter-consumer-key'],
            'twitter_consumer_secret'       =>  $_POST['twitter-consumer-secret'],
            'instagram-client-id'           =>  $_POST['instagram-client-id'],
            'youtube_status'                =>  $_POST['active-youtube'],
            'vimeo_status'                  =>  $_POST['active-vimeo'],
            'instagram_status'              =>  $_POST['active-instagram'],
            'flickr_status'                 =>  $_POST['active-flickr'],
            'custom_status'                 =>  $_POST['active-custom']
        );
        
        global $wall_social_feed_options;
        $wall_social_feed_options['general'] = $general;
        update_option('wall_social_feed_options', $wall_social_feed_options);
        $this->reset_scheduled();

        $this->update_schedules();
        wp_safe_redirect( add_query_arg( 'page', 'dwsf-social-feed', admin_url() ) );
    }
    public function update_schedules() {
        $wall_social_feed_options = get_option('wall_social_feed_options');
    }

    public function reset_scheduled() {
        global $dwsf_facebook, $dwsf_twitter;
        $dwsf_facebook->reschedule_event();
        $dwsf_twitter->reschedule_event();
    }
}
$GLOBALS['dwsf_settings'] = new DWSF_Settings();


?>