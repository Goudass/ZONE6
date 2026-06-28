<?php
/**
 * Bootstrap WordPress and run an eval file (fallback without WP-CLI).
 */

if ( $argc < 3 ) {
	fwrite( STDERR, "Usage: php run-wp-eval.php <wp-path> <eval-file.php>\n" );
	exit( 1 );
}

$wp_path   = rtrim( $argv[1], '/\\' );
$eval_file = $argv[2];

if ( ! is_file( $wp_path . '/wp-load.php' ) ) {
	fwrite( STDERR, "Invalid WordPress path: {$wp_path}\n" );
	exit( 1 );
}

if ( ! is_file( $eval_file ) ) {
	fwrite( STDERR, "Missing eval file: {$eval_file}\n" );
	exit( 1 );
}

define( 'WP_USE_THEMES', false );
require $wp_path . '/wp-load.php';
require $eval_file;

echo "OK: sample content seeded.\n";
