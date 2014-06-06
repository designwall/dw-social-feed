<?php  
/**
 * DW Social Feeds: Vimeo
 * Make wordpress cron from all facebook profiles. The auto get content
 */
class DWSF_Vimeo extends DWSF_Feed {
	public $profile; //current_profile
	public $name = 'vimeo';
	public $slug = 'vm';
	public $task_hook_name = 'dwsf_vimeo_hook';

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
			'vtype'					=> false,
            'video_embed_width'     => 200,
            'video_embed_height'    => 200,
            'video_autoplay'        => false,
            'video_loop'            => false
		) );
		//make a feed link
		if( ! isset($profile['query']) )
			return false;

		$profile['vtype'] = ! $profile['vtype'] ? '/' : "/$vtype/" ;
		$fetch_url = 'http://vimeo.com/api/v2'.$profile['vtype'] . $profile['query'] . '/videos.json';
		$response_data = json_decode( $this->remote_get_content($fetch_url) ) ;

        $posts = array();
        if( !empty($response_data) ) {
        	$count = 0;
        	foreach ($response_data as $entry) {
        		if( $count > $profile['limit'] )
        			break;
        		$count ++; // Limit the number of entries
        		
        		if( $profile['status'] != 'enable' ) 
        			continue;
        		//post title
        		$title = ($entry->title ? wp_strip_all_tags( $entry->title ): $fb_identify );

        		$post_id = $this->post_exists( $title );

        		if( $post_id && !$profile['update_post'] ) {
        			continue;
        		}

				$embed = '[embed width="'.$profile['video_embed_width'].'" height="'.$profile['video_embed_height'].'"]http://player.vimeo.com/video/'.$entry->id.'[/embed]<br />';

        		$posts[$entry->id] = array(
        			'origin_post'	 => $entry->description,
					'post_content'   => $embed . '<br>' . $entry->description,
					'post_title'     => $title,
					'post_date'      => date( 'Y-m-d H:i:s',strtotime($entry->upload_date) ),
					'thumbnails'	 => array(
						'small'		=> $entry->thumbnail_small,
						'medium'	=> $entry->thumbnail_medium,
						'large'		=> $entry->thumbnail_large
					)
				);  

        		if( $post_id ) {
        			$posts[$entry->id]['ID'] = $post_id;
        		}

        		$posts[$entry->id] = array_merge( $posts[$entry->id], $this->generate_post_template( $profile ) );
        	}
        }
        return $posts;
    }

    public function add_thumbnail( $post_id, $post, $width, $height, $add ) {
        if( $add ){
            //images
            $img_caption = 'Thumbnail for ' . $post['post_title'];

            if( isset($post['thumbnails']['large']) ) {
                $source_images = $post['thumbnails']['large'];
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
}
$GLOBALS['dwsf_vimeo'] = new DWSF_Vimeo();

?>