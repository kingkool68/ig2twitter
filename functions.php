<?php
require 'vendor/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;
use GuzzleHttp\Client;
include 'class-instagram-scraper.php';

function get_root_url() {
	$url = '';
	if ( isset( $_SERVER['SCRIPT_URI'] ) ) {
		$url = $_SERVER['SCRIPT_URI'];
	}
	if ( ! $url ) {
		$url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'];
	}
	$url = str_replace( '/index.php', '/', $url );
	$url = str_replace( '/new-setup.php', '/', $url );
	if ( ! empty( $_SERVER['HTTPS'] ) ) {
		$url = str_replace( 'http://', 'https://', $url );
	}

	return rtrim( $url, '/' );
}

function truncate( $text, $chars = 140 ) {
	$text = $text . ' ';
	$text = substr( $text, 0, $chars );
	$text = substr( $text, 0, strrpos( $text, ' ' ) );

	return $text;
}

function get_history() {

    $filename = 'HISTORY';
    if ( ! file_exists( $filename ) ) {
		$scraper = new Instagram_Scraper();
		$media = $scraper->get_user_media( INSTAGRAM_SCREEN_NAME );
		$media_item = $media[0];
		$history = array();
		$history[ $media_item->id ] = $media_item->date;
    	file_put_contents( $filename, serialize( $history ) );
    }
    $data = unserialize( file_get_contents( $filename ) );
    if ( ! is_array( $data ) ) {
    	$data = array();
    }

    return $data;
}

function save_history( $data = array() ) {
    if ( ! is_array( $data ) ) {
        $data = array();
    }
    $filename = 'HISTORY';
    file_put_contents( $filename, serialize( $data ) );
}

function tweet_media( $media ) {
    $client = new Client();
    $scraper = new Instagram_Scraper();

    // TODO: Differentiate between pictures and video...
	$id = $media->id;
	$caption = $media->caption;
    $instagram_url = $scraper->get_permalink( $media->code );
	$src = $media->display_src;
	$img = $client->get( $src );
	$img_body = $img->getBody();
	file_put_contents ( $id . '.jpg', $img_body );

	// Create a Twitter connection object
	$connection = new TwitterOAuth( TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, TWITTER_ACCESS_TOKEN, TWITTER_ACCESS_SECRET );

	// Get the path of the image to post
	$twitter_img = getcwd() . '/' . $id . '.jpg';

	// Figure out the maximum length our tweet text can be using URL length variables from https://api.twitter.com/1.1/help/configuration
	$short_url_length = 23;
	$media_length = 24;
	$text_limit = 140 - $media_length - $short_url_length;

	$status = truncate( $caption, $text_limit );
	$status .= ' ' . $instagram_url;

	// Upload the image to get a media_id to associate with the tweet
	$media = $connection->upload( 'media/upload', array( 'media' => $twitter_img ) );
	$args = array(
    	'status' => $status,
	);
	if ( isset( $media->media_id_string ) ) {
		$args['media_ids'] = $media->media_id_string;
	}

	// Post the tweet to Twitter
	$result = $connection->post( 'statuses/update', $args );
	$tweet_id = $result->id_str;
	$tweet_user = $result->user->screen_name;
	$tweet_url = 'https://twitter.com/' . $tweet_user . '/status/' . $tweet_id . '/';

    // Cleanup
	unlink( $id . '.jpg' );
    $output = array(
        'success' => true,
        'tweet_id' => $tweet_id,
        'tweet_user' => $tweet_user,
        'tweet_url' => $tweet_url,
    );

    return $output;
}
