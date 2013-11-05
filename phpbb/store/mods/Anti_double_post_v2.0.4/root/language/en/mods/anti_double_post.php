<?php
/** 
*
* acp_forums [Standard french]
* translated originally by PhpBB-fr.com <http://www.phpbb-fr.com/> and phpBB.biz <http://www.phpBB.biz>
*
* @package language
* @version $Id: forums.php,v 1.21 2008/04/10 12:53:34 elglobo Exp $
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
	'ADP_DOUBLE_POST'		=> 'You cannot post because you are the last poster in this topic.<br /><br />%sPlease edit your post%s.',

	'FORUM_ADP'				=> 'Anti-Double Posts MOD',
	'ADP_ENABLE'			=> 'MOD activation',
	
	'ADP_ADMINS'			=> 'Administrators can make double post',
	'ADP_MODOS'				=> 'Moderators can make double post',
	
	'ADP_AUTO_EDIT'			=> 'Add to the latest message',
	'ADP_AUTO_EDIT_EXPLAIN'	=> '<strong>Yes</strong> : double posts are added to the topic\'s latest message.<br/><strong>No</strong> : display an error message.', 
	'ADP_TEXT_EDIT'			=> 'Separator',
	'ADP_TEXT_EDIT_EXPLAIN'	=> 'This text is insered between the latest post and the double post.<br />Use <strong>%D</strong> in order to insert the double post\'s time.', 
	
	'ADP_ALWAYS'			=> 'Always disallow double posts.',
	'ADP_ALWAYS_EXPLAIN'	=> 'Yes : double posts will be always disallowed. The next parameters will not be considered.',
	'ADP_DAYS'				=> 'Number of days',
	'ADP_DAYS_EXPLAIN'		=> 'Number of days during which double posts are not possible.',
	'ADP_HOURS'				=> 'Number of hours',
	'ADP_HOURS_EXPLAIN'		=> 'Number of hours during which double posts are not possible.',
	'ADP_MINS'				=> 'Number of minutes',
	'ADP_MINS_EXPLAIN'		=> 'Number of minutes during which double posts are not possible.',
	'ADP_SECS'				=> 'Number of seconds',
	'ADP_SECS_EXPLAIN'		=> 'Number of seconds during which double posts are not possible.',
	'ADP_APPLY_TO_ALL'				=> 'Apply to all forums',
	'ADP_APPLY_TO_ALL_EXPLAIN'		=> '<strong>WARNING :</strong> If you check this, all forums will be set with this ADP settings.',	
	
	
	'ADDED_PERMISSIONS'		=> 'You have successfully added Anti Double Post MOD\' permission options to your database.',	
));

?>