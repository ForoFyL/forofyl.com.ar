<?php
/**
*
* @package phpBB Statistics
* @version $Id: stats_test.php 162 2010-12-11 13:29:18Z marc1706 $
* @copyright (c) 2009 - 2010 Marc Alexander(marc1706) www.m-a-styles.de, (c) TheUniqueTiger - Nayan Ghosh
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @based on: Forum Statistics by TheUniqueTiger - Nayan Ghosh
* @translator (c) ( Marc Alexander - http://www.m-a-styles.de ), TheUniqueTiger - Nayan Ghosh
*/

if (!defined('IN_PHPBB') || !defined('IN_STATS_MOD'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}


/*	Example:
$lang = array_merge($lang, array(	
	'STATS'								=> 'phpBB Statistics',	

));
*/

$lang = array_merge($lang, array(	
	'STATS_TEST'				=> 'Test',
	'STATS_TEST_EXPLAIN'		=> 'This is just a test add-on',
	'STATS_TEST_SHOW'			=> 'Show test add-on (does not do anything)',
	'STATS_TEST_SHOW_EXPLAIN' 	=> 'This really does not do anything',
	'INFO'						=> 'This add-on is just for testing purposes. You can remove it if you already added an add-on.',
));
?>