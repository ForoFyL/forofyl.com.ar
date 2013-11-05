<?php
/**
*
* @package acp
* @version $Id: acp_thanks_reput.php,v 128 2010-04-11 10:02:51 Палыч$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
class acp_thanks_reput
{
	var $u_action;

	function main($id, $mode)
	{
		global $db, $user, $auth, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$config_vars = array(
			'thanks_post_reput_view'	=> 'THANKS_POST_REPUT_VIEW',
			'thanks_topic_reput_view'	=> 'THANKS_TOPIC_REPUT_VIEW',
			'thanks_forum_reput_view'	=> 'THANKS_FORUM_REPUT_VIEW',
			'thanks_reput_height'		=> 'THANKS_REPUT_HEIGHT',
			'thanks_reput_level'		=> 'THANKS_REPUT_LEVEL',
			'thanks_number_digits'		=> 'THANKS_NUMBER_DIGITS',
			'thanks_number_row_reput'	=> 'THANKS_NUMBER_ROW_REPUT',
			'thanks_reput_graphic'		=> 'THANKS_REPUT_GRAPHIC',
			'thanks_reput_image'		=> 'THANKS_REPUT_IMAGE',
			'thanks_reput_image_back'	=> 'THANKS_REPUT_IMAGE_BACK',
		);

		$this->tpl_name = 'acp_thanks_reput';
		$this->page_title = 'ACP_THANKS_REPUT';
		$form_key = 'acp_thanks_reput';
		add_form_key($form_key);

		$submit = request_var('submit', false);
		
		if ($submit && check_form_key($form_key))
		{
			$config_vars = array_keys($config_vars);
			foreach ($config_vars as $config_var)
			{
				set_config($config_var, request_var($config_var, ''));
			}
			trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
		}
		else if($submit)
		{
				trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action));
		}
		else
		{	
			foreach ($config_vars as $config_var => $template_var)
			{
				$template->assign_var($template_var, (isset($_REQUEST[$config_var])) ? request_var($config_var, '') : $config[$config_var]);
			}
				$template->assign_vars(array(
					'THANKS_REPUT_IMAGE_SRC'		=> ($config['thanks_reput_image']) ? $phpbb_root_path . $config['thanks_reput_image'] : '',
					'THANKS_REPUT_IMAGE_BACK_SRC'	=> ($config['thanks_reput_image_back']) ? $phpbb_root_path . $config['thanks_reput_image_back'] : '',
                    'GRAPHIC_STAR_BLUE_EXAMPLE' => $phpbb_root_path . $user->lang['GRAPHIC_STAR_BLUE'],
                    'GRAPHIC_STAR_GOLD_EXAMPLE' => $phpbb_root_path . $user->lang['GRAPHIC_STAR_GOLD'],
                    'GRAPHIC_STAR_BACK_EXAMPLE' => $phpbb_root_path . $user->lang['GRAPHIC_STAR_BACK'],
                    'GRAPHIC_BLOCK_RED_EXAMPLE' => $phpbb_root_path . $user->lang['GRAPHIC_BLOCK_RED'],
                    'GRAPHIC_BLOCK_BACK_EXAMPLE' => $phpbb_root_path . $user->lang['GRAPHIC_BLOCK_BACK'],
 				));
		}
	}
}	
?>