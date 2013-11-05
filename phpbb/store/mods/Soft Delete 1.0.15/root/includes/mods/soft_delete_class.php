<?php
/**
* @package phpBB3 Soft Delete
* @copyright (c) 2007 EXreaction,
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

class delete
{
	var $shadow_topic_ids = array(); // stores shadow topic ID's and relating topic_id

	var $post_data = array(); // ( stored as an array with post_id => array(data) )
	var $topic_data = array(); // ( stored as an array with topic_id => array(data) )
	var $forum_data = array(); // ( stored as an array with forum_id => array(data) )

	var $soft_delete = array('p' => array(), 't' => array()); // The topics/posts we will soft delete
	var $hard_delete = array('p' => array(), 't' => array()); // The topics/posts we will hard delete
	var $undelete = array('p' => array(), 't' => array()); // The topics/posts we will undelete

/*
* Get data/information ----------------------------------------------------------------------------
*/
	/**
	* Get the post data
	*/
	function get_post_data($post_ids)
	{
		global $auth, $config, $db, $phpbb_root_path, $phpEx, $user;

		$to_query = array();

		foreach ($post_ids as $id)
		{
			if (!array_key_exists($id, $this->post_data))
			{
				$to_query[] = $id;
			}
		}

		if (sizeof($to_query))
		{
			$topic_ids = array();

			$sql = 'SELECT * FROM ' . POSTS_TABLE . '
				WHERE ' . $db->sql_in_set('post_id', $to_query);
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$this->post_data[$row['post_id']] = $row;
				$topic_ids[] = $row['topic_id'];
			}
			$db->sql_freeresult($result);

			$this->get_shadow_topic_ids($topic_ids);
			$this->get_topic_data($topic_ids);
		}
	}

	/**
	* Get the topic data
	*/
	function get_topic_data($topic_ids)
	{
		global $auth, $config, $db, $phpbb_root_path, $phpEx, $user;

		$to_query = array();

		// Grab data for the shadow topic ID's as well
		if (sizeof($this->shadow_topic_ids))
		{
			foreach ($this->shadow_topic_ids as $id)
			{
				if (!array_key_exists($id, $this->topic_data))
				{
					$to_query[] = $id;
				}
			}
		}

		foreach ($topic_ids as $id)
		{
			if (!array_key_exists($id, $this->topic_data))
			{
				$to_query[] = $id;
			}
		}

		if (sizeof($to_query))
		{
			$forum_ids = array();

			$sql = 'SELECT * FROM ' . TOPICS_TABLE . '
				WHERE ' . $db->sql_in_set('topic_id', $to_query);
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$this->topic_data[$row['topic_id']] = $row;
				$forum_ids[] = $row['forum_id'];
			}
			$db->sql_freeresult($result);

			$this->get_forum_data($forum_ids);
		}
	}

	/**
	* Get the forum data
	*/
	function get_forum_data($forum_ids)
	{
		global $auth, $config, $db, $phpbb_root_path, $phpEx, $user;

		$to_query = array();

		foreach ($forum_ids as $id)
		{
			if (!array_key_exists($id, $this->forum_data))
			{
				$to_query[] = $id;
			}
		}

		if (sizeof($to_query))
		{
			$sql = 'SELECT * FROM ' . FORUMS_TABLE . '
				WHERE ' . $db->sql_in_set('forum_id', $to_query);
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$this->forum_data[$row['forum_id']] = $row;
			}
			$db->sql_freeresult($result);
		}
	}

	/**
	* Get the shadow topic ID's
	*/
	function get_shadow_topic_ids($topic_ids)
	{
		global $auth, $config, $db, $phpbb_root_path, $phpEx, $user;

		if (sizeof($topic_ids))
		{
			$sql = 'SELECT topic_id, topic_moved_id FROM ' . TOPICS_TABLE . '
				WHERE ' . $db->sql_in_set('topic_moved_id', $topic_ids);
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$this->shadow_topic_ids[$row['topic_moved_id']] = $row['topic_id'];
			}
			$db->sql_freeresult($result);
		}
	}

/*
* Check data/information --------------------------------------------------------------------------
*/
	/**
	* Check which posts/topics need to be hard and soft deleted and organize them into an array with each
	*/
	function hard_or_soft_check($post_ids, $topic_ids, $mcp_delete_topic = false)
	{
		global $auth, $config, $db, $phpbb_root_path, $phpEx, $user;

		if ($post_ids !== false && sizeof($post_ids))
		{
			foreach ($post_ids as $id)
			{
				if ($this->post_data[$id]['post_deleted'] != 0)
				{
					if ($auth->acl_get('m_harddelete', $this->post_data[$id]['forum_id']))
					{
						if ($mcp_delete_topic)
						{
							if ($this->topic_data[$this->post_data[$id]['topic_id']]['topic_deleted'] != 0)
							{
								$this->hard_delete['p'][] = $id;
							}
						}
						else
						{
							$this->hard_delete['p'][] = $id;
						}
					}
				}
				else
				{
					if ($auth->acl_get('m_harddelete', $this->post_data[$id]['forum_id']) || $auth->acl_get('m_delete', $this->post_data[$id]['forum_id']) || ($auth->acl_get('f_delete', $this->post_data[$id]['forum_id']) && $this->post_data[$id]['poster_id'] == $user->data['user_id']))
					{
						$this->soft_delete['p'][] = $id;
					}
				}
			}
		}

		if ($topic_ids !== false && sizeof($topic_ids))
		{
			foreach ($topic_ids as $id)
			{
				if ($this->topic_data[$id]['topic_deleted'] != 0)
				{
					if ($auth->acl_get('m_harddelete', $this->topic_data[$id]['forum_id']))
					{
						$this->hard_delete['t'][] = $id;
					}
				}
				else
				{
					if ($auth->acl_get('m_harddelete', $this->topic_data[$id]['forum_id']) || $auth->acl_get('m_delete',  $this->topic_data[$id]['forum_id']) || ($auth->acl_get('f_delete', $this->topic_data[$id]['forum_id']) && $this->topic_data[$id]['poster_id'] == $user->data['user_id']))
					{
						$this->soft_delete['t'][] = $id;
					}
				}
			}
		}
	}

	/**
	* Check which posts/topics need to be hard and soft deleted and organize them into an array with each
	*/
	function undelete_check($post_ids, $topic_ids)
	{
		global $auth, $config, $db, $phpbb_root_path, $phpEx, $user;

		if ($post_ids !== false && sizeof($post_ids))
		{
			foreach ($post_ids as $id)
			{
				if ($auth->acl_get('m_harddelete', $this->post_data[$id]['forum_id']) || $auth->acl_get('m_delete', $this->post_data[$id]['forum_id']) || ($auth->acl_get('f_delete', $this->post_data[$id]['forum_id']) && $this->post_data[$id]['post_deleted'] == $user->data['user_id']))
				{
					$this->undelete['p'][] = $id;
				}
			}
		}

		if ($topic_ids !== false && sizeof($topic_ids))
		{
			foreach ($topic_ids as $id)
			{
				if ($auth->acl_get('m_harddelete', $this->topic_data[$id]['forum_id']) || $auth->acl_get('m_delete',  $this->topic_data[$id]['forum_id']) || ($auth->acl_get('f_delete', $this->topic_data[$id]['forum_id']) && $this->topic_data[$id]['topic_deleted'] == $user->data['user_id']))
				{
					$this->soft_delete['t'][] = $id;
				}
			}
		}
	}

/*
* Soft Delete -------------------------------------------------------------------------------------
*/
	/**
	* Soft delete the required posts
	*/
	function soft_delete_posts()
	{
		global $auth, $config, $db, $phpbb_root_path, $phpEx, $user;

		if (sizeof($this->soft_delete['p']))
		{
			$sql_data = array(
				'post_deleted'			=> $user->data['user_id'],
				'post_deleted_time'		=> time(),
			);
			$sql = 'UPDATE ' . POSTS_TABLE . '
				SET ' . $db->sql_build_array('UPDATE', $sql_data) . '
					WHERE ' . $db->sql_in_set('post_id', $this->soft_delete['p']);
			$db->sql_query($sql);

			// Update the search index to remove any posts we have soft deleted
			$this->update_search_index($this->soft_delete['p'], true);

			foreach ($this->soft_delete['p'] as $id)
			{
				$topic_id = intval($this->post_data[$id]['topic_id']);
				$forum_id = intval($this->post_data[$id]['forum_id']);

				// Update the first/last topic info if we must
				if (($this->topic_data[$topic_id]['topic_first_post_id'] == $id && $this->topic_data[$topic_id]['topic_last_post_id'] == $id) || ($this->topic_data[$topic_id]['topic_deleted_reply_count'] == ($this->topic_data[$topic_id]['topic_replies_real'])))
				{
					// Since we are deleting the only post left we shall soft delete the topic
					$this->soft_delete['t'][] = $topic_id;

					if ($this->topic_data[$topic_id]['topic_poster'] != $user->data['user_id'])
					{
						add_log('mod', $forum_id, $topic_id, 'LOG_SOFT_DELETE_TOPIC', $this->topic_data[$topic_id]['topic_title']);
					}
				}
				else
				{
					// Update the first or last post if we have to.
					if ($this->topic_data[$topic_id]['topic_first_post_id'] == $id)
					{
						$this->update_first_post_topic($topic_id);
					}
					else if ($this->topic_data[$topic_id]['topic_last_post_id'] == $id)
					{
						$this->update_last_post_topic($topic_id);
					}

					if ($this->post_data[$id]['poster_id'] != $user->data['user_id'])
					{
						add_log('mod', $forum_id, $topic_id, 'LOG_SOFT_DELETE_POST', $this->post_data[$id]['post_subject']);
					}
				}

				// Fix post reported
				if ($this->post_data[$id]['post_reported'])
				{
					$sql = 'UPDATE ' . POSTS_TABLE . '
						SET post_reported = 0
							WHERE post_id = ' . $id;
					$db->sql_query($sql);

					$this->post_data[$id]['post_reported'] = 0;

					$some_reported = false;
					$sql = 'SELECT post_reported FROM ' . POSTS_TABLE . '
						WHERE topic_id = ' . $topic_id;
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						if ($row['post_reported'])
						{
							$some_reported = true;
							break;
						}
					}
					$db->sql_freeresult($result);

					// If none of the posts in this topic are reported anymore, reset it for the topic
					if (!$some_reported)
					{
						$sql = 'UPDATE ' . TOPICS_TABLE . '
							SET topic_reported = 0
								WHERE topic_id = ' . $topic_id;
						$db->sql_query($sql);
					}
				}

				// Update the deleted reply count for the topic
				$sql = 'UPDATE ' . TOPICS_TABLE . '
					SET topic_deleted_reply_count = topic_deleted_reply_count + 1
						WHERE topic_id = ' . $topic_id;
				$db->sql_query($sql);
				$this->topic_data[$topic_id]['topic_deleted_reply_count']++;

				// Update the deleted reply count for the shadow topics
				if (array_key_exists($topic_id, $this->shadow_topic_ids))
				{
					$sql = 'UPDATE ' . TOPICS_TABLE . '
						SET topic_deleted_reply_count = topic_deleted_reply_count + 1
							WHERE topic_id = ' .  intval($this->shadow_topic_ids[$topic_id]);
					$db->sql_query($sql);
					$this->topic_data[$this->shadow_topic_ids[$topic_id]]['topic_deleted_reply_count']++;
				}

				// If the topic is a global announcement, do not attempt to do any updates to forums
				if ($this->topic_data[$topic_id]['topic_type'] != POST_GLOBAL)
				{
					// Update the deleted reply count for the forum
					$sql = 'UPDATE ' . FORUMS_TABLE . '
						SET forum_deleted_reply_count = forum_deleted_reply_count + 1
							WHERE forum_id = ' . $forum_id;
					$db->sql_query($sql);
					$this->forum_data[$forum_id]['forum_deleted_reply_count']++;

					// Update the last post info for the forum if we must
					if ($this->forum_data[$forum_id]['forum_last_post_id'] == $id)
					{
						$this->update_last_post_forum($forum_id);
					}

					if (array_key_exists($topic_id, $this->shadow_topic_ids))
					{
						if ($this->forum_data[$this->topic_data[$this->shadow_topic_ids[$topic_id]]['forum_id']]['forum_last_post_id'] == $id)
						{
							$this->update_last_post_forum($this->topic_data[$this->shadow_topic_ids[$topic_id]]['forum_id']);
						}
					}
				}
			}

			// Soft delete the topics
			$this->soft_delete_topics();
		}
	}

	/**
	* Soft Delete the required topics
	*/
	function soft_delete_topics()
	{
		global $auth, $config, $db, $phpbb_root_path, $phpEx, $user;

		if (sizeof($this->soft_delete['t']))
		{
			$sql_data = array(
				'topic_deleted'			=> $user->data['user_id'],
				'topic_deleted_time'	=> time(),
			);

			$to_update = array();
			foreach ($this->soft_delete['t'] as $id)
			{
				if (array_key_exists($id, $this->shadow_topic_ids))
				{
					$to_update[] = $this->shadow_topic_ids[$id];
					$this->topic_data[$this->shadow_topic_ids[$id]] = array_merge($this->topic_data[$this->shadow_topic_ids[$id]], $sql_data);
				}

				$to_update[] = $id;
				$this->topic_data[$id] = array_merge($this->topic_data[$id], $sql_data);
			}

			$sql = 'UPDATE ' . TOPICS_TABLE . '
				SET ' . $db->sql_build_array('UPDATE', $sql_data) . '
					WHERE ' . $db->sql_in_set('topic_id', $to_update);
			$db->sql_query($sql);

			foreach ($to_update as $id)
			{
				// If the topic is a global announcement, do not attempt to do any updates to forums
				if ($this->topic_data[$id]['topic_type'] != POST_GLOBAL)
				{
					$sql = 'UPDATE ' . FORUMS_TABLE . '
						SET forum_deleted_topic_count = forum_deleted_topic_count + 1
							WHERE forum_id = ' . intval($this->topic_data[$id]['forum_id']);
					$db->sql_query($sql);
					$this->forum_data[$this->topic_data[$id]['forum_id']]['forum_deleted_topic_count']++;
				}
			}
		}

		$this->update_board_stats();
		$this->update_user_stats();
	}

/*
* Undelete ----------------------------------------------------------------------------------------
*/
	/**
	* Undelete the required posts
	*/
	function undelete_posts()
	{
		global $auth, $config, $db, $phpbb_root_path, $phpEx, $user;

		if (sizeof($this->undelete['p']))
		{
			$sql_data = array(
				'post_deleted'			=> 0,
				'post_deleted_time'		=> 0,
			);
			$sql = 'UPDATE ' . POSTS_TABLE . '
				SET ' . $db->sql_build_array('UPDATE', $sql_data) . '
					WHERE ' . $db->sql_in_set('post_id', $this->undelete['p']);
			$db->sql_query($sql);

			// Add the post info to the search index since we are undeleting it
			$this->update_search_index($this->undelete['p'], false);

			foreach ($this->undelete['p'] as $id)
			{
				$topic_id = intval($this->post_data[$id]['topic_id']);
				$forum_id = intval($this->post_data[$id]['forum_id']);

				// Update the first/last topic info if we must
				if ($this->topic_data[$topic_id]['topic_deleted'] != 0)
				{
					// Since the topic was deleted, undelete it
					$this->undelete['t'][] = $topic_id;

					if ($this->topic_data[$topic_id]['topic_poster'] != $user->data['user_id'])
					{
						add_log('mod', $forum_id, $topic_id, 'LOG_UNDELETE_TOPIC', $this->topic_data[$topic_id]['topic_title']);
					}
				}
				else
				{
					if ($this->post_data[$id]['poster_id'] != $user->data['user_id'])
					{
						add_log('mod', $forum_id, $topic_id, 'LOG_UNDELETE_POST', $this->post_data[$id]['post_subject']);
					}
				}

				if ($this->topic_data[$topic_id]['topic_first_post_id'] > $id)
				{
					$this->update_first_post_topic($topic_id);
				}

				if ($this->topic_data[$topic_id]['topic_last_post_id'] < $id)
				{
					$this->update_last_post_topic($topic_id);
				}

				$sql = 'UPDATE ' . TOPICS_TABLE . '
					SET topic_deleted_reply_count = topic_deleted_reply_count - 1
						WHERE topic_id = ' . $topic_id;
				$db->sql_query($sql);
				$this->topic_data[$topic_id]['topic_deleted_reply_count']--;

				// If there is a shadow topic, we will update it too.
				if (array_key_exists($topic_id, $this->shadow_topic_ids))
				{
					$sql = 'UPDATE ' . TOPICS_TABLE . '
						SET topic_deleted_reply_count = topic_deleted_reply_count - 1
							WHERE topic_id = ' . intval($this->shadow_topic_ids[$topic_id]);
					$db->sql_query($sql);

					$this->topic_data[$this->shadow_topic_ids[$topic_id]]['topic_deleted_reply_count']--;
				}

				// If the topic is a global announcement, do not attempt to do any updates to forums
				if ($this->topic_data[$topic_id]['topic_type'] != POST_GLOBAL)
				{
					// Update the last post info for the forum if we must
					if ($this->forum_data[$forum_id]['forum_last_post_id'] < $id)
					{
						$this->update_last_post_forum($forum_id);
					}

					if (array_key_exists($topic_id, $this->shadow_topic_ids))
					{
						if ($this->forum_data[$this->topic_data[$this->shadow_topic_ids[$topic_id]]['forum_id']]['forum_last_post_id'] == $id)
						{
							$this->update_last_post_forum($this->topic_data[$this->shadow_topic_ids[$topic_id]]['forum_id']);
						}
					}

					$sql = 'UPDATE ' . FORUMS_TABLE . '
						SET forum_deleted_reply_count = forum_deleted_reply_count - 1
							WHERE forum_id = ' . $forum_id;
					$db->sql_query($sql);
					$this->forum_data[$forum_id]['forum_deleted_reply_count']--;
				}
			}

			// Undelete the topics
			$this->undelete_topics();
		}
	}

	/**
	* Undelete the required topics
	*/
	function undelete_topics()
	{
		global $auth, $config, $db, $phpbb_root_path, $phpEx, $user;

		if (sizeof($this->undelete['t']))
		{
			$sql_data = array(
				'topic_deleted'			=> 0,
				'topic_deleted_time'	=> 0,
			);

			$to_update = array();
			foreach ($this->undelete['t'] as $id)
			{
				if (array_key_exists($id, $this->shadow_topic_ids))
				{
					$to_update[] = $this->shadow_topic_ids[$id];
					$this->topic_data[$this->shadow_topic_ids[$id]] = array_merge($this->topic_data[$this->shadow_topic_ids[$id]], $sql_data);
				}

				$to_update[] = $id;
				$this->topic_data[$id] = array_merge($this->topic_data[$id], $sql_data);
			}

			$sql = 'UPDATE ' . TOPICS_TABLE . '
				SET ' . $db->sql_build_array('UPDATE', $sql_data) . '
					WHERE ' . $db->sql_in_set('topic_id', $to_update);
			$db->sql_query($sql);

			foreach ($to_update as $id)
			{
				$this->update_first_post_topic($id);
				$this->update_last_post_topic($id);

				// If the topic is a global announcement, do not attempt to do any updates to forums
				if ($this->topic_data[$id]['topic_type'] != POST_GLOBAL)
				{
					if ($this->forum_data[$this->topic_data[$id]['forum_id']]['forum_deleted_topic_count'] > 0)
					{
						$sql = 'UPDATE ' . FORUMS_TABLE . '
							SET forum_deleted_topic_count = forum_deleted_topic_count - 1
								WHERE forum_id = ' . intval($this->topic_data[$id]['forum_id']);
						$db->sql_query($sql);
						$this->forum_data[$this->topic_data[$id]['forum_id']]['forum_deleted_topic_count']--;
					}
				}
			}
		}

		$this->update_board_stats();
		$this->update_user_stats();
	}

/*
* Hard Delete -------------------------------------------------------------------------------------
*/
	/**
	* Soft delete the required posts
	*/
	function hard_delete_posts()
	{
		global $auth, $config, $db, $phpbb_root_path, $phpEx, $user;

		if (sizeof($this->hard_delete['p']))
		{
			$sql = 'DELETE FROM ' . POSTS_TABLE . '
				WHERE ' . $db->sql_in_set('post_id', $this->hard_delete['p']);
			$db->sql_query($sql);

			foreach ($this->hard_delete['p'] as $id)
			{
				$topic_id = intval($this->post_data[$id]['topic_id']);
				$forum_id = intval($this->post_data[$id]['forum_id']);
				$sql_data = $sql_data1 = '';

				if ($this->topic_data[$topic_id]['topic_deleted'] != 0 && (($this->topic_data[$topic_id]['topic_first_post_id'] == $id && $this->topic_data[$topic_id]['topic_last_post_id'] == $id) || ($this->topic_data[$topic_id]['topic_deleted_reply_count'] == 1 && $this->topic_data[$topic_id]['topic_replies_real'] == 0)))
				{
					$this->hard_delete['t'][] = $topic_id;
					add_log('mod', $forum_id, $topic_id, 'LOG_HARD_DELETE_TOPIC', $this->topic_data[$topic_id]['topic_title']);

					if (array_key_exists($topic_id, $this->shadow_topic_ids))
					{
						$this->hard_delete['t'][] = $this->shadow_topic_ids[$topic_id];
					}

					if ($this->topic_data[$topic_id]['topic_type'] != POST_GLOBAL && $this->forum_data[$forum_id]['forum_deleted_topic_count'] > 0)
					{
						$sql_data1 .= 'forum_deleted_topic_count = forum_deleted_topic_count - 1, ';
						$this->forum_data[$forum_id]['forum_deleted_topic_count']--;
					}
				}
				else
				{
					add_log('mod', $forum_id, $topic_id, 'LOG_HARD_DELETE_POST', $this->post_data[$id]['post_subject']);

					if ($this->topic_data[$topic_id]['topic_replies'] > 0)
					{
						$sql_data .= 'topic_replies = topic_replies - 1, ';
						$this->topic_data[$topic_id]['topic_replies']--;
					}

					if ($this->topic_data[$topic_id]['topic_replies_real'] > 0)
					{
						$sql_data .= 'topic_replies_real = topic_replies_real - 1, ';
						$this->topic_data[$topic_id]['topic_replies_real']--;
					}
				}

				if ($this->topic_data[$topic_id]['topic_deleted_reply_count'] > 0)
				{
					$sql_data .= 'topic_deleted_reply_count = topic_deleted_reply_count - 1, ';
					$this->topic_data[$topic_id]['topic_deleted_reply_count']--;
				}

				if ($this->topic_data[$topic_id]['topic_type'] != POST_GLOBAL && $this->forum_data[$forum_id]['forum_deleted_reply_count'] > 0)
				{
					$sql_data1 .= 'forum_deleted_reply_count = forum_deleted_reply_count - 1, ';
					$this->forum_data[$forum_id]['forum_deleted_reply_count']--;
				}

				if ($this->topic_data[$topic_id]['topic_type'] != POST_GLOBAL && $this->forum_data[$forum_id]['forum_posts'] > 0)
				{
					$sql_data1 .= 'forum_posts = forum_posts - 1, ';
					$this->forum_data[$forum_id]['forum_posts']--;
				}

				if ($sql_data != '')
				{
					$sql = 'UPDATE ' . TOPICS_TABLE . '
						SET ' . $sql_data;
					$sql = substr($sql, 0, -2);
					$sql .= ' WHERE topic_id = ' . $topic_id;
					$db->sql_query($sql);
				}
				if ($sql_data1 != '' && $this->topic_data[$topic_id]['topic_type'] != POST_GLOBAL)
				{
					$sql = 'UPDATE ' . FORUMS_TABLE . '
						SET ' . $sql_data1;
						$sql = substr($sql, 0, -2);
						$sql .= ' WHERE forum_id = ' . $forum_id;
					$db->sql_query($sql);
				}
			}

			$this->delete_attachment_data();

			$this->hard_delete_topics();
		}
	}

	/**
	* Hard delete topics
	*/
	function hard_delete_topics()
	{
		global $auth, $config, $db, $phpbb_root_path, $phpEx, $user;

		if (sizeof($this->hard_delete['t']))
		{
			$sql = 'DELETE FROM ' . ATTACHMENTS_TABLE . '
				WHERE ' . $db->sql_in_set('topic_id', $this->hard_delete['t']);
			$db->sql_query($sql);

			$sql = 'DELETE FROM ' . BOOKMARKS_TABLE . '
				WHERE ' . $db->sql_in_set('topic_id', $this->hard_delete['t']);
			$db->sql_query($sql);

			$sql = 'DELETE FROM ' . POSTS_TABLE . '
				WHERE ' . $db->sql_in_set('topic_id', $this->hard_delete['t']);
			$db->sql_query($sql);

			$sql = 'DELETE FROM ' . TOPICS_TABLE . '
				WHERE ' . $db->sql_in_set('topic_id', $this->hard_delete['t']);
			$db->sql_query($sql);

			$sql = 'DELETE FROM ' . TOPICS_POSTED_TABLE . '
				WHERE ' . $db->sql_in_set('topic_id', $this->hard_delete['t']);
			$db->sql_query($sql);

			$sql = 'DELETE FROM ' . TOPICS_TRACK_TABLE . '
				WHERE ' . $db->sql_in_set('topic_id', $this->hard_delete['t']);
			$db->sql_query($sql);

			$sql = 'DELETE FROM ' . TOPICS_WATCH_TABLE . '
				WHERE ' . $db->sql_in_set('topic_id', $this->hard_delete['t']);
			$db->sql_query($sql);

			$forum_update = array();
			foreach ($this->hard_delete['t'] as $id)
			{
				$forum_id = $this->topic_data[$id]['forum_id'];

				if ($this->topic_data[$id]['topic_type'] != POST_GLOBAL)
				{
					if (!isset($forum_update[$forum_id]))
					{
						$forum_update[$forum_id]['topics'] = 1;
					}
					else
					{
						$forum_update[$forum_id]['topics']++;
					}
				}
			}

			foreach ($forum_update as $forum_id => $amt)
			{
				$sql_data = array(
					'forum_topics'				=> max(($this->forum_data[$forum_id]['forum_topics'] - $amt['topics']), 0),
					'forum_topics_real'			=> max(($this->forum_data[$forum_id]['forum_topics_real'] - $amt['topics']), 0),
					'forum_deleted_topic_count'	=> max(($this->forum_data[$forum_id]['forum_deleted_topic_count'] - $amt['topics']), 0),
				);

				$sql = 'UPDATE ' . FORUMS_TABLE . '
					SET ' . $db->sql_build_array('UPDATE', $sql_data) . '
						WHERE forum_id = ' . intval($forum_id);
				$db->sql_query($sql);
			}
		}
	}

	/**
	* Deletes attachment data (when hard deleting posts)
	*/
	function delete_attachment_data()
	{
		global $auth, $config, $db, $phpbb_root_path, $phpEx, $user;

		if (sizeof($this->hard_delete['p']))
		{
			$num_files = $config['num_files'];

			$sql = 'SELECT * FROM ' . ATTACHMENTS_TABLE . '
				WHERE ' . $db->sql_in_set('post_msg_id', $this->hard_delete['p']) . '
					AND in_message = 0
					AND topic_id != 0';
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				@unlink($phpbb_root_path . $config['upload_path'] . '/' . $row['physical_filename']);
				$num_files--;
			}

			if ($num_files != $config['num_files'])
			{
				set_config('num_files', $num_files, true);
			}

			$sql = 'DELETE FROM ' . ATTACHMENTS_TABLE . '
				WHERE ' . $db->sql_in_set('post_msg_id', $this->hard_delete['p']) . '
					AND in_message = 0
					AND topic_id != 0';
			$db->sql_query($sql);
		}
	}

/*
* Update data/information -------------------------------------------------------------------------
*/
	/**
	* Updates the first post information for a topic
	*/
	function update_first_post_topic($topic_id)
	{
		global $auth, $db, $phpbb_root_path, $phpEx, $user;

		$topic_id = (int) $topic_id;

		$sql = 'SELECT p.post_id, u.user_id, u.username, u.user_colour FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u
			WHERE p.topic_id = ' . $topic_id . '
				AND p.post_deleted = 0
				AND u.user_id = p.poster_id
					ORDER BY p.post_time ASC';
		$result = $db->sql_query_limit($sql, 1);
		$first_post_data = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$first_post_data || $this->topic_data[$topic_id]['topic_first_post_id'] == $first_post_data['post_id'])
		{
			return;
		}

		$sql_data = array(
			'topic_poster'				=> $first_post_data['user_id'],
			'topic_first_post_id'		=> $first_post_data['post_id'],
			'topic_first_poster_name'	=> $first_post_data['username'],
			'topic_first_poster_colour'	=> $first_post_data['user_colour'],
		);

		$this->topic_data[$topic_id] = array_merge($this->topic_data[$topic_id], $sql_data);

		if (array_key_exists($topic_id, $this->shadow_topic_ids))
		{
			$this->topic_data[$this->shadow_topic_ids[$topic_id]] = array_merge($this->topic_data[$this->shadow_topic_ids[$topic_id]], $sql_data);
			$sql_where = 'WHERE ' . $db->sql_in_set('topic_id', array($topic_id, $this->shadow_topic_ids[$topic_id]));
		}
		else
		{
			$sql_where = 'WHERE topic_id = ' . $topic_id;
		}

		$sql = 'UPDATE ' . TOPICS_TABLE . '
			SET ' . $db->sql_build_array('UPDATE', $sql_data) . ' ' .
				$sql_where;
		$db->sql_query($sql);
	}

	/**
	* Updates the last post information for a topic
	*/
	function update_last_post_topic($topic_id)
	{
		global $auth, $db, $phpbb_root_path, $phpEx, $user;

		$topic_id = (int) $topic_id;

		$sql = 'SELECT p.post_id, p.post_subject, p.post_time, u.user_id, u.username, u.user_colour FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u
			WHERE p.topic_id = ' . $topic_id . '
				AND p.post_deleted = 0
				AND u.user_id = p.poster_id
					ORDER BY p.post_time DESC';
		$result = $db->sql_query_limit($sql, 1);
		$last_post_data = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$last_post_data || $this->topic_data[$topic_id]['topic_last_post_id'] == $last_post_data['post_id'])
		{
			return;
		}

		$sql_data = array(
			'topic_last_post_id'		=> $last_post_data['post_id'],
			'topic_last_poster_id'		=> $last_post_data['user_id'],
			'topic_last_poster_name'	=> $last_post_data['username'],
			'topic_last_poster_colour'	=> $last_post_data['user_colour'],
			'topic_last_post_subject'	=> $last_post_data['post_subject'],
			'topic_last_post_time'		=> $last_post_data['post_time'],
		);

		$this->topic_data[$topic_id] = array_merge($this->topic_data[$topic_id], $sql_data);

		if (array_key_exists($topic_id, $this->shadow_topic_ids))
		{
			$this->topic_data[$this->shadow_topic_ids[$topic_id]] = array_merge($this->topic_data[$this->shadow_topic_ids[$topic_id]], $sql_data);
			$sql_where = 'WHERE ' . $db->sql_in_set('topic_id', array($topic_id, $this->shadow_topic_ids[$topic_id]));
		}
		else
		{
			$sql_where = 'WHERE topic_id = ' . $topic_id;
		}

		$sql = 'UPDATE ' . TOPICS_TABLE . '
			SET ' . $db->sql_build_array('UPDATE', $sql_data) . ' ' .
				$sql_where;
		$db->sql_query($sql);
	}

	/**
	* Updates the last post information for a forum
	*/
	function update_last_post_forum($forum_id)
	{
		global $auth, $db, $phpbb_root_path, $phpEx, $user;

		$forum_id = (int) $forum_id;

		if (!isset($this->forum_data[$forum_id]))
		{
			$this->get_forum_data(array($forum_id));
		}

		// If it still is not set the forum does not exist, so return.
		if (!isset($this->forum_data[$forum_id]))
		{
			return false;
		}

		$sql = 'SELECT p.post_id, p.topic_id, p.post_subject, p.post_time, u.user_id, u.username, u.user_colour FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u
			WHERE p.forum_id = ' . $forum_id . '
				AND p.post_deleted = 0
				AND u.user_id = p.poster_id
					ORDER BY p.post_time DESC';
		$result = $db->sql_query_limit($sql, 1);
		$last_post_data = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($this->forum_data[$forum_id]['forum_last_post_id'] == $last_post_data['post_id'])
		{
			return;
		}

		if ($last_post_data)
		{
			$sql_data = array(
				'forum_last_post_id'		=> $last_post_data['post_id'],
				'forum_last_poster_id'		=> $last_post_data['user_id'],
				'forum_last_post_subject'	=> $last_post_data['post_subject'],
				'forum_last_post_time'		=> $last_post_data['post_time'],
				'forum_last_poster_name'	=> $last_post_data['username'],
				'forum_last_poster_colour'	=> $last_post_data['user_colour'],
			);
		}
		else
		{
			$sql_data = array(
				'forum_last_post_id'		=> 0,
				'forum_last_poster_id'		=> 0,
				'forum_last_post_subject'	=> '',
				'forum_last_post_time'		=> 0,
				'forum_last_poster_name'	=> '',
				'forum_last_poster_colour'	=> '',
			);
		}

		$this->forum_data[$forum_id] = array_merge($this->forum_data[$forum_id], $sql_data);

		$sql = 'UPDATE ' . FORUMS_TABLE . '
			SET ' . $db->sql_build_array('UPDATE', $sql_data) . '
				WHERE forum_id = ' . $forum_id;
		$db->sql_query($sql);

		if ($this->forum_data[$forum_id]['parent_id'] != 0)
		{
			$this->update_last_post_forum($this->forum_data[$forum_id]['parent_id']);
		}
	}

	/**
	* Updates search information for a post
	*/
	function update_search_index($post_ids, $delete)
	{
		global $auth, $config, $db, $phpbb_root_path, $phpEx, $user;

		// Remove the message from the search index
		$search_type = basename($config['search_type']);

		if (!file_exists($phpbb_root_path . 'includes/search/' . $search_type . '.' . $phpEx))
		{
			trigger_error('NO_SUCH_SEARCH_MODULE');
		}

		include_once("{$phpbb_root_path}includes/search/{$search_type}.$phpEx");

		$error = false;
		$search = new $search_type($error);

		if ($error)
		{
			trigger_error($error);
		}

		$author_ids = array();
		foreach ($post_ids as $id)
		{
			$author_ids[] = $this->post_data[$id]['poster_id'];
		}

		if ($delete)
		{
			$search->index_remove($post_ids, $author_ids, false);
		}
		else
		{
			foreach ($post_ids as $id)
			{
				$search->index('reply', $id, $this->post_data[$id]['post_text'], $this->post_data[$id]['post_subject'], $this->post_data[$id]['poster_id'], $this->post_data[$id]['forum_id']);
			}
		}
	}

	/**
	* Updates Board Statistics
	*/
	function update_board_stats()
	{
		global $auth, $config, $db, $phpbb_root_path, $phpEx, $user;

		$num_posts = $config['num_posts'];
		$num_posts -= sizeof($this->soft_delete['p']);
		$num_posts += sizeof($this->undelete['p']);

		if ($num_posts != $config['num_posts'])
		{
			set_config('num_posts', $num_posts, true);
		}

		$num_topics = $config['num_topics'];
		$num_topics -= sizeof($this->soft_delete['t']);
		$num_topics += sizeof($this->undelete['t']);

		if ($num_topics != $config['num_topics'])
		{
			set_config('num_topics', $num_topics, true);
		}
	}

	/**
	* Update User Statistics
	*/
	function update_user_stats()
	{
		global $auth, $config, $db, $phpbb_root_path, $phpEx, $user;

		$posters_update = array();

		if (sizeof($this->soft_delete['p']))
		{
			foreach ($this->soft_delete['p'] as $id)
			{
				$poster_id = $this->post_data[$id]['poster_id'];
				if (!isset($posters_update[$poster_id]))
				{
					$posters_update[$poster_id] = -1;
				}
				else
				{
					$posters_update[$poster_id]--;
				}
			}
		}

		if (sizeof($this->hard_delete['p']))
		{
			foreach ($this->hard_delete['p'] as $id)
			{
				if ($this->post_data[$id]['post_deleted'] == 0)
				{
					$poster_id = $this->post_data[$id]['poster_id'];
					if (!isset($posters_update[$poster_id]))
					{
						$posters_update[$poster_id] = -1;
					}
					else
					{
						$posters_update[$poster_id]--;
					}
				}
			}
		}

		if (sizeof($this->undelete['p']))
		{
			foreach ($this->undelete['p'] as $id)
			{
				$poster_id = $this->post_data[$id]['poster_id'];
				if (!isset($posters_update[$poster_id]))
				{
					$posters_update[$poster_id] = 1;
				}
				else
				{
					$posters_update[$poster_id]++;
				}
			}
		}

		$user_data = array();
		$sql = 'SELECT user_id, user_posts FROM ' . USERS_TABLE . '
			WHERE ' . $db->sql_in_set('user_id', array_keys($posters_update));
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$user_data[$row['user_id']] = $row;
		}
		$db->sql_freeresult($result);

		foreach ($posters_update as $poster_id => $amt)
		{
			$user_data[$poster_id]['user_posts'] += $amt;

			if ($user_data[$poster_id]['user_posts'] < 0)
			{
				$user_data[$poster_id]['user_posts'] = 0;
			}

			$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array('user_posts' => $user_data[$poster_id]['user_posts'])) . '
				WHERE user_id = ' . $poster_id;
			$db->sql_query($sql);
		}
	}
}
?>