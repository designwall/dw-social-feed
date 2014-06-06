<?php  
/**
 * DW Social Feeds: RSS
 * Make wordpress cron from all facebook profiles. The auto get content
 */
class DWSF_RSS extends DWSF_Feed {
	public $profile; //current_profile
	public $name = 'custom';
	public $slug = 'custom';
	public $task_hook_name = 'dwsf_custom_hook';

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

        $url = 'https://ajax.googleapis.com/ajax/services/feed/load?v=1.0&q='.urlencode($profile['query']).'&num='.$profile['limit'];
        $response = json_decode( $this->remote_get_content( $url ) );

        $posts = array();
        if( !empty($response->responseData->feed->entries) ) {
        	$count = 0;
        	foreach ($response->responseData->feed->entries as $entry) {
        		if( $count > $profile['limit'] )
        			break;
        		$count ++; // Limit the number of entries
        		
        		if( $profile['status'] != 'enable' ) 
        			continue;

                $title = wp_strip_all_tags( $entry->title );

                $post_name = $entry->title;

        		$post_id = $this->post_name_exists( sanitize_title( $post_name ) );

        		if( $post_id && !$profile['update_post'] ) {
        			continue;
        		}

                $content = $entry->content;

                $source_images = false;
                if( isset($entry->mediaGroups) ){
                    $mediaGroups = $entry->mediaGroups;
                    $mediaGroupsContents = $mediaGroups[0]->contents;
                    $source_images = $mediaGroupsContents[0]->url;
                    $img_caption = $mediaGroupsContents[0]->title;
                }else{
                    $aImages = $this->parseImageFromContent($content, '');
                    $aImages2 = $this->getValidImages($aImages,1, 200, 200);
                    $img_caption = $title;
                    
                    if(count($aImages2)) {
                        $images = $aImages2[0];
                    }
                    if( !empty($images) ) {
                        $source_images = $images;
                    }
                }


        		$posts[$post_name] = array(
        			'origin_post'	 => $entry->content,
					'post_content'   =>  $content,
					'post_title'     => $title,
					'post_date'      => date( 'Y-m-d H:i:s',strtotime($entry->publishedDate) ),
                    'post_name'      => $post_name,
					'extra_fields'	 => array(
						'_link'       => $entry->link,
                        'thumbnail'   => $source_images,
                        'img_caption' => $img_caption
					)
				);

        		if( $post_id ) {
        			$posts[$post_name]['ID'] = $post_id;
        		}

        		$posts[$post_name] = array_merge( $posts[$post_name], $this->generate_post_template( $profile ) );
        	}
        }
        return $posts;
    }


    public function add_thumbnail( $post_id, $post, $width, $height, $add ) {
        if( $add ) {
            //images
            $img_caption = __('Thumbnail for','dwsf') . ' ' . $post['extra_fields']['img_caption'];
            $source_images = $post['extra_fields']['thumbnail'];
            
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
$GLOBALS['dwsf_rss'] = new DWSF_RSS();

?>