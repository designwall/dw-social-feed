<div id="wp_sf_main" class="postbox">
    <h3><?php _e('DW Social Feed','dwsf') ?></h3>
    <div class="wrap">
        <ul id="wall-menu-tabs" class="float_left">
            <li id="tab-general" class="tab"><span><?php _e('General','dwsf') ?></span></li>
            <li id="tab-facebook" class="tab"><span><?php _e('Facebook','dwsf') ?></span></li>
            <li id="tab-twitter" class="tab"><span><?php _e('Twitter','dwsf') ?></span></li>
            <li id="tab-youtube" class="tab"><span><?php _e('YouTube','dwsf') ?></span></li>
            <li id="tab-vimeo" class="tab"><span><?php _e('Vimeo','dwsf') ?></span></li>
            <li id="tab-instagram" class="tab"><span><?php _e('Instagram','dwsf') ?></span></li>
            <li id="tab-flickr" class="tab"><span><?php _e('Flickr','dwsf') ?></span></li>
            <li id="tab-custom" class="tab"><span><?php _e('Rss feed', 'dwsf') ?></span></li>
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
                        <?php  
                            $schedules = wp_get_schedules();
                            foreach ($schedules as $schedule) :
                        ?>
                            <option <?php selected($global_schedule_time, $schedule['interval']); ?> value="<?php echo $schedule['interval']; ?>"><?php echo $schedule['display'] ?></option>
                        <?php endforeach; ?>
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
                                    <?php  
                                        $schedules = wp_get_schedules();
                                        foreach ($schedules as $schedule) :
                                    ?>
                                        <option <?php selected($schedule_time, $schedule['interval']); ?> value="<?php echo $schedule['interval']; ?>"><?php echo $schedule['display'] ?></option>
                                    <?php endforeach; ?>
                                    <option <?php echo  !dwsf_custom_setting($schedule_time) ? 'selected="selected"' : ''; ?> value="-1">Custom time</option>
                                </select>
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
                    <br /> <span class="hint"><?php _e('Select source for import content automatically from social media sites.','dwsf') ?></span>
                </p>
                
                <div class="twitter-api-key">
                    <h4><strong><?php _e('Twitter API Setting','dwsf') ?></strong></h4>
                    <p>
                        <label for="twitter-consumer-key"><?php _e('Twitter API Key','dwsf') ?> <span class="description">(<a href="http://www.designwall.com/guide/dw-twitter-plugin/#setup_plugin" target="_blank" title="<?php _e('Get API guide','dwsf') ?>" >?</a>)</span></label>
                        <input type="text" class="widefat" id="twitter-consumer-key" name="twitter-consumer-key" value="<?php echo $wall_social_feed_options['general']['twitter_consumer_key'] ?>">
                    </p>
                    <p>
                        <label for="twitter-consumer-secret"><?php _e('Twitter API Secret','dwsf') ?> <span class="description">(<a href="http://www.designwall.com/guide/dw-twitter-plugin/#setup_plugin" target="_blank" title="<?php _e('Get API guide','dwsf') ?>" >?</a>)</span></label>
                        <input type="text" class="widefat" id="twitter-consumer-secret" name="twitter-consumer-secret" value="<?php echo $wall_social_feed_options['general']['twitter_consumer_secret'] ?>">
                    </p>
                </div>
                
                <div class="instagram-api-id">
                    <h4><strong><?php _e('Instagram API', 'dwsf') ?></strong></h4>
                    <p>
                        <label for="instagram-client-id"><?php _e('Client ID','dwsf') ?> <span class="description">(<a href="http://instagram.com/developer/" target="_blank" title="<?php _e('How to get API Client ID','dwsf') ?>" >?</a>)</span></label>
                        <input type="text" class="widefat" id="instagram-client-id" name="instagram-client-id" value="<?php echo isset($wall_social_feed_options['general']['instagram-client-id']) ? $wall_social_feed_options['general']['instagram-client-id'] : ''; ?>">
                    </p>
                </div>

                <?php wp_nonce_field('general_setting', 'cron_wpnonce'); ?>
                <input type="submit" name="cron-run" value="<?php _e('Save option'); ?>" class="button-primary" />
                <?php if( isset($message) && $message ){ 
                    echo '<p>'.__('If you have not any profile yet, please enter at least one profile ','dwsf').'('.$message.')</p>'; 
                } ?>
            </form>
        </div>

        <?php include_once DWSF_PATH . 'templates/setting-tab.php'; ?>
    
        <div style="clear:both"></div>    
    </div>
    <div style="clear:both"></div>
</div>