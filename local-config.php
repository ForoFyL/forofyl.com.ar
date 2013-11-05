<?php

// =======================
// Database configuration.
// =======================

$dbms = 'mysql'; // Probably mysql
$dbhost = 'localhost'; // Probably localhost
$dbport = ''; // Empty for default port
$dbname = 'forofyl_phpbb'; // Your DB name
$dbuser = 'root'; // Probably root
$dbpasswd = 'blank'; // Probably blank

// ====================
// Cache configuration.
// ====================

$acm_type = 'apc';
$load_extensions = 'memcache';

@define( 'PHPBB_ACM_MEMCACHE_HOST', 'localhost' ); // Memcache server hostname
@define( 'PHPBB_ACM_MEMCACHE_PORT', 11211 ); // Memcache server port
@define( 'PHPBB_ACM_MEMCACHE_COMPRESS', false ); // Compress stored data

// ===================
// Debugging settings.
// ===================

//define( 'DEBUG', true );
//define( 'DEBUG_EXTRA', true );