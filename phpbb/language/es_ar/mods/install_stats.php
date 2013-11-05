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
	'INSTALL_CONGRATS_EXPLAIN'		=> 	'<p>You have succesfully installed phpBB Statistics v%s<br/><br/><strong>Now delete, move or rename the "install"-folder before you use your board. As long as this directory is present, you will only have access to your ACP.</strong></p>',
	'INSTALL_INTRO_BODY'			=> 	'This installation system will guide you through installing phpBB Statistics to your phpBB forum.',

	'MISSING_CONSTANTS'			=> 	'Prior to run the install script, you have to upload the edited files, especially /includes/constants.php.',
	'MODULES_CREATE_PARENT'		=> 	'Create parent standard module',
	'MODULES_PARENT_SELECT'		=> 	'Select parent module',
	'MODULES_SELECT_4ACP'		=> 	'Parent module for the ACP',
	'MODULES_SELECT_NONE'		=> 	'No parent module',

	'STAGE_ADVANCED_EXPLAIN'        =>  'The phpBB Statistics modules will now be created.',
	'STAGE_CREATE_TABLE_EXPLAIN'	=> 	'The phpBB Statistics database tables have been created and initialized with basic values. Proceed to the next step to finish the phpBB Statistics installation.',
	'STAGE_ADVANCED_IN_PROGRESS'	=> 	'The BBCode and Smilies in your posts are being counted. This might take a while and the page will refresh itself every 5 seconds.<br />Please be patient and let the script finish.',
	'STAGE_ADVANCED_SUCCESSFUL'		=> 	'The phpBB Statistics modules have been created. Proceed to finish the phpBB Statistics installation.',
	'STAGE_UNINSTALL'				=> 	'Uninstall',

	'FILES_EXISTS'				=> 	'File still exists',
	'FILES_OUTDATED'			=> 	'Out-of-date files',
	'FILES_OUTDATED_EXPLAIN'	=> 	'<strong>Out-of-date files</strong> - please delete these files to avoid security issues.',
	'FILES_CHANGE'				=> 	'File has been changed in the current release',
	'FILES_CHANGED'				=> 	'Changed files',
	'FILES_CHANGED_EXPLAIN'	=> 	'<strong>Changed files</strong> - please make sure you copied the changed files to your website.',
	'REQUIREMENTS_EXPLAIN'		=> 	'Please delete all out-of-date files from your server prior to proceed with updating.',
	'NOT_REQUIREMENTS_EXPLAIN'	=> 	'No out-of-date files were found on your server, you may proceed with updating.',

	'UPDATE_INSTALLATION'			=> 	'Update phpBB Statistics',
	'UPDATE_INSTALLATION_EXPLAIN'	=> 	'This option will update your phpBB Statistics to the current version.',
	'UPDATE_CONGRATS_EXPLAIN'		=> 	'<p>You have updated your phpBB Statistics successfully to v%s<br/><br/><strong>Now delete, move or rename the "install"-folder before you use your board. As long as this directory is present, you will only have access to your ACP.</strong></p>',

	'UNINSTALL_INTRO'				=> 	'Welcome to Uninstall',
	'UNINSTALL_INTRO_BODY'			=> 	'This installation system will guide you through uninstalling the phpBB Statistics from your phpBB forum.',
	'CAT_UNINSTALL'					=> 	'Uninstall',
	'UNINSTALL_CONGRATS'			=> 	'<h1>phpBB Statistics removed.</h1>
									You have successfully uninstalled the phpBB Statistics.',
	'UNINSTALL_CONGRATS_EXPLAIN'	=> 	'<strong>Now delete, move or rename the "install"-folder before you use your board. As long as this directory is present, you will only have access to your ACP.<br /><br />Make sure to delete the Portal-related files and reverse all Portal-related edits of phpBB core files.</strong></p>',

	'SUPPORT_BODY'		=> 	'Support for the latest version of the phpBB Statistics is available free of charge for:</p><ul><li>Installation</li><li>Technical questions</li><li>Program-related issues</li><li>Updating Forum Statistics MOD or betas to the latest version of phpBB Statistics</li></ul><p>You will find support in these forums:</p><ul><li><a href="http://www.m-a-styles.de/">M-A-Styles - Homepage of Marc Alexander (marc1706) - MOD author</a></li><li><a href="http://www.phpbb.de/">phpbb.de</a></li><li><a href="http://www.phpbb.com/">phpbb.com</a></li></ul><p>',
	'GOTO_INDEX'		=> 	'Proceed to forum',
	'GOTO_STATS'		=> 	'Proceed to phpBB Statistics',
	'UNSUPPORTED_DB'	=>	'Sorry, unsupported Databases found',
	'UNSUPPORTED_VERSION' => 'Sorry, unsupported version found',
));

?>