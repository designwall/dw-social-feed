<?php  
/**
 * DW Social Feeds: Youtube
 * Make wordpress cron from all facebook profiles. The auto get content
 */

class DWSF_Youtube extends DWSF_Feed {
	public $profile; //current_profile
	public $name = 'youtube';
	public $slug = 'yb';
	public $task_hook_name = 'dwsf_youtube_hook';

	public function __construct(){
   		parent::__construct();
	}

	// Retrive content from facebook feed and return sanitiziled posts data 
	// return array of post objects
	public function fetch_content( $profile ){
		$profile = wp_parse_args( $profile, array(
			'limit'					=> 10,
			'status'				=> 'publish',
			'post_type'				=> 'post',
			'author'				=> 1,
			'comment_open'			=> 'open', //closed
			'categories' 			=> '',
			'tags'					=> '',
			'update_exists_post'	=> false,
            'video_embed_width'     => 200,
            'video_embed_height'    => 200,
            'video_autoplay'        => false,
            'video_loop'            => false
		) );
		//make a feed link
		if( ! isset($profile['query']) )
			return false;

		$fetch_url = "http://gdata.youtube.com/feeds/api/videos?max-results=".$profile['limit']."&alt=json&format=5&author=".$profile['query'];
		$response_data = json_decode( $this->remote_get_content($fetch_url) ) ;

        $posts = array();
        if( !empty($response_data) && isset($response_data->feed->entry) && is_array($response_data->feed->entry) ) {
        	$count = 0;
        	foreach ($response_data->feed->entry as $entry) {
        		if( $count > $profile['limit'] )
        			break;
        		$count ++; // Limit the number of entries
        		
        		if( $profile['status'] != 'enable' ) 
        			continue;

                $id = str_replace('http://gdata.youtube.com/feeds/api/videos/','',$entry->id->{'$t'});

                $title = $entry->title->{'$t'};

                if( ! $title ) {
                    $title = '#youtube-' . $id;
                }

        		$post_id = $this->post_exists( $title );

        		if( $post_id && !$profile['update_post'] ) {
        			continue;
        		}

                $content = '[embed width="'.$profile['video_embed_width'].'" height="'.$profile['video_embed_height'].'" autoplay="'.( $profile['video_autoplay'] ? 'y' : '').'" loop="'.$profile['video_loop'].'"]'.$entry->link[0]->href.'[/embed]';
                $content .= $this->getYoutubeContent($entry->content->{'$t'});

        		$posts[$id] = array(
        			'origin_post'	 => $entry->content->{'$t'},
					'post_content'   => $content,
					'post_title'     => $title,
					'post_date'      => date( 'Y-m-d H:i:s',strtotime($entry->published->{'$t'}) ),
                    'thumbnails'     => $entry->{'media$group'}->{'media$thumbnail'}
				);  
        		

        		if( $post_id ) {
        			$posts[$id]['ID'] = $post_id;
        		}

        		$posts[$id] = array_merge( $posts[$id], $this->generate_post_template( $profile ) );

        	}
        }
        //var_dump($posts);
        return $posts;
    }

    public function add_thumbnail( $post_id, $post, $width, $height, $add ) {
        if( $add ){
            //images
            $img_caption = 'Thumbnail for ' . $post['post_title'];

            if( isset($post['thumbnails'][0]) ) {
                $source_images = $post['thumbnails'][0]['url'];
            } 
            
            // Add images sourse
            if( $source_images ){
                $attach_id = $this->add_attachments( $post_id, $source_images, $img_caption);
                set_post_thumbnail( $post_id, $attach_id );
            }
        }else{
            delete_post_thumbnail($post_id);
        }
    }

    public function getYoutubeContent($content){
        $pattern = '/<div style=\"font-size: 12px; margin: 3px 0px;\">(.*)<\/div>/';
        preg_match($pattern, $content, $matches);
        if(isset($matches[1])){
            return strip_tags($matches[1]);
        
        }
        return false;
    }
}
$GLOBALS['dwsf_youtube'] = new DWSF_Youtube();

?>