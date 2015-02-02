<?php

// =============
// PHP settings.
// =============

ini_set( 'memory_limit', '128M' ); // Because sometimes phpBB takes lots of memory.
ini_set( 'display_errors', '0' );  // Don't show ugly phpBB errors.

// =======================
// Database configuration.
// =======================

// The values for the following constants are magically rewritten by PHP-Stack.

$dbhost = '%%DB_HOST%%';
$dbname = '%%DB_NAME%%';
$dbuser = '%%DB_USER%%';
$dbpasswd = '%%DB_PASSWORD%%';
$dbport = ''; // Empty for default port

// ====================
// Cache configuration.
// ====================

$acm_type = 'apc';
$load_extensions = 'memcache';

@define( 'PHPBB_ACM_MEMCACHE_HOST', 'localhost' ); // Memcache server hostname
@define( 'PHPBB_ACM_MEMCACHE_PORT', 11211 ); // Memcache server port
@define( 'PHPBB_ACM_MEMCACHE_COMPRESS', false ); // Compress stored data
