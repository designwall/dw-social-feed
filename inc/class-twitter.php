<?php

class DWSF_Twitter extends DWSF_Feed {
    public $consumer_key;
    public $consumer_secret;
    public $token;

    public $profile; //current_profile
    public $name = 'twitter';
    public $slug = 'tw';
    public $task_hook_name = 'dwsf_twitter_hook';

    public function __construct(){
        parent::__construct();
        $options = get_option('wall_social_feed_options');

        $this->consumer_key = $options['general']['twitter_consumer_key'];
        $this->consumer_secret = $options['general']['twitter_consumer_secret'];

        $this->token = $this->get_tweets_bearer_token();
    }


    public function update_tweet_urls($content) {
        $maxLen = 16;
        //split long words
        $pattern = '/[^\s\t]{'.$maxLen.'}[^\s\.\,\+\-\_]+/';
        $content = preg_replace($pattern, '$0 ', $content);

        //
        $pattern = '/\w{2,4}\:\/\/[^\s\"]+/';
        $content = preg_replace($pattern, '<a href="$0" title="" target="_blank">$0</a>', $content);

        //search
        $pattern = '/\#([a-zA-Z0-9_-]+)/';
        $content = preg_replace($pattern, '<a href="https://twitter.com/search?q=%23$1&src=hash" title="" target="_blank">$0</a>', $content);
        //user
        $pattern = '/\@([a-zA-Z0-9_-]+)/';
        $content = preg_replace($pattern, '<a href="https://twitter.com/#!/$1" title="" target="_blank">$0</a>', $content);

        return $content;
    }


    public function get_tweets_bearer_token( ){
        $consumer_key = rawurlencode( $this->consumer_key );
        $consumer_secret = rawurlencode( $this->consumer_secret );

        if( ! $consumer_key || ! $consumer_secret ) {
            return false;
        }
        $token = $this->get_token();

        if( ! is_array($token) || empty($token) || $token['consumer_key'] != $consumer_key || empty($token['access_token']) ) {

            $authorization = base64_encode( $consumer_key . ':' . $consumer_secret );
            $args = array(
                'httpversion' => '1.1',
                'headers' => array(
                    'Authorization' => 'Basic ' . $authorization,
                    'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8'
                ),
                'body' => array( 'grant_type' => 'client_credentials' )
            );
            add_filter('https_ssl_verify', '__return_false');

            $remote_get_tweets = wp_remote_post( 'https://api.twitter.com/oauth2/token', $args );
            $result = json_decode( wp_remote_retrieve_body(  $remote_get_tweets ) );
            if( isset($result->errors) ) {
                foreach ($result->errors as $error) {
                    $error->message . '<br>';
                }
                return $error;
            }
            $token = array(
                'consumer_key'      => $consumer_key,
                'access_token'      => $result->access_token
            );
            update_option( 'dwsf-twitter-token', $token );
        }
        return $token;

    }

    public function get_token( ) {
        $token = get_option( 'dwsf-twitter-token' );
        if( $token && is_array($token) ) {
            return $token;
        }
        return false;
    }

    public function fetch_content( $profile ){

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

        $token = $this->token;

        if( ! $token['access_token'] ) {
             return false;
        }

        if( strpos($profile['query'], 'from:') === 0  ) {
            $query_type = 'user_timeline';
            $profile['query'] = substr($profile['query'], 5);
            $url = 'https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name='.rawurlencode($profile['query']).'&count='.$profile['limit'];
            if( isset($profile['exclude_replies']) && $profile['exclude_replies'] ) {
                $url .= '&exclude_replies=true';
            }
        } else {
            $query_type = 'search';
            $url =  'https://api.twitter.com/1.1/search/tweets.json?q='.rawurlencode($profile['query']).'&count='.$profile['limit'];
        }

        $remote_get_tweets = wp_remote_get( $url, array(
            'headers'   => array(
                'Authorization' => 'Bearer '. (is_array($token) && isset($token['access_token']) ? $token['access_token'] : '')
            ),
             // disable checking SSL sertificates
            'sslverify'=>false
        ) );

        $result = json_decode( wp_remote_retrieve_body( $remote_get_tweets ) );

        if( isset( $result->errors ) ) {
            if( $result->errors[0]->code == 89 || $result->errors[0]->code == 215 ) {
                //re-generate token
                delete_option( 'dwsf-twitter-token' );
                $this->get_tweets_bearer_token();
                return $this->get_tweets();
            } else {
                print_r($result);
                die('');
            }
        }


        $tweets = array();
        if( 'user_timeline' == $query_type ) {
            if( !empty($result) ) {
                $tweets = $result;
            }
        } else {
            if( !empty($result->statuses) ) {
                $tweets = $result->statuses;
            }

        }
        $posts = array();
        foreach ($tweets as $tweet) {
            if( $profile['status'] != 'enable' )
                continue;

            $title = '#tweet-' . $tweet->id_str;
            if( $query_type != 'user_timeline' ) {
                $title .= ' ' . __('by','dwsf') . ' @' . $tweet->user->screen_name;
            }
            $post_id = $this->post_exists( $title );

            if( $post_id && !$profile['update_post'] ) {
                continue;
            }

            $posts[$tweet->id_str] = array(
                'origin_post'    => $tweet->text,
                'post_content'   => $this->make_clickable( $tweet->text ),
                'post_title'     => $title,
                'post_date'      => date( 'Y-m-d H:i:s',strtotime($tweet->created_at) ),
                'meta_fields'    => array(
                    'tweet_source'    => $tweet->source,
                    'tweet_link'      => 'https://twitter.com/' . $tweet->user->screen_name . '/' . $tweet->id_str
                ),
                'entities'      => $tweet->entities
            );

            if( $post_id ) {
                $posts[$tweet->id_str]['ID'] = $post_id;
            }

            $posts[$tweet->id_str] = array_merge( $posts[$tweet->id_str], $this->generate_post_template( $profile ) );
        }
        return $posts;
    }

    public function make_clickable( $text ) {
        $text = make_clickable( sanitize_text_field( $text ) );
        $text = preg_replace('#@([\\d\\w]+)#', '<a href="http://twitter.com/$1">$0</a>', $text);
        $text = preg_replace('/#([\\d\\w]+)/', '<a href="http://twitter.com/search?q=%23$1&src=hash">$0</a>', $text);
        return $text;
    }

    public function add_thumbnail( $post_id, $post, $width, $height, $add ) {
        if( $add ){
            //images
            $img_caption = $this->getImageCaption($post['post_content']);

            if( isset($post['entities']->media) && $post['entities']->media[0] ) {
                $media = $post['entities']->media[0];
                if( $media->type == 'photo' ) {
                    $source_images = $media->media_url;
                }
            } else {
                $source_images = $this->getTweetImage($post['origin_post'], $img_caption, $img_caption, $width, $height);
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

    public function getTweetImage($tweet, $caption, $alt, $width_limit, $height_limit) {
        $url = $this->parseTweetUrls($tweet);
        if( $url ) {
            $info = $this->parsePageInfo($url, $width_limit, $height_limit);
            if(count($info['images'])) {
                $url = $info['images'][0];
                if( $this->checkImgSize($url,$width_limit, $height_limit) ){
                    return $url;
                }
                return false;
            }
        }
        return false;
    }

    public function parseTweetUrls($content) {
        //
        $pattern = '/\w{2,4}\:\/\/[^\s\"]+/';
        preg_match($pattern, $content, $matches);

        if(isset($matches[0])) {
            return $this->getRealUrl($matches[0]);
        } else {
            return false;
        }
    }
}

$GLOBALS['dwsf_twitter'] = new DWSF_Twitter();

?>