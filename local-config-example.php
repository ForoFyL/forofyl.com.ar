<?php

// =======================
// Database configuration.
// =======================

$dbms = 'mysql'; // Probably mysql
$dbhost = 'localhost'; // Probably localhost
$dbport = ''; // Empty for default port
$dbname = 'forofyl_phpbb'; // Your DB name
$dbuser = 'forofyl_admin'; // Change the default user for production!
$dbpasswd = 'forofyl'; // Also change the pass for production!

// ====================
// Cache configuration.
// ====================

$acm_type = 'null';
$load_extensions = '';

// ===================
// Debugging settings.
// ===================

define( 'DEBUG', true );
define( 'DEBUG_EXTRA', true );
