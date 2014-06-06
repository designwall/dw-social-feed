<?php 
    global $dw_social_feed;
    $wall_social_feed_options = $dw_social_feed->get_options();
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
            if( 'twitter' == $key 
                && ( !$wall_social_feed_options['general']['twitter_consumer_key'] 
                    || ! $wall_social_feed_options['general']['twitter_consumer_secret'] )
            ) {
        ?>
        <div class="error">
            <p>
                <?php 
                    printf(
                        '%s. %s <a href="%s">%s</a> %s.',
                        __('Twitter API Key was not config','dwsf'),
                        __('Goto','dwsf'),
                        admin_url( '?page=dwsf-social-feed' ),
                        __('DW Social Feed General Settings','dwsf'),
                        __('to add your api keys','dwsf')
                    );
                ?>
            </p>
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
                            <option selected="selected" value=""><?php _e('Username','dwsf') ?></option>
                            <option value="album"><?php _e('Album','dwsf') ?></option>
                            <option  value="group"><?php _e('Group','dwsf') ?></option>
                            <option value="channel"><?php _e('Channel','dwsf') ?></option>
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

                <p><label class="hasTip" title="If Yes, the plugin will automatically extract image from the Url that user shared in post then set it into feature image of post." for=""><?php _e('Fetch Image ') ?></label>
                    <select class="tiny" id="<?php echo $key ?>_item_useimage" name="<?php echo $key ?>_item_useimage">
                    <?php $obj_item_useimage = isset($obj_item['use_image']) ? $obj_item['use_image'] : 0; ?>
                        <option <?php if( $obj_item_useimage ) echo 'selected="selected"'; ?> value="1">yes</option>
                        <option <?php if( !$obj_item_useimage ) echo 'selected="selected"'; ?> value="0">no</option>
                    </select>
                </p>

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
                <?php endif; ?>


                <?php wp_nonce_field($key.'_item_update', $key.'_item_update_nonce'); ?>
                <p><input type="submit" value="<?php _e('Update Profile'); ?>" class="button-primary" name="<?php echo $key ?>_item_update" /> &nbsp; 
                <a id="<?php echo $key ?>_run_cron_for_each" class="button" style="float:right; 
                <?php if( $obj_item['query'] ){ ?> display:block; <?php } else{ ?> display:none; <?php } ?>" href='<?php echo admin_url( 'admin-ajax.php?action=dwsf-do-event-'.$key ); ?>'><?php _e('Run now','dwsf') ?></a>
                </p>
            </form>
        </div><?php } ?>
    </div>
<?php } ?>