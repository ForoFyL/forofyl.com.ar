<?php
/**
*
* user_team [English]
*
* @package language
* @version $Id: user_team.php,v 0.1.4 2009/02/19 15:35:00 mtrs Exp $
* @copyright (c) 2008 mtrs
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
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//


//Begin: User Team  MOD

$lang = array_merge($lang, array(
        'USERNAMES_DUPE_IP'         =>  'Common IP usernames',
        'USERNAMES_DUPE_IP_EXPLAIN' =>	'Users who also used the same IP with this user registration IP adress',
));
//End: User Team  MOD

?>