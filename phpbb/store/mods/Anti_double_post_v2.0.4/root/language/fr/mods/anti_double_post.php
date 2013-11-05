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
	'ADP_DOUBLE_POST'		=> 'Vous ne pouvez pas poster alors que vous êtes le dernier posteur du sujet.<br /><br />%sMerci d\'éditer votre message%s. ',	
	
	'FORUM_ADP'				=> 'MOD Anti-Double Posts',
	'ADP_ENABLE'			=> 'Activation du MOD',
	
	'ADP_ADMINS'			=> 'Les administrateurs peuvent faire des doubles posts',
	'ADP_MODOS'				=> 'Les modérateurs peuvent faire des doubles posts',
	
	'ADP_AUTO_EDIT'			=> 'Fusion avec le dernier message',
	'ADP_AUTO_EDIT_EXPLAIN'	=> '<strong>Oui</strong> : le double post est fusionné avec le dernier message du topic.<br/><strong>Non</strong> : un message d\'erreur apparaît.',
	'ADP_TEXT_EDIT'			=> 'Texte de séparation',
	'ADP_TEXT_EDIT_EXPLAIN'	=> 'Texte délimitant le message original du double post lorsque la fusion est effectuée autmatiquement (oui à l\'option précédente).<br />Utilisez <strong>%D</strong> pour insérer la date et l\'heure du double post.',
	
	'ADP_ALWAYS'			=> 'Toujours empêcher les double posts',
	'ADP_ALWAYS_EXPLAIN'	=> 'Si oui, les double posts seront toujours interdits dans ce forum. Les paramètres suivants ne seront donc pas pris en compte.',
	'ADP_DAYS'				=> 'Nombre de jours',
	'ADP_DAYS_EXPLAIN'		=> 'Nombre de jours pendant lesquels un double post n\'est pas possible.',
	'ADP_HOURS'				=> 'Nombre d\'heures',
	'ADP_HOURS_EXPLAIN'		=> 'Nombre d\'heures pendant lesquels un double post n\'est pas possible.',
	'ADP_MINS'				=> 'Nombre de minutes',
	'ADP_MINS_EXPLAIN'		=> 'Nombre de minutes pendant lesquels un double post n\'est pas possible.',
	'ADP_SECS'				=> 'Nombre de secondes',
	'ADP_SECS_EXPLAIN'		=> 'Nombre de secondes pendant lesquels un double post n\'est pas possible.',
	'ADP_APPLY_TO_ALL'				=> 'Appliquer à tous les forums',
	'ADP_APPLY_TO_ALL_EXPLAIN'		=> '<strong>ATTENTION :</strong> En cochant cette case, les paramètres ci-dessus seront appliqués à tous vos forums.',
	
	'ADDED_PERMISSIONS'		=> 'Les permissions du MOD Anti Double Post ont été ajoutées avec succès.',	
));

?>