<?php
/**
*
* @package acp
* @version $Id: acp_thanks_truncate.php,v 127 2010-04-17 10:02:51 Палыч$
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
class acp_thanks_truncate  
{
	var $u_action;

	function main($id, $mode)
	{
		global $db, $user, $auth, $template;

		$this->tpl_name = 'acp_thanks_truncate';
		$this->page_title = 'ACP_THANKS_TRUNCATE';

		$all_posts_thanks = $all_thanks = $del_thanks = $del_uposts = $del_posts = 0; 

		$sql = 'SELECT COUNT(post_id) as total_match_count
			FROM ' . THANKS_TABLE;
		$result = $db->sql_query($sql);
		$all_thanks = $db->sql_fetchfield('total_match_count');
		$db->sql_freeresult($result);
	
		$sql = 'SELECT COUNT(DISTINCT post_id) as total_match_count
			FROM ' . THANKS_TABLE;
		$result = $db->sql_query($sql);
		$all_posts_thanks = $del_posts = $db->sql_fetchfield('total_match_count');
		$db->sql_freeresult($result);

		$sql = 'SELECT COUNT(DISTINCT user_id) as total_match_count
			FROM ' . THANKS_TABLE;
		$result = $db->sql_query($sql);
		$all_users_thanks = $del_uposts = $db->sql_fetchfield('total_match_count');
		$db->sql_freeresult($result);
			
		$truncate = request_var('truncate', false);
			
		if ($truncate)
		{	
			$sql = 'TRUNCATE TABLE ' . THANKS_TABLE;
			$result = $db->sql_query($sql);
			$db->sql_freeresult($result);
		}
			$sql = 'SELECT COUNT(post_id) as total_match_count
				FROM ' . THANKS_TABLE;
			$result = $db->sql_query($sql);
			$end_thanks = $db->sql_fetchfield('total_match_count');
			$db->sql_freeresult($result);

			$end_posts_thanks = $all_posts_thanks - $del_posts;
			$end_users_thanks = $all_users_thanks - $del_uposts;	
			$del_thanks = $all_thanks - $end_thanks;
	
		$template->assign_vars(array(
			'ALLTHANKS'		=> $all_thanks,
			'POSTSTHANKS'	=> $all_posts_thanks,
			'USERSTHANKS'	=> $all_users_thanks,
			'POSTSEND'		=> $end_posts_thanks,
			'USERSEND'		=> $end_users_thanks,
			'THANKSEND'		=> $end_thanks,
			'S_TRUNCATE' 	=> $truncate,			
		));
	}
}
?>