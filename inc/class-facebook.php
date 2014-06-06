<?php  
/**
 * DW Social Feeds: Facebook
 * Make wordpress cron from all facebook profiles. The auto get content
 */
class DWSF_Facebook extends DWSF_Feed {
	public $profile; //current_profile
	public $name = 'facebook';
	public $slug = 'fb';
	public $task_hook_name = 'dwsf_facebook_hook';

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

		$fb_identify = $profile['query'];
		if( is_string($fb_identify) ) {
			$fb_id = $this->get_facebook_id( $fb_identify );
		} else {
			$fb_id = $fb_identify;
		}
        $feed_link = 'http://www.facebook.com/feeds/page.php?id='.$fb_id.'&format=json';  
        $response_data = json_decode( $this->remote_get_content($feed_link) ) ;

        $posts = array();
        if( is_object( $response_data) && isset( $response_data->entries ) && is_array($response_data->entries) ) {
        	$count = 0;

        	foreach ( $response_data->entries as $entry ) {
        		if( $count > $profile['limit'] )
        			break;
        		$count ++; // Limit the number of entries
        		
        		if( $profile['status'] != 'enable' ) 
        			continue;
        		//post title
        		$title = ($entry->title ? wp_strip_all_tags( $entry->title ): $fb_identify );

        		$post_id = $this->post_name_exists( $title );

        		if( $post_id && !$profile['update_post'] ) {
        			continue;
        		}

        		$posts[$entry->id] = array(
        			'origin_post'	 => $entry->content,
					'post_content'   => sanitize_text_field( $entry->content ),
					'post_title'     => $title,
					'post_date'      => date( 'Y-m-d H:i:s',strtotime($entry->published) )
				);  
        		

        		if( $post_id ) {
        			$posts[$entry->id]['ID'] = $post_id;
        		}

        		$posts[$entry->id] = array_merge( $posts[$entry->id], $this->generate_post_template( $profile ) );
        	}
        }
        return $posts;
    }

	public function ajax_get_facebook_id() {
		if( ! isset($_REQUEST['query']) ) {
			wp_send_json_error( array(
				'message' => __('Query is missing', 'dwsf')
			) );
		}
		$id = $this->get_facebook_id( $_REQUEST['query'] );
		if( $id ) {
			wp_send_json_success( array(
				'id' => $id
			) );
		} else {
			wp_send_json_error( array(
				'message' => __('User or page not found','dwsf')
			) );
		}
	}

	public function get_facebook_id( $query ){
	    $query = sanitize_text_field( $query );
	    $response = wp_remote_get( 'https://graph.facebook.com/' . $query );
	    $graph = json_decode( wp_remote_retrieve_body( $response ) );

	    if( isset($graph->id) ) {
	    	return $graph->id;
	    }
	    return false;
	}

	public function get_images($post, $caption, $alt,$width_limit, $height_limit) {
		$images = false;
		$post = $this->updateFacebookImage($post);
		$aImages = $this->parseImageFromContent($post, '');

		$aImages2 = $this->getValidImages($aImages,1, $width_limit, $height_limit);
		
		if(count($aImages2)) {
			$images = $aImages2[0];
			//$images = $this->saveImage($url, 'twitter', $caption, $alt);
		}

		return $images;
	}
	public function updateFacebookImage($post) {

		$pattern = '/http[^\'\"\>\<]+(?:safe_image|app_full_proxy).php\?([^\"\'\>]+)/i';
		preg_match($pattern, $post, $matches);
		if(isset($matches[1])) {
			$params = str_replace('&amp;', '&', $matches[1]);

			$patternUrl = '/\&(?:url|src)=([^\"\'\&]+)/';
			preg_match($patternUrl, $params, $matches2);
			if(isset($matches2[1])) {
				$url = rawurldecode($matches2[1]);
				$post = str_replace($matches[0], $url, $post);
			}
		}else{
    		$pattern = '/<img[^>]+src\s*=\s*"([^"]+)_s.([^"]+)"/i';
			preg_match( $pattern, $post, $rb1 );
			if( isset($rb1[1]) ){
				$post = str_replace( '_s.', '_n.', $post);
			}
		}
		return $post;
	}
}
$GLOBALS['dwsf_facebook'] = new DWSF_Facebook();

?>