<?php
/**
* @package phpBB3 Soft Delete
* @copyright (c) 2007 EXreaction,
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (!defined('SOFT_DELETE_INCLUDED'))
{
	define('SOFT_DELETE_INCLUDED', true);

	/**
	* Updates the reply and real reply count for a topic to include the number of soft deleted posts if the user can view them.
	*	It also handles the check to see if a post is soft deleted or not (if it is it gives the error to regular users)
	*/
	function soft_delete_update_reply_count(&$topic_data, $error_on_deleted = true)
	{
		global $db, $auth, $user;

		if ((!isset($topic_data['topic_deleted_reply_count']) || !isset($topic_data['topic_deleted'])) && ($auth->acl_get('m_harddelete', $topic_data['forum_id']) || $auth->acl_get('m_delete', $topic_data['forum_id']) || $auth->acl_get('f_delete', $topic_data['forum_id'])))
		{
			$sql = 'SELECT topic_deleted, topic_deleted_reply_count FROM ' . TOPICS_TABLE . ' WHERE topic_id = ' . intval($topic_data['topic_id']);
			$result = $db->sql_query_limit($sql, 1);
			$count = $db->sql_fetchrow($result);
			$topic_data['topic_deleted_reply_count'] = $count['topic_deleted_reply_count'];
			$db->sql_freeresult($result);
			unset ($count);
		}

		if  ($topic_data['topic_deleted_reply_count'] == 0 || $auth->acl_get('m_harddelete', $topic_data['forum_id']) || $auth->acl_get('m_delete', $topic_data['forum_id']))
		{
			return;
		}

		if ($auth->acl_get('f_delete', $topic_data['forum_id']))
		{
			$sql = 'SELECT count(post_id) AS topic_deleted_reply_count FROM ' . POSTS_TABLE . '
				WHERE topic_id = ' . intval($topic_data['topic_id']) . '
					AND post_deleted != 0
					AND post_deleted != ' . $user->data['user_id'];
			$result = $db->sql_query($sql);
			$total = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$topic_data['topic_replies'] -= $total['topic_deleted_reply_count'];
			$topic_data['topic_replies_real'] -= $total['topic_deleted_reply_count'];
		}
		else
		{
			$topic_data['topic_replies'] -= $topic_data['topic_deleted_reply_count'];
			$topic_data['topic_replies_real'] -= $topic_data['topic_deleted_reply_count'];
		}

		if ($topic_data['topic_deleted'] != 0 &&  $topic_data['topic_deleted'] != $user->data['user_id'])
		{
			$topic_data['soft_deleted_error'] = true;

			if ($error_on_deleted)
			{
				// If the user->setup has not been run we need to run it.
				if (empty($user->lang_name))
				{
					$user->setup('viewtopic', $topic_data['forum_style']);
				}

				if (!isset($user->lang['TOPIC_SOFT_DELETED']))
				{
					$user->add_lang('mods/soft_delete');
				}

				trigger_error('TOPIC_SOFT_DELETED');
			}

			if (isset($total))
			{
				if ($total['topic_deleted_reply_count'] > 0)
				{
					return;
				}
			}
		}
		
	}

	/**
	* Updates the topic count for viewforum
	*/
	function soft_delete_update_topic_count(&$forum_data)
	{
		global $db, $auth, $user;

		if ($auth->acl_get('m_harddelete', $forum_data['forum_id']) || $auth->acl_get('m_delete', $forum_data['forum_id']))
		{
			return;
		}

		if (!isset($forum_data['forum_deleted_topic_count']) || $auth->acl_get('f_delete', $forum_data['forum_id']))
		{
			$sql = 'SELECT count(topic_id) AS forum_topic_count FROM ' . TOPICS_TABLE . '
				WHERE forum_id = ' . intval($forum_data['forum_id']) . 
					(($auth->acl_get('f_delete', $forum_data['forum_id'])) ? " AND topic_deleted = 0 OR topic_deleted = '{$user->data['user_id']}'" : " AND topic_deleted = 0");
			$result = $db->sql_query($sql);
			$count = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$forum_data['forum_topics'] = $count['forum_topic_count'];
			$forum_data['forum_topics_real'] = $count['forum_topic_count'];
		}
		else
		{
			$forum_data['forum_topics'] -= $forum_data['forum_deleted_topic_count'];
			$forum_data['forum_topics_real'] -= $forum_data['forum_deleted_topic_count'];
		}
	}

	/**
	* Updates the topic and reply count for the forum index & subforums
	*/
	function soft_delete_update_topic_post_count(&$forum_data)
	{
		global $auth;

		if ($auth->acl_get('m_harddelete', $forum_data['forum_id']) || $auth->acl_get('m_delete', $forum_data['forum_id']))
		{
			return;
		}

		if (isset($forum_data['forum_deleted_topic_count']))
		{
			// this time we just subtract the topic count instead of doing a bunch of SQL queries to find out exactly how many there is for other users
			$forum_data['forum_topics'] -= $forum_data['forum_deleted_topic_count'];
			$forum_data['forum_topics_real'] -= $forum_data['forum_deleted_topic_count'];
		}

		if (isset($forum_data['forum_deleted_reply_count']))
		{
			// this time we just subtract the reply count instead of doing a bunch of SQL queries to find out exactly how many there is for other users
			$forum_data['forum_posts'] -= $forum_data['forum_deleted_reply_count'];
			//$forum_data['forum_posts'] += $forum_data['forum_deleted_topic_count'];
		}
	}

	/**
	* Get Info on users who deleted the posts
	*/
	function get_soft_delete_users($rowset, &$user_cache)
	{
		global $auth, $user, $db;

		$delete_users_to_query = array();
		foreach ($rowset as $row)
		{
			if ($row['post_deleted'] != 0 && !array_key_exists($row['post_deleted'], $user_cache))
			{
				$delete_users_to_query[] = $row['post_deleted'];
			}
		}

		if (sizeof($delete_users_to_query))
		{
			$sql = 'SELECT user_id, username, user_colour FROM ' . USERS_TABLE . '
				WHERE ' . $db->sql_in_set('user_id', $delete_users_to_query);
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				if (!isset($user_cache[$row['user_id']]))
				{
					$user_cache[$row['user_id']] = array(
						'username'		=> $row['username'],
						'user_colour'	=> $row['user_colour'],
					);
				}
			}
		}
	}

	/**
	* Handles deleting of posts/topics for posting.php
	*/
	function handle_delete($post_id)
	{
		global $user, $db, $auth, $phpbb_root_path, $phpEx;

		$user->setup('common');
		$user->add_lang('mods/soft_delete');

		if (!$post_id)
		{
			trigger_error('NO_POST');
		}

		include($phpbb_root_path . 'includes/mods/soft_delete_class.' . $phpEx);
		$delete = new delete();
		$delete->get_post_data(array($post_id));
		$delete->hard_or_soft_check(array($post_id), false);
		$topic_id = $delete->post_data[$post_id]['topic_id'];
		$forum_id = $delete->post_data[$post_id]['forum_id'];

		if (!sizeof($delete->soft_delete['p']) && !sizeof($delete->hard_delete['p']))
		{
			trigger_error('NO_AUTH_OPERATION');
		}

		if (confirm_box(true))
		{
			// If they selected the Hard delete option on the confirm page and they are authorized to and the post has not already been hard deleted we will also hard delete it.
			if (isset($_POST['hard_delete']) && $auth->acl_get('m_harddelete', $forum_id) && $delete->post_data[$post_id]['post_deleted'] == 0)
			{
				$delete->hard_delete['p'][] = $post_id;
			}

			$delete->soft_delete_posts();
			$delete->hard_delete_posts();

			$redirect_post = append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f={$forum_id}&amp;t={$topic_id}&amp;p={$post_id}#p$post_id");
			$redirect_topic = append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f={$forum_id}&amp;t={$topic_id}");
			$redirect_forum = append_sid("{$phpbb_root_path}viewforum.$phpEx", "f={$forum_id}");

			if (sizeof($delete->hard_delete['t']))
			{
				$message = $user->lang['TOPIC_HARD_DELETE_SUCCESS'] . '<br/><br/>';

				$message .= sprintf($user->lang['CLICK_RETURN_FORUM'], "<a href=\"{$redirect_forum}\">", '</a><br/>');

				meta_refresh(3, $redirect_forum);
			}
			else
			{
				if (!sizeof($delete->hard_delete['p']))
				{
					if (sizeof($delete->soft_delete['t']))
					{
						$message = $user->lang['TOPIC_SOFT_DELETE_SUCCESS'] . '<br/><br/>';
					}
					else
					{
						$message = $user->lang['POST_SOFT_DELETE_SUCCESS'] . '<br/><br/>';
					}

					$message .= sprintf($user->lang['CLICK_RETURN_POST'], "<a href=\"{$redirect_post}\">", '</a><br/>');
					$message .= sprintf($user->lang['CLICK_RETURN_TOPIC'], "<a href=\"{$redirect_topic}\">", '</a><br/>');
					$message .= sprintf($user->lang['CLICK_RETURN_FORUM'], "<a href=\"{$redirect_forum}\">", '</a><br/>');

					meta_refresh(3, $redirect_post);
				}
				else
				{
					if (sizeof($delete->hard_delete['t']))
					{
						$message = $user->lang['TOPIC_HARD_DELETE_SUCCESS'] . '<br/><br/>';
						meta_refresh(3, $redirect_forum);
					}
					else
					{
						$message = $user->lang['POST_HARD_DELETE_SUCCESS'] . '<br/><br/>';
						$message .= sprintf($user->lang['CLICK_RETURN_TOPIC'], "<a href=\"{$redirect_topic}\">", '</a><br/>');

						meta_refresh(3, $redirect_topic);
					}

					$message .= sprintf($user->lang['CLICK_RETURN_FORUM'], "<a href=\"{$redirect_forum}\">", '</a><br/>');
				}
			}

			trigger_error($message);
		}
		else
		{
			$s_hidden_fields = build_hidden_fields(array(
				'p'		=> $post_id,
				'mode'	=> 'delete')
			);

			// What really should be done is add an extra field to the confirm_body.html file.  However that would require more edits, and this seems to work without any problems, so I'll use it.
			if ($auth->acl_get('m_harddelete', $forum_id) && $delete->post_data[$post_id]['post_deleted'] == 0)
			{
				$s_hidden_fields .= '<input type="checkbox" name="hard_delete" id="hard_delete"  /> <strong>' . $user->lang['HARD_DELETE'] . '</strong><br /><br />';
			}

			if (sizeof($delete->soft_delete['p']))
			{
				confirm_box(false, 'SOFT_DELETE_MESSAGE', $s_hidden_fields);
			}
			else
			{
				confirm_box(false, 'HARD_DELETE_MESSAGE', $s_hidden_fields);
			}
		}

		redirect(append_sid("{$phpbb_root_path}viewtopic.$phpEx", "p=$post_id") . "#p$post_id");
	}

	/**
	* Handles undeleting of posts/topics for posting.php
	*/
	function handle_undelete($post_id)
	{
		global $user, $db, $auth, $phpbb_root_path, $phpEx;

		$user->setup('common');
		$user->add_lang('mods/soft_delete');

		if (!$post_id)
		{
			trigger_error('NO_POST');
		}

		include($phpbb_root_path . 'includes/mods/soft_delete_class.' . $phpEx);
		$delete = new delete();
		$delete->get_post_data(array($post_id));
		$delete->undelete_check(array($post_id), false);

		if (!sizeof($delete->undelete['p']))
		{
			trigger_error('NO_AUTH_OPERATION');
		}

		if (confirm_box(true))
		{
			$delete->undelete_posts();

			$redirect_post = append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f={$delete->post_data[$post_id]['forum_id']}&amp;t={$delete->post_data[$post_id]['topic_id']}&amp;p={$post_id}#p$post_id");
			$redirect_topic = append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f={$delete->post_data[$post_id]['forum_id']}&amp;t={$delete->post_data[$post_id]['topic_id']}");
			$redirect_forum = append_sid("{$phpbb_root_path}viewforum.$phpEx", "f={$delete->post_data[$post_id]['forum_id']}");

			if (sizeof($delete->undelete['t']))
			{
				$message = $user->lang['TOPIC_UNDELETE_SUCCESS'] . '<br/><br/>';
			}
			else
			{
				$message = $user->lang['POST_UNDELETE_SUCCESS'] . '<br/><br/>';
			}

			$message .= sprintf($user->lang['CLICK_RETURN_POST'], "<a href=\"{$redirect_post}\">", '</a><br/>');
			$message .= sprintf($user->lang['CLICK_RETURN_TOPIC'], "<a href=\"{$redirect_topic}\">", '</a><br/>');
			$message .= sprintf($user->lang['CLICK_RETURN_FORUM'], "<a href=\"{$redirect_forum}\">", '</a><br/>');

			meta_refresh(3, $redirect_post);
			trigger_error($message);
		}
		else
		{
			$s_hidden_fields = build_hidden_fields(array(
				'p'		=> $post_id,
				'mode'	=> 'undelete')
			);

			confirm_box(false, 'UNDELETE_POST', $s_hidden_fields);
		}

		redirect(append_sid("{$phpbb_root_path}viewtopic.$phpEx", "p=$post_id") . "#p$post_id");
	}

	/**
	* MCP Helper for splitting/merging posts
	*/
	function mcp_split_merge_posts_helper($topic_id, $to_topic_id)
	{
		global $db, $user;

		$topic_id = (int) $topic_id;
		$to_topic_id = (int) $to_topic_id;

		$sql = 'SELECT * FROM ' . TOPICS_TABLE . ' WHERE topic_id = ' . $topic_id;
		$result = $db->sql_query($sql);
		$topic_data = $db->sql_fetchrow($result);

		$sql = 'SELECT * FROM ' . TOPICS_TABLE . ' WHERE topic_id = ' . $to_topic_id;
		$result = $db->sql_query($sql);
		$to_topic_data = $db->sql_fetchrow($result);

		$to_do = array('topic_id' => $topic_data, 'to_topic_id' => $to_topic_data);

		foreach($to_do as $do => $data)
		{
			if ($data['topic_deleted'])
			{
				$sql = 'UPDATE ' . FORUMS_TABLE . ' SET forum_deleted_topic_count = forum_deleted_topic_count - 1 WHERE forum_id = ' . intval($data['forum_id']);
				$db->sql_query($sql);
			}

			// Update the replies/reply deleted count/topic deleted data for the original topic
			$num_posts = $num_posts_real = $num_deleted_posts = 0;
			$sql = 'SELECT post_id, post_approved, post_deleted FROM ' . POSTS_TABLE . ' WHERE topic_id = ' . $$do;
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$num_posts++;

				if ($row['post_approved'])
				{
					$num_posts_real++;
				}

				if ($row['post_deleted'] != 0)
				{
					$num_deleted_posts++;
				}
			}
			$db->sql_freeresult($result);

			$sql_ary = array(
				'topic_replies'				=> $num_posts - 1,
				'topic_replies_real'		=> $num_posts_real - 1,
				'topic_deleted_reply_count'	=> $num_deleted_posts,
				'topic_deleted'				=> 0,
				'topic_deleted_time'		=> 0,
			);

			if ($num_deleted_posts == $num_posts)
			{
				if ($topic_data['topic_deleted'] != 0)
				{
					$sql_ary['topic_deleted'] = $topic_data['topic_deleted'];
					$sql_ary['topic_deleted_time'] = $topic_data['topic_deleted_time'];
				}
				else
				{
					$sql_ary['topic_deleted'] = $user->data['user_id'];
					$sql_ary['topic_deleted_time'] = time();
				}

				// Update the topic_deleted count for the forums 
				$sql = 'UPDATE ' . FORUMS_TABLE . ' SET forum_deleted_topic_count = forum_deleted_topic_count + 1
					WHERE forum_id = ' . intval($data['forum_id']);
				$db->sql_query($sql);
			}

			$sql = 'UPDATE ' . TOPICS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE topic_id = ' . $$do;
			$db->sql_query($sql);
		}
	}
	/**
	* MCP Helper for merging topics
	*/
	function mcp_merge_topics($topic_ids, $to_topic_id, $to_forum_id)
	{
		global $db, $user;

		$deleted_forums = array();
		$deleted_topics = $deleted_posts = $total_posts = 0;

		$sql = 'SELECT post_deleted, forum_id FROM ' . POSTS_TABLE . ' WHERE ' . $db->sql_in_set('topic_id', array_merge($topic_ids, array($to_topic_id)));
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['post_deleted'] != 0)
			{
				if (isset($deleted_forums[$row['forum_id']]['post']))
				{
					$deleted_forums[$row['forum_id']]['post']++;
				}
				else
				{
					$deleted_forums[$row['forum_id']] = array('topic' => 0, 'post' => 1);
				}
				$deleted_posts++;
			}
			$total_posts++;
		}

		$sql = 'SELECT topic_deleted, forum_id FROM ' . TOPICS_TABLE . ' WHERE ' . $db->sql_in_set('topic_id', array_merge($topic_ids, array($to_topic_id)));
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['topic_deleted'] != 0)
			{
				if (isset($deleted_forums[$row['forum_id']]['topic']))
				{
					$deleted_forums[$row['forum_id']]['topic']++;
				}
				else
				{
					$deleted_forums[$row['forum_id']] = array('topic' => 1, 'post' => 0);
				}
				$deleted_topics++;
			}
		}

		foreach ($deleted_forums as $id => $data)
		{
			$sql = 'SELECT forum_deleted_reply_count, forum_deleted_topic_count FROM ' . FORUMS_TABLE . ' WHERE forum_id = ' . $id;
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);

			$row['forum_deleted_reply_count'] -= $data['post'];
			$row['forum_deleted_topic_count'] -= $data['topic'];

			$sql_ary = array(
				'forum_deleted_reply_count' => (($row['forum_deleted_reply_count'] >= 0) ? $row['forum_deleted_reply_count'] : 0),
				'forum_deleted_topic_count'	=> (($row['forum_deleted_topic_count'] >= 0) ? $row['forum_deleted_topic_count'] : 0),
			);

			$sql = 'UPDATE ' . FORUMS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE forum_id = ' . $id;
			$db->sql_query($sql);
		}

		$sql_ary = array(
			'forum_deleted_reply_count' => (($row['forum_deleted_reply_count'] >= 0) ? $row['forum_deleted_reply_count'] : 0),
			'forum_deleted_topic_count'	=> (($row['forum_deleted_topic_count'] >= 0) ? $row['forum_deleted_topic_count'] : 0),
		);

		$sql = 'UPDATE ' . FORUMS_TABLE . ' SET ';
		$sql .= (($total_posts == $deleted_posts) ? 'forum_deleted_topic_count = forum_deleted_topic_count + 1, ' : '');
		$sql .= 'forum_deleted_reply_count = forum_deleted_reply_count + ' . $deleted_posts . '
				WHERE forum_id = ' . $to_forum_id;
		$db->sql_query($sql);

		if ($total_posts == $deleted_posts)
		{
			$sql_ary = array(
				'topic_deleted_reply_count'		=> $deleted_posts,
				'topic_deleted'					=> $user->data['user_id'],
				'topic_deleted_time'			=> time(),
			);

			$sql = 'UPDATE ' . TOPICS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE topic_id = ' . $to_topic_id;
			$db->sql_query($sql);
		}
		else
		{
			$sql_ary = array(
				'topic_deleted_reply_count'		=> $deleted_posts,
				'topic_deleted'					=> 0,
				'topic_deleted_time'			=> 0,
			);

			$sql = 'UPDATE ' . TOPICS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE topic_id = ' . $to_topic_id;
			$db->sql_query($sql);
		}
	}

	/**
	* MCP Delete Topics
	*/
	function mcp_delete_topics($topic_ids)
	{
		global $auth, $user, $db, $phpEx, $phpbb_root_path;

		$user->add_lang('mods/soft_delete');

		$redirect = request_var('redirect', build_url(array('_f_', 'action', 'quickmod')));
		$forum_id = request_var('f', 0);

		if (!is_array($topic_ids))
		{
			$topic_ids = array($topic_ids);
		}

		$s_hidden_fields = build_hidden_fields(array(
			'topic_id_list'	=> $topic_ids,
			'f'				=> $forum_id,
			'action'		=> 'delete_topic',
			'redirect'		=> $redirect)
		);

		include($phpbb_root_path . 'includes/mods/soft_delete_class.' . $phpEx);
		$delete = new delete();

		$redirect_forum = append_sid("{$phpbb_root_path}viewforum.$phpEx", "f={$forum_id}");

		$delete->get_topic_data($topic_ids);
		$post_ids = array();
		foreach ($topic_ids as $id)
		{
			if ($delete->topic_data[$id]['topic_moved_id'] != 0)
			{
				// They are attempting to delete a shadow topic, which should be just hard deleted.
				$delete->hard_delete['t'][] = $id;
			}
			else
			{
				$sql = 'SELECT post_id FROM ' . POSTS_TABLE . ' WHERE topic_id = ' . intval($id);
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$post_ids[] = $row['post_id'];
				}
			}
		}
		$delete->get_post_data($post_ids);

		$delete->hard_or_soft_check($post_ids, false, true);
		if (!sizeof($delete->soft_delete['p']) && !sizeof($delete->hard_delete['p']) && !sizeof($delete->hard_delete['t']))
		{
			trigger_error('NO_AUTH_OPERATION');
		}

		if (confirm_box(true))
		{
			$delete->soft_delete_posts();
			$delete->hard_delete_posts();
			$delete->hard_delete_topics(); // This deletes the shadow topics if that is what they wanted to do.

			$message = $user->lang['TOPICS_MCP_DELETE_SUCCESS'] . '<br/><br/>';
			$message .= sprintf($user->lang['CLICK_RETURN_FORUM'], "<a href=\"{$redirect_forum}\">", '</a><br/>');

			meta_refresh(3, $redirect_forum);
			trigger_error($message);
		}
		else
		{
			if (sizeof($delete->hard_delete['p']))
			{
				confirm_box(false, (sizeof($topic_ids) == 1) ? 'HARD_DELETE_TOPIC' : 'HARD_DELETE_TOPICS', $s_hidden_fields);
			}
			else
			{
				confirm_box(false, (sizeof($topic_ids) == 1) ? 'DELETE_TOPIC' : 'DELETE_TOPICS', $s_hidden_fields);
			}
		}

		meta_refresh(3, $redirect_forum);
	}

	/**
	* MCP Delete Posts
	*/
	function mcp_delete_posts($post_ids)
	{
		global $auth, $user, $db, $phpEx, $phpbb_root_path;

		$user->add_lang('mods/soft_delete');

		$redirect = request_var('redirect', build_url(array('_f_', 'action', 'quickmod')));
		$forum_id = request_var('f', 0);

		if (!is_array($post_ids))
		{
			$post_ids = array($post_ids);
		}

		$s_hidden_fields = build_hidden_fields(array(
			'post_id_list'	=> $post_ids,
			'f'				=> $forum_id,
			'action'		=> 'delete_post',
			'redirect'		=> $redirect)
		);

		include($phpbb_root_path . 'includes/mods/soft_delete_class.' . $phpEx);
		$delete = new delete();
		$delete->get_post_data($post_ids);
		$delete->hard_or_soft_check($post_ids, false);

		if (!sizeof($delete->soft_delete['p']) && !sizeof($delete->hard_delete['p']))
		{
			trigger_error('NO_AUTH_OPERATION');
		}

		if (sizeof($delete->topic_data) == 1)
		{
			$redirect_topic = append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f={$forum_id}&amp;t={$delete->post_data[$post_ids[0]]['topic_id']}");
		}
		else
		{
			$redirect_topic = false;
		}

		$redirect_forum = append_sid("{$phpbb_root_path}viewforum.$phpEx", "f={$forum_id}");

		if (confirm_box(true))
		{	
			$delete->soft_delete_posts();
			$delete->hard_delete_posts();

			$message = $user->lang['POSTS_MCP_DELETE_SUCCESS'] . '<br/><br/>';

			if ($redirect_topic)
			{
				$message .= sprintf($user->lang['CLICK_RETURN_TOPIC'], "<a href=\"{$redirect_topic}\">", '</a><br/>');
				meta_refresh(3, $redirect_topic);
			}
			else
			{
				meta_refresh(3, $redirect_forum);
			}
			$message .= sprintf($user->lang['CLICK_RETURN_FORUM'], "<a href=\"{$redirect_forum}\">", '</a><br/>');

			trigger_error($message);
		}
		else
		{
			confirm_box(false, (sizeof($delete->topic_data) == 1) ? 'DELETE_POST' : 'DELETE_POSTS', $s_hidden_fields);
		}

		if ($redirect_topic)
		{
			meta_refresh(3, $redirect_topic);
		}
		else
		{
			meta_refresh(3, $redirect_forum);
		}
	}

	/**
	* MCP Move Topics
	*/
	function mcp_move_topics($topic_ids)
	{
		global $auth, $user, $db, $template;
		global $phpEx, $phpbb_root_path;

		// Here we limit the operation to one forum only
		$forum_id = check_ids($topic_ids, TOPICS_TABLE, 'topic_id', array('m_move'), true);

		if ($forum_id === false)
		{
			return;
		}

		$to_forum_id = request_var('to_forum_id', 0);
		$redirect = request_var('redirect', build_url(array('_f_', 'action', 'quickmod')));
		$additional_msg = $success_msg = '';

		include($phpbb_root_path . 'includes/mods/soft_delete_class.' . $phpEx);
		$delete = new delete();
		$delete->get_forum_data(array($forum_id, $to_forum_id));

		$s_hidden_fields = build_hidden_fields(array(
			'topic_id_list'	=> $topic_ids,
			'f'				=> $forum_id,
			'action'		=> 'move',
			'redirect'		=> $redirect)
		);

		if ($to_forum_id)
		{
			$forum_data = get_forum_data($to_forum_id);

			if (!sizeof($forum_data))
			{
				$additional_msg = $user->lang['FORUM_NOT_EXIST'];
			}
			else
			{
				$forum_data = $forum_data[$to_forum_id];

				if ($forum_data['forum_type'] != FORUM_POST)
				{
					$additional_msg = $user->lang['FORUM_NOT_POSTABLE'];
				}
				else if (!$auth->acl_get('f_post', $to_forum_id))
				{
					$additional_msg = $user->lang['USER_CANNOT_POST'];
				}
				else if ($forum_id == $to_forum_id)
				{
					$additional_msg = $user->lang['CANNOT_MOVE_SAME_FORUM'];
				}
			}
		}
		else if (isset($_POST['confirm']))
		{
			$additional_msg = $user->lang['FORUM_NOT_EXIST'];
		}

		if (!$to_forum_id || $additional_msg)
		{
			unset($_POST['confirm']);
			unset($_REQUEST['confirm_key']);
		}

		if (confirm_box(true))
		{
			$topic_data = get_topic_data($topic_ids);
			$leave_shadow = (isset($_POST['move_leave_shadow'])) ? true : false;

			$topics_moved = $topics_total_moved = $deleted_topics_moved = 0;
			$forum_sync_data = array();

			$forum_sync_data[$forum_id] = current($topic_data);
			$forum_sync_data[$to_forum_id] = $forum_data;

			$db->sql_transaction('begin');

			// Move topics, but do not resync yet
			move_topics($topic_ids, $to_forum_id, false);

			$forum_ids = array($to_forum_id);
			foreach ($topic_data as $topic_id => $row)
			{
				// Get the list of forums to resync, add a log entry
				$forum_ids[] = $row['forum_id'];
				add_log('mod', $to_forum_id, $topic_id, 'LOG_MOVE', $row['forum_name']);

				if ($row['topic_deleted'])
				{
					$deleted_topics_moved++;
				}

				if ($row['topic_approved'])
				{
					$topics_moved++;
				}

				$topics_total_moved++;

				// If we have moved a global announcement, we need to correct the topic type
				if ($row['topic_type'] == POST_GLOBAL)
				{
					$sql = 'UPDATE ' . TOPICS_TABLE . '
						SET topic_type = ' . POST_ANNOUNCE . '
						WHERE topic_id = ' . (int) $row['topic_id'];
					$db->sql_query($sql);
				}

				// Leave a redirection if required and only if the topic is visible to users
				if ($leave_shadow && $row['topic_approved'] && $row['topic_type'] != POST_GLOBAL)
				{
					$shadow = array(
						'forum_id'					=> (int) $row['forum_id'],
						'icon_id'					=> (int) $row['icon_id'],
						'topic_attachment'			=> (int) $row['topic_attachment'],
						'topic_approved'			=> 1,
						'topic_reported'			=> (int) $row['topic_reported'],
						'topic_title'				=> (string) $row['topic_title'],
						'topic_poster'				=> (int) $row['topic_poster'],
						'topic_time'				=> (int) $row['topic_time'],
						'topic_time_limit'			=> (int) $row['topic_time_limit'],
						'topic_views'				=> (int) $row['topic_views'],
						'topic_replies'				=> (int) $row['topic_replies'],
						'topic_replies_real'		=> (int) $row['topic_replies_real'],
						'topic_status'				=> ITEM_MOVED,
						'topic_type'				=> POST_NORMAL,
						'topic_first_post_id'		=> (int) $row['topic_first_post_id'],
						'topic_first_poster_colour'	=> (string) $row['topic_first_poster_colour'],
						'topic_first_poster_name'	=> (string) $row['topic_first_poster_name'],
						'topic_last_post_id'		=> (int) $row['topic_last_post_id'],
						'topic_last_poster_id'		=> (int) $row['topic_last_poster_id'],
						'topic_last_poster_colour'	=> (string) $row['topic_last_poster_colour'],
						'topic_last_poster_name'	=> (string) $row['topic_last_poster_name'],
						'topic_last_post_subject'	=> (string)  $row['topic_last_post_subject'],
						'topic_last_post_time'		=> (int) $row['topic_last_post_time'],
						'topic_last_view_time'		=> (int) $row['topic_last_view_time'],
						'topic_moved_id'			=> (int) $row['topic_id'],
						'topic_bumped'				=> (int) $row['topic_bumped'],
						'topic_bumper'				=> (int) $row['topic_bumper'],
						'poll_title'				=> (string) $row['poll_title'],
						'poll_start'				=> (int) $row['poll_start'],
						'poll_length'				=> (int) $row['poll_length'],
						'poll_max_options'			=> (int) $row['poll_max_options'],
						'poll_last_vote'			=> (int) $row['poll_last_vote'],
						'topic_deleted'				=> (int) $row['topic_deleted'],
						'topic_deleted_time'		=> (int) $row['topic_deleted_time'],
						'topic_deleted_reply_count'	=> (int) $row['topic_deleted_reply_count'],
					);

					$db->sql_query('INSERT INTO ' . TOPICS_TABLE . $db->sql_build_array('INSERT', $shadow));
				}
			}
			unset($topic_data);

			$sql = 'SELECT count(post_id) AS topic_posts
				FROM ' . POSTS_TABLE . '
				WHERE ' . $db->sql_in_set('topic_id', $topic_ids);
			$result = $db->sql_query($sql);
			$row_data = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$sql = 'SELECT count(post_id) AS topic_posts
				FROM ' . POSTS_TABLE . '
				WHERE ' . $db->sql_in_set('topic_id', $topic_ids) . '
					AND post_deleted != 0';
			$result = $db->sql_query($sql);
			$deleted_row_data = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$sync_sql = array();

			$sync_sql[$forum_id][]		= 'forum_posts = forum_posts - ' . (int) $row_data['topic_posts'];
			$sync_sql[$to_forum_id][]	= 'forum_posts = forum_posts + ' . (int) $row_data['topic_posts'];
			$sync_sql[$forum_id][]		= 'forum_deleted_reply_count = forum_deleted_reply_count - ' . (int) $deleted_row_data['topic_posts'];
			$sync_sql[$to_forum_id][]	= 'forum_deleted_reply_count = forum_deleted_reply_count + ' . (int) $deleted_row_data['topic_posts'];

			$sync_sql[$forum_id][]		= 'forum_topics = forum_topics - ' . (int) $topics_moved;
			$sync_sql[$to_forum_id][]	= 'forum_topics = forum_topics + ' . (int) $topics_moved;

			$sync_sql[$forum_id][]		= 'forum_topics_real = forum_topics_real - ' . (int) $topics_total_moved;
			$sync_sql[$to_forum_id][]	= 'forum_topics_real = forum_topics_real + ' . (int) $topics_total_moved;
			$sync_sql[$forum_id][]		= 'forum_deleted_topic_count = forum_deleted_topic_count - ' . (int) $deleted_topics_moved;
			$sync_sql[$to_forum_id][]	= 'forum_deleted_topic_count = forum_deleted_topic_count + ' . (int) $deleted_topics_moved;

			$success_msg = (sizeof($topic_ids) == 1) ? 'TOPIC_MOVED_SUCCESS' : 'TOPICS_MOVED_SUCCESS';

			foreach ($sync_sql as $forum_id_key => $array)
			{
				$sql = 'UPDATE ' . FORUMS_TABLE . '
					SET ' . implode(', ', $array) . '
					WHERE forum_id = ' . intval($forum_id_key);
				$db->sql_query($sql);
			}

			$db->sql_transaction('commit');

			sync('forum', 'forum_id', array($forum_id, $to_forum_id));
			$delete->update_last_post_forum($forum_id);
			$delete->update_last_post_forum($to_forum_id);
		}
		else
		{
			$template->assign_vars(array(
				'S_FORUM_SELECT'		=> make_forum_select($to_forum_id, $forum_id, false, true, true, true),
				'S_CAN_LEAVE_SHADOW'	=> true,
				'ADDITIONAL_MSG'		=> $additional_msg)
			);

			confirm_box(false, 'MOVE_TOPIC' . ((sizeof($topic_ids) == 1) ? '' : 'S'), $s_hidden_fields, 'mcp_move.html');
		}

		$redirect = request_var('redirect', "index.$phpEx");
		$redirect = reapply_sid($redirect);

		if (!$success_msg)
		{
			redirect($redirect);
		}
		else
		{
			meta_refresh(3, $redirect);

			$message = $user->lang[$success_msg];
			$message .= '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . $redirect . '">', '</a>');
			$message .= '<br /><br />' . sprintf($user->lang['RETURN_FORUM'], '<a href="' . append_sid("{$phpbb_root_path}viewforum.$phpEx", "f=$forum_id") . '">', '</a>');
			$message .= '<br /><br />' . sprintf($user->lang['RETURN_NEW_FORUM'], '<a href="' . append_sid("{$phpbb_root_path}viewforum.$phpEx", "f=$to_forum_id") . '">', '</a>');

			trigger_error($message);
		}
	}

	/**
	* Fork Topic
	*/
	function mcp_fork_topics($topic_ids)
	{
		global $auth, $user, $db, $template, $config;
		global $phpEx, $phpbb_root_path;

		$to_forum_id = request_var('to_forum_id', 0);
		$forum_id = request_var('f', 0);
		$redirect = request_var('redirect', build_url(array('_f_', 'action', 'quickmod')));
		$additional_msg = $success_msg = '';

		$s_hidden_fields = build_hidden_fields(array(
			'topic_id_list'	=> $topic_ids,
			'f'				=> $forum_id,
			'action'		=> 'fork',
			'redirect'		=> $redirect)
		);

		if ($to_forum_id)
		{
			$forum_data = get_forum_data($to_forum_id);

			if (!sizeof($topic_ids))
			{
				$additional_msg = $user->lang['NO_TOPICS_SELECTED'];
			}
			else if (!sizeof($forum_data))
			{
				$additional_msg = $user->lang['FORUM_NOT_EXIST'];
			}
			else
			{
				$forum_data = $forum_data[$to_forum_id];

				if ($forum_data['forum_type'] != FORUM_POST)
				{
					$additional_msg = $user->lang['FORUM_NOT_POSTABLE'];
				}
				else if (!$auth->acl_get('f_post', $to_forum_id))
				{
					$additional_msg = $user->lang['USER_CANNOT_POST'];
				}
			}
		}
		else if (isset($_POST['confirm']))
		{
			$additional_msg = $user->lang['FORUM_NOT_EXIST'];
		}

		if ($additional_msg)
		{
			unset($_POST['confirm']);
			unset($_REQUEST['confirm_key']);
		}

		if (confirm_box(true))
		{
			include($phpbb_root_path . 'includes/mods/soft_delete_class.' . $phpEx);
			$delete = new delete();

			$delete->get_topic_data($topic_ids);

			$total_posts = $num_soft_deleted_topics = $num_soft_deleted_replies = 0;
			$new_topic_id_list = array();

			foreach ($topic_ids as $topic_id)
			{
				$topic_id = (int) $topic_id;

				if (!isset($delete->topic_data[$topic_id]))
				{
					continue;
				}
				$topic_row = $delete->topic_data[$topic_id];

				if ($topic_row['topic_deleted'] != 0)
				{
					$num_soft_deleted_topics++;
				}

				$sql_ary = array_merge($topic_row, array(
					'forum_id'					=> (int) $to_forum_id,
					'topic_approved'			=> 1,
					'topic_reported'			=> 0,
				));
				unset($sql_ary['topic_id']);

				$db->sql_query('INSERT INTO ' . TOPICS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
				$new_topic_id = $db->sql_nextid();
				$new_topic_id_list[$topic_id] = $new_topic_id;

				if ($topic_row['poll_start'])
				{
					$sql = 'SELECT *
						FROM ' . POLL_OPTIONS_TABLE . "
						WHERE topic_id = $topic_id";
					$result = $db->sql_query($sql);

					while ($row = $db->sql_fetchrow($result))
					{
						$sql_ary = array(
							'poll_option_id'	=> (int) $row['poll_option_id'],
							'topic_id'			=> (int) $new_topic_id,
							'poll_option_text'	=> (string) $row['poll_option_text'],
							'poll_option_total'	=> 0
						);

						$db->sql_query('INSERT INTO ' . POLL_OPTIONS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
					}
				}

				$sql = 'SELECT *
					FROM ' . POSTS_TABLE . "
					WHERE topic_id = $topic_id
					ORDER BY post_time ASC";
				$result = $db->sql_query($sql);

				$post_rows = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$post_rows[] = $row;
				}
				$db->sql_freeresult($result);

				if (!sizeof($post_rows))
				{
					continue;
				}

				$total_posts += sizeof($post_rows);
				foreach ($post_rows as $row)
				{
					if ($row['post_deleted'] != 0)
					{
						$num_soft_deleted_replies++;
					}

					$sql_ary = array_merge($row, array(
						'topic_id'			=> (int) $new_topic_id,
						'forum_id'			=> (int) $to_forum_id,
						'post_approved'		=> 1,
						'post_reported'		=> 0,
						'post_postcount'	=> 0,
					));
					unset($sql_ary['post_id']);

					$db->sql_query('INSERT INTO ' . POSTS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
					$new_post_id = $db->sql_nextid();

					// Copy whether the topic is dotted
					markread('post', $to_forum_id, $new_topic_id, 0, $row['poster_id']);

					// Copy Attachments
					if ($row['post_attachment'])
					{
						$sql = 'SELECT * FROM ' . ATTACHMENTS_TABLE . "
							WHERE post_msg_id = {$row['post_id']}
								AND topic_id = $topic_id
								AND in_message = 0";
						$result = $db->sql_query($sql);

						$sql_ary = array();
						while ($attach_row = $db->sql_fetchrow($result))
						{
							$sql_ary[] = array_merge($attach_row, array(
								'post_msg_id'		=> (int) $new_post_id,
								'topic_id'			=> (int) $new_topic_id,
								'in_message'		=> 0,
							));
							unset($sql_ary[(sizeof($sql_ary) - 1)]['attach_id']);
						}
						$db->sql_freeresult($result);

						if (sizeof($sql_ary))
						{
							$db->sql_multi_insert(ATTACHMENTS_TABLE, $sql_ary);
						}
					}
				}

				$sql = 'SELECT user_id, notify_status
					FROM ' . TOPICS_WATCH_TABLE . '
					WHERE topic_id = ' . $topic_id;
				$result = $db->sql_query($sql);

				$sql_ary = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$sql_ary[] = array(
						'topic_id'		=> (int) $new_topic_id,
						'user_id'		=> (int) $row['user_id'],
						'notify_status'	=> (int) $row['notify_status'],
					);
				}
				$db->sql_freeresult($result);

				if (sizeof($sql_ary))
				{
					$db->sql_multi_insert(TOPICS_WATCH_TABLE, $sql_ary);
				}
			}

			// Sync new topics, parent forums and board stats
			sync('topic', 'topic_id', $new_topic_id_list);

			$sync_sql = array();

			$sync_sql[$to_forum_id][]	= 'forum_posts = forum_posts + ' . $total_posts;
			$sync_sql[$to_forum_id][]	= 'forum_topics = forum_topics + ' . sizeof($new_topic_id_list);
			$sync_sql[$to_forum_id][]	= 'forum_topics_real = forum_topics_real + ' . sizeof($new_topic_id_list);
			$sync_sql[$to_forum_id][]	= 'forum_deleted_topic_count = forum_deleted_topic_count + ' . $num_soft_deleted_topics;
			$sync_sql[$to_forum_id][]	= 'forum_deleted_reply_count = forum_deleted_reply_count + ' . $num_soft_deleted_replies;

			foreach ($sync_sql as $forum_id_key => $array)
			{
				$sql = 'UPDATE ' . FORUMS_TABLE . '
					SET ' . implode(', ', $array) . '
					WHERE forum_id = ' . intval($forum_id_key);
				$db->sql_query($sql);
			}

			sync('forum', 'forum_id', $to_forum_id);
			set_config('num_topics', $config['num_topics'] + sizeof($new_topic_id_list), true);
			set_config('num_posts', $config['num_posts'] + $total_posts, true);

			foreach ($new_topic_id_list as $topic_id => $new_topic_id)
			{
				$delete->get_forum_data(array($topic_row['forum_id']));
				add_log('mod', $to_forum_id, $new_topic_id, 'LOG_FORK', $delete->forum_data[$topic_row['forum_id']]['forum_name']);
			}

			$success_msg = (sizeof($topic_ids) == 1) ? 'TOPIC_FORKED_SUCCESS' : 'TOPICS_FORKED_SUCCESS';
		}
		else
		{
			$template->assign_vars(array(
				'S_FORUM_SELECT'		=> make_forum_select($to_forum_id, false, false, true, true, true),
				'S_CAN_LEAVE_SHADOW'	=> false,
				'ADDITIONAL_MSG'		=> $additional_msg)
			);

			confirm_box(false, 'FORK_TOPIC' . ((sizeof($topic_ids) == 1) ? '' : 'S'), $s_hidden_fields, 'mcp_move.html');
		}

		$redirect = request_var('redirect', "index.$phpEx");
		$redirect = reapply_sid($redirect);

		if (!$success_msg)
		{
			redirect($redirect);
		}
		else
		{
			$redirect_url = append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $forum_id);
			//meta_refresh(3, $redirect_url);
			$return_link = sprintf($user->lang['RETURN_FORUM'], '<a href="' . $redirect_url . '">', '</a>');

			if ($forum_id != $to_forum_id)
			{
				$return_link .= '<br /><br />' . sprintf($user->lang['RETURN_NEW_FORUM'], '<a href="' . append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $to_forum_id) . '">', '</a>');
			}

			trigger_error($user->lang[$success_msg] . '<br /><br />' . $return_link);
		}
	}
}
?>