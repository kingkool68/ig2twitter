<?php
require 'functions.php';
$root_url = get_root_url();

if ( file_exists( 'config.php' ) ) {
    require 'config.php';

    $undefined = array();
    $empty = array();
    $constants_to_check = array( 'INSTAGRAM_SCREEN_NAME', 'TWITTER_CONSUMER_KEY', 'TWITTER_CONSUMER_SECRET', 'TWITTER_ACCESS_TOKEN', 'TWITTER_ACCESS_SECRET' );
    foreach ( $constants_to_check as $constant_name ) {
        if ( ! defined( $constant_name ) ) {
            $undefined[] = $constant_name;
            continue;
        }

        if ( empty( constant( $constant_name ) ) ) {
            $empty[] = $constant_name;
        }
    }

    if ( empty( $undefined ) && empty( $empty ) ) {
        echo '<p>Everything looks good :-)</p>';
        die();
    }

}

// Check if post variables are set
$field_names = array( 'instagram-screen-name', 'twitter-api-key', 'twitter-api-secret', 'twitter-access-token', 'twitter-access-secret' );
$post_data = array();
$missing_data = array();
foreach ( $field_names as $field_name ) {
    if ( isset( $_POST[ $field_name ] ) && ! empty( $_POST[ $field_name ] ) ) {
        $val = filter_var( $_POST[ $field_name ], FILTER_SANITIZE_STRING );
    } else {
        $val = '';
        $missing_data[] = $field_name;
    }
    $new_field_name = str_replace( '-', '_', $field_name );
    $post_data[ $new_field_name ] = $val;
}
extract( $post_data );
?>
<!DOCTYPE html>
 <html>
 	<head>
 		<meta charset="utf-8">
 		<meta http-equiv="X-UA-Compatible" content="IE=edge">
 		<title>Setup</title>
 		<meta name="description" content="">
 		<meta name="author" content="Russell Heimlich">
        <link href="css/set-up.css" rel="stylesheet" type="text/css" media="all">
 	</head>
 	<body>
        <div class="holder">

<?php if ( empty( $missing_data ) ) :
    $scraper = new Instagram_Scraper();
    $user = $scraper->get_user( $instagram_screen_name );
    if ( $user->is_private ) {
        echo $instagram_screen_name . ' is private so we can\'t get their media :(';
        die();
    }
?>
        <h1>Next Step</h1>
        <p>Create a new file on your server called <strong>config.php</strong> and copy and paste the following text:</p>
        <textarea rows="15" cols="60" onfocus="this.select();">
<?php echo '<?php' . "\n"; // How meta! ?>
define( 'INSTAGRAM_SCREEN_NAME', '<?php echo $instagram_screen_name; ?>' );

// Register a new Twitter app and paste the details here (https://apps.twitter.com/app/new)
define( 'TWITTER_CONSUMER_KEY', '<?php echo $twitter_api_key; ?>' );
define( 'TWITTER_CONSUMER_SECRET', '<?php echo $twitter_api_secret; ?>' );
define( 'TWITTER_ACCESS_TOKEN', '<?php echo $twitter_access_token; ?>' );
define( 'TWITTER_ACCESS_SECRET', '<?php echo $twitter_access_secret; ?>' );

// Our own constants
define( 'LOGGING', false );
        </textarea>
        <p>After that, <a href="new-setup.php">check if everything is working</a>.</p>
<?php endif;


if ( ! empty( $missing_data ) ): ?>

		<form method="post">
            <h1>Setup</h1>
            <label for="instagram-screen-name">Instagram Screen Name</label>
			<input type="text" value="<?php echo $instagram_screen_name; ?>" name="instagram-screen-name" id="instagram-screen-name">

			<p>Go to <a href="https://apps.twitter.com/app/new" target="_blank">https://apps.twitter.com/app/new</a> and register a new app.</p>

			<p>Click on the <strong>Keys and Access Tokens</strong> tab. Copy the following application settings values:</p>

			<label for="twitter-api-key">Consumer Key (API Key)</label>
			<input type="text" id="twitter-api-key" name="twitter-api-key" value="<?php echo $twitter_api_key;?>">

			<label for="twitter-api-secret">Consumer Secret (API Secret)</label>
			<input type="text" id="twitter-api-secret" name="twitter-api-secret" value="<?php echo $twitter_api_secret; ?>">

			<p>Under <strong>Your Access Token</strong> generate an access token by clicking the <strong>Create my access token</strong> button. Copy the following access token values:</p>

			<label for="twitter-access-token">Access Token</label>
			<input type="text" id="twitter-access-token" name="twitter-access-token" value="<?php echo $twitter_access_token;?>">

			<label for="twitter-access-secret">Access Token Secret</label>
			<input type="text" id="twitter-access-secret" name="twitter-access-secret" value="<?php echo $twitter_access_secret;?>">

			<button>Submit</button>
		</form>

<?php endif; ?>
        </div>
 	</body>
 </html>
