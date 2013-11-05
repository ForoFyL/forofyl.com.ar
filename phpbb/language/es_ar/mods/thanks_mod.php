<?php
/**
*
* thanks_mod[English]
*
* @package language
* @version $Id: thanks.php,v 127 2010-04-17 10:02:51Палыч $
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
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'GIVEN'						=> 'Agradeci&oacute;',
	'GRATITUDES'				=> 'Agradecimientos',
	
	'INCORRECT_THANKS'			=> 'Agradecimiento inv&aacute;lido',
	
	'JUMP_TO_FORUM'				=> 'Saltar a foro',
	'JUMP_TO_TOPIC'				=> 'Saltar a thread',

	'FOR_MESSAGE'				=> ' por post',
	'FURTHER_THANKS'     	    => ' y un usuario más',
	'FURTHER_THANKS_PL'         => ' y %d usuarios más',
	
	'NO_VIEW_USERS_THANKS'		=> 'No estás autorizado a ver la lista de agradecimientos.',

	'RECEIVED'					=> 'Le agradecieron',
	'REMOVE_THANKS'				=> 'Remover agradecimiento a ',
	'REMOVE_THANKS_CONFIRM'		=> '¿Estás seguro de que querés remover tu agradecimiento?',
	'REPUT'						=> 'Rating',
	'REPUT_TOPLIST'				=> 'Toplist',
	'RETING_LOGIN_EXPLAIN'		=> 'No estás autorizado a ver la toplist.',
	'RATING_NO_VIEW_TOPLIST'	=> 'No estás autorizado a ver la toplist.',
	'RATING_VIEW_TOPLIST_NO'	=> 'Toplist está vacía o deshabilitada por la administración',
	'RATING_FORUM'				=> 'Foro',
	'RATING_POST'				=> 'Post',
	'RATING_TOP_FORUM'			=> 'Rating de foros',
	'RATING_TOP_POST'			=> 'Rating de posts',
	'RATING_TOP_TOPIC'			=> 'Rating de threads',	
	'RATING_TOPIC'				=> 'Thread',
	'RETURN_POST'				=> 'Volver',

	'THANK'						=> 'vez',
	'THANK_FROM'				=> 'de',
	'THANK_TEXT_1'				=> 'Por este mensaje le dieron las gracias a ',
	'THANK_TEXT_2'				=> ':',
	'THANK_TEXT_2pl'			=> ' ha recibido %d agradecimientos',
	'THANK_POST'				=> 'Agradecer por el mensaje del autor: ',
	'THANKS'					=> 'veces',
	'THANKS_BACK'				=> 'Volver',
	'THANKS_INFO_GIVE'			=> 'Acabás de hacer un agradecimiento por un mensaje.',
	'THANKS_INFO_REMOVE'		=> 'Acabás de remover tu agradecimiento.',
	'THANKS_LIST'				=> 'Ver/Cerrar lista',
	'THANKS_PM_MES_GIVE'		=> 'Gracias por tu mensaje',
	'THANKS_PM_MES_REMOVE'		=> 'Remover agradecimiento',
	'THANKS_PM_SUBJECT_GIVE'	=> 'Gracias por tu mensaje',
	'THANKS_PM_SUBJECT_REMOVE'	=> 'Remover agradecimiento',
	'THANKS_USER'				=> 'Lista de agradecimientos',
// Install block
	'THANKS_INSTALLED'			=> 'Gracias por tu mensaje',
	'THANKS_INSTALLED_EXPLAIN'  => '<strong>CAUTION!<br />Strongly recommend to run this installation only after following the instructions on changes to the code files conference (or perform the installation using AutoMod)!<br />Also strongly recommend select Yes to Display Full Results (below)!</strong>',
	'THANKS_CUSTOM_FUNCTION'	=> 'Update values table _thanks',
));
?>