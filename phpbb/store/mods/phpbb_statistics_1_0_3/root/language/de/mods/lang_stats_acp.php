<?php
/**
*
* @package phpBB Statistics
* @version $Id: lang_stats_acp.php 167 2011-02-09 01:07:15Z marc1706 $
* @copyright (c) 2009 - 2010 Marc Alexander(marc1706) www.m-a-styles.de
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @based on: lang_portal_acp.php included in the Board3 Portal package (www.board3.de)
* @translator (c) ( Marc Alexander - http://www.m-a-styles.de )
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
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine


$lang = array_merge($lang, array(
	'ACP_STATS_VERSION'							=> '<strong>phpBB Statistics v%s</strong>',
	// General
	'ACP_STATS_GENERAL_INFO' 					=> 'phpBB Statistics Administration',
	'ACP_STATS_GENERAL_INFO_EXPLAIN'			=> 'Danke, dass du dich für phpBB Statistics entschieden hast. Auf dieser Seite kannst du die Statistiken verwalten. Diese Anzeige gibt dir einen schnellen Überblick über die verschiedenen Einstellungen. Die Links auf der linken Seite dieser Anzeige ermöglichen dir alle Einstellungen vorzunehmen, welche den phpBB Statistics MOD betreffen.',
	'ACP_STATS_GENERAL_SETTINGS' 				=> 'Allgemeine Einstellungen',
	'ACP_STATS_GENERAL_SETTINGS_EXPLAIN'		=> 'Hier kannst du Einstellungen verändern, die den ganzen phpBB Statistics MOD betreffen',
	'ACP_STATS_ENABLE'							=> 'Statistiken anschalten',
	'ACP_STATS_ENABLE_EXPLAIN'					=> 'Den phpBB Statistics MOD anschalten',
	'ACP_BASIC_BASIC_ENABLE'					=> 'Basis Forum-Statistiken',
	'ACP_BASIC_BASIC_ENABLE_EXPLAIN'			=> 'Diese Statistiken aktivieren',
	'ACP_BASIC_ADVANCED_ENABLE'					=> 'Erweiterte Forum-Statistiken',
	'ACP_BASIC_ADVANCED_ENABLE_EXPLAIN'			=> 'Diese Statistiken aktivieren',
	'ACP_BASIC_MISCELLANEOUS_ENABLE'			=> 'Verschiedene Statistiken',
	'ACP_BASIC_MISCELLANEOUS_ENABLE_EXPLAIN'	=> 'Diese Statistiken aktivieren',
	'ACP_ACTIVITY_FORUMS_ENABLE'				=> 'Statistiken Forumaktivität',
	'ACP_ACTIVITY_FORUMS_ENABLE_EXPLAIN'		=> 'Diese Statistiken aktivieren',
	'ACP_ACTIVITY_TOPICS_ENABLE'				=> 'Statistiken Themenaktivität',
	'ACP_ACTIVITY_TOPICS_ENABLE_EXPLAIN'		=> 'Diese Statistiken aktivieren',
	'ACP_ACTIVITY_USERS_ENABLE'					=> 'Statistiken Benutzeraktivität',
	'ACP_ACTIVITY_USERS_ENABLE_EXPLAIN'			=> 'Diese Statistiken aktivieren',
	'ACP_CONTRIBUTIONS_ATTACHMENTS_ENABLE' 		=> 'Dateianhänge Statistiken',
	'ACP_CONTRIBUTIONS_ATTACHMENTS_ENABLE_EXPLAIN' => 'Diese Statistiken aktivieren',
	'ACP_CONTRIBUTIONS_POLLS_ENABLE'			=> 'Umfragen Statistiken',
	'ACP_CONTRIBUTIONS_POLLS_ENABLE_EXPLAIN'	=> 'Diese Statistiken aktivieren',
	'ACP_PERIODIC_DAILY_ENABLE'					=> 'Tägliche Statistiken',
	'ACP_PERIODIC_DAILY_ENABLE_EXPLAIN'			=> 'Diese Statistiken aktivieren',
	'ACP_PERIODIC_MONTHLY_ENABLE'				=> 'Monatliche Statistiken',
	'ACP_PERIODIC_MONTHLY_ENABLE_EXPLAIN'		=> 'Diese Statistiken aktivieren',
	'ACP_PERIODIC_HOURLY_ENABLE'				=> 'Stündliche Statistiken',
	'ACP_PERIODIC_HOURLY_ENABLE_EXPLAIN'		=> 'Diese Statistiken aktivieren',
	'ACP_SETTINGS_BOARD_ENABLE'					=> 'Statistiken Board-Einstellungen',
	'ACP_SETTINGS_BOARD_ENABLE_EXPLAIN'			=> 'Diese Statistiken aktivieren',
	'ACP_SETTINGS_PROFILE_ENABLE'				=> 'Statistiken Profil-Einstellungen',
	'ACP_SETTINGS_PROFILE_ENABLE_EXPLAIN'		=> 'Diese Statistiken aktivieren',
	'ACP_STATS_RESYNC_TIMEFRAME'				=> 'Dauer bis Resynchronisierung',
	'ACP_STATS_RESYNC_TIMEFRAME_EXPLAIN'		=> 'Wähle die Anzahl der Tage, nach denen die gecacheten Statistiken aktualisiert werden sollen. 0 deaktiviert diese Funktion.',
	
	// Advanced Stats
	'ACP_BASIC_ADVANCED_INFO'					=> 'Erweiterte Forum-Statistiken',
	'ACP_BASIC_ADVANCED_INFO_EXPLAIN'			=> 'Hier kannst du Einstellungen verändern, die die erweiterten Forum-Statistiken betreffen',
	'ACP_BASIC_ADVANCED_SETTINGS'				=> 'Erweiterte Forum-Statistik Einstellungen',
	'ACP_BASIC_ADVANCED_SECURITY'				=> 'Aktiviere sichere, erweiterte Forum-Statistiken',
	'ACP_BASIC_ADVANCED_SECURITY_EXPLAIN'		=> 'Falls aktiviert, werden phpBB Version und Datenbank Information nicht angzeigt',
	'ACP_BASIC_ADVANCED_PRETEND'				=> 'Täusche neueste phpBB Version vor',
	'ACP_BASIC_ADVANCED_PRETEND_EXPLAIN'		=> 'Anstatt der installierten phpBB Version, wird bei den erweiterten Forum-Statistiken die neueste angezeigt.<br /><strong>BEACHTE:</strong> Diese Funktion tritt nur in Kraft, wenn sichere, erweiterte Forum-Statistiken ausgeschaltet sind. Außerdem funktioniert die Funktion nur, wenn auch die Versionsüberprüfung im Admin-Bereich funktioniert.',
	
	// Miscellaneous Stats
	'ACP_BASIC_MISCELLANEOUS_INFO'				=> 'Verschiedene Statistiken',
	'ACP_BASIC_MISCELLANEOUS_INFO_EXPLAIN'		=> 'Hier kannst du Einstellungen verändern, die die "Verschiedenen Statistiken" betreffen',
	'ACP_BASIC_MISCELLANEOUS_SETTINGS'			=> 'Verschiedene Statistiken Einstellungen',
	'ACP_BASIC_MISCELLANEOUS_WARNINGS'			=> 'Verstecke Verwarnungsstatistiken',
	'ACP_BASIC_MISCELLANEOUS_WARNINGS_EXPLAIN'	=> 'Falls dies aktiviert ist, werden die Verwarnungsstatistiken nicht angezeigt',
	'ACP_BASIC_MISCELLANEOUS_BBCODES'			=> 'Resynchronisiere BBCode- und Smiley-Statistiken',
	'ACP_BASIC_MISCELLANEOUS_BBCODES_EXPLAIN'	=> 'Aktiviere diese Option, falls benutzerdefinierte BBCodes hinzugefügt oder geändert wurden und falls die BBCode- und Smiley-Statistiken falsche Werte anzeigen. Nach der Resynchronisation wird dies automatisch wieder deaktiviert',
	'ACP_BASIC_MISCELLANEOUS_TIMEFRAME'			=> 'Dauer bis Resynchronisierung',
	'ACP_BASIC_MISCELLANEOUS_TIMEFRAME_EXPLAIN'	=> 'Wähle die Anzahl der Tage, nach denen die Smiley und BBCode Statistiken aktualisiert werden sollen. 0 deaktiviert diese Funktion.',
	
	// Users Activity Stats
	'ACP_ACTIVITY_USERS_INFO'				=> 'Statistiken Benutzeraktivität',
	'ACP_ACTIVITY_USERS_INFO_EXPLAIN'		=> 'Hier kannst du Einstellungen verändern, die die Statistiken der Benutzeraktivität betreffen',
	'ACP_ACTIVITY_USERS_SETTINGS'			=> 'Statistiken Benutzeraktivtät Einstellungen',
	'ACP_ACTIVITY_USERS_HIDE_ANONYMOUS'			=> 'Verstecke Gäste in den Top XX Benutzerstatistiken',
	'ACP_ACTIVITY_USERS_HIDE_ANONYMOUS_EXPLAIN' => 'Falls dies aktiviert ist, werden Gäste nicht in den Statistiken der Benutzeraktivität angezeigt',
	
	// Add-Ons
	'INSTALLED_ADDONS'						=> 'Installierte Add-Ons',
	'UNINSTALLED_ADDONS'					=> 'Nicht installierte Add-Ons',

));

?>