<?php
/**
 *  Class dwsf_social_feed process get feed content
 */

class dwsf_social_feed{
    //Properties
    public $facebook;
    public $twitter;
    public $youtube;
    public $vimeo;
    public $instagram;
    public $flickr;
    public $get_url_service;
    public $cron_times;
    public $custom;

    public $valid_img_width = 200;
    public $valid_img_height = 200;
    /**
     * Fetch data from facebook result, get all result and import data to blogs
     *	
     * @param $fbId int Facebook ID for pages
     */
    function __construct() {
        global $wall_social_feed_options;
    	$this->cron_times = $wall_social_feed_options['general']['cron_times'];
    	$this->facebook = $wall_social_feed_options['general']['facebook_status'];
    	$this->twitter = $wall_social_feed_options['general']['twitter_status'];
    	$this->youtube = $wall_social_feed_options['general']['youtube_status'];
    	$this->vimeo = $wall_social_feed_options['general']['vimeo_status'];
    	$this->instagram = $wall_social_feed_options['general']['instagram_status'];
    	$this->flickr = $wall_social_feed_options['general']['flickr_status'];
    	$this->custom = $wall_social_feed_options['general']['custom_status'];
    }
    
    public function dwsf_get_feed_foreach($type, $id){

    	set_time_limit(3600);
    	$wall_social_feed_options = get_option('wall_social_feed_options');
    	if ( array_key_exists( $id, $wall_social_feed_options[$type] ) ){
    		$value = $wall_social_feed_options[$type][$id];
    	}else{
    		return;
    	}
    	if( $value['status'] == 'disable'){ return; }

    	if( !$value['query'] ){ return ; }

    	switch ($type) {
    		case 'facebook':
    				$this->fetch_content_facebook($id, $value );
    			break;
    		case 'twitter':
    				$this->fetch_twitter_feeds($id, $value );
    			break;
    		case 'youtube':
    				$this->fetch_youtube_feed($id, $value );
    			break;
    		case 'vimeo':
    				$this->fetch_vimeo_feed($id, $value );
    			break;
    		case 'instagram':
    				$this->fetch_instagram_feed($id, $value );
    			break;
    		case 'flickr':
    				$this->fetch_flickr_feed($id, $value );
    			break;
    		case 'custom':
    				$this->dwsf_fetch_custom_feeds( $id, $value);
    			break;
    	}
    }

    public function dwsf_get_feed_data_facebook(){
    	set_time_limit(3600);
    	$wall_social_feed_options = get_option('wall_social_feed_options');

    	if( $this->facebook && !empty($wall_social_feed_options['facebook']) ){

    		foreach ($wall_social_feed_options['facebook'] as $key => $value) {
    			$fbName = $key;
    			$status = $value['status'];
    			if( $status == 'disable' ) continue;

    			$query = $value['query'];
    			if( !$query ){ continue; }

    			$this->fetch_content_facebook($fbName, $value );
    		}
    	}
    }

    public function dwsf_get_feed_data_twitter(){
    	set_time_limit(3600);
    	$wall_social_feed_options = get_option('wall_social_feed_options');

    	if( $this->twitter && !empty($wall_social_feed_options['twitter']) ){
    		foreach ($wall_social_feed_options['twitter'] as $key => $value) {
    			$profileName = $key;
    			$status = $value['status'];
    			if( $status == 'disable' ) continue;

    			$query = $value['query'];
    			if( !$query ){ continue; }

    			$this->fetch_twitter_feeds($profileName, $value );
    		}
    	}
    }
    public function dwsf_get_feed_data_youtube(){
    	set_time_limit(3600);
    	$wall_social_feed_options = get_option('wall_social_feed_options');
    	if( $this->youtube && !empty($wall_social_feed_options['youtube']) ){
    		foreach ($wall_social_feed_options['youtube'] as $key => $value) {
    			$channel = $key;
    			$status = $value['status'];
    			if( $status == 'disable' ) continue;

    			$query = $value['query'];
    			if( !$query ) continue; 
    			
    			$this->fetch_youtube_feed($channel, $value );
    		}
    	}
    }

    public function dwsf_get_feed_data_vimeo(){
    	set_time_limit(3600);
    	$wall_social_feed_options = get_option('wall_social_feed_options');
    	if( $this->vimeo && !empty($wall_social_feed_options['vimeo']) ){
    		foreach ($wall_social_feed_options['vimeo'] as $key => $value) {
    			$channel = $key;
    			$status = $value['status'];
    			if( $status == 'disable' ) continue;

    			$query = $value['query'];
    			if( !$query ) continue; 

    			$this->fetch_vimeo_feed($channel, $value );
    		}
    	}
    }
    public function dwsf_get_feed_data_instagram(){
    	set_time_limit(3600);
    	$wall_social_feed_options = get_option('wall_social_feed_options');
    	if( $this->instagram && !empty($wall_social_feed_options['instagram']) ){
    		foreach ($wall_social_feed_options['instagram'] as $key => $value) {
    			$user = $key;
    			$status = $value['status'];
    			if( $status == 'disable' ) continue;

    			$query = $value['query'];
    			if( !$query ) continue;

    			$this->fetch_instagram_feed($user, $value );
    		}
    	}
    }
    public function dwsf_get_feed_data_flickr(){
    	set_time_limit(3600);
    	$wall_social_feed_options = get_option('wall_social_feed_options');
    	if( $this->flickr && !empty($wall_social_feed_options['flickr']) ){
    		foreach ($wall_social_feed_options['flickr'] as $key => $value) {
    			$frid = $key;
    			$status = $value['status'];
    			if( $status == 'disable' ) continue;

    			$query = $value['query'];
    			if( !$query ) continue; 

    			$this->fetch_flickr_feed($frid, $value );
    		}
    	}
    }
    public function dwsf_get_custom_feeds_cron_job(){
    	set_time_limit(3600);
    	$wall_social_feed_options = get_option('wall_social_feed_options');
    	if( $this->custom && !empty($wall_social_feed_options['custom']) ){
    		foreach ($wall_social_feed_options['custom'] as $key => $value) {
    			$status = $value['status'];
    			if( $status == 'disable' ) continue;

    			$query = $value['query'];
    			if( !$query ) continue; 

    			$this->dwsf_fetch_custom_feeds($key, $value );
    		}
    	}
    }
    public function dwsf_get_feed_data(){
    	$this->dwsf_get_feed_data_facebook();
    	$this->dwsf_get_feed_data_twitter();
    	$this->dwsf_get_feed_data_youtube();
    	$this->dwsf_get_feed_data_vimeo();
    	$this->dwsf_get_feed_data_instagram();
    	$this->dwsf_get_feed_data_flickr();
    	$this->dwsf_get_custom_feeds_cron_job();
    }

    private function fetch_content_facebook($fbName = '', $profile){
    	$profile['fbName'] = $fbName;
    	extract($profile);

        $fbId = $this->fetch_id_facebook($query);
        if( !$fbId ) return false;

        $facebook_feed = 'http://www.facebook.com/feeds/page.php?id='.$fbId.'&format=json';  

        $content = json_decode( $this->getContent($facebook_feed) ) ;
        $numResult = 0;

        if( is_object( $content) && isset( $content->entries ) && is_array($content->entries) ){
        	for ($i=0; $i<count($content->entries); $i++) {
				if($numResult >= $limit) {
					break;
				}else{
					$numResult++;
					$dt = $content->entries[$i];
					$facebook_id = 'facebook-id-'.$dt->id;
					$alias = sanitize_title($facebook_id);

					$profile['alias'] = $alias;
					$post_id = $this->dwsf_post_exists( $alias, $posttype );

					if( $post_id ){
						if( $update_post ){

							$profile['post_id'] = $post_id;
							$this->dwsf_facebook_insert_post($profile, $dt, true);
						}else{
							continue;
						}
					}else{
						$profile['post_id'] = 0;
						$this->dwsf_facebook_insert_post($profile, $dt);
					}
				}
			}
        }
    }


    private function dwsf_facebook_insert_post($profile, $dt, $func_update = false){
    	extract($profile);

    	$tcontent = (trim($dt->content) != '') ? $dt->content : $dt->title;
    
							
		$testContent = preg_replace('/[\r\n\s\t]+/', '', $tcontent);
		if(empty($testContent)) {
			continue;
		}
		//update url
		$pattern = '/href="\/l.php\?u=(http[^\'"]+)/i';

		$tcontent = preg_replace_callback($pattern, 
				create_function(
					'$matches',
					'
						$pos = strpos($matches[1], "&");
						$url = substr($matches[1], 0, $pos);
						return "href=\"".rawurldecode($url);'
				),
				$tcontent
			);
		// Create post for import
		$post = array(
			'post_name' => $alias,
			'post_title' => ($dt->title ? $dt->title : $fbName),
			'post_date'	=> date( 'Y-m-d H:i:s',strtotime($dt->published) ),
			'post_status' => 'publish',
			'post_content' => $tcontent,
			'post_author' => $author,
			'post_type' => $posttype,
			'post_category' => array ( $category )
		);
		if( true === $func_update){
			$post['ID'] = $post_id;
			$post_id = wp_update_post($post);
		}else{
			$post_id = wp_insert_post($post);
		}


		update_post_meta($post_id, 'style', 'facebook');
		update_post_meta($post_id, 'source_url', $dt->alternate);

		if( !is_object($post_id) && $post_id ){

			if( $use_image ){
				////images
				$img_caption = $this->getImageCaption($tcontent);
				$source_images = $this->getFacebookImage($tcontent, $img_caption, $img_caption, $width_image_limit, $height_image_limit);
				// Add images sourse
				if( $source_images ){
					$attach_id = $this->dwsf_add_attachments($post_id,$source_images,$img_caption);

					set_post_thumbnail($post_id, $attach_id);
				}
			}else{
				delete_post_thumbnail($post_id);
			}
		}
    }

    /** 
	 * Fetch twitter feed by query search,
	 * //$url = 'http://api.twitter.com/1/statuses/user_timeline.json?screen_name='
	 *
	 *	@param $queryString string Twitter search query
	 */



	private function fetch_twitter_feeds($profileName = '', $profile){
		extract($profile);
		$profile['profileName'] = $profileName;
		
		$rpp = ($limit < 100) ? $limit : 100;
		$page = 1;

		
		//$url = 'https://api.twitter.com/1.1/search/tweets.json?';
        

		$total = 0;
		do {

            global $wall_social_feed_options;
            $twitter_request = new DWSF_Twitter( $wall_social_feed_options['general']['twitter_consumer_key'], $wall_social_feed_options['general']['twitter_consumer_secret'] );

            $args = array(
                'query'             => $query,
                'number'            => $rpp,
                'exclude_replies'   => true
            );
            $data = $twitter_request->get_tweets( $args );

			$numResult = 0;

			if( !empty( $data ) && is_array($data) ) {
				for ( $i=0; $i < count( $data) ; $i++ ) {
					$numResult++;
					$dt = $data[$i];
					$twitter_id = 'twitter-id-'.$dt->id_str;

					$alias = sanitize_title($twitter_id);
					$profile['alias'] = $alias;

					$post_id = $this->dwsf_post_exists( $alias, $posttype );
					if( strpos($query, 'from:') === 0 ){
                        $dt->from_user = $dt->user->screen_name;
                    }else{
                        $dt->from_user = $dt->from_user_name;
                    } 

					if( $post_id ){
						if( $update_post ){
							$profile['post_id'] = $post_id;
							$this->dwsf_twitter_insert_post($profile, $dt, true);
						}else{
							continue;
						}
					}else{
						$profile['post_id'] = 0;
						$this->dwsf_twitter_insert_post($profile, $dt);
					}
				}
			}
			//get next page
			$page++;
			$total += $numResult;
		} while ( ($total < $limit) && ($page <= 15) && $numResult == $rpp);
	}

	private function dwsf_twitter_insert_post($profile, $dt, $func_update = false){
		extract($profile);

		if(!$retweet) {
			if($this->isRetweet($dt->text)) {
				return;
			}
		}
		$source_url = 'http://twitter.com/'.$dt->from_user. '/status/'.$dt->id_str;
		$tcontent = $this->updateTweetUrls($dt->text);
	
		$post = array(
			'post_name' => $alias,
			'post_title' => isset($dt->from_user) ? $dt->from_user : '',
			'post_date' => date('Y-m-d H:i:s',strtotime($dt->created_at) ),
			'post_content' => $tcontent/*$source_url*/,
			'post_status' => 'publish',
			'post_author' => $author,
			'post_type' => $posttype,
			'post_category' => array ( $category )
		);

		if( true === $func_update ){
			$post['ID'] = $post_id;
			$post_id = wp_update_post($post);
		}else{
			$post_id = wp_insert_post($post);
		}

		if( !is_object($post_id) && $post_id ){
			set_post_format($post_id, 'status');

			update_post_meta($post_id, 'style', 'twitter');
			update_post_meta($post_id, 'source_url', $source_url);

			if( $use_image ){
				//images
				$img_caption = $this->getImageCaption($dt->text);
				$source_images = $this->getTweetImage($dt->text, $img_caption, $img_caption, $width_image_limit, $height_image_limit);
				// Add images sourse
				if( $source_images ){
					$attach_id = $this->dwsf_add_attachments($post_id,$source_images,$img_caption);

					set_post_thumbnail($post_id, $attach_id);
				}
			}else{
				delete_post_thumbnail($post_id);
			}
		}
	}


	/**
	 *	Youtube channel feed
	 *
	 *	@param $author string Author name of youtube channel
	 */

	private function fetch_youtube_feed($userName = 'joomlart', $profile){
		
		extract($profile);

		$profile['profileName'] = $userName;
		
		$fetchUrl = "http://gdata.youtube.com/feeds/api/videos?max-results=".$limit."&alt=json&format=5&author=".$query;

		$content = $this->getContent($fetchUrl);

		$data = json_decode($content);
		$numResult = 0;

		if(is_object($data) && isset($data->feed->entry) && is_array($data->feed->entry)) {

			$youtube_author = isset($data->feed->author) ? @$data->feed->author[0] : false;
			$authorName = $author ? @$author->name->{'$t'} : '';
			
			for ($i=0; $i<count($data->feed->entry); $i++) {
				if($numResult >= $limit) {
					break;
				}
				
				$dt = $data->feed->entry[$i];
				$etid = str_replace('http://gdata.youtube.com/feeds/api/videos/','',$dt->id->{'$t'});
                $profile['etid'] = $etid;

				$youtube_id = 'youtube-id-'.$etid;

				$alias = sanitize_title($youtube_id);
				$profile['alias'] = $alias;
				$post_id = $this->dwsf_post_exists( $alias, $posttype );

				// echo $alias;
				if( $post_id ){
					if( $update_post ){
						$profile['post_id'] = $post_id;
						$this->dwsf_youtube_insert_post($profile, $dt, true);
					}else{ continue; }
				}else{
					$profile['post_id'] = 0;
					$this->dwsf_youtube_insert_post($profile, $dt);
				}
				//
				$numResult++;
			}
		}
		return ;
	}

	private function dwsf_youtube_insert_post($profile, $dt, $func_update = false){
		extract($profile);

		$published = $dt->published->{'$t'};
		$title = $dt->title->{'$t'};
		$tcontent = '<iframe width="'.$video_embed_width.'" height="'.$video_embed_height.'" src="http://www.youtube.com/embed/'.$etid.'?rel=0&autoplay='.$video_autoplay.'&loop='.$video_loop.'" frameborder="0" allowfullscreen></iframe>';
		$tcontent .= $this->getYoutubeContent($dt->content->{'$t'});
		
		if(trim($title) == '') {
			continue;
		}
		$published = $dt->published->{'$t'};
		$post = array(
			'post_name' => $alias,
			'post_title' => $title,
			'post_content' => $tcontent,
			'post_date'	=>  date('Y-m-d H:i:s',strtotime($published) ),
			'post_status' => 'publish',
			'post_author' => $author,
			'post_type' => $posttype,
			'post_category' => array ( $category ),
			'filter'	=> 'display'
		);
		if( true === $func_update){
			$post['ID'] = $post_id;
			remove_filter('content_save_pre', 'wp_filter_post_kses');
			$post_id = wp_update_post($post);
			add_filter('content_save_pre', 'wp_filter_post_kses');
		}else{
			remove_filter('content_save_pre', 'wp_filter_post_kses');
			$post_id = wp_insert_post($post);
			add_filter('content_save_pre', 'wp_filter_post_kses');
		}

		if( !is_object($post_id) ){
			set_post_format( $post_id, 'video' );

			update_post_meta($post_id, 'source_url', 'http://www.youtube.com/watch?v='.$etid);
			update_post_meta($post_id, 'source_id', $etid);
			update_post_meta($post_id, 'style', 'youtube');

			$img_caption = $title;
			//no need check size for image getting from youtube, since they was cropped to the same size by youtube
			//$post['source_images'] = $this->getArticleImageObj("http://img.youtube.com/vi/".$etid."/0.jpg", $img_caption, $img_caption, 'youtube');
			
			$source_images = 'http://img.youtube.com/vi/'.$etid.'/0.jpg';

			// Add images sourse
			if( $source_images ){
				$attach_id = $this->dwsf_add_attachments($post_id,$source_images,$img_caption);

				set_post_thumbnail($post_id, $attach_id);
			}
		}
	}
	/**
	 *	vimeo channel feed
	 *
	 *	@param $author string Author name of vimeo channel
	 */

	private function fetch_vimeo_feed($profileName = 'joomlart',$profile){
		extract($profile);
		$profile['profileName'] = $profileName;
	    $vtype = ($vtype=='') ? '/' : "/$vtype/" ;
		$fetchUrl = "http://vimeo.com/api/v2$vtype$query/videos.json";

		$content = $this->getContent($fetchUrl);

		$data = json_decode($content);
		$numResult = 0;

		if(is_array($data) && count($data)>0) {
           
			for ($i=0; $i<count($data); $i++) {
				if($numResult >= $limit) {
					break;
				}
				
				$dt = $data[$i];
				$etid = $dt->id;
                $profile['etid'] = $etid;

				$vimeo_id = 'vimeo-id-'.$etid;
				$alias = sanitize_title($vimeo_id);
				$profile['alias'] = $alias;

				$post_id = $this->dwsf_post_exists( $alias, $posttype);

				if( $post_id ){
					if( $update_post ){
						$profile['post_id'] = $post_id;
						$this->dwsf_vimeo_insert_post($profile,$dt,true);
					}else{continue;}
				}else{
					$profile['post_id'] = 0;
					$this->dwsf_vimeo_insert_post($profile,$dt);
				}
				
				
				//
				$numResult++;
			}
		}
		return ;
	}

	private function dwsf_vimeo_insert_post($profile, $dt, $func_update = false){
		extract($profile);

		$title = $dt->title;
		$tcontent = '<iframe src="http://player.vimeo.com/video/'.$etid.'?autoplay='.$video_autoplay.'&amp;loop='.$video_loop.'" width="'.$video_embed_width.'" height="'.$video_embed_height.'" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe><br />';

		$tcontent .= $dt->description;
		
		if(trim($title) == '') {
			continue;
		}

		$post = array(
			'post_name' => $alias,
			'post_title' => $title,
			'post_content' => $tcontent,
			'post_date'	=>  date('Y-m-d H:i:s',strtotime($dt->upload_date) ),
			'post_status' => 'publish',
			'post_author' => $author,
			'post_type' => $posttype,
			'post_category' => array ( $category ),
			'filter' => 'display'
		);
		
		if( true === $func_update ){
			$post['ID'] = $post_id;
			remove_filter('content_save_pre', 'wp_filter_post_kses');
			$post_id = wp_update_post($post);
			add_filter('content_save_pre', 'wp_filter_post_kses');
		}else{
			remove_filter('content_save_pre', 'wp_filter_post_kses');
			$post_id = wp_insert_post($post);
			add_filter('content_save_pre', 'wp_filter_post_kses');
		}

		if( !is_object($post_id) ){
			set_post_format( $post_id, 'video' );

			update_post_meta($post_id, 'source_url', 'http://www.vimeo.com/'.$etid);
			update_post_meta($post_id, 'source_id', $etid);
			update_post_meta($post_id, 'style', 'vimeo');

			$img_caption = $title;

			$source_images = $dt->thumbnail_large;

			// Add images sourse
			if( $source_images ){
				$attach_id = $this->dwsf_add_attachments($post_id,$source_images,$img_caption);
				set_post_thumbnail($post_id, $attach_id);
			}
		}
	}
	/**
	 * 	Fetch Instagram feed and import to wordpress
	 *
	 *	@param $queryString string Query search for instagram  
	 */

	private function fetch_instagram_feed($profileName = 'joomlart',$profile){
		extract($profile);
		$profile['profileName'] = $profileName;

		$items = $this->getInstagramItems( $query, $limit );

		$numResult = 0;
		//$aResult = array();

		if(count($items)) {
			$i = 0;
			foreach ($items as $dt) {
				$i++;

				$instagram_id = 'instagram-id-'.$dt->id;

				$alias = sanitize_title($instagram_id);
				$profile['alias'] = $alias;

				$post_id = $this->dwsf_post_exists( $alias, $posttype );

				if ( $post_id ){
					if( $update_post ){
						$profile['post_id'] = $post_id; 
						$this->dwsf_instagram_insert_post($profile,$dt,true);	
					}else{ continue; }
				}else{
					$this->dwsf_instagram_insert_post($profile,$dt);
				}

				

				$numResult++;
			}
		}
		return ;
	}	

	private function dwsf_instagram_insert_post($profile, $dt, $func_update = false){
		extract($profile);

		$title = $this->getPostTitle($dt->title);
		$tcontent = $dt->title;
		
		$post = array(
			'post_name' => $alias,
			'post_title' => $title,
			'post_date'	=> date('Y-m-d H:i:s',strtotime($dt->pubDate) ),
			'post_content' => $tcontent,
			'post_status' => 'publish',
			'post_author' => $author,
			'post_type' => $posttype,
			'post_category' => array ( $category )

		);

		if( true === $func_update ){
			$post['ID'] = $post_id;
			$post_id = wp_update_post($post);
		}else{
			$post_id = wp_insert_post($post);
		}

		if( !is_object($post_id)){
			set_post_format( $post_id, 'image' );

			update_post_meta($post_id, 'source_author', $dt->author);
			update_post_meta($post_id, 'source_url', $dt->link);
			update_post_meta($post_id, 'style', 'instagram');

			$source_images = $dt->image;

			// Add images sourse
			if( $source_images ){
				$attach_id = $this->dwsf_add_attachments($post_id,$source_images,$title);

				set_post_thumbnail($post_id, $attach_id);
			}
		}
	}
	/**
	 *	Fetch data feed from flickr 
	 */
	private function fetch_flickr_feed($profileName, $profile ){
		$flickr_get_favorite_photos = 0;
		extract($profile);
		$profile['profileName'] = $profileName;

		$regex = '/\d+\@[^\@]+/';
		$searchById = 0;
		$fetchUrl = 'http://api.flickr.com/services/feeds/photos_public.gne?format=php_serial';
		if(preg_match($regex, $query)) {
			$searchById = 1;
			$fetchUrl .= '&id='.$query;
		} else {
			$fetchUrl .= '&tags='.$query;
		}
		
		$content = $this->getContent($fetchUrl);
		$data = unserialize($content);

		$numResult = 0;
		//$aResult = array();
		
		$items = array();
		if(isset($data['items']) && is_array($data['items']) && count($data['items'])) {
			$items = $data['items'];
		}
		
		//Favorite Photos
		if($searchById && $flickr_get_favorite_photos) {
			$fetchUrl2 = 'http://api.flickr.com/services/feeds/photos_faves.gne?format=php_serial&id='.$query;
			$content2 = $this->getContent($fetchUrl2);
			$data2 = unserialize($content2);
			
			if(isset($data2['items']) && is_array($data2['items']) && count($data2['items'])) {
				foreach ($data2['items'] as $dt) {
					$items[] = $dt;
				}
			}
		}

		if(count($items)) {
			$i = 0;
			foreach ($items as $dt) {
				$i++;
				if($numResult >= $limit) {
					break;
				}
				
				//$dt['id'] = preg_replace('/.*?([0-9]+)\/*$/', '$1', $dt->link);
				$dt['id'] = str_replace('/', '', $dt['guid']);

				$flickr_id = 'flickr-id-'.$dt['id'];
				$alias = sanitize_title($flickr_id);
				$profile['alias'] = $alias;

				$post_id = $this->dwsf_post_exists($alias,$posttype);
				
				if( $post_id ){
					if( $update_post ){
						$profile['post_id'] = $post_id;
						$this->dwsf_flickr_insert_post($profile,$dt,true);
					}else{ continue; }
				}else{
					$this->dwsf_flickr_insert_post($profile,$dt);
				}

				$numResult++;
			}
		}
		return ;
	}

	private function dwsf_flickr_insert_post($profile, $dt, $func_update = false){
		extract($profile);

		$title = $dt['title'];
		$tcontent = $dt['description'];
		
		$post = array(
			'post_name' => $alias,
			'post_title' => $title,
			'post_date'	=> date('Y-m-d H:i:s', $dt['date']),
			'post_status' => 'publish',
			'post_content' => preg_replace("/<img[^>]+\>/i", " ", $tcontent),
			'post_category' => array ( $category ),
			'post_type' => $posttype,
			'post_author' => $author,
		);

		if( true === $func_update ){
			$post['ID'] = $post_id;
			$post_id = wp_update_post($post);
		}else{
			$post_id = wp_insert_post($post);
		}

		if( !is_object($post_id) && $post_id ){
			update_post_meta($post_id, 'author', $dt['author_name'], $unique = true);
			update_post_meta($post_id, 'source_url', $dt['url'], $unique = true);
			update_post_meta($post_id, 'style', 'flickr');

			$img_caption = $title;
			$source_images = $dt['l_url'];

			// Add images sourse
			if( $source_images ){
				$attach_id = $this->dwsf_add_attachments($post_id,$source_images,$img_caption);

				set_post_thumbnail($post_id, $attach_id);
			}
		}
	}

	/**
	 *	Fetch rss feed from custom link
	 */
	private function dwsf_fetch_custom_feeds($profileName, $profile){
		extract($profile);
		$profile['profileName'] = $profileName;

		
		$fetchUrl = $query;
		
		$content = $this->dwsf_get_custom_feeds($fetchUrl, $limit);
		if( $content->responseStatus == 200 ){
			$items = $content->responseData->feed->entries;
			if(count($items)) {
				foreach ($items as $entry) {
					
					$alias = $entry->title . $entry->publishedDate;
					$alias = 'custom-id-'.$alias;
					$alias = sanitize_title($alias);
					$profile['alias'] = $alias;

					$post_id = $this->dwsf_post_exists($alias,$posttype);
					
					if( $post_id ){
						if( $update_post ){
							$profile['post_id'] = $post_id;
							$this->dwsf_custom_insert_post($profile,$entry,true);
						}else{ continue; }
					}else{
						$this->dwsf_custom_insert_post($profile,$entry);
					}
				}
			}
		}else{ return; }
		return ;
	}

	private function dwsf_custom_insert_post($profile, $entry, $func_update = false ){
		extract($profile);

		$title = $entry->title;
		$tcontent = $entry->content;
		$excerpt = $entry->contentSnippet;
		
		
		$post = array(
			'post_name' => $alias,
			'post_title' => $title,
			'post_date'	=> date('Y-m-d H:i:s', strtotime($entry->publishedDate) ),
			'post_status' => 'publish',
			'post_content' => preg_replace("/<img[^>]+\>/i", " ", $tcontent),
			'post_category' => array ( $category ),
			'post_type' => $posttype,
			'post_author' => $author,
		);
		if( true === $func_update ){
			$post['ID'] = $post_id;
			$post_id = wp_update_post($post);
		}else{
			$post_id = wp_insert_post($post);
		}

		if( !is_object($post_id) && $post_id ){
			update_post_meta($post_id, 'author', $entry->author, $unique = true);
			update_post_meta($post_id, 'source_url', $entry->link, $unique = true);
			update_post_meta($post_id, 'style', 'rss');

			$img_caption = $title;
			
			if( $use_image ){

				
				if( isset($entry->mediaGroups) ){
					$mediaGroups = $entry->mediaGroups;
					$mediaGroupsContents = $mediaGroups[0]->contents;
					$source_images = $mediaGroupsContents[0]->url;
					$img_caption = $mediaGroupsContents[0]->title;
				}else{
					$aImages = $this->parseImageFromContent($tcontent, '');
					$aImages2 = $this->getValidImages($aImages,1, 200, 200);
					$img_caption = $title;
					
					if(count($aImages2)) {
						$images = $aImages2[0];
					}

					$source_images = $images;
				}
				// Add images sourse
				if( $source_images ){

					$attach_id = $this->dwsf_add_attachments($post_id,$source_images,$img_caption);

					set_post_thumbnail($post_id, $attach_id);
				}
				
			}else{
				delete_post_thumbnail($post_id);
			}
		}
	}

	/**
	 *	Retrieve feed from  Rss custom Link 
	 *
	 *	@param $url string URL of Rss feed link
	 *	@param $limit int Limit of results
	 */
	private function dwsf_get_custom_feeds($url, $limit){
		$url = 'https://ajax.googleapis.com/ajax/services/feed/load?v=1.0&q='.urlencode($url).'&num='.$limit;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 600);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5) Gecko/20041107 Firefox/1.0');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_CAINFO, DWSF_PATH . "inc/googleapis.crt");
		$content = curl_exec($ch);
		curl_close($ch);
		return json_decode($content);
	}

	/**
	 *	Instagram item feeds xml 
	 *
	 *	@param $keyword string Key for Instagram search
	 *	@param $limit int Number of result for return
	 */
	private function getInstagramItems($keyword, $limit) {
		$keyword = trim($keyword);
		$author = '';
		if(strtoupper($keyword) == '[POPULAR]') {
			$xml = ("http://web.stagram.com/rss/popular/");
		} elseif (substr($keyword, 0, 1) == '@') {
			$username = substr($keyword, 1);
			$xml = ('http://widget.stagram.com/rss/n/'.$username.'/');
		} elseif (substr($keyword, 0, 1) == '#') {
			$keyword = substr($keyword, 1);
			$xml = ('http://widget.stagram.com/rss/tag/'.$keyword.'/');
		} else {
			$xml = ('http://widget.stagram.com/rss/tag/'.$keyword.'/');
		}
		$xmlDoc = new DOMDocument();
		$xmlDoc->load($xml);
		
		$items = array();
		
		$x= $xmlDoc->getElementsByTagName('item');
		if(is_object($x)) {
			for ($i=0; $i<$limit; $i++) {
				$item = @$x->item($i);
				if(!is_object($item)) {
					break;
				}
				
				$it = new stdClass();
				$it->image 		= @$item->getElementsByTagName('image')->item(0)->getElementsByTagName('url')->item(0)->childNodes->item(0)->nodeValue;
				if(!$it->image) {
					continue;
				}
				$it->title		= $item->getElementsByTagName('title')->item(0)->childNodes->item(0)->nodeValue;
				$it->link		= $item->getElementsByTagName('link')->item(0)->childNodes->item(0)->nodeValue;
				$it->desc		= $item->getElementsByTagName('description')->item(0)->childNodes->item(0)->nodeValue;
				$it->pubDate	= $item->getElementsByTagName('pubDate')->item(0)->childNodes->item(0)->nodeValue;
				//$it->author		= $item->getElementsByTagName('author')->item(0)->childNodes->item(0)->nodeValue;
				if(!empty($author)) {
					$it->author		= $author;
				} else {
					preg_match('/>\s*(\@[^<]+)</', $it->desc, $matches);
					if(isset($matches[1])) {
						$it->author		= trim(substr($matches[1], 1));
					} else {
						$it->author		= '';
					}
				}
				
				
				$it->id = preg_replace('/.*?([0-9_]+)\s*$/', '$1', $it->link);
				
				$items[] = $it;
			}
		}
		return $items;
	}

    /**
     *	Get Facebook id from facebook page name	
     * 	
     *	@param $username string User facebook name
     */

    static function fetch_id_facebook( $username ){
		$url = 'http://graph.facebook.com/'.$username;
    	$content = dwsf_social_feed::getContent($url);

    	$data = json_decode($content);
    	if( isset($data->is_published) && $data->is_published ){
    		return $data->id;
    	}else{
    		return false;
    	}

    }
    

    /**
     *	Upload images and create attachments
     *	
     *	@param $url string Url of image 
     */
    private function dwsf_add_attachments($post_id,$url,$caption){

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

    /**
     * Get image caption on facebook feed content
     */
    private function getImageCaption($txt) {
		$txt = strip_tags($txt);
		$txt = preg_replace('/[\s\t\r\n]+/', ' ', $txt);
		$txt = substr($txt, 0, 32);
		return $txt;
	}

    // Get all feeds from given url to import to wordpress
    static function getContent($url) {
        $response = wp_remote_get( $url );
        return wp_remote_retrieve_body( $response );
	}
    
    /**
     * Fetch facebook images from content
     * 
     * @param $post content of post on facebook
     * @param $caption caption of image
     */
    private function getFacebookImage($post, $caption, $alt,$width_limit, $height_limit) {
		
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

	/**
	 *	Replace facebook safe image(small size) with original images( big size ) in it's content
	 *
	 *	@param $post string Content of facebook item
	 */
	private function updateFacebookImage($post) {
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

	/**
	 *	Valid image array and return only one right image
	 *
	 *	@param $aImages array Aray of iamge urls 
	 */
	private function getValidImages($aImages, $limit = 1, $width_limit = 200, $height_limit = 200) {
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

	/**
	 *	get only image with width is lager than 360px
	 *
	 *	@param $url string Image url for valid
	 */
	private function checkImgSize($url,$width_limit = 200,$height_limit = 200) {
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
	/**
	 *	Retrieve an image from content
	 *
	 *	@param $content string Content that was retrieved for get image
	 *	@param $url string
	 */
	private function parseImageFromContent($content, $url = '') {
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

	private function getIgnoreWords () {
		$aIgnore = array('logo', 'banner', 'advertising');
		return $aIgnore;
	}
	

	

	/** 
	 * Get tweet image
	 * @param $tweet string tweet content
	 * @param $caption string Image caption
	 * @param $alt string Alt of image
	 */
	private function getTweetImage($tweet, $caption, $alt, $width_limit, $height_limit) {
		$url = $this->parseTweetUrls($tweet);
		if($url) {
			$info = $this->parsePageInfo($url, $width_limit, $height_limit);
			if(count($info['images'])) {
				$url = $info['images'][0];
				if( $this->checkImgSize($url,$width_limit, $height_limit) ){
					return $url;
				}
				//return $this->saveImage($url, 'twitter', $caption, $alt);
				return false;
			}
		}
		return false;
	}

	/**
	 *	@param $content string Content of tweet
	 */

	private function parseTweetUrls($content) {
		//
		$pattern = '/\w{2,4}\:\/\/[^\s\"]+/';
		preg_match($pattern, $content, $matches);

		if(isset($matches[0])) {
			return $this->getRealUrl($matches[0]);
		} else {
			return false;
		}
	}

	/**
	 * Get real url for shorten url of twitter
	 * 
	 * @param $shortenUrl string Shorten Url
	 */

	private function getRealUrl($shortenUrl) {
		if($this->get_url_service == 'realurl.org') {
			
			//E.g: http://bit.ly/10QRTY
			$checkUrl = 'http://realurl.org/api/v1/getrealurl.php?url='.rawurlencode($shortenUrl);
			$content = $this->getContent($checkUrl);
	
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
			$realUrl = $this->getContent($checkUrl);
			if($realUrl) {
				return $realUrl;
			} else {
				return $shortenUrl;
			}
		}
	}

	/**
	 *	@param $url string Url of single post
	 */
	function parsePageInfo($url,$width_limit,$height_limit) {
		$aImages = array();
		$page_title = '';
		$urlIsImg = 0;
		if($this->urlIsImage($url)) {
			$aImages[] = $url;
			$urlIsImg = 1;
		} else {
			$content = $this->getContent($url);
			$aImages = $this->parseImageFromContent($content, $url);
		}
		$aImages2 = $this->getValidImages($aImages,1,$width_limit,$height_limit);
		return array('images' => $aImages2, 'title' => $page_title);
	}

	/**
	 *	@param $content string Content to check
	 */
	private function isRetweet($content) {
		return (preg_match('/^[\s\t\r\n]*RT/', $content) ? 1 : 0);
	}
	/**
	 * 	@param $content string A Tweet for update
	 */
	private function updateTweetUrls($content) {
		$maxLen = 16;
		//split long words
		$pattern = '/[^\s\t]{'.$maxLen.'}[^\s\.\,\+\-\_]+/';
		$content = preg_replace($pattern, '$0 ', $content);

		//
		$pattern = '/\w{2,4}\:\/\/[^\s\"]+/';
		$content = preg_replace($pattern, '<a href="$0" title="" target="_blank">$0</a>', $content);

		//search
		$pattern = '/\#([a-zA-Z0-9_-]+)/';
		$content = preg_replace($pattern, '<a href="https://twitter.com/#%21/search/%23$1" title="" target="_blank">$0</a>', $content);

		//user
		$pattern = '/\@([a-zA-Z0-9_-]+)/';
		$content = preg_replace($pattern, '<a href="https://twitter.com/#!/$1" title="" target="_blank">$0</a>', $content);

		return $content;
	}

	/**
	 *	Check an url for image type
	 *
	 *	@param $url string Url for valid image type
	 */
	private function urlIsImage($url) {
		$rPos = strrpos($url, '?');
		if($rPos !== false) {
			$url = substr($url, 0, $rPos);
		}
		if(preg_match('/\.(jpg|jpeg|png|gif|bmp)$/i', $url)) {
			return $url;
		}
		return false;
	}

	/**
	 *	Get youtube video description from html script
	 *
	 *	@param $content string Html script
	 */
	private function getYoutubeContent($content){
		$pattern = '/<div style=\"font-size: 12px; margin: 3px 0px;\">(.*)<\/div>/';
		preg_match($pattern, $content, $matches);
		if(isset($matches[1])){
		    return strip_tags($matches[1]);
		
		}
		return false;
	}

	/**
	 *	Get youtube post title
	 *
	 *	@param $txt string html script for retrieve title
	 */
	private function getPostTitle($txt) {
		$txt = strip_tags($txt);
		$txt = preg_replace('/[^a-zA-Z0-9\.\,\-\_\?\!\@\#\s]/', '', $txt);
		if(strlen($txt) > 64) {
			$posDot = strpos($txt, '.', 32);
			$posCom = strpos($txt, ',', 32);
			if($posDot !== false && $posDot < 64) {
				$txt = substr($txt, 0, $posDot);
			} elseif($posCom !== false && $posCom < 64) {
				$txt = substr($txt, 0, $posCom);
			} else {
				$txt = substr($txt, 0, 64);
			}
		}
		
		return $txt;
	}
	/**
	*   Determine if a post existing base on post_name
	*   
	*   @param $post_name string unique post name
	*/

	private function dwsf_post_exists($post_name,$post_type){
		global $wpdb;
		$post_name = $post_name.'%';

		$query = "SELECT ID FROM $wpdb->posts WHERE 1=1";
		$args = array();

		if ( !empty ( $post_name ) ) {
		     $query .= " AND post_name LIKE '%s' ";
		     $args[] = $post_name;
		}
		if ( !empty ( $post_type ) ) {
		     $query .= " AND post_type = '%s' ";
		     $args[] = $post_type;
		}

		//$query .= " AND post_status = 'publish' ";

		if ( !empty ( $args ) )
		     return $wpdb->get_var( $wpdb->prepare($query, $args) );

		return 0;
	}
}