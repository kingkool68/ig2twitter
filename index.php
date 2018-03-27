<?php
require 'functions.php';

// Set-up some stuff...
$root_url = get_root_url();

if ( ! file_exists( 'config.php' ) ) {
	header( 'Location: ' . $root_url . '/new-setup.php' );
	die();
}
require 'config.php';
if ( ! defined( 'LOGGING' ) ) {
	define( 'LOGGING', false );
}

$history = get_history();

$scraper = new Instagram_Scraper();
$media = $scraper->get_user_media( INSTAGRAM_SCREEN_NAME );
$media_to_tweet = array();
foreach ( $media as $item ) {
	if ( isset( $history[ $item->node->id ] ) ) {
		break;
	}
	$media_to_tweet[] = $item->node;
}
// The items from Instagram go from most recent to least recent, we need to do the reverse order when we tweet them.
$media_to_tweet = array_reverse( $media_to_tweet );
foreach( $media_to_tweet as $item ) {
	$result = tweet_media( $item );
	if ( $result['success'] ) {
		$history[ $item->id ] = $item->taken_at_timestamp;

		echo '<a href="' . $scraper->get_permalink( $item->shortcode ) . '" target="_blank">';
		echo '<img src="' . $item->thumbnail_src . '" width="320">';
		echo '</a>';
		echo '<p>' . $item->$media->edge_media_to_caption->edges[0]->node->text . '</p>';
		echo '<hr>';
	}
}

save_history( $history );

die();

// Time to show something by default...
if ( LOGGING ) {
	$tbody = array();
	$file = fopen( 'log.csv', 'r' );
	while( ($row = fgetcsv( $file ) ) !== false ) {
		$tbody[] = '<td>' . implode( '</td><td>', $row ) . '</td>';
	}
	$tbody = array_reverse( $tbody );
	?>
	<h1><?php echo count( $tbody );?> Instagram photos have been tweeted!</h1>
	<table>
		<thead>
			<tr>
				<th colspan="2">Date/Time</th>
				<th>Instagram URL</th>
				<th>Tweet</th>
				<th>Caption</th>
			</tr>
		</thead>
		<tbody>
			<tr>
			<?php echo implode( '</tr><tr>', $tbody ); ?>
			</tr>
		</tbody>
	</table>
	<?php
}
