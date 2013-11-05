<?php
/**
*
* @package acp
* @version $Id: acp_thanks_refresh.php,v 127 2010-04-17 10:02:51 Палыч$
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
class acp_thanks_refresh  
{
	var $u_action;

	function main($id, $mode)
	{
		global $db, $user, $auth, $template, $cache, $template;

		$this->tpl_name = 'acp_thanks_refresh';
		$this->page_title = 'ACP_THANKS_REFRESH';
		if (!$cache->get('_stepr') || $cache->get('_stepr') == 0)
		{
			$refresh = request_var('refresh', false);
			if ($refresh)
			{
				$cache->put('_stepr', $stepr = 1);
			}
			else
			{
				$cache->put('_stepr', $stepr = 0);
			}
		}
		$stepr = $cache->get('_stepr');
		switch ($stepr)
		{
			case '0':
				$all_users = $all_posts = $all_users_thanks = $all_posts_thanks = $all_thanks = $del_thanks = $del_uposts = $del_posts = $postmin = $usermin = $maxuid = $maxid = 0;
			return;
			case '1':
				$posts_thanks = $posts_delete = $users_thanks = $users_delete = array();
				$all_users_thanks = $all_posts_thanks = $all_thanks = $del_thanks = $del_uposts = $del_posts = $postmin = $usermin = $maxuid = $maxid = 0; 
		
				$cache->put('_posts_thanks', $posts_thanks);
				$cache->put('_posts_delete', $posts_delete);
				$cache->put('_users_thanks', $users_thanks);
				$cache->put('_users_delete', $users_delete);
				$cache->put('_all_posts_thanks', $all_posts_thanks);
				$cache->put('_all_users_thanks', $all_users_thanks);
				$cache->put('_all_thanks', $all_thanks);
				$cache->put('_del_thanks', $del_thanks);
				$cache->put('_del_uposts', $del_uposts);
				$cache->put('_del_posts', $del_posts);
				$cache->put('_postmin', $postmin);
				$cache->put('_usermin', $usermin);
				$cache->put('_maxuid', $maxuid);
				$cache->put('_maxid', $maxid);
				
				$sql = 'SELECT COUNT(post_id) as total_match_count
					FROM ' . THANKS_TABLE;
				$result = $db->sql_query($sql);
				$all_thanks = $db->sql_fetchfield('total_match_count');
				$db->sql_freeresult($result);
		
				$template->assign_vars(array(
					'S_REFRESH'	=> true,
					'L_WARNING' 	=> sprintf($user->lang['WARNING'].' '.$user->lang['STEPR'],$stepr),
				));
				$stepr = $stepr + 1;
				$cache->put('_stepr', $stepr);	
				$cache->put('_all_thanks', $all_thanks);	
				meta_refresh(0, append_sid($this->u_action));				
				return;
			break;
			case '2':
			//posts
				$del_posts = $cache->get('_del_posts');			
				$posts_thanks = $cache->get('_posts_thanks');	
				$all_posts_thanks = $cache->get('_all_posts_thanks');
				$maxid = $cache->get('_maxid');	
				$postmin = $cache->get('_postmin');	
				$posts_posts = $cache->get('_posts_posts');
				$posts_delete = $cache->get('_posts_delete');
		
				$sql = 'SELECT DISTINCT post_id
					FROM ' . THANKS_TABLE;
				$result = $db->sql_query($sql);
		
				while ($row = $db->sql_fetchrow($result))
				{
					$posts_thanks[] = $row['post_id'];
					$all_posts_thanks++;
				}
				natsort ($posts_thanks);
			
				$sql = 'SELECT MAX(post_id) AS maxid
					FROM ' . POSTS_TABLE;
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$maxid = $row['maxid'];
				$db->sql_freeresult($result);
			
				for ($i=1, $end = 1 + $maxid / 5000; $i < $end; $i++)
				{
					$postmax = 5000;
					$posts_posts = array();
			
					$sql = 'SELECT post_id
						FROM ' . POSTS_TABLE;
					$result = $db->sql_query_limit($sql, $postmax, $postmin);
			
					if (!$db->sql_fetchrow($result))
					{
						break;
					}
					$numberpost = 0;
					while ($row = $db->sql_fetchrow($result))
					{
						$posts_posts[$row['post_id']] = $row['post_id'];
						$numberpost++;
					}
			
					$db->sql_freeresult($result);
					$max = end($posts_posts);
					$min = reset($posts_posts);
			
					for ($j=0; $j < $all_posts_thanks; $j++)
					{
						if ($posts_thanks[$j] > $min)
						{
							if ($posts_thanks[$j] < $max)
							{
								if (!isset($posts_posts[$posts_thanks[$j]]))
								{
									$posts_delete[] = $posts_thanks[$j];
									$del_posts++;
								}
							}
						}
					}
					unset($posts_posts);
					$posts = $postmin;
					$postmin = $postmin + $postmax - 1;
				}
				$template->assign_vars(array(
					'S_REFRESH'	=> true,
					'L_WARNING' 	=> sprintf($user->lang['WARNING'].' '.$user->lang['STEPR'],$stepr),
				));		
				$stepr = $stepr + 1;
				$cache->put('_stepr', $stepr);
				$cache->put('_posts', $posts);
				$cache->put('_numberpost', $numberpost);
				$cache->put('_all_posts_thanks', $all_posts_thanks);
				$cache->put('_del_posts', $del_posts);		
				$cache->put('_posts_delete', $posts_delete);	
				$cache->put('_posts_thanks', $posts_thanks);	
				$cache->put('_maxid', $maxid);		
				meta_refresh(0, append_sid($this->u_action));	
				return;
			break;
			case '3':
				//overpost
				$del_posts = $cache->get('_del_posts');
				$posts_delete = $cache->get('_posts_delete');
				$posts_thanks = $cache->get('_posts_thanks');
				$maxid = $cache->get('_maxid');

				for ($i=0, $end = count($posts_thanks); $i < $end; $i++)
				{
					if ($posts_thanks[$i] > $maxid)
					{
						$posts_delete[] = $posts_thanks[$i];
						$del_posts++;
					}
				}
				$template->assign_vars(array(
					'S_REFRESH'	=> true,
					'L_WARNING' 	=> sprintf($user->lang['WARNING'].' '.$user->lang['STEPR'],$stepr),
				));
				$stepr = $stepr + 1;
				$cache->put('_stepr', $stepr);
				$cache->put('_del_posts', $del_posts);	
				$cache->put('_posts_delete', $posts_delete);	
				meta_refresh(0, append_sid($this->u_action));	
				return;
			break;
			case '4':
				//users
				$all_users_thanks = $cache->get('_all_users_thanks');
				$users_thanks = $cache->get('_users_thanks');
				$maxuid = $cache->get('_maxuid');
				$posts = $cache->get('_posts');		
				$numberpost = $cache->get('_numberpost');		
				$all_thanks = $cache->get('_all_thanks');
				$all_posts_thanks = $cache->get('_all_posts_thanks');
				$del_posts = $cache->get('_del_posts');
				$usermin = $cache->get('_usermin');
				$del_uposts = $cache->get('_del_uposts');
		
				$sql = 'SELECT DISTINCT user_id
					FROM ' . THANKS_TABLE;
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$users_thanks[] = $row['user_id'];
					$all_users_thanks++;
				}
				natsort ($users_thanks);
				$db->sql_freeresult($result);
		
				$sql = 'SELECT MAX(user_id) AS maxuid
					FROM ' . USERS_TABLE;
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$maxuid = $row['maxuid'];
				$db->sql_freeresult($result);
			
				for ($i=1, $end = 1 + $maxuid / 5000; $i < $end; $i++)
				{
					$usermax = 5000;
					$users_users = array();
			
					$sql = 'SELECT user_id
						FROM ' . USERS_TABLE;
					$result = $db->sql_query_limit($sql, $usermax, $usermin);
			
					if (!$db->sql_fetchrow($result))
					{
						break;
					}
					$numberuser = 0;
					while ($row = $db->sql_fetchrow($result))
					{
						$users_users[$row['user_id']] = $row['user_id'];
						$numberuser++;
					}
			
					$db->sql_freeresult($result);
					$max = end($users_users);
					$min = reset($users_users);
					for ($j=0; $j < $all_users_thanks; $j++)
					{
						if ($users_thanks[$j] > $min)
						{
							if ($users_thanks[$j] < $max)
							{
								if (!isset($users_users[$users_thanks[$j]]))
								{
									$users_delete[] = $users_thanks[$j];
									$del_uposts++;
								}
							}
						}
					}
					unset($users_users);
					$users = $usermin;
					$usermin = $usermin + $usermax - 1;
				}	
				//delete
				$posts_delete = $cache->get('_posts_delete');
				if(!empty($posts_delete))
				{
					$del_thanks = count($posts_delete);
					$sql = 'DELETE FROM ' . THANKS_TABLE ."
						WHERE " . $db->sql_in_set('post_id', $posts_delete);
					$result = $db->sql_query($sql);		
				}
			
				if(!empty($users_delete))
				{
					$del_thanks = $del_thanks + count($users_delete);
					$sql = 'DELETE FROM ' . THANKS_TABLE ."
						WHERE " . $db->sql_in_set('user_id', $users_delete);
					$result = $db->sql_query($sql);		
				}	

				$all_posts = $posts + $numberpost + 1;
				$all_users = $users + $numberuser + 1;
			//no break;	
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
			'POSTS'			=> $all_posts,
			'USERS'			=> $all_users,
			'ALLTHANKS'		=> $all_thanks,
			'POSTSTHANKS'	=> $all_posts_thanks,
			'USERSTHANKS'	=> $all_users_thanks,
			'DELPOST'		=> $del_posts,
			'DELUPOST'		=> $del_uposts,
			'DELTHANKS'		=> $del_thanks,
			'POSTSEND'		=> $end_posts_thanks,
			'USERSEND'		=> $end_users_thanks,
			'THANKSEND'		=> $end_thanks,
			'S_REFRESH'		=> false,
		));
		$cache->destroy('_posts_thanks');
		$cache->destroy('_posts_delete');
		$cache->destroy('_users_thanks');
		$cache->destroy('_users_delete');
		$cache->destroy('_all_posts_thanks');
		$cache->destroy('_all_users_thanks');
		$cache->destroy('_all_thanks');
		$cache->destroy('_del_thanks');
		$cache->destroy('_del_uposts');
		$cache->destroy('_del_posts');
		$cache->destroy('_postmin');
		$cache->destroy('_usermin');
		$cache->destroy('_maxuid');
		$cache->destroy('_maxid');
		$cache->destroy('_stepr');
		$cache->destroy('_posts');
		$cache->destroy('_numberpost');
	}
}
?>