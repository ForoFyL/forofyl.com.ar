<?php

// ================================================================
// We're gonna keep configuration files outside phpBB installation,
// just to keep it as clean as possible.
// ================================================================

$current_dir = dirname( __FILE__ );

if ( file_exists( $local_config =  $current_dir . '/../local-config.php' ) ) {
	include_once( $local_config );
}
else {
	// A second config.php is outside phpBB because PHPBB-Stack expects it to be in the root folder.
	include_once( $current_dir  . '/../config.php' );
}

// ======================================================================
// Load common configuration, for both local and production environments.
// ======================================================================

include_once( $current_dir . '/../common-config.php' );