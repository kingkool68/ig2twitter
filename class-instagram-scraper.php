<?php
use GuzzleHttp\Client;

class Instagram_Scraper {

	private $INSTAGRAM_URL = 'https://www.instagram.com';

	public function __construct() {

	}

	public function get_json_payload( $request_url = '', $args = array() ) {
		$client = new Client();
		$response = $client->get( $request_url, $args );
		$response_code = $response->getStatusCode();


		if ( $response_code != 200 ) {
			return;
		}
		$body = $response->getBody();

		// Parse the page response and extract the JSON string.
		// via https://github.com/raiym/instagram-php-scraper/blob/849f464bf53f84a93f86d1ecc6c806cc61c27fdc/src/InstagramScraper/Instagram.php#L32
		$arr = explode( 'window._sharedData = ', $body );
		$json = explode( ';</script>', $arr[1] );
		$json = $json[0];

		return json_decode( $json );
	}

	public function get_user_media( $username = '' ) {
		$url = $this->INSTAGRAM_URL . '/' . $username . '/';
		$json = $this->get_json_payload( $url );

		if ( isset( $json->entry_data->ProfilePage[0]->user->media->nodes ) ) {
			return $json->entry_data->ProfilePage[0]->user->media->nodes;
		}

		return false;
	}
	public function get_user( $username = '' ) {
		$url = $this->INSTAGRAM_URL . '/' . $username . '/';
		$json = $this->get_json_payload( $url );

		$output = array();
		if ( isset( $json->entry_data->ProfilePage[0]->user ) ) {
			$user = $json->entry_data->ProfilePage[0]->user;
			$output['username'] = $user->username;
			$output['full_name'] = $user->full_name;
			$output['biography'] = $user->biography;
			$output['id'] = $user->id;
			$output['profile_pic_url'] = $user->profile_pic_url;
			$output['follows'] = $user->follows->count;
			$output['followed_by'] = $user->followed_by->count;
			$output['media_count'] = $user->media->count;
			$output['page_info'] = $user->media->page_info;
			$output['external_url'] = $user->external_url;
			$output['is_verified'] = $user->is_verified;
			$output['is_private'] = $user->is_private;
			$output = (object) $output;
		}

		return $output;
	}

	public function get_permalink( $code = '' ) {
		return $this->INSTAGRAM_URL . '/p/' . $code . '/';
	}
}
