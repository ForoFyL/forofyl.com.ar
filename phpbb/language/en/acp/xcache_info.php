<?php
/**
*
* acp_xcache_info [English]
*
* @package language
* @version $Id: xcache_info.php joebert $
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'SLOT_USE'				=> '<acronym title="Slot Usage">SU</acronym>',
	'MEM_USE'				=> '<acronym title="Memory Usage">MU</acronym>',
	'COMPILING'				=> '<acronym title="Compiling, \'yes\' if the cache is busy compiling php script">CP</acronym>',
	'HITS'					=> '<acronym title="Hit Rate, cache hits">HR</acronym>',
	'CLOGS'					=> '<acronym title="Compiling Clogs, when compiling is needed but blocked because the cache is busy compiling already">CC</acronym>',
	'OOMS'					=> '<acronym title="Out Of Memory, how many times a new item should be stored but there isn\'t enough memory in the cache">OOM</acronym>',
	'PROTECTED'				=> '<acronym title="Read Only, whether read-only protection is enabled for this cache">RO</acronym>',
	'DELETED'				=> '<acronym title="Deletes Pending, the number of expired items that still have references to them">DP</acronym>',
	'GC'					=> '<acronym title="Garbage Collection countdown">GC</acronym>',
	'GC_AND_EMPTY'			=> '<acronym title="Garbage Collection countdown and manual emptying">GC</acronym>',
	'CLEAR_CONFIRM'			=> 'Are you sure you want to empty this cache ?',
	'CLEAR'					=> 'Empty',
	'CACHES'				=> 'Caches',
	'CACHE'					=> 'Cache',
	'BLOCKS'				=> 'Blocks',
	'PHPINFO'				=> 'PHPInfo',
	'FREE_BLOCKS'			=> 'Free Blocks',
	'SIZE'					=> 'Size',
	'OFFSET'				=> 'Offset',
	'NOT_LOADED'			=> 'XCache is not loaded',
	'XCACHE_STATUS_EXPLAIN'	=> 'Place your mouse over the headers in the cache list to see what they stand for.',
	'CLEARED_XCACHE'		=>	'Cleared XCache: <b>%s</b>'
));

?>
