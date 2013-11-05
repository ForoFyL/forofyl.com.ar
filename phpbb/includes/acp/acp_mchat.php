<?php

/**
*
* @author Stokerpiller, RMcGirr83
* @package - mchat
* @version $Id acp_mchat.php 1.3.5 2009-10-20 06:16:46 EST RMcGirr83 $ 
* @copyright (c) Stokerpiller, RMcGirr83
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package acp
*/
class acp_mchat
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template;
		global $phpbb_root_path, $phpEx;

		$this->tpl_name = 'acp_mchat';
		$this->page_title = $user->lang['MCHAT_TITLE'];
		$this->configuration();
		add_form_key('acp_mchat');

	}
	
	function configuration ()
	{
		global $cache, $config, $db, $user, $auth, $template;
		global $phpbb_root_path, $phpEx;
		
		// install file been run?
		if (!isset($config['mchat_version']))
		{
			trigger_error('MCHAT_NOT_INSTALLED');
		}
		
		// something was submitted
		$submit = (isset($_POST['submit'])) ? true : false;
		
		$mchat_row = array(
			'location'			=> request_var('mchat_location', 0),
			'refresh' 			=> request_var('mchat_refresh', 0),
			'message_limit'		=> request_var('mchat_message_limit', 0),
			'archive_limit'		=> request_var('mchat_archive_limit', 0),
			'flood_time'		=> request_var('mchat_flood_time', 0),
			'max_message_lngth'	=> request_var('mchat_max_message_lngth', 0),
			'custom_page'		=> request_var('mchat_custom_page', 0),
			'date'				=> request_var('mchat_date', '', true),
			'whois'				=> request_var('mchat_whois', 0),
			'whois_refresh'		=> request_var('mchat_whois_refresh', 0),
			'bbcode_disallowed'	=> request_var('mchat_bbcode_disallowed', ''),
			'prune_enable'		=> request_var('mchat_prune_enable', 0),
			'prune_num'			=> request_var('mchat_prune_num', 0),
		);		
		
		if ($submit)
		{
			if (!function_exists('validate_data'))
			{
				include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
			}
			
			// validate the entries...most of them anyway
			$mchat_array = array(
				'refresh'			=> array('num', false, 5, 60),
				'message_limit'		=> array('num', false, 10, 20),
				'archive_limit'		=> array('num', false, 25, 50),
				'flood_time'		=> array('num', false, 0, 30),
				'max_message_lngth'	=> array('num', false, 0, 500),
				'date'				=> array('string', false, 1, 30),
				'whois_refresh'		=> array('num', false, 30, 300),
			);		
			
			$error = validate_data($mchat_row, $mchat_array);

			if (!check_form_key('acp_mchat'))
			{
				$error[] = 'FORM_INVALID';
			}

			if (!sizeof($error))
			{
				foreach ($mchat_row as $config_name => $config_value)
				{
					$sql = 'UPDATE ' . MCHAT_CONFIG_TABLE . "
						SET config_value = '" . $db->sql_escape($config_value) . "'
						WHERE config_name = '" . $db->sql_escape($config_name) . "'";
					$db->sql_query($sql);
				}
				
				//update setting in config table for mod enabled or not
				set_config('mchat_enable', request_var('mchat_enable', 0));
				// update setting in config table for allowing on index or not
				set_config('mchat_on_index', request_var('mchat_on_index', 0));				
				
				// and an entry into the log table
				add_log('admin', 'LOG_MCHAT_CONFIG_UPDATE');
				
				// purge the cache
				$cache->destroy('_mchat_config');

				trigger_error($user->lang['MCHAT_CONFIG_SAVED'] . adm_back_link($this->u_action));
			}
					
			// Replace "error" strings with their real, localised form
			$error = preg_replace('#^([A-Z_]+)$#e', "(!empty(\$user->lang['\\1'])) ? \$user->lang['\\1'] : '\\1'", $error);			
		}
		
		// let's get it on
		$sql = 'SELECT * FROM ' . MCHAT_CONFIG_TABLE;
		$result = $db->sql_query($sql);
		$mchat_config = array();
		while ($row = $db->sql_fetchrow($result))	
		{
			$mchat_config[$row['config_name']] = $row['config_value'];
		}
		$db->sql_freeresult($result);
						
		$mchat_enable = isset($config['mchat_enable']) ? $config['mchat_enable'] : 0;
		$mchat_on_index = isset($config['mchat_on_index']) ? $config['mchat_on_index'] : 0;
		$mchat_version = isset($config['mchat_version']) ? $config['mchat_version'] : '';

		$dateformat_options = '';

		foreach ($user->lang['dateformats'] as $format => $null)
		{
			$dateformat_options .= '<option value="' . $format . '"' . (($format == $mchat_config['date']) ? ' selected="selected"' : '') . '>';
			$dateformat_options .= $user->format_date(time(), $format, false) . ((strpos($format, '|') !== false) ? $user->lang['VARIANT_DATE_SEPARATOR'] . $user->format_date(time(), $format, true) : '');
			$dateformat_options .= '</option>';
		}

		$s_custom = false;
		$dateformat_options .= '<option value="custom"';
		if (!isset($user->lang['dateformats'][$mchat_config['date']]))
		{
			$dateformat_options .= ' selected="selected"';
			$s_custom = true;
		}
		$dateformat_options .= '>' . $user->lang['MCHAT_CUSTOM_DATEFORMAT'] . '</option>';
		
		$template->assign_vars(array(
			'MCHAT_ERROR'					=> isset($error) ? ((sizeof($error)) ? implode('<br />', $error) : '') : '',
			'MCHAT_VERSION'					=> $mchat_version,
			'MCHAT_PRUNE'					=> !empty($mchat_row['prune_enable']) ? $mchat_row['prune_enable'] : $mchat_config['prune_enable'],
			'MCHAT_PRUNE_NUM'				=> !empty($mchat_row['prune_num']) ? $mchat_row['prune_num'] : $mchat_config['prune_num'],
			'MCHAT_ENABLE'					=> ($mchat_enable) ? true : false,
			'MCHAT_ON_INDEX'				=> ($mchat_on_index) ? true : false,
			'MCHAT_LOCATION'				=> !empty($mchat_row['location']) ? $mchat_row['location'] : $mchat_config['location'],
			'MCHAT_REFRESH'					=> !empty($mchat_row['refresh']) ? $mchat_row['refresh'] : $mchat_config['refresh'],
			'MCHAT_WHOIS_REFRESH'			=> !empty($mchat_row['whois_refresh']) ? $mchat_row['whois_refresh'] : $mchat_config['whois_refresh'],
			'MCHAT_MESSAGE_LIMIT'			=> !empty($mchat_row['message_limit']) ? $mchat_row['message_limit'] : $mchat_config['message_limit'],
			'MCHAT_ARCHIVE_LIMIT'			=> !empty($mchat_row['archive_limit']) ? $mchat_row['archive_limit'] : $mchat_config['archive_limit'],
			'MCHAT_FLOOD_TIME'				=> !empty($mchat_row['flood_time']) ? $mchat_row['flood_time'] : $mchat_config['flood_time'],
			'MCHAT_MAX_MESSAGE_LNGTH'		=> !empty($mchat_row['max_message_lngth']) ? $mchat_row['max_message_lngth'] : $mchat_config['max_message_lngth'],
			'MCHAT_CUSTOM_PAGE'				=> !empty($mchat_row['custom_page']) ? $mchat_row['custom_page'] : $mchat_config['custom_page'],
			'MCHAT_DATE'					=> !empty($mchat_row['date']) ? $mchat_row['date'] : $mchat_config['date'],
			'S_MCHAT_DATEFORMAT_OPTIONS'	=> $dateformat_options,
			'S_CUSTOM_DATEFORMAT'			=> $s_custom,
			'MCHAT_DEFAULT_DATEFORMAT'		=> $config['default_dateformat'],		
			'MCHAT_WHOIS'					=> !empty($mchat_row['whois']) ? $mchat_row['whois'] : $mchat_config['whois'],
			'MCHAT_BBCODE_DISALLOWED'		=> !empty($mchat_row['bbcode_disallowed']) ? $mchat_row['bbcode_disallowed'] : $mchat_config['bbcode_disallowed'],
			
			'U_ACTION'						=> $this->u_action)
		);
	}	
}

?>