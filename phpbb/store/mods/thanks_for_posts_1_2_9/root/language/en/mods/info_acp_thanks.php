<?php
/**
*
* mod_thanks [English]
*
* @package language
* @version $Id: info_acp_thanks.php 128 2010-05-31 10:02:51Палыч $
* @copyright (c) 2008 phpBB Group
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
	'acl_f_thanks' 						=> array('lang' => 'Can thanks for posts', 'cat' => 'misc'),
	'acl_u_viewthanks' 					=> array('lang' => 'Can view list of all thanks', 'cat' => 'misc'),
	'acl_u_viewtoplist'					=> array('lang' => 'Can view toplist', 'cat' => 'misc'),
	'ACP_DELTHANKS'						=> 'Deleted recorded thanks',
	'ACP_DELPOST'						=> 'No posts (deleted)',
	'ACP_DELUPOST'						=> 'No users (deleted)',	
	'ACP_POSTS'							=> 'Total posts',
	'ACP_POSTSEND'						=> 'Remains posts with thanks',
	'ACP_POSTSTHANKS'					=> 'Total posts with thanks',
	'ACP_THANKS'						=> 'Thanks for posts',
	'ACP_THANKS_MOD_VER'				=> 'MOD version: ',
	'ACP_THANKS_TRUNCATE'				=> 'Clear the list of thanks',
	'ACP_ALLTHANKS'						=> 'Was taken into account thanks',
	'ACP_THANKSEND'						=> 'It remains to take into account thanks',
	'ACP_THANKS_REPUT'					=> 'Options Rating',
	'ACP_THANKS_REPUT_SETTINGS'			=> 'Options Rating',
	'ACP_THANKS_REPUT_SETTINGS_EXPLAIN'	=> 'Here you can set the default settings for the rating for posts, topics and forums, based on the system Thanks. <br /> Subject (post, topic or forum), which has the largest total number of appreciation, is taken as 100% rating.',
	'ACP_THANKS_SETTINGS'				=> 'Thanks Settings',
	'ACP_THANKS_SETTINGS_EXPLAIN'		=> 'Here you can install custom values of functions Thanks for posts.',
	'ACP_THANKS_REFRESH'				=> 'Update counters',
	'ACP_USERS'							=> 'Total users',
	'ACP_USERSEND'						=> 'Remains users who thanked',
	'ACP_USERSTHANKS'					=> 'Total users who thanked',

	'GRAPHIC_BLOCK_BACK'				=> 'styles/prosilver/theme/images/reput_block_back.gif',
	'GRAPHIC_BLOCK_RED'					=> 'styles/prosilver/theme/images/reput_block_red.gif',
	'GRAPHIC_DEFAULT'					=> 'Images',
	'GRAPHIC_OPTIONS'					=> 'Graphics Options',
	'GRAPHIC_STAR_BACK'					=> 'styles/prosilver/theme/images/reput_star_back.gif',
	'GRAPHIC_STAR_BLUE'					=> 'styles/prosilver/theme/images/reput_star_blue.gif',
	'GRAPHIC_STAR_GOLD'					=> 'styles/prosilver/theme/images/reput_star_gold.gif',
	
	'IMG_THANKPOSTS'					=> 'To thank for the message',
	'IMG_REMOVETHANKS'					=> 'Cancel thanks',

	'REFRESH'							=> 'Refresh',
	'REMOVE_THANKS'						=> 'Remove of thanks',
	'REMOVE_THANKS_EXPLAIN'				=> 'If enabled users can remove of thanks',
	
	'STEPR'								=> ' - executed, step %s',
	
	'THANKS_COUNTERS_VIEW'				=> 'Counters of thanks',
	'THANKS_COUNTERS_VIEW_EXPLAIN'		=> 'If enabled, the block information about the author will show the number of issued/received of thanks',
	'THANKS_FORUM_REPUT_VIEW'			=> 'Show rating for forums',
	'THANKS_FORUM_REPUT_VIEW_EXPLAIN'	=> 'If enabled, will be displayed rating forum in the list of forums',
	'THANKS_INFO_PAGE'					=> 'Information messages',
	'THANKS_INFO_PAGE_EXPLAIN'			=> 'If enabled, after the issuance / cancellation of gratitude displayed information messages',
	'THANKS_NUMBER'						=> 'Number of thanks from the list in profile',
	'THANKS_NUMBER_EXPLAIN'				=> 'Maximum number of displayed gratitude when viewing profile. <br /> <strong> Remember that slow down will be noticed if this value is set over 250. </strong>',
	'THANKS_NUMBER_DIGITS'				=> 'The number of decimal places for rating',
	'THANKS_NUMBER_DIGITS_EXPLAIN'		=> 'Specify the number of decimal places for the value rating',
	'THANKS_NUMBER_ROW_REPUT'			=> 'The number of rows in the toplist for rating',
	'THANKS_NUMBER_ROW_REPUT_EXPLAIN'	=> 'Specify the number of rows to display a toplist rating posts, topics and forums',
	'THANKS_NUMBER_POST'				=> 'Number of thanks from the list in post',
	'THANKS_NUMBER_POST_EXPLAIN'		=> 'Maximum number of displayed gratitude when viewing post. <br /> <strong> Remember that slow down will be noticed if this value is set over 250. </strong>',
	'THANKS_ONLY_FIRST_POST'			=> 'Only to the first post in the topic',
	'THANKS_ONLY_FIRST_POST_EXPLAIN'	=> 'If enabled, you can declare of thanks only the first post in the topic',
	'THANKS_POST_REPUT_VIEW'			=> 'Show rating posts',
	'THANKS_POST_REPUT_VIEW_EXPLAIN'	=> 'If enabled, will display the rating value for posts when viewing a topic',
	'THANKS_POSTLIST_VIEW'				=> 'List thanks in post',
	'THANKS_POSTLIST_VIEW_EXPLAIN'		=> 'If enabled, the message will display a list of users, to thank the author of the post. <br/> Note that this option would be effective if the administrator has enabled the permission to give thanks for the post in that forum.',
	'THANKS_PROFILELIST_VIEW'			=> 'List thanks in profile',
	'THANKS_PROFILELIST_VIEW_EXPLAIN'	=> 'If this option is enabled, complete of thanks info including number of thanks and which posts a user received of thanks will be displayed.',
	'THANKS_REFRESH'					=> 'Update counters of thanks',
	'THANKS_REFRESH_EXPLAIN'			=> 'Here you can update the counters of thanks after the mass removal of posts/topics/users. This may take some time.',
	'THANKS_REFRESH_MSG'				=> 'Upgrade performance can take a few minutes. All incorrect entries of thanks will be deleted! <br /> Аction is not reversible!',
	'THANKS_REFRESHED_MSG'				=> 'Counters updated',
	'THANKS_REPUT_GRAPHIC'				=> 'Graphic display of the rating',
	'THANKS_REPUT_GRAPHIC_EXPLAIN'		=> 'If enabled, the rating value will be displayed graphically using the below pictures',
	'THANKS_REPUT_HEIGHT'				=> 'Height graphics',
	'THANKS_REPUT_HEIGHT_EXPLAIN'		=> 'Specify the height of the slider for the ranking in pixels. <br /> <strong> Attention! To correctly display should indicate the height, equal to the height of the following image! </strong>',
	'THANKS_REPUT_IMAGE'				=> 'The main image for the slider',
	'THANKS_REPUT_IMAGE_DEFAULT'		=> 'Examples of graphic images',
	'THANKS_REPUT_IMAGE_DEFAULT_EXPLAIN' => 'Here you can see the image itself and the path to the image for the style prosilver. Image sizes 15x15 pixels. <br /> You can draw your own images - the foreground and background. <strong>The height and width of the image should be the same for the correct construction of the graphical scale.</strong>',
	'THANKS_REPUT_IMAGE_EXPLAIN'		=> 'The path relative to the root folder of phpBB basic image chosen for the graphic scale.',
	'THANKS_REPUT_IMAGE_BACK'			=> 'The background image for the slider',
	'THANKS_REPUT_IMAGE_BACK_EXPLAIN'	=> 'The path relative to the root of phpBB to a background image, chosen for the graphic scale.',
	'THANKS_REPUT_LEVEL'				=> 'Number of images in a graphic scale',
	'THANKS_REPUT_LEVEL_EXPLAIN'		=> 'The maximum number of images corresponding to 100% of the value of the rating scale in the graphic',
	'THANKS_TIME_VIEW'					=> 'THANKs time',
	'THANKS_TIME_VIEW_EXPLAIN'			=> 'If enabled, in the post will be displayed THANKs time',
	'THANKS_TOP_NUMBER'					=> 'The number of users in toplist',
	'THANKS_TOP_NUMBER_EXPLAIN'			=> 'Specify the number of users to print to the toplist',
	'THANKS_TOPIC_REPUT_VIEW'			=> 'Rating for topics',
	'THANKS_TOPIC_REPUT_VIEW_EXPLAIN'	=> 'If included will be displayed rating for topic when viewing a forum',
	'TRUNCATE'							=> 'Clean',	
	'TRUNCATE_THANKS'					=> 'Clear the list of thanks',
	'TRUNCATE_THANKS_EXPLAIN'			=> 'This procedure completely clears all counters thanks (removes all issued gratitude). <br /> Action is not reversible!',
	'TRUNCATE_THANKS_MSG'				=> 'Counters thanks cleaned.',
));
?>