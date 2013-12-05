<?php  

class DWSF_Twitter {
    public $consumer_key;
    public $consumer_secret; 
    public $token;

    function __construct( $consumer_key, $consumer_secret ){
        $this->consumer_key = $consumer_key;
        $this->consumer_secret = $consumer_secret;
        $this->token = $this->get_tweets_bearer_token();
    }

    function update_tweet_urls($content) {
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


    function get_tweets_bearer_token( ){
        $consumer_key = rawurlencode( $this->consumer_key );
        $consumer_secret = rawurlencode( $this->consumer_secret );

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

            $token = array(
                'consumer_key'      => $consumer_key,
                'access_token'      => $result->access_token
            );
            update_option( 'dwsf-twitter-token', $token );
        }
        return $token;

    }

    function get_token( ) {
        $token = get_option( 'dwsf-twitter-token' );
        if( $token && is_array($token) ) {
            return $token;
        }
        return false;
    }
    function get_tweets( $args ){
        extract($args);

        $token = $this->token;

        if( ! $token['access_token'] ) {
             return false;
        }

        if( strpos($query, 'from:') === 0  ) {
            $query_type = 'user_timeline';
            $query = substr($query, 5);
            $url = 'https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name='.rawurlencode($query).'&count='.$number;
            if( $exclude_replies ) {
                $url .= '&exclude_replies=true';
            }
        } else {
            $query_type = 'search';
            $url =  'https://api.twitter.com/1.1/search/tweets.json?q='.rawurlencode($query).'&count='.$number;
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
        return $tweets;
    }
}
?>