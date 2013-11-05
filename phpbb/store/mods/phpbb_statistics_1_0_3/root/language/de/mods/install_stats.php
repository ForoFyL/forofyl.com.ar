<?php
/**
*
* @package phpBB Statistics
* @version $Id: install_stats.php 162 2010-12-11 13:29:18Z marc1706 $
* @copyright (c) 2009 - 2010 Marc Alexander(marc1706) www.m-a-styles.de
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @based on: Board3 Portal Installer (www.board3.de)
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

$lang = array_merge($lang, array(
	'INSTALL_CONGRATS_EXPLAIN'		=> '<p>Du hast phpBB Statistics v%s erfolgreich installiert.<br/><br/><strong>Bitte lösche oder verschiebe jetzt das Installations-Verzeichnis "install" oder benenne es um, bevor du dein Board benutzt. Solange dieses Verzeichnis existiert, ist nur der Administrations-Bereich zugänglich.</strong></p>',
	'INSTALL_INTRO_BODY'			=> 'Dieser Assistent unterstützt dich bei der Installation von phpBB Statistics in deinem phpBB-Forum.',

	'MISSING_CONSTANTS'				=> 'Bevor du das Installations-Skript aufrufen kannst, musst du die bearbeiteten Dateien hochladen, insbesondere /includes/constants.php.',
	'MODULES_CREATE_PARENT'			=> 'Übergeordnetes Standard-Modul erstellen',
	'MODULES_PARENT_SELECT'			=> 'Übergeordnetes Modul auswählen',
	'MODULES_SELECT_4ACP'			=> 'Übergeordnetes Modul für den "Administrations-Bereich"',
	'MODULES_SELECT_NONE'			=> 'kein übergeordnetes Modul',

	'STAGE_ADVANCED_EXPLAIN'		=> 'Die phpBB Statistics Module werden jetzt erstellt.',
	'STAGE_CREATE_TABLE_EXPLAIN'	=> 'Die von phpBB Statistics genutzten Datenbank-Tabellen wurden erstellt und mit einigen Ausgangswerten gefüllt. Gehe weiter zum nächsten Schritt, um die Installation von phpBB Statistics abzuschließen.',
	'STAGE_ADVANCED_IN_PROGRESS'	=> 'Die BBCodes und Smilies in deinen Beiträgen werden gezählt. Bis zur Vollendung kann das etwas dauern. Die Seite wird sich alle 5 Sekunden aktualisieren.<br />Bitte warte bis das Skript beendet ist und breche diesen Vorgang nicht ab.',
	'STAGE_ADVANCED_SUCCESSFUL'		=> 'Die von phpBB Statistics genutzten Module wurden erstellt. Gehe weiter um die Installation von phpBB Statistics abzuschließen.',
	'STAGE_UNINSTALL'				=> 'Deinstallieren',
    
	'FILES_EXISTS'					=> 'Datei existiert noch',
	'FILES_OUTDATED'				=> 'Veraltete Dateien',
	'FILES_OUTDATED_EXPLAIN'		=> '<strong>Veraltete Dateien</strong> - bitte entferne die folgenden Dateien, um eventuelle Sicherheitslücken zu schließen.',
	'FILES_CHANGE'					=> 	'Datei wurde im aktuellen Release abgeändert oder neu hinzugefügt',
	'FILES_CHANGED'					=> 	'Geänderte oder neue Dateien',
	'FILES_CHANGED_EXPLAIN'			=> 	'<strong>Geänderte oder neue Dateien</strong> - bitte stelle sicher, dass die Dateien auf deine Webseite geladen hast.',
	'REQUIREMENTS_EXPLAIN'			=> 'Bitte entferne erst alle veralteten Dateien von deinem Server, bevor mit dem Update fortfährst.',
	'NOT_REQUIREMENTS_EXPLAIN'		=> 'Es wurden keine veraltete Dateien auf deinem Server gefunden, du kannst mit dem Update fortfahren.',

	'UPDATE_INSTALLATION'			=> 'phpBB Statistics aktualisieren',
	'UPDATE_INSTALLATION_EXPLAIN' 	=> 'Mit dieser Option kannst du phpBB Statistics auf den aktuellen Versionsstand bringen.',
	'UPDATE_CONGRATS_EXPLAIN'		=> '<p>Du hast phpBB Statistics erfolgreich auf v%s aktualisiert.<br/><br/><strong>Bitte lösche oder verschiebe jetzt das Installations-Verzeichnis "install" oder benenne es um, bevor du dein Board benutzt. Solange dieses Verzeichnis existiert, ist nur der Administrations-Bereich zugänglich.</strong></p>',
    
	'UNINSTALL_INTRO'				=> 'Willkommen bei der Deinstallation',
	'UNINSTALL_INTRO_BODY'			=> 'Dieser Assistent unterstützt dich bei der De-Installation von phpBB Statistics.',
	'CAT_UNINSTALL'					=> 'Deinstallieren',
	'UNINSTALL_CONGRATS'			=> '<h1>phpBB Statistics deinstalliert.</h1>
                                        Du hast phpBB Statistics erfolgreich deinstalliert.',
	'UNINSTALL_CONGRATS_EXPLAIN'	=> '<strong>Bitte lösche oder verschiebe jetzt das Installations-Verzeichnis "install" oder benenne es um, bevor du dein Board benutzt. Solange dieses Verzeichnis existiert, ist nur der Administrations-Bereich zugänglich.<br /><br />Denke daran die Portal-Dateien zu löschen und Dateiänderungen am Originalsystem rückgängig zu machen.</strong></p>',

	'SUPPORT_BODY'					=> 'Für die aktuelle, stabile Version von "phpBB Statistics" wird kostenloser Support gewährt. Dieser umfasst:</p><ul><li>Installation</li><li>Technische Fragen</li><li>Probleme durch eventuelle Fehler in der Software</li><li>Aktualisierung vom Forum Statistics MOD oder Betas zur aktuellen Version von phpBB Statistics</li></ul><p>Support gibt es in folgenden Foren:</p><ul><li><a href="http://www.m-a-styles.de/">m-a-styles.de - Homepage des MOD-Autor\'s marc1706</a></li><li><a href="http://www.phpbb.de/">phpbb.de</a></li><li><a href="http://www.phpbb.com/">phpbb.com</a></li></ul><p>',
	'GOTO_INDEX'					=> 'Gehe zum Forum',
	'GOTO_STATS'					=> 'Gehe zu phpBB Statistics',
	'UNSUPPORTED_DB'				=> 'Datenbank wird nicht unterstützt',
	'UNSUPPORTED_VERSION' 			=> 'Version wird nicht unterstützt',
));

?>