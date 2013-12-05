<?php  
function dwsf_init_settings(){
    global $wall_social_feed_options;
    $wall_social_feed_options = get_option( 'wall_social_feed_options' );
}
add_action( 'init', 'dwsf_init_settings' );

function dwsf_cron_run_now(){

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
        'youtube_status'                =>  $_POST['active-youtube'],
        'vimeo_status'                  =>  $_POST['active-vimeo'],
        'instagram_status'              =>  $_POST['active-instagram'],
        'flickr_status'                 =>  $_POST['active-flickr'],
        'custom_status'                 =>  $_POST['active-custom']
    );
    
    global $wall_social_feed_options;
    $wall_social_feed_options['general'] = $general;
    update_option('wall_social_feed_options', $wall_social_feed_options);

    dwsf_active();
    wp_safe_redirect( add_query_arg( 'page', 'dwsf-social-feed', admin_url() ) );
}
add_action( 'admin_init', 'dwsf_cron_run_now' );

function dwsf_update_profile(){
    global $wall_social_feed_options, $dwsf_events;

    if( isset( $_POST ) && isset( $_POST['item_type'] ) ){
        $item_type = $_POST['item_type'];
        $item_types = array('facebook', 'twitter', 'vimeo', 'flickr', 'youtube', 'instagram', 'custom');

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
add_action( 'admin_init', 'dwsf_update_profile' );

function dwsf_settings_template(){
    global $wall_social_feed_options, $dwsf_events;
    ?>
    <div id="wp_sf_main" class="postbox">
        <h3>DW Social Feed</h3>
        <div class="wrap">
            <ul id="wall-menu-tabs" class="float_left">
                <li id="tab-general" class="tab"><span>General</span></li>
                <li id="tab-facebook" class="tab"><span>Facebook</span></li>
                <li id="tab-twitter" class="tab"><span>Twitter</span></li>
                <li id="tab-youtube" class="tab"><span>YouTube</span></li>
                <li id="tab-vimeo" class="tab"><span>Vimeo</span></li>
                <li id="tab-instagram" class="tab"><span>Instagram</span></li>
                <li id="tab-flickr" class="tab"><span>Flickr</span></li>
                <li id="tab-custom" class="tab"><span>Rss feed</span></li>
            </ul>

            <!-- Tab for general setting -->
            <div id="main-tab-general" class="tab-content main-tab float_left hide" >
                <form name="wall-general-setting" action="" method="post" accept-charset="utf-8">

                    <p><label class="hasTip" title="Select an real URL service to extract original url from Shorter URL (E.a: bit.ly)"><?php _e('URL converter service: '); ?></label>
                        <select class="tiny" name="converter-server" >
                            <option <?php echo $wall_social_feed_options['general']['converter_server'] == 'untiny.com' ? 'selected="selected" ' : ''; ?> value="untiny.com">untiny.com</option>
                            <option <?php echo $wall_social_feed_options['general']['converter_server'] == 'realurl.org' ? 'selected="selected" ' : ''; ?> value="realurl.org">realurl.org</option>
                        </select><br />
                        <span class="hint"> </span>
                    </p>


                    <p><label class="hasTip" title=" The time interval(in seconds) for plugin to check and pull more contents from social media sites" ><?php _e('Schedule times: ') ?></label>
                        <?php $global_schedule_time = $wall_social_feed_options['general']['cron_times']; ?>
                        <input type="text" id="cron-times" name="cron-times" value="<?php echo $global_schedule_time; ?>" class="tiny <?php if( dwsf_custom_setting($global_schedule_time) ) echo 'hide'; ?>" />
                        <select data-storage="cron-times" name="pre_setting_time" id="pre_setting_time" class="custom_cron_time_select" >
                            <option <?php selected($global_schedule_time, 60); ?> value="60">Every minute</option>
                            <option <?php selected($global_schedule_time, 300); ?> value="300">Every 5 minutes</option>
                            <option <?php selected($global_schedule_time, 600); ?> value="600">Every 10 minutes</option>
                            <option <?php selected($global_schedule_time, 1800); ?> value="1800">Every 30 minutes</option>
                            <option <?php selected($global_schedule_time, 3600); ?> value="3600">Every hour</option>
                            <option <?php selected($global_schedule_time, 10800); ?> value="10800">Every 3 hours</option>
                            <option <?php selected($global_schedule_time, 14400); ?> value="14400">Every 4 hours</option>
                            <option <?php selected($global_schedule_time, 21600); ?> value="21600">Every 6 hours </option>
                            <option <?php selected($global_schedule_time, 43200); ?> value="43200">Every 12 hours</option>
                            <option <?php selected($global_schedule_time, 86400); ?> value="86400">Every day</option>
                            <option <?php echo !dwsf_custom_setting($global_schedule_time) ? 'selected="selected"' : ''; ?> value="-1">Custom time</option>
                        </select><br />
                        <span class="hint"></span>
                    </p>
                    <?php $use_custom_time = isset($wall_social_feed_options['general']['use_custom_time']) ? $wall_social_feed_options['general']['use_custom_time'] : 0; ?>
                    <p><label for="dwsf_use_custom_time"><input type="checkbox" <?php echo $use_custom_time ? 'checked="checked"' : ''; ?> name="use_custom_time" id="dwsf_use_custom_time" value="1" > <span class="hint"><?php _e('Use custom time for each social media sites. ') ?></span></label></p>

                    <table class="custom_cron_time_box" <?php echo $use_custom_time ? '' : 'style="display:none"'; ?> >
                        <tbody>
                            <?php
                                $socials = array( 
                                        'fb' => 'Facebook',
                                        'tw' => 'Twitter',
                                        'yb' => 'YouTube',
                                        'vm' => 'Vimeo',
                                        'igm' => 'Instagram',
                                        'fkr' => 'Flickr',
                                        'custom' => 'RSS'
                                );
                                foreach ($socials as $key => $value) {
                                    $schedule_time = isset($wall_social_feed_options['general'][$key.'_cron_time']) ? $wall_social_feed_options['general'][$key.'_cron_time'] :  $global_schedule_time ;
                            ?>
                            <tr>
                                <td valign="top">
                                    <label for="<?php echo $key ?>_cron_time_value"><?php _e($value.' schedules time: ') ?>
                                    <input type="text" name="<?php echo $key ?>_cron_time_value" id="<?php echo $key ?>_cron_time_value" value="<?php echo $schedule_time ?>" <?php echo $use_custom_time ? '' : 'disabled="disabled"'; ?> placeholder="<?php _e('interval(in seconds)'); ?>" class="tiny <?php echo dwsf_custom_setting($schedule_time) ? 'hide' : ''; ?> custom_cron_time" >
                                </td>
                                <td>
                                    <select data-storage="<?php echo $key ?>_cron_time_value" name="<?php echo $key ?>_pre_setting_time" id="<?php echo $key ?>_pre_setting_time" class="custom_cron_time_select" >
                                        <option <?php selected($schedule_time, 60); ?> value="60">Every minute</option>
                                        <option <?php selected($schedule_time, 300); ?> value="300">Every 5 minutes</option>
                                        <option <?php selected($schedule_time, 600); ?> value="600">Every 10 minutes</option>
                                        <option <?php selected($schedule_time, 1800); ?> value="1800">Every 30 minutes</option>
                                        <option <?php selected($schedule_time, 3600); ?> value="3600">Every hour</option>
                                        <option <?php selected($schedule_time, 10800); ?> value="10800">Every 3 hours</option>
                                        <option <?php selected($schedule_time, 14400); ?> value="14400">Every 4 hours</option>
                                        <option <?php selected($schedule_time, 21600); ?> value="21600">Every 6 hours </option>
                                        <option <?php selected($schedule_time, 43200); ?> value="43200">Every 12 hours</option>
                                        <option <?php selected($schedule_time, 86400); ?> value="86400">Every day</option>
                                        <option <?php echo  !dwsf_custom_setting($schedule_time) ? 'selected="selected"' : ''; ?> value="-1">Custom time</option>
                                    </select>
                                    <?php  
                                        $next_time = wp_next_scheduled( $dwsf_events[$key]['event'] );
                                        if( $next_time ) {
                                            printf('<br><span class="hint">%s : <strong>%s</strong> (%d %s)</span>',
                                                __('Next run time start at'),
                                                date_i18n('Y-m-d G:i:s', $next_time),
                                                $next_time - time(),
                                                __('seconds left','dwsf')
                                                );
                                        }
                                    ?>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table> 
                    <p>
                        <label style="width:100px" class="hasTip" title="Enable get feed from Facebook" ><input type="checkbox" name="active-facebook" value="active" <?php echo ( isset($wall_social_feed_options['general']['facebook_status']) && $wall_social_feed_options['general']['facebook_status'] ) ? 'checked="checked" ' : ''; ?> > <?php _e('Facebook') ?></label>
                        <label style="width:100px" class="hasTip" title="Enable get feed from Twitter" ><input  type="checkbox" name="active-twitter" value="active" <?php echo $wall_social_feed_options['general']['twitter_status'] ? 'checked="checked" ' : ''; ?> > <?php _e('Twitter') ?></label>
                        <label style="width:100px" class="hasTip" title="Enable get feed from Youtube" ><input type="checkbox" name="active-youtube" value="active" <?php echo $wall_social_feed_options['general']['youtube_status'] ? 'checked="checked" ' : ''; ?> > <?php _e('YouTube') ?></label>
                        <label style="width:100px" class="hasTip" title="Enable get feed from Vimeo" ><input type="checkbox" name="active-vimeo" value="active" <?php echo $wall_social_feed_options['general']['vimeo_status'] ? 'checked="checked" ' : ''; ?> > <?php _e('Vimeo') ?></label>
                        <label style="width:100px" class="hasTip" title="Enable get feed from Instagram" ><input type="checkbox" name="active-instagram" value="active" <?php echo $wall_social_feed_options['general']['instagram_status'] ? 'checked="checked" ' : ''; ?> > <?php _e('Instagram') ?></label>
                        <label style="width:100px" class="hasTip" title="Enable get feed from Flickr" ><input type="checkbox" name="active-flickr" value="active" <?php echo $wall_social_feed_options['general']['flickr_status'] ? 'checked="checked" ' : ''; ?> > <?php _e('Flickr') ?></label>
                        <label style="width:100px" class="hasTip" title="Enable get feed from custom rss feed" ><input type="checkbox" name="active-custom" value="active" <?php echo ( isset($wall_social_feed_options['general']['custom_status']) && $wall_social_feed_options['general']['custom_status'] ) ? 'checked="checked" ' : ''; ?> > <?php _e('Rss') ?></label>
                        <br /> <span class="hint">Select source for import content automatically from social media sites.</span>
                    </p>
                    
                    <div class="twitter-api-key">
                        <p>
                            <label for="twitter-consumer-key">Twitter Consumer Key <span class="description">(?)</span></label>
                            <input type="text" class="widefat" id="twitter-consumer-key" name="twitter-consumer-key" value="<?php echo $wall_social_feed_options['general']['twitter_consumer_key'] ?>">
                        </p>
                        <p>
                            <label for="twitter-consumer-secret">Twitter Consumer Secret <span class="description">(?)</span></label>
                            <input type="text" class="widefat" id="twitter-consumer-secret" name="twitter-consumer-secret" value="<?php echo $wall_social_feed_options['general']['twitter_consumer_secret'] ?>">
                        </p>
                    </div>
                    <?php wp_nonce_field('general_setting', 'cron_wpnonce'); ?>
                    <input type="submit" name="cron-run" value="<?php _e('Save option'); ?>" class="button-primary" />
                    <a style="float:right" class="button" href="<?php echo get_admin_url().'admin.php?page=dwsf-social-feed-cron'; ?>"><?php _e('Run now') ?></a>
                    <?php if( isset($message) && $message ){ 
                        echo '<p>If you have not any profile yet, please enter at least one profile ('.$message.')</p>'; 
                    } ?>
                </form>
            </div>

            <?php dwsf_create_tab(); ?>
        
            <div style="clear:both"></div>    
        </div>
        <div style="clear:both"></div>
    </div>

    <?php
}

function dwsf_create_tab(){
    $wall_social_feed_options = dwsf_get_options();
    foreach ($wall_social_feed_options as $key => $options) {
        if( $key == 'general' ){ continue; }

        $obj_item_id = null;
        if( $options ) {
            foreach( $options as $query => $item ){
                $obj_item = $item;
                $obj_item_id = $query;
                break;
            }
        }
    ?>
    <div id="main-tab-<?php echo $key ?>" class="tab-content social-tab float_left hide" >
        <?php 
            if( ! $wall_social_feed_options['general']['twitter_consumer_key'] 
                || ! $wall_social_feed_options['general']['twitter_consumer_secret'] 
            )  {
        ?>
        <div class="error">
            <p>Twitter API Key was not config. Goto <a href="<?php echo admin_url( '?page=dwsf-social-feed' ); ?>">DW Social Feed General Settings</a> to add your api keys.</p>
        </div>
        <?php } ?>
        <div class="feed-user float_left">
            <ul id="<?php echo $key ?>_items" >
                <?php $btn_to_left = ''; ?>
                <?php if( $options ){
                    $i = 0;
                    foreach ( $options as $query => $item ) {
                        $i++;
                        $label = isset($item['label']) ? $item['label'] : $query;
                        $selected= ( $i == 1 ) ? ' active' : '';
                        echo '<li class="dwsf-profile-tab '.$query.' '.$selected.'" data-query="'.$query.'" data-key="'.$key.'" >'.$label.'<a href="#">x</a></li>';
                    } 
                }else{
                    $btn_to_left = 'style="float:left"';
                } ?>
            </ul>
            <input type="button" class="button" value="Add new <?php echo ucfirst($key); ?> profile" <?php echo $btn_to_left; ?> />
            <div class="add_new_social">
                <form onsubmit="return create_item(this)" action="" id="<?php echo $key ?>_create_item" name="<?php echo $key ?>_create_item" method="post" >
                    <p>
                        <input type="text" id="<?php echo $key ?>_user" name="<?php echo $key ?>_user" value="" placeholder="<?php echo $key ?>" class="medium" />
                        <?php if($key=='vimeo'): ?> 
                        <select class="tiny" name="vimeo_item_vtype" id="vimeo_item_vtype_add" class="">
                            <option selected="selected" value=""><?php _e('Username') ?></option>
                            <option value="album"><?php _e('Album') ?></option>
                            <option  value="group"><?php _e('Group') ?></option>
                            <option value="channel"><?php _e('Channel') ?></option>
                        </select> 
                        <?php endif;?>
                        <input type="submit" name="btn_<?php echo $key ?>_item_submit" value="Add" />
                        <input type="hidden" name="create_type" id="create_type" value="<?php echo $key ?>" /> 
                        <?php wp_nonce_field($key.'_create_item', $name = $key.'_create_item_wpnonce'); ?>
                    </p>
                </form>
            </div>
            <div style="clear:both"></div>    
        </div>

        
        <?php if( isset( $obj_item_id ) && $obj_item_id ){ ?>
        <div class="feed-user-options float_left">
            <form name="remove_<?php echo $key ?>_item" action="" method="post">
                <input type="hidden" name="item_type" id="item_type" value="<?php echo $key ?>" />
                <p>
                    <input type="hidden" id="<?php echo $key ?>_item_remove" name="<?php echo $key ?>_item_remove" value="<?php echo $obj_item_id; ?>" />
                    <?php wp_nonce_field('remove_'.$key.'_item','remove_'.$key.'_item_nonce'); ?>

                </p>
            </form>
            <form action="" method="post" accept-charset="utf-8">
                <input type="hidden" id="<?php echo $key ?>_item_id" name="<?php echo $key ?>_item_id" value="<?php echo $obj_item_id; ?>" />
                <input type="hidden" name="item_type" id="item_type" value="<?php echo $key ?>" />
                <p><label class="hasTip" title="The enabled status of this profile" ><?php _e('Status') ?></label> 
                    <select class="tiny" name="<?php echo $key; ?>_item_status" id="<?php echo $key; ?>_item_status" >
                    <?php $obj_item_status = isset($obj_item['status']) ? $obj_item['status'] : 'enable'; ?>
                        <option value="disable" <?php if( $obj_item_status == 'disable' ) echo 'selected="selected"'; ?> >Disable</option>
                        <option value="enable" <?php if( $obj_item_status == 'enable' ) echo 'selected="selected"'; ?> >Enable</option>
                    </select>
                </p>

                <?php 

                    switch ($key) {
                        case 'facebook':
                            $toolTip = 'Providing a Facebook page name where module will get content from, and the mapped category where Facebook post will be stored into.';
                            $label = 'Facebook pagename';
                            break;
                        case 'twitter':
                            $toolTip ='Providing a Twitter query string (E.g: by specific account <strong>from:joomlart</strong>, by hashtag <strong>#joomlart</strong>, by mention <strong>@joomlart</strong>)';
                            $label = 'Twitter Search';
                            break;
                        case 'youtube':
                            $toolTip = 'Provide a YouTube username.';
                            $label = 'YouTube Username';
                            break;
                        case 'vimeo':
                            $toolTip = 'Depending on your profile type, you need enter valid name or id of vimeo user, group, channel or album in this field.<br>If you profile type is for:<br>- <strong>User</strong>: you can enter Either the shortcut URL or ID of the user, an email address will NOT work.<br>- <strong>Group</strong>: you can enter Either the shortcut URL or ID of the group.<br>- <strong>Channel</strong>: you must enter the shortcut URL of the channel.<br>- <strong>Album</strong>: you must enter The ID of the album.';
                            $label = 'Vimeo Search';
                            break;
                        case 'instagram':
                            $toolTip = 'You can display Instagram photos from specific user, a tag or get the most popular photos.<br>Tips<br>- Using <strong>@</strong> to filter by username. E.g: @joomlart<br>- Using <strong>#</strong> to filter by tag. E.g: #joomla<br>- Using <strong>[POPULAR]</strong> to get popular photos.';
                            $label = 'Instagram Search';
                            break;
                        case 'flickr':
                            $toolTip = 'Provide a Flickr ID: <br />(E.g: <strong>58736703@N00</strong> ).';
                            $label = 'Flickr ID';
                            break;
                        default:
                            $label = 'Rss URL';
                            $toolTip = 'Rss link E.g: http://feeds.feedburner.com/joomlart/blog';
                            break;
                    }
                ?>
                <p><label class="hasTip" title="<?php echo $toolTip; ?>" for="<?php echo $key; ?>_item_query"><?php _e($label); ?></label>
                <input type="text" id="<?php echo $key; ?>_item_query" name="<?php echo $key; ?>_item_query" value="<?php echo $obj_item['query'] ?>" placeholder="query for search feed" class="medium" /> <a href="javascript:void(0)" data-key="<?php echo $key; ?>" class="button dwsf-verify-feed"><?php _e('Verify'); ?><span class="spinner"></span></a>
                </p>

                <?php if($key=='vimeo'): ?> 
                <p><label class="hasTip" title="Depending on your profile type, you need enter valid name or id of vimeo user, group, channel or album in this field.<br />If you profile type is for:<br />- <strong>User</strong>: you can enter Either the shortcut URL or ID of the user, an email address will NOT work.<br />- <strong>Group</strong>: you can enter Either the shortcut URL or ID of the group.<br />- <strong>Channel</strong>: you must enter the shortcut URL of the channel.<br />- <strong>Album</strong>: you must enter The ID of the album." for=""><?php _e('Type: ') ?></label> 
                    <select class="tiny" name="vimeo_item_vtype" id="vimeo_item_vtype" class="">
                        <?php 
                          $obj_item_vtype = isset($obj_item['vtype']) ? $obj_item['vtype'] : '';
                          $vimeo_types = array("Username" => "","Album"=>'album',"Group"=>'group',"Channel"=>'channel');

                          foreach ($vimeo_types as $vtype => $value) {
                              $option = "<option ";
                              $option .= ($obj_item_vtype == $value)?' selected="selected" ':"";
                              $option .= " value=\"$value\" >$vtype</option>";
                              echo $option;
                          }

                        ?>
                    </select> 
                </p>
                <?php endif; ?>

                <?php $source_text = isset($obj_item['source_text']) ? $obj_item['source_text'] : ''; ?>
                <p><label class="hasTip" title="The text for original link" for="<?php echo $key ?>_item_source_text"><?php _e('Source text'); ?></label>
                <input type="text" id="<?php echo $key ?>_item_source_text" name="<?php echo $key ?>_item_source_text" value="<?php echo $source_text; ?>" class="medium" ></p>
                <hr />

                <p><label class="hasTip" title="Imported item will be imported to this category" for="<?php echo $key ?>_item_category"><?php _e('Category ') ?></label> 
                    <select class="medium" id="<?php echo $key ?>_item_category" name="<?php echo $key ?>_item_category">
                    <?php
                        $args = array( 'hide_empty' => 0 ); 
                        $categories = get_categories($args);
                     
                        foreach( $categories as $category ){
                            $option = '<option ';
                            $option .= $obj_item['category'] == $category->term_id ? 'selected="selected" ' : '';
                            $option .= 'value="'.$category->term_id.'">'.$category->name.'</option>';
                            echo $option;
                        } 
                    ?>
                    </select>
                </p>

                <?php $author = isset($obj_item['author']) ? $obj_item['author'] : 1; ?>
                <p><label class="hasTip" title="Imported item will be related to this author" for="<?php echo $key; ?>_item_postauthor"><?php _e('Author') ?></label>
                <?php $users = get_users(); ?>
                <select class="tiny" name="<?php echo $key; ?>_item_postauthor" id="<?php echo $key; ?>_item_postauthor" class="medium" >
                    <?php foreach ($users as $user ) {
                        $selected = ($user->ID == $author) ? ' selected="selected" ' : '';
                        ?>
                    <option value="<?php echo $user->ID ?>" <?php echo $selected ?> ><?php echo $user->user_nicename ?></option>
                        <?php
                    } ?>
                </select>
                </p>

                <?php $post_types=get_post_types( array('public'=> true) ); ?>
                <?php $cur_posttype = isset($obj_item['posttype']) ? $obj_item['posttype'] : 'post'; ?>
                <p><label class="hasTip" title="Set post type like 'post' or custom post type for imported items" for="<?php echo $key; ?>_item_posttype"><?php _e('Post type ') ?></label>
                    <select class="tiny" id="<?php echo $key; ?>_item_posttype" name="<?php echo $key; ?>_item_posttype">
                        <?php foreach ($post_types as $post_type) {
                            if( in_array($post_type, array('attachment', 'page') ) ){ continue; }
                            $selected = ($post_type == $cur_posttype) ? ' selected="selected" ' : '';
                            ?>
                        <option <?php echo $selected ?> value="<?php echo $post_type ?>"><?php _e($post_type); ?></option>
                            <?php   
                        } ?>
                    </select>
                </p>


                <p><label class="hasTip" title="If Yes, it will update content for existing articles. If not, the system will not update content of existing items."><?php _e("Overide exited post") ?></label> 
                    <select class="tiny" name="<?php echo $key; ?>_item_update_status" id="<?php echo $key; ?>_item_update_status" >
                    <?php $obj_item_update  = isset($obj_item['update_post']) ? $obj_item['update_post'] : 0; ?>
                        <option <?php if( !$obj_item_update )  echo 'selected="selected"'; ?> value="0">no</option>
                        <option <?php if( $obj_item_update ) echo 'selected="selected"'; ?> value="1">yes</option>
                    </select>
                </p>

                <?php if( $key == 'twitter') : ?>
                <?php $retweet = isset($obj_item['retweet']) ? $obj_item['retweet'] : false; ?>
                <p><label class="hasTip" title="Fetch Tweet that is re-posted or not?" for="<?php echo $key; ?>_item_retweet"><?php _e('Fetch Retweet: ') ?> </label>
                    <select class="tiny" name="<?php echo $key; ?>_item_retweet" id="<?php echo $key; ?>_item_retweet" >
                        <option <?php if( !$retweet ) echo 'selected="selected"'; ?> value="0">no</option>
                        <option <?php if( $retweet ) echo 'selected="selected"'; ?> value="1">yes</option>
                    </select>
                </p>
                <?php endif; ?>

                <p><label title="The maximum number of posts to import each time the Cron runs." class="hasTip"><?php _e('Number posts limit ') ?></label>
                    <input type="text" id="<?php echo $key ?>_item_limit" name="<?php echo $key ?>_item_limit" value="<?php echo $obj_item['limit']; ?>" class="medium" />
                </p>

                <?php if( in_array($key, array('facebook','twitter', 'custom') )){ ?>
                <p><label class="hasTip" title="If Yes, the plugin will automatically extract image from the Url that user shared in post then set it into feature image of post." for=""><?php _e('Fetch Image ') ?></label>
                    <select class="tiny" id="<?php echo $key ?>_item_useimage" name="<?php echo $key ?>_item_useimage">
                    <?php $obj_item_useimage = isset($obj_item['use_image']) ? $obj_item['use_image'] : 0; ?>
                        <option <?php if( $obj_item_useimage ) echo 'selected="selected"'; ?> value="1">yes</option>
                        <option <?php if( !$obj_item_useimage ) echo 'selected="selected"'; ?> value="0">no</option>
                    </select>
                </p>
                <?php } ?>

                <?php if( in_array( $key, array( 'facebook', 'twitter') ) ) : ?>
                <p><label class="hasTip" title="Images with less width than mentioned, will not be imported. Helps to have consistent image size to display"><?php _e('Minimum image width (in px) '); ?></label> 
                    <input type="text" name="<?php echo $key; ?>_item_image_width_limit" id="<?php echo $key; ?>_item_image_width_limit" value="<?php echo $obj_item['width_image_limit'] ?>" class="medium" ></p>
                <p>
                    <label class="hasTip" title="Images with less height than mentioned, will not be imported. Helps to have consistent image size to display"><?php _e('Minimum image height (in px)'); ?></label> 
                    <input type="text" name="<?php echo $key; ?>_item_image_height_limit" id="<?php echo $key; ?>_item_image_height_limit" value="<?php echo $obj_item['height_image_limit'] ?>" class="medium" >
                </p>
                <?php endif; ?>

                <?php if( in_array( $key, array( 'youtube', 'vimeo') ) ) : ?>
                <?php
                    $video_embed_width = isset($obj_item['video_embed_width']) ? $obj_item['video_embed_width'] : 200;
                    $video_embed_height = isset($obj_item['video_embed_height']) ? $obj_item['video_embed_height'] : 200;
                    $video_loop = isset($obj_item['video_loop']) ? $obj_item['video_loop'] : 0;
                    $video_autoplay =  isset($obj_item['video_autoplay']) ? $obj_item['video_autoplay'] : 0;
                ?>
                <p><label class="hasTip" title="The Width of video (in pixel, minimum is 200px)"><?php _e('Video embed width (in px) '); ?></label> 
                    <input type="text" name="<?php echo $key; ?>_item_video_embed_width" id="<?php echo $key; ?>_item_video_embed_width" value="<?php echo $video_embed_width; ?>" class="medium" ></p>
                <p>
                    <label class="hasTip" title="The Height of video (in pixel, minimum is 200px)"><?php _e('Video embed height (in px)'); ?></label> 
                    <input type="text" name="<?php echo $key; ?>_item_video_embed_height" id="<?php echo $key; ?>_item_video_embed_height" value="<?php echo $video_embed_height ?>" class="medium" >
                </p>
                <p>
                    <label class="hasTip" title="Enable video loop function"><?php _e('Video Loop '); ?></label> 
                    <input type="radio" name="<?php echo $key; ?>_item_video_loop" <?php echo $video_loop == 1 ? 'checked="checked"' : ''; ?> value="1" >Yes
                    <input type="radio" name="<?php echo $key; ?>_item_video_loop" <?php echo $video_loop == 0 ? 'checked="checked"' : ''; ?> value="0" >No
                </p>
                <p>
                    <label class="hasTip" title="Enable video autoplay function"><?php _e('Video Autoplay '); ?></label> 
                    <input type="radio" name="<?php echo $key; ?>_item_video_autoplay" <?php echo $video_autoplay == 1 ? 'checked="checked"' : ''; ?> value="1" >Yes
                    <input type="radio" name="<?php echo $key; ?>_item_video_autoplay" <?php echo $video_autoplay == 0 ? 'checked="checked"' : ''; ?> value="0" >No
                </p>
                <?php endif; ?>


                <?php wp_nonce_field($key.'_item_update', $key.'_item_update_nonce'); ?>
                <p><input type="submit" value="<?php _e('Update Profile'); ?>" class="button-primary" name="<?php echo $key ?>_item_update" /> &nbsp; 
                <a id="<?php echo $key ?>_run_cron_for_each" class="button" style="float:right; 
                <?php if( $obj_item['query'] ){ ?> display:block; <?php } else{ ?> display:none; <?php } ?>" href='<?php echo get_admin_url().'admin.php?page=dwsf-social-feed-cron&amp;type='.$key.'&amp;id='.$obj_item_id; ?>'><?php _e('Run now') ?></a>
                </p>
            </form>
        </div><?php } ?>
        
    </div>
        <?php } ?>
<?php
}
?>