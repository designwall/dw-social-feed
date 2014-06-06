<?php  
/**
 * DW Social Feeds: Flickr
 * Make wordpress cron from all facebook profiles. The auto get content
 */
class DWSF_Flickr extends DWSF_Feed {
	public $profile; //current_profile
	public $name = 'flickr';
	public $slug = 'fkr';
	public $task_hook_name = 'dwsf_flickr_hook';

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
			'update_exists_post'	=> false
		) );
		//make a feed link
		if( ! isset($profile['query']) )
			return false;
        
		$regex = '/\d+\@[^\@]+/';
        $searchById = 0;
        $fetchUrl = 'http://api.flickr.com/services/feeds/photos_public.gne?format=php_serial';
        if(preg_match($regex, $profile['query'])) {
            $searchById = 1;
            $fetchUrl .= '&id='.$profile['query'];
        } else {
            $fetchUrl .= '&tags='.$profile['query'];
        }
        
        $content = $this->remote_get_content($fetchUrl);
        $data = unserialize($content);

        $posts = array();
        if( !empty($data['items']) ) {
        	$count = 0;
        	foreach ($data['items'] as $entry) {
        		if( $count > $profile['limit'] )
        			break;
        		$count ++; // Limit the number of entries
        		
        		if( $profile['status'] != 'enable' ) 
        			continue;

        		//post title
                $title = wp_strip_all_tags( $entry['description'] );
                $title = substr($title, 0, 60) . '...';

                $post_name = $entry['title'];

        		$post_id = $this->post_name_exists( sanitize_title( $post_name ) );

        		if( $post_id && !$profile['update_post'] ) {
        			continue;
        		}

                $content = '';
                if( isset($entry['l_url']) ) {
                    $content .= '<img src="'.$entry['l_url'].'" ><br />';
                }
                $content .= wp_strip_all_tags( $entry['description'] );

        		$posts[$entry['title']] = array(
        			'origin_post'	 => $entry['description'],
					'post_content'   =>  $content,
					'post_title'     => $title,
					'post_date'      => date( 'Y-m-d H:i:s',strtotime($entry['date_taken']) ),
                    'post_name'      => $post_name,
					'thumbnails'	 => array(
						'm'	=> $entry['m_url'],
                        't' => $entry['t_url'],
                        'l' => $entry['l_url']
					)
				);

        		if( $post_id ) {
        			$posts[$entry['title']]['ID'] = $post_id;
        		}

        		$posts[$entry['title']] = array_merge( $posts[$entry['title']], $this->generate_post_template( $profile ) );
        	}
        }
        return $posts;
    }


    public function add_thumbnail( $post_id, $post, $width, $height, $add ) {
        if( $add ) {
            //images
            $img_caption = __('Thumbnail for','dwsf') . ' ' . $post['post_title'];
            $source_images = false;
            if( isset($post['thumbnails']['l']) ) {
                $source_images = $post['thumbnails']['l'];
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
$GLOBALS['dwsf_flickr'] = new DWSF_Flickr();

?>