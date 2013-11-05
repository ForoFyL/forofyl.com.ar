<?php
/**
*
* @package phpBB3
* @version $Id: toplist.php,v 128 2010-05-31 10:02:51 Палыч$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/functions_thanks.' . $phpEx);
include($phpbb_root_path . 'includes/functions_thanks_forum.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup(array('memberlist', 'groups', 'mods/thanks_mod', 'search'));

// Grab data
$mode = request_var('mode', '');
$end_row_rating = $config['thanks_number_row_reput'];
$full_post_rating = $full_topic_rating = $full_forum_rating = false;
$u_search_post = $u_search_topic = $u_search_forum = '';
$total_match_count = 0;
$page_title = $user->lang['REPUT_TOPLIST'];
$topic_id = request_var('t', 0);
$return_chars = request_var('ch', ($topic_id) ? -1 : 300);
$words = array();

$pagination_url = append_sid("{$phpbb_root_path}toplist.$phpEx",'mode='.$mode);

if (!$auth->acl_gets('u_viewtoplist'))
{
	if ($user->data['user_id'] != ANONYMOUS)
	{
		trigger_error('RATING_NO_VIEW_TOPLIST');
	}
	login_box('', ((isset($user->lang['LOGIN_EXPLAIN_' . strtoupper($mode)])) ? $user->lang['LOGIN_EXPLAIN_' . strtoupper($mode)] : $user->lang['RETING_LOGIN_EXPLAIN']));
}
$notoplist = true;
$start	= request_var('start', 0);
$max_post_thanks = get_max_post_thanks();
$max_topic_thanks = get_max_topic_thanks();
$max_forum_thanks = get_max_forum_thanks();
switch ($mode)
{
		case 'post':
		$sql = 'SELECT COUNT(DISTINCT post_id) as total_post_count
			FROM ' . THANKS_TABLE;
		$result = $db->sql_query($sql);
		$total_match_count = (int) $db->sql_fetchfield('total_post_count');
		$db->sql_freeresult($result);			
		$full_post_rating = true;
		$notoplist = false;
		break;
		
		case 'topic':
		$sql = 'SELECT COUNT(DISTINCT topic_id) as total_topic_count
			FROM ' . THANKS_TABLE;
		$result = $db->sql_query($sql);
		$total_match_count = (int) $db->sql_fetchfield('total_topic_count');
		$db->sql_freeresult($result);
		$full_topic_rating = true;
		$notoplist = false;
		break;
		
		case 'forum':
		$sql = 'SELECT COUNT(DISTINCT forum_id) as total_forum_count
			FROM ' . THANKS_TABLE;
		$result = $db->sql_query($sql);
		$total_match_count = (int) $db->sql_fetchfield('total_forum_count');
		$db->sql_freeresult($result);
		$full_forum_rating = true;
		$notoplist = false;
		break;
		
		default:
		$page_title = $user->lang['REPUT_TOPLIST'];
		$total_match_count = 0;
}
//post rating
	if (!$full_forum_rating && !$full_topic_rating && $config['thanks_post_reput_view'])
	{
		$end = ($full_post_rating) ?  $config['topics_per_page'] : $end_row_rating;
	
		$sql_p_array['FROM']	= array(THANKS_TABLE => 't');		
		$sql_p_array['SELECT'] = 'u.user_id, u.username, u.user_colour, p.post_subject, p.post_id, p.post_time, p.poster_id, p.post_username, p.topic_id, p.forum_id, p.post_text, p.bbcode_uid, p.bbcode_bitfield, p.post_attachment';
		$sql_p_array['SELECT'].= ', t.post_id, COUNT(*) AS post_thanks';			
		$sql_p_array['LEFT_JOIN'][] = array(
			'FROM'	=> array (POSTS_TABLE => 'p'),
			'ON'	=> 't.post_id = p.post_id',
			);
		$sql_p_array['LEFT_JOIN'][] = array(
			'FROM'	=> array(USERS_TABLE => 'u'),
			'ON'	=> 'p.poster_id = u.user_id'
		);
		$sql_p_array['GROUP_BY'] = 't.post_id';	
		$sql_p_array['ORDER_BY'] = 'post_thanks DESC';		
			
		$sql = $db->sql_build_query('SELECT',$sql_p_array);
		$result = $db->sql_query_limit($sql, $end, $start);	
		$u_search_post = append_sid("{$phpbb_root_path}toplist.$phpEx", "mode=post");		
		if (!$row = $db->sql_fetchrow($result))
		{
			trigger_error('RATING_VIEW_TOPLIST_NO');
		}		
		else
		{
			$notoplist = false;
			$bbcode_bitfield = $text_only_message = '';		
			do
			{
					// We pre-process some variables here for later usage
					$row['post_text'] = censor_text($row['post_text']);
					$text_only_message = $row['post_text'];
					// make list items visible as such
					if ($row['bbcode_uid'])
					{
						$text_only_message = str_replace('[*:' . $row['bbcode_uid'] . ']', '&sdot;&nbsp;', $text_only_message);
						// no BBCode in text only message
						strip_bbcode($text_only_message, $row['bbcode_uid']);
					}

					if ($return_chars == -1 || utf8_strlen($text_only_message) < ($return_chars + 3))
					{
						$row['display_text_only'] = false;
						$bbcode_bitfield = $bbcode_bitfield | base64_decode($row['bbcode_bitfield']);

						// Does this post have an attachment? If so, add it to the list
						if ($row['post_attachment'] && $config['allow_attachments'])
						{
							$attach_list[$row['forum_id']][] = $row['post_id'];
						}
					}
					else
					{
						$row['post_text'] = $text_only_message;
						$row['display_text_only'] = true;
					}
					$rowset[] = $row;
					unset($text_only_message);

					// Instantiate BBCode if needed
					if ($bbcode_bitfield !== '' and !class_exists('bbcode'))
						{
							include($phpbb_root_path . 'includes/bbcode.' . $phpEx);
							$bbcode = new bbcode(base64_encode($bbcode_bitfield));
						}
					// Replace naughty words such as farty pants
					$row['post_subject'] = censor_text($row['post_subject']);

					if ($row['display_text_only'])
					{
						$row['post_text'] = get_context($row['post_text'], $words, $return_chars);
						$row['post_text'] = bbcode_nl2br($row['post_text']);
					}
					else
					{
						// Second parse bbcode here
						if ($row['bbcode_bitfield'])
						{
							$bbcode->bbcode_second_pass($row['post_text'], $row['bbcode_uid'], $row['bbcode_bitfield']);
						}

						$row['post_text'] = bbcode_nl2br($row['post_text']);
						$row['post_text'] = smiley_text($row['post_text']);						
					}
			
			
//for read\unread			$topic_tracking_info = get_complete_topic_tracking($row['forum_id'], $row['topic_id']);
//for read\unread			$post_unread = (isset($topic_tracking_info[$row['topic_id']]) && $row['post_time'] > $topic_tracking_info[$row['topic_id']]) ? true : false;
				$post_url = append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'p='.$row['post_id'].'#p'.$row['post_id']);
				$template->assign_block_vars('toppostrow', array(
//for read\unread			'MINI_POST_IMG'				=> ($post_unread) ? $user->img('icon_post_target_unread', 'NEW_POST') : $user->img('icon_post_target', 'POST'),
					'MESSAGE'			=> ($auth->acl_get('f_read', $row['forum_id'])) ? $row['post_text'] : ((!empty($row['forum_id'])) ? $user->lang['SORRY_AUTH_READ'] : $row['post_text']),
					'POST_DATE'					=> (!empty($row['post_time'])) ? $user->format_date($row['post_time']) : '',
					'MINI_POST_IMG'				=> $user->img('icon_post_target', 'POST'),
					'POST_ID'					=> $post_url,
					'POST_SUBJECT'		=> ($auth->acl_get('f_read', $row['forum_id'])) ? $row['post_subject'] : ((!empty($row['forum_id'])) ? '' : $row['post_subject']),
					'POST_AUTHOR'				=> get_username_string('full', $row['poster_id'], $row['username'], $row['user_colour'], $row['post_username']),
					'POST_REPUT'				=> round($row['post_thanks'] / ($max_post_thanks / 100), $config['thanks_number_digits']),
					'S_THANKS_POST_REPUT_VIEW' 	=> $config['thanks_post_reput_view'],
					'S_THANKS_REPUT_GRAPHIC' 	=> $config['thanks_reput_graphic'],
					'THANKS_REPUT_HEIGHT'		=> sprintf('%dpx', $config['thanks_reput_height']),
					'THANKS_REPUT_GRAPHIC_WIDTH' 	=> sprintf('%dpx', $config['thanks_reput_level']*$config['thanks_reput_height']),
					'THANKS_REPUT_IMAGE' 		=> ($config['thanks_reput_image']) ? $phpbb_root_path . $config['thanks_reput_image'] : '',
					'THANKS_REPUT_IMAGE_BACK'	=> ($config['thanks_reput_image_back']) ? $phpbb_root_path . $config['thanks_reput_image_back'] : '',
				));
			}
			while ($row = $db->sql_fetchrow($result));
			$db->sql_freeresult($result);			
		}
	}
//topic rating
	if (!$full_forum_rating && !$full_post_rating && $config['thanks_topic_reput_view'])
	{
		$end = ($full_topic_rating) ?  $config['topics_per_page'] : $end_row_rating;
	
		$sql_t_array['FROM']	= array(THANKS_TABLE => 'f');		
		$sql_t_array['SELECT'] = 'u.user_id, u.username, u.user_colour, t.topic_title, t.topic_id, t.topic_time, t.topic_poster, t.topic_first_poster_name, t.topic_first_poster_colour, t.forum_id';
		$sql_t_array['SELECT'].= ', f.topic_id, COUNT(*) AS topic_thanks';			
		$sql_t_array['LEFT_JOIN'][] = array(
			'FROM'	=> array (TOPICS_TABLE => 't'),
			'ON'	=> 'f.topic_id = t.topic_id',
			);
		$sql_t_array['LEFT_JOIN'][] = array(
			'FROM'	=> array(USERS_TABLE => 'u'),
			'ON'	=> 't.topic_poster = u.user_id'
		);
		$sql_t_array['GROUP_BY'] = 'f.topic_id';	
		$sql_t_array['ORDER_BY'] = 'topic_thanks DESC';		
			
		$sql = $db->sql_build_query('SELECT',$sql_t_array);
		$result = $db->sql_query_limit($sql, $end, $start);
		$u_search_topic = append_sid("{$phpbb_root_path}toplist.$phpEx", "mode=topic");			
		if (!$row = $db->sql_fetchrow($result))
		{
			trigger_error('RATING_VIEW_TOPLIST_NO');
		}		
		else
		{
			$notoplist = false;
			do
			{
				$view_topic_url_params = 'f=' . (($row['forum_id']) ? $row['forum_id'] : '') . '&amp;t=' . $row['topic_id'];
				$view_topic_url = append_sid("{$phpbb_root_path}viewtopic.$phpEx", $view_topic_url_params);
				$template->assign_block_vars('toptopicrow', array(
					'TOPIC_FOLDER_IMG_SRC'		=> $user->img('topic_read', 'NO_NEW_POSTS', false, '', 'src'),
					'TOPIC_TITLE'				=> ($auth->acl_get('f_read', $row['forum_id'])) ? $row ['topic_title'] : ((!empty($row['forum_id'])) ? $user->lang['SORRY_AUTH_READ'] : $row ['topic_title']),
					'U_VIEW_TOPIC'				=> $view_topic_url,
					'TOPIC_AUTHOR'				=> get_username_string('full', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
					'TOPIC_REPUT'				=> round($row['topic_thanks'] / ($max_topic_thanks / 100), $config['thanks_number_digits']),
					'S_THANKS_TOPIC_REPUT_VIEW' => $config['thanks_topic_reput_view'],
					'S_THANKS_REPUT_GRAPHIC' 	=> $config['thanks_reput_graphic'],
					'THANKS_REPUT_HEIGHT'		=> sprintf('%dpx', $config['thanks_reput_height']),
					'THANKS_REPUT_GRAPHIC_WIDTH'=> sprintf('%dpx', $config['thanks_reput_level']*$config['thanks_reput_height']),
					'THANKS_REPUT_IMAGE' 		=> ($config['thanks_reput_image']) ? $phpbb_root_path . $config['thanks_reput_image'] : '',
					'THANKS_REPUT_IMAGE_BACK'	=> ($config['thanks_reput_image_back']) ? $phpbb_root_path . $config['thanks_reput_image_back'] : '',
				));
			}
			while ($row = $db->sql_fetchrow($result));
			$db->sql_freeresult($result);			
		}
	}
//forum rating
	if (!$full_topic_rating && !$full_post_rating && $config['thanks_forum_reput_view'])
	{
		$end = ($full_forum_rating) ?  $config['topics_per_page'] : $end_row_rating;
	
		$sql_f_array['FROM']	= array(THANKS_TABLE => 't');		
		$sql_f_array['SELECT'] = 'f.forum_name, f.forum_id';
		$sql_f_array['SELECT'].= ', t.forum_id, COUNT(*) AS forum_thanks';			
		$sql_f_array['LEFT_JOIN'][] = array(
			'FROM'	=> array (FORUMS_TABLE => 'f'),
			'ON'	=> 't.forum_id = f.forum_id',
			);
		$sql_f_array['GROUP_BY'] = 't.forum_id';	
		$sql_f_array['ORDER_BY'] = 'forum_thanks DESC';		
			
		$sql = $db->sql_build_query('SELECT',$sql_f_array);
		$result = $db->sql_query_limit($sql, $end, $start);	
		$u_search_forum = append_sid("{$phpbb_root_path}toplist.$phpEx", "mode=forum");				
		if (!$row = $db->sql_fetchrow($result))
		{
			trigger_error('RATING_VIEW_TOPLIST_NO');
		}		
		else
		{
			$notoplist = false;
			do
			{
				if (!empty($row['forum_id']))
				{
					$u_viewforum = append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $row['forum_id']);
					$template->assign_block_vars('topforumrow', array(
						'FORUM_FOLDER_IMG_SRC'		=> $user->img('forum_read', 'NO_NEW_POSTS', false, '', 'src'),
						'FORUM_NAME'				=> ($auth->acl_get('f_read', $row['forum_id'])) ? $row['forum_name'] : ((!empty($row['forum_id'])) ? $user->lang['SORRY_AUTH_READ'] : $row['forum_name']),
						'U_VIEW_FORUM'				=> $u_viewforum,
						'FORUM_REPUT'				=> round($row['forum_thanks'] / ($max_forum_thanks / 100), $config['thanks_number_digits']),
						'S_THANKS_FORUM_REPUT_VIEW' => $config['thanks_forum_reput_view'],
						'S_THANKS_REPUT_GRAPHIC' 	=> $config['thanks_reput_graphic'],
						'THANKS_REPUT_HEIGHT'		=> sprintf('%dpx', $config['thanks_reput_height']),
						'THANKS_REPUT_GRAPHIC_WIDTH'=> sprintf('%dpx', $config['thanks_reput_level']*$config['thanks_reput_height']),
						'THANKS_REPUT_IMAGE' 		=> ($config['thanks_reput_image']) ? $phpbb_root_path . $config['thanks_reput_image'] : '',
						'THANKS_REPUT_IMAGE_BACK'	=> ($config['thanks_reput_image_back']) ? $phpbb_root_path . $config['thanks_reput_image_back'] : '',
					));
				}
			}
			while ($row = $db->sql_fetchrow($result));
			$db->sql_freeresult($result);			
		}
	}
	if ($notoplist)
	{
		trigger_error('RATING_VIEW_TOPLIST_NO');	
	}
	
// Output the page
		$template->assign_vars(array(
			'PAGINATION'				=> generate_pagination($pagination_url, $total_match_count, $config['posts_per_page'], $start),
			'PAGE_NUMBER'				=> on_page($total_match_count, $config['posts_per_page'], $start),
			'PAGE_TITLE'				=> $page_title,
			'S_THANKS_FORUM_REPUT_VIEW' => $config['thanks_forum_reput_view'],
			'S_THANKS_TOPIC_REPUT_VIEW' => $config['thanks_topic_reput_view'],
			'S_THANKS_POST_REPUT_VIEW'	=> $config['thanks_post_reput_view'],
			'S_FULL_POST_RATING'		=> $full_post_rating,
			'S_FULL_TOPIC_RATING'		=> $full_topic_rating,
			'S_FULL_FORUM_RATING'		=> $full_forum_rating,
			'U_SEARCH_POST'				=> $u_search_post,
			'U_SEARCH_TOPIC'			=> $u_search_topic,
			'U_SEARCH_FORUM'			=> $u_search_forum,
		));

page_header($page_title);
$template->set_filenames(array(
	'body' => 'toplist_body.html'));
	
make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();
?>