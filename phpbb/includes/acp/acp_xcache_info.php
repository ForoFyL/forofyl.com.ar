<?php
/**
*
* @package acp
* @version $Id: acp_xcache_info.php joebert $
* @copyright (c) 2005 phpBB Group
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
class acp_xcache_info
{
	var $u_action;

	function main($id, $mode)
	{
		global $db, $user, $auth, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;
		
		$user->add_lang('acp/xcache_info');
		$this->tpl_name = 'acp_xcache_info';
		$this->page_title = 'ACP_XCACHE_INFO';
		
		if(!extension_loaded('XCache'))
		{
			$template->assign_var('XCACHE_INSTALLED', false);
			return;
		}
		
		// Values of "xcache.admin.user" and "xcache.admin.pass" (before MD5)
		//$_SERVER["PHP_AUTH_USER"] = "admin";
		//$_SERVER["PHP_AUTH_PW"] = "password";
		
		$clear_type		= request_var('type', -1);
		$clear_cacheid	= request_var('cacheid', -1);
		$can_empty		= (int)$user->data['user_type'] === USER_FOUNDER;
		$cacheinfos		= array();
		
		$template->assign_vars(array(
			'XCACHE_INSTALLED'	=> true,
			'CAN_EMPTY'			=> $can_empty
		));

		if($can_empty && request_var('clearcache', false))
		{
			$s_hidden_fields = build_hidden_fields(array(
				'type'			=> $clear_type,
				'cacheid'		=> $clear_cacheid
			));
			confirm_box(false, 'CLEAR_CONFIRM', $s_hidden_fields);
		}
		if($can_empty && confirm_box(true))
		{
			if($clear_type == XC_TYPE_PHP || $clear_type == XC_TYPE_VAR)
			{
				xcache_clear_cache($clear_type, $clear_cacheid);
				add_log('admin', 'LOG_PURGED_XCACHE');
			}
		}

		for($p = 0, $pc = xcache_count(XC_TYPE_PHP); $p < $pc; $p++)
		{
			$data = xcache_info(XC_TYPE_PHP, $p);
			$data['type'] = XC_TYPE_PHP;
			$data['cache_name'] = "php#$p";
			$data['cacheid'] = $p;
			$cacheinfos[] = $data;
		}
		for($v = 0, $vc = xcache_count(XC_TYPE_VAR); $v < $vc; $v++)
		{
			$data = xcache_info(XC_TYPE_VAR, $v);
			$data['type'] = XC_TYPE_VAR;
			$data['cache_name'] = "var#$v";
			$data['cacheid'] = $v;
			$cacheinfos[] = $data;
		}
		
		switch($mode)
		{			
			case 'status':
				foreach ($cacheinfos as $i => $ci)
				{
					$ci['compiling']    = $ci['type'] == XC_TYPE_PHP ? ($ci['compiling'] ? $user->lang['YES'] : $user->lang['NO']) : '-';
					$ci['can_readonly'] = $ci['can_readonly'] ? $user->lang['YES'] : $user->lang['NO'];
					
					$mem_use = $ci['size'] - $ci['avail'];
					$ci['gc'] = max(0, $ci['gc']);
					$su_percent = ceil($ci['cached'] / $ci['slots'] * 100);

					$template->assign_block_vars('cache_info', array(
						'CACHE_NAME'	=> $ci['cache_name'],
						'SLOT_USE'		=> sprintf('<acronym %stitle="%d / %d">%d%%</acronym>', ($su_percent >= 100 ? 'style="color:red" ' : ''), $ci['cached'], $ci['slots'], $su_percent),
						'MEM_USE'		=> sprintf('%s/%s <span style="float:right">(%d%%)</span>', $this->size($mem_use), $this->size($ci['size']), ceil($mem_use / $ci['size'] * 100)),
						'TYPE'			=> $ci['type'],
						'CACHEID'		=> $ci['cacheid'],
						'COMPILING'		=> $ci['compiling'],
						'HITS'			=> sprintf('<acronym title="%d / %d">%d%%</acronym>', $ci['hits'], ($ci['hits'] + $ci['misses']), floor($ci['hits'] / ($ci['hits'] + $ci['misses']) * 100)),
						'CLOGS'			=> $ci['clogs'],
						'OOMS'			=> $ci['ooms'],
						'CAN_READONLY'	=> $ci['can_readonly'],
						'DELETED'		=> $ci['deleted'],
						'GC'			=> sprintf('%s:%s:%s', floor($ci['gc'] / 3600), ($ci['gc'] / 60 < 10 ? '0' : '') . floor($ci['gc'] / 60), ($ci['gc'] % 60 < 10 ? '0' : '') . $ci['gc'] % 60)
					));
					
					$template->assign_block_vars('free_block_row', array(
						'CACHE_NAME'	=> $ci['cache_name']
					));
					foreach($ci['free_blocks'] as &$block)
					{
						$template->assign_block_vars('free_block_row.free_block', array(
							'SIZE'		=> $this->size($block['size']),
							'OFFSET'	=> $this->size($block['offset'])
						));
					}
				}

				ob_start();
				@phpinfo(INFO_MODULES);
				$phpinfo = trim(ob_get_clean());

				if(preg_match('!XCache</a></h2>(.*?)<h2><a name="module_!is', $phpinfo, $output))
				{
					$output = preg_replace('#<table[^>]+>#i', '<table>', $output[1]);
					$output = str_replace(array('class="e"', 'class="v"', 'class="h"', '<hr />', '<font', '</font>'), array('class="row1"', 'class="row2"', '', '', '<span', '</span>'), $output);
					$orig_output = $output;
					preg_match_all('#<div class="center">(.*)</div>#siU', $output, $output);
					$output = (!empty($output[1][0])) ? $output[1][0] : $orig_output;
				}
				else
				{
					$output = false;
				}

				$template->assign_var('PHPINFO', $output);
			break;
			
			default:
				trigger_error('NO_MODE', E_USER_ERROR);
			break;
		}
		$template->assign_vars(array(
			'U_ACTION'	=> $this->u_action
		));
	}
	
	function size($size, $precision = 2)
	{
		$suffixes = array(
			'Y' => '1208925819614629174706176',
			'Z' => '1180591620717411303424',
			'E' => '1152921504606846976',
			'P' => '1125899906842624',
			'T' => '1099511627776',
			'G' => '1073741824',
			'M' => '1048576',
			'K' => '1024'
		);
		foreach($suffixes as $suffix => $divisor)
		{
			if(bccomp($size, $divisor) > -1)
			{
				return bcdiv($size, $divisor, $precision) . $suffix;
			}
		}
		return $size . 'b';
	}
}

?>
