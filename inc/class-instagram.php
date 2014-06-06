<?php  

class DWSF_Instagram extends DWSF_Feed {
    public $profile; //current_profile
    public $name = 'instagram';
    public $slug = 'igm';
    public $task_hook_name = 'dwsf_twitter_hook';

    public function fetch_content( $profile ) {

        wp_parse_args( $profile, array(
            'limit'                 => 10,
            'status'                => 'publish',
            'post_type'             => 'post',
            'author'                => 1,
            'comment_open'          => 'open', //closed
            'categories'            => '',
            'tags'                  => '',
            'update_exists_post'    => false
        ) );

        $posts = array();

        $keyword = trim($profile['query']);

        $wall_social_feed_options = get_option('wall_social_feed_options');
        $client_id = isset($wall_social_feed_options['general']['instagram-client-id']) ? $wall_social_feed_options['general']['instagram-client-id'] : 0 ;
        
        if( ! $client_id ) {
            return false;
        }
        if(  strpos($keyword, '@') === 0 ) {
            // Get id of instagram username
            $api = 'https://api.instagram.com/v1/users/search?q='.$keyword.'&client_id=' . $client_id;
            $request = wp_remote_get( $api );
            $insta = json_decode(wp_remote_retrieve_body( $request ) );

            if( $insta->meta->code == 200 ) {
                $insta_id = $insta->data[0]->id;
                $api = 'https://api.instagram.com/v1/users/'.$insta_id.'/media/recent/?client_id=' . $client_id;
            } else {
                return false;    
            }
        } else {
            if( strpos($keyword, '#') === 0 ) {
                $keyword = str_replace('#', '', $keyword);
            }
            $api = 'https://api.instagram.com/v1/tags/'.$keyword.'/media/recent?&client_id=' . $client_id;
        }

        // Get XML Documetn from rss url
        $request = wp_remote_get( $api );
        $response = json_decode( wp_remote_retrieve_body( $request ) );
        $images = array();

        if( $response->meta->code == 200 && !empty($response->data) ) {
            
            foreach ( $response->data as $item ) { 
                if( $item->type != 'image' )
                    continue;
                if( $profile['status'] != 'enable' )
                    continue;

                $title = wp_strip_all_tags( $item->caption->text );
                $title = substr($title, 0, 60) . '...';

                $post_name = sanitize_title( $title );
                $post_id = $this->post_name_exists( $post_name );

                if( $post_id && !$profile['update_post'] ) {
                    continue;
                }

                $content = '';
                $content .= '<img src="'.$item->images->standard_resolution->url.'" ><br />';
                $content .= wp_strip_all_tags( $item->caption->text );

                $posts[$item->id] = array(
                    'origin_post'    => $item->caption->text,
                    'post_content'   => $content,
                    'post_title'     => $title,
                    'post_date'      => date( 'Y-m-d H:i:s', $item->created_time ),
                    'meta_fields'    => array(
                        'instagram_link'      => $item->link
                    ),
                    'thumbnails'    => array(
                        'standard'  => $item->images->standard_resolution->url,
                        'low'       => $item->images->low_resolution->url,
                        'thumbnail' => $item->images->thumbnail->url
                    )
                );

                if( $post_id ) {
                    $posts[$item->id]['ID'] = $post_id;
                }

                $posts[$item->id] = array_merge( $posts[$item->id], $this->generate_post_template( $profile ) );

                // if( $item->type == 'image' ) {

                //     $it = new stdClass();
                //     $it->image = $item->images->standard_resolution->url;
                //     $it->link       = $item->link;
                //     $it->desc = $it->title = $item->caption->text;
                //     $it->author     = $item->user->username;
                //     $it->id         = $item->id;

                //     $images[] = $it;
                // }
            }
        } else {
            return false;    
        }

        return $posts;
    }

    public function add_thumbnail( $post_id, $post, $width, $height, $add ) {
        if( $add ) {
            //images
            $img_caption = __('Thumbnail for','dwsf') . ' ' . $post['post_title'];
            $source_images = false;
            if( isset($post['thumbnails']['standard']) ) {
                $source_images = $post['thumbnails']['standard'];
            } 
            
            // Add images sourse
            if( $source_images ){
                $attach_id = $this->add_attachments( $post_id, $source_images, $img_caption);
                set_post_thumbnail( $post_id, $attach_id );
            }
        } else {
            delete_post_thumbnail( $post_id );
        }
    }
}
$GLOBALS['dwsf_instagram'] = new DWSF_Instagram();

?>
