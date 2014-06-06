<?php  
/**
 * Base class to get feed from a social page
 */

class DWSF_Feed {
	public $task_hook_name;
	public $schedule_time;
	public $name = '';
	public $slug = '';

	public function __construct() {
		$options = get_option('wall_social_feed_options');
		if( $options['general']['use_custom_time'] ) {
			$this->schedule_time = $options['general'][ $this->slug . '_cron_time'];
		} else {
			$this->schedule_time = $options['general']['cron_times'];
		}

		add_action( 'init', array( $this, 'init') );
		add_action( 'wp_ajax_dwsf-do-event-' . $this->name , array( $this, 'ajax_do_event') );

		register_deactivation_hook( DWSF_PATH . 'dw-social-feed.php', array($this, 'remove_schedule_event') );
	}

	public function init() {
		$this->schedule_event();
	}

	public function schedule_event() {
		//Prepare schedule for this feed type
		$schedule = $this->get_schedule();
		if ( ! wp_next_scheduled( $this->task_hook_name ) ) {
		 	wp_schedule_event( time(), $schedule, $this->task_hook_name );
		}
		add_action( $this->task_hook_name, array( $this, 'do_event') );
	}

	public function remove_schedule_event(){
		remove_action(  $this->task_hook_name, array( $this, 'do_event') );
		wp_clear_scheduled_hook( $this->task_hook_name );
	}
	public function clear_scheduled_event() {
		wp_clear_scheduled_hook( $this->task_hook_name );
	}

	public function reschedule_event() {
		$this->clear_scheduled_event();
		$this->schedule_event();
	}

	public function add_custom_schedule( $schedules ) {
	    $schedules[$this->task_hook_name] = array(
	        'interval' => $this->schedule_time, // seconds
	        'display'  => __( 'Custom '. $this->name .' Time', 'dwsf' )
	    );
		return $schedules;
	}

	public function ajax_do_event() {
		$this->do_event();
	}

	public function do_event() {
		set_time_limit(3600);
		// foreach profile, make posts and insert into database
		// Get profiles
		$options = get_option('wall_social_feed_options');

		if( $options['general'][$this->name.'_status'] == 'active' 
			&& !empty($options[$this->name]) ) {
			foreach ($options[$this->name] as $key => $profile) {
    			if( 'disable' == $profile['status'] ) 
    				continue;
    			if( !$profile['query'] ) 
    				continue; 

    			$posts = $this->fetch_content( $profile );
    			if( ! empty($posts) )  {
	    			foreach ($posts as $post) {
	    				$post_id = wp_insert_post( $post );
	    				if( $post_id ) {
	    					$this->add_thumbnail( $post_id, $post, $profile['width_image_limit'], $profile['height_image_limit'], $profile['use_image']  );
	    				} 
	    				echo '<p><strong>' . $post['post_title'] . '</strong></p>';
	    			}
    			}
    		}
		}
		set_time_limit(0);
	}

	public function fetch_content( $profile ){}

	public function get_schedule(){
	    $schedules = wp_get_schedules();
	    $matches = array_filter( $schedules, array( $this, 'filter_schedules_callback') );
	    if( !empty($matches) ) {
		    reset($matches);
		    return key( $matches );
	    } 	

		add_filter( 'cron_schedules', array( $this, 'add_custom_schedule') );
		return $this->task_hook_name;
	}

	public function filter_schedules_callback( $schedule ) {
		return $schedule['interval'] == $this->schedule_time && 1;
	}

	public function generate_post_template( $profile ) {
		$profile = wp_parse_args( $profile, array(
			'status'				=> 'enable', // This profile is enable
			'post_status'			=> 'publish',
			'limit'					=> 10,
			'posttype'				=> 'post',
			'author'				=> 1,
			'comment_open'			=> 'open', //closed
			'category' 				=> '',
			'tags'					=> '',
			'update_exists_post'	=> false
		) );

		return array(
			'post_status'    => $profile['post_status'],
			'post_type'      => $profile['posttype'],
			'post_author'    => $profile['author'],
			'comment_status' => $profile['comment_open'],
			'post_category'  => array( $profile['category'] ),
			'tags_input'     => array( $profile['tags'] )
		);  
	}

	public function remote_get_content( $url ) {
		$response = wp_remote_get( $url );
		return wp_remote_retrieve_body( $response );
	}

	public function add_thumbnail( $post_id, $post, $width, $height, $add ) {
		if( $add ){
			////images
			$img_caption = $post['post_title'] ? __('Thumbnail for','dwsf') . ' ' . $post['post_title'] : $this->getImageCaption($post['post_content']);

			$source_images = $this->get_images($post['origin_post'], $img_caption, $img_caption, $width, $height );

			// Add images sourse
			if( $source_images ){

				$attach_id = $this->add_attachments( $post_id, $source_images, $img_caption);
				set_post_thumbnail( $post_id, $attach_id );
			}
		}else{
			delete_post_thumbnail($post_id);
		}
	}
	public function getImageCaption($txt) {
		$txt = strip_tags($txt);
		$txt = preg_replace('/[\s\t\r\n]+/', ' ', $txt);
		$txt = substr($txt, 0, 32);
		return $txt;
	}

	public function get_images($post, $caption, $alt,$width_limit, $height_limit) { 
		return false;
	}

	public function add_attachments($post_id,$url,$caption){

    	$url = str_replace('https', 'http', $url);

    	// get upload directory
    	$upload_dir = wp_upload_dir();

		$pathinfo = pathinfo($url);
		$ext = $pathinfo['extension'];

    	$image_name = time().'.'.$ext;
    	if( wp_mkdir_p($upload_dir['path']) )
	        $file = $upload_dir['path'] . '/' . $image_name;
	    else
	        $file = $upload_dir['basedir'] . '/' . $image_name;
	    //http://profile.ak.fbcdn.net/hprofile-ak-snc4/174867_6427302910_1109589_s.jpg

	    @copy($url,$file);
	   	@unlink($url);

	   	$wp_filetype = wp_check_filetype(basename($file), null );

		$attachment = array(
		 'guid' => $upload_dir['baseurl'] .'/' ._wp_relative_upload_path( $file ), 
		 'post_mime_type' => $wp_filetype['type'],
		 'post_title' => $caption,
		 'post_content' => $caption,
		 'post_status' => 'inherit'
		);
		$attach_id = wp_insert_attachment( $attachment, $file, $post_id );
		// you must first include the image.php file
		// for the function wp_generate_attachment_metadata() to work
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
		wp_update_attachment_metadata( $attach_id, $attach_data );

		return $attach_id;
    }

    public function parseImageFromContent($content, $url = '') {
		$aImages = array();
		$aIgnore = $this->getIgnoreWords();
		$numIngnore = count($aIgnore);

		$pattern = '/<img[^>]+src\s*=\s*"([^"]+)"/i';
		preg_match_all($pattern, $content, $matches);
		if(isset($matches[1])) {
			for ($i = 0; $i < count($matches[1]); $i++) {
				$imgName = basename($matches[1][$i]);
				$imgName = strtolower($imgName);
				$ignore = 0;
				for($j=0; $j < $numIngnore; $j++) {
					if(strpos($imgName, $aIgnore[$j]) !== false) {
						$ignore = 1;
						break;
					}
				}
				if(!$ignore) {
					$aImages[] = $matches[1][$i];
				}
			}
		}
		//UPDATE URLS TO ABSOLUTE FORMAT
		if(!empty($url)) {
			$domain = preg_replace('/(^\w+\:\/\/[^\/]+).*/i', '$1', $url);
			$path = preg_replace('/^\w+\:\/\/[^\/]+/i', '', $url);//remove domain
			$path = preg_replace('/\?.*$/i', '', $path);//remove query string
			$paths = explode('/', $path);
	
			$aPaths = array();
			if(count($paths)) {
				$path = $domain.'/';
				$aPaths[] = $path;
				for ($i=0; $i<count($paths); $i++) {
					if(!empty($paths[$i])) {
						$path .= $paths[$i].'/';
						$aPaths[] = $path;
					}
				}
			}
	
			if(!count($aPaths)) {
				$aPaths[] = $domain . '/';
			}
	
	
			$numPath = count($aPaths);
			if($numPath) {
				$pattern = '/^\w+\:\/\//i';
				$aImgTmp = array();
	
				$aCheck = array();
	
				for ($j=0; $j < $numPath; $j++) {
					for ($i = 0; $i < count($aImages); $i++) {
						if(isset($aCheck[$i])) continue;
	
						$img = $aImages[$i];
						$img = preg_replace('/^[\/\\\\]+/', '', $img);
						if(!preg_match($pattern, $img)) {
							$aImgTmp[] = $aPaths[$j] . $img;
						} else {
							$aImgTmp[] = $img;
							$aCheck[] = $i;
						}
					}
				}
				$aImgTmp = array_unique($aImgTmp);
	
				$aImages = $aImgTmp;
			}
		} 
		return $aImages;
	}

	public function getIgnoreWords () {
		$aIgnore = array('logo', 'banner', 'advertising');
		return $aIgnore;
	}

	public function getValidImages($aImages, $limit = 1, $width_limit = 200, $height_limit = 200) {
		$aImages2 = array();
		if(count($aImages)) {
			$cnt = 0;
			foreach ($aImages as $img) {
				//if(!is_file($img)) continue;
				$isValidImg = $this->checkImgSize($img, $width_limit, $height_limit);

				if($isValidImg) {
					$cnt++;
					$aImages2[] = $img;
					if($cnt >= $limit) {
						break;//get only 1 images
					}
				}
			}
		}
		return $aImages2;
	}

	public function checkImgSize($url,$width_limit = 200,$height_limit = 200) {
		$url = str_replace('https', 'http', $url);
		$result = @getimagesize($url);

		if($result) {
			list($width, $height, $type, $attr) = $result;
			//get only image with width is lager than 360px
			if($width < $width_limit || $height < $height_limit) {
				return false;
			}else{
				return true;
			}
		} else {
			return false;
		}
	}


	public function parsePageInfo($url,$width_limit,$height_limit) {
		$aImages = array();
		$page_title = '';
		$urlIsImg = 0;
		if($this->urlIsImage($url)) {
			$aImages[] = $url;
			$urlIsImg = 1;
		} else {
			$content = $this->remote_get_content($url);
			$aImages = $this->parseImageFromContent($content, $url);
		}
		$aImages2 = $this->getValidImages($aImages,1,$width_limit,$height_limit);
		return array('images' => $aImages2, 'title' => $page_title);
	}

	public function getRealUrl($shortenUrl) {
		$options = get_option('wall_social_feed_options');
		if( $options['general']['converter_server'] == 'realurl.org') {
			
			//E.g: http://bit.ly/10QRTY
			$checkUrl = 'http://realurl.org/api/v1/getrealurl.php?url='.rawurlencode($shortenUrl);
			$content = $this->remote_get_content( $checkUrl );
	
			$pattern = '/<real>([\s\S]*?)<\/real>/i';
			preg_match($pattern, $content, $matches);
			if(isset($matches[1])) {
				return trim($matches[1]);
			} else {
				return $shortenUrl;
			}
		} else {
			//untiny.com
			
			$checkUrl = 'http://www.untiny.com/api/1.0/extract/?url='.base64_encode($shortenUrl).'&format=text&enc=64al';
			$realUrl = $this->remote_get_content( $checkUrl );
			if($realUrl) {
				return $realUrl;
			} else {
				return $shortenUrl;
			}
		}
	}

	public function urlIsImage($url) {
		$rPos = strrpos($url, '?');
		if($rPos !== false) {
			$url = substr($url, 0, $rPos);
		}
		if(preg_match('/\.(jpg|jpeg|png|gif|bmp)$/i', $url)) {
			return $url;
		}
		return false;
	}

	public function post_exists($title, $content = '', $date = '') {
		global $wpdb;

		$post_title = wp_unslash( sanitize_post_field( 'post_title', $title, 0, 'db' ) );
		$post_content = wp_unslash( sanitize_post_field( 'post_content', $content, 0, 'db' ) );
		$post_date = wp_unslash( sanitize_post_field( 'post_date', $date, 0, 'db' ) );

		$query = "SELECT ID FROM $wpdb->posts WHERE 1=1";
		$args = array();

		if ( !empty ( $date ) ) {
			$query .= ' AND post_date = %s';
			$args[] = $post_date;
		}

		if ( !empty ( $title ) ) {
			$query .= ' AND post_title = %s';
			$args[] = $post_title;
		}

		if ( !empty ( $content ) ) {
			$query .= 'AND post_content = %s';
			$args[] = $post_content;
		}

		if ( !empty ( $args ) )
			return (int) $wpdb->get_var( $wpdb->prepare($query, $args) );

		return 0;
	}


	public function post_name_exists($post_name) {
		global $wpdb;

		$query = "SELECT ID FROM $wpdb->posts WHERE 1=1";
		$args = array();

		if ( !empty ( $post_name ) ) {
			$query .= ' AND post_name LIKE %s';
			$args[] = $post_name . '%'; 
		}
		if ( !empty ( $args ) )
			return (int) $wpdb->get_var( $wpdb->prepare($query, $args) );

		return 0;
	}
}

?>