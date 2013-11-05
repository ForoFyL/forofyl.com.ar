<?php
/**
*
* @package phpBB3
* @version $Id: thankslist.php,v 125 2009-12-01 10:02:51 Палыч$
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

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup(array('memberlist', 'groups', 'mods/thanks_mod', 'search'));

// Grab data
$mode = request_var('mode', '');
$author_id = request_var('author_id', ANONYMOUS);
$give = request_var('give', '');
$row_number	= 0;
$givens = array();
$reseved = array();
$rowsp = array();
$rowsu = array();
$total_users = 0;
$words = array();
$sthanks = false;

if (!$auth->acl_gets('u_viewthanks'))
{
	if ($user->data['user_id'] != ANONYMOUS)
	{
		trigger_error('NO_VIEW_USERS_THANKS');
	}
	login_box('', ((isset($user->lang['LOGIN_EXPLAIN_' . strtoupper($mode)])) ? $user->lang['LOGIN_EXPLAIN_' . strtoupper($mode)] : $user->lang['LOGIN_EXPLAIN_MEMBERLIST']));
}
$top = request_var('top', 0);
$start = request_var('start', 0);
$submit = (isset($_POST['submit'])) ? true : false;
$default_key = 'a';
$sort_key = request_var('sk', $default_key);
$sort_dir = request_var('sd', 'd');
$topic_id = request_var('t', 0);
$return_chars = request_var('ch', ($topic_id) ? -1 : 300);
$order_by = '';

switch ($mode)
{
		case 'givens':
		$per_page = $config['topics_per_page'];
		$total_match_count = 0;
		$page_title = $user->lang['SEARCH'];
		$template_html = 'thanks_results.html';

		switch ($give)
		{
				case 'true':
				$u_search = append_sid("{$phpbb_root_path}thankslist.$phpEx", "mode=givens&amp;author_id=$author_id&amp;give=true");
				$sql = 'SELECT COUNT(user_id) AS total_match_count
					FROM ' . THANKS_TABLE . "
					WHERE user_id = $author_id"; 
				$where = 'user_id';
				break;
				
				case 'false':
				$u_search = append_sid("{$phpbb_root_path}thankslist.$phpEx", "mode=givens&amp;author_id=$author_id&amp;give=false");
				$sql = 'SELECT COUNT(DISTINCT post_id) as total_match_count
					FROM ' . THANKS_TABLE . "
					WHERE poster_id = $author_id";
				$where = 'poster_id';
				break;		
		}
		
		$result = $db->sql_query($sql);

		if (!$row = $db->sql_fetchrow($result))
		{
			break;
		}
		else
		{
			$total_match_count = $row['total_match_count'];
			$db->sql_freeresult($result);

			$sql_array = array(
				'SELECT'	=> 'u.username, u.user_colour, p.poster_id, p.post_id, p.topic_id, p.forum_id, p.post_time, p.post_subject, p.post_text, p.post_username, p.bbcode_bitfield, p.bbcode_uid, p.post_attachment',
				'FROM'		=> array (THANKS_TABLE => 't'),
				'WHERE'		=> 't.' . $where . "= $author_id"
			);
			$sql_array['LEFT_JOIN'][] = array(
				'FROM'	=> array(USERS_TABLE => 'u'),
				'ON'	=> 't.poster_id = u.user_id'
			);
			$sql_array['LEFT_JOIN'][] = array(
				'FROM'	=> array(POSTS_TABLE => 'p'),
				'ON'	=> 't.post_id = p.post_id'
			);
			$sql = $db->sql_build_query('SELECT_DISTINCT', $sql_array);
			$result = $db->sql_query_limit($sql, $config['topics_per_page'], $start);
	
			if (!$row = $db->sql_fetchrow($result))
			{
				break;
			}		
			else
			{
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
					$forum_id = $row['forum_id'];

					$template->assign_block_vars('searchresults', array (
						'POST_AUTHOR_FULL'		=> get_username_string('full', $row['poster_id'], $row['username'], $row['user_colour'], $row['post_username']),
						'POST_AUTHOR_COLOUR'	=> get_username_string('colour', $row['poster_id'], $row['username'], $row['user_colour'], $row['post_username']),
						'POST_AUTHOR'			=> get_username_string('username', $row['poster_id'], $row['username'], $row['user_colour'], $row['post_username']),
						'U_POST_AUTHOR'			=> get_username_string('profile', $row['poster_id'], $row['username'], $row['user_colour'], $row['post_username']),
						'POST_SUBJECT'		=> ($auth->acl_get('f_read', $row['forum_id'])) ? $row['post_subject'] : ((!empty($row['forum_id'])) ? '' : $row['post_subject']),
						'POST_DATE'			=> (!empty($row['post_time'])) ? $user->format_date($row['post_time']) : '',
						'MESSAGE'			=> ($auth->acl_get('f_read', $row['forum_id'])) ? $row['post_text'] : ((!empty($row['forum_id'])) ? $user->lang['SORRY_AUTH_READ'] : $row['post_text']),
						'FORUM_ID'			=> $row['forum_id'],
						'TOPIC_ID'			=> $row['topic_id'],
						'POST_ID'			=> $row['post_id'],
						'U_VIEW_TOPIC'		=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 't=' . $row['topic_id']),
						'U_VIEW_FORUM'		=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $row['forum_id']),
						'U_VIEW_POST'		=> (!empty($row['post_id'])) ? append_sid("{$phpbb_root_path}viewtopic.$phpEx", "t=" . $row['topic_id'] . '&amp;p=' . $row['post_id']) . '#p' . $row['post_id'] : '',
					));
				}
				while ($row = $db->sql_fetchrow($result));

				$db->sql_freeresult($result);
			}		
		}
		if ($total_match_count > 1000)
		{
			$total_match_count--;
			$l_search_matches = sprintf($user->lang['FOUND_MORE_SEARCH_MATCHES'], $total_match_count);
		}
		else
		{
		$l_search_matches = ($total_match_count == 1) ? sprintf($user->lang['FOUND_SEARCH_MATCH'], $total_match_count) : sprintf($user->lang['FOUND_SEARCH_MATCHES'], $total_match_count);
		}
		$template->assign_vars(array(
			'PAGINATION'		=> generate_pagination($u_search, $total_match_count, $per_page, $start),
			'PAGE_NUMBER'		=> on_page($total_match_count, $per_page, $start),
			'TOTAL_MATCHES'		=> $total_match_count,
			'SEARCH_MATCHES'	=> $l_search_matches,
			'U_THANKS'			=> append_sid("{$phpbb_root_path}thankslist.$phpEx"),
		));

		break;
		
		default:
		$page_title = $user->lang['THANKS_USER'];
		$template_html = 'thankslist_body.html';

		// Grab relevant data thanks
		$sql = 'SELECT user_id, COUNT(*) AS tally
			FROM ' . THANKS_TABLE . "
			GROUP BY user_id";
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$givens[$row['user_id']] = $row['tally'];
		}
		$db->sql_freeresult($result);
			
		$sql = 'SELECT poster_id, COUNT(*) AS tally
			FROM ' . THANKS_TABLE . "
			GROUP BY poster_id";
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$reseved[$row['poster_id']] = $row['tally'];
		}
		$db->sql_freeresult($result);	

		// Sorting
		$sort_key_text = array('a' => $user->lang['SORT_USERNAME'], 'b' => $user->lang['SORT_LOCATION'], 'c' => $user->lang['SORT_JOINED'], 'd' => $user->lang['SORT_POST_COUNT'], 'e' => 'R_THANKS', 'f' => 'G_THANKS',);
		$sort_key_sql = array('a' => 'u.username_clean', 'b' => 'u.user_from', 'c' => 'u.user_regdate', 'd' => 'u.user_posts', 'e' => 'count_thanks', 'f' => 'count_thanks');
		$sort_dir_text = array('a' => $user->lang['ASCENDING'], 'd' => $user->lang['DESCENDING']);
		if ($auth->acl_get('u_viewonline'))
		{
			$sort_key_text['l'] = $user->lang['SORT_LAST_ACTIVE'];
			$sort_key_sql['l'] = 'u.user_lastvisit';
		}

		$s_sort_key = '';
		foreach ($sort_key_text as $key => $value)
		{
			$selected = ($sort_key == $key) ? ' selected="selected"' : '';
			$s_sort_key .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
		}
		$s_sort_dir = '';
		foreach ($sort_dir_text as $key => $value)
		{
			$selected = ($sort_dir == $key) ? ' selected="selected"' : '';
			$s_sort_dir .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
		}

		// Sorting and order
		if (!isset($sort_key_sql[$sort_key]))
		{
			$sort_key = $default_key;
		}

		$order_by .= $sort_key_sql[$sort_key] . ' ' . (($sort_dir == 'a') ? 'ASC' : 'DESC');

		// Build a relevant pagination_url
		$params = array();
		$check_params = array(
			'sk'			=> array('sk', $default_key),
			'sd'			=> array('sd', 'a'),
		);
		foreach ($check_params as $key => $call)
		{
			if (!isset($_REQUEST[$key]))
			{
				continue;
			}

			$param = call_user_func_array('request_var', $call);
			$param = urlencode($key) . '=' . ((is_string($param)) ? urlencode($param) : $param);
			$params[] = $param;

			if ($key != 'sk' && $key != 'sd')
			{
				$sort_params[] = $param;
			}
		}
		$pagination_url = append_sid("{$phpbb_root_path}thankslist.$phpEx", implode('&amp;', $params));
		$sort_url = append_sid("{$phpbb_root_path}thankslist.$phpEx", $mode);

		// Grab relevant data
		$sql = 'SELECT DISTINCT poster_id
			FROM ' . THANKS_TABLE;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$rowsp[] = $row['poster_id']; 
		}

		$sql = 'SELECT DISTINCT user_id
			FROM ' . THANKS_TABLE;
		$result = $db->sql_query($sql);	

		while ($row = $db->sql_fetchrow($result))
		{
			$rowsu[] = $row['user_id']; 
		}
		
		if ($sort_key == 'e')
		{
			$sortparam = 'poster';
			$rows = $rowsp;
		}
		else if ($sort_key == 'f')
		{
			$sortparam = 'user';
			$rows = $rowsu;
		}
		else
		{
			$sortparam = '';		
			$rows = array_merge($rowsp,$rowsu);
		}

		$total_users = count(array_unique($rows));
		
		if (empty($rows))
		{
			break;
		}
		

		
		$sql_array = array(
			'SELECT'	=> 'u.user_id, u.username, u.user_posts, u.user_colour, u.user_rank, u.user_inactive_reason, u.user_type, u.username_clean, u.user_regdate, u.user_website, u.user_from, u.user_lastvisit',
			'FROM'		=> array(USERS_TABLE => 'u'),
			'ORDER_BY'	=> $order_by,
		);
		
		if ($top)
		{
			$total_users = $top;
			$start = 0;
			$page_title = $user->lang['REPUT_TOPLIST'];
		}
		else
		{
			$top = $config['topics_per_page'];
		}
		
		if ($sortparam)
		{	
			$sql_array['FROM']	= array(THANKS_TABLE => 't');		
			$sql_array['SELECT'].= ', count(t.'.$sortparam.'_id) as count_thanks';	
			$sql_array['LEFT_JOIN'][] = array(
					'FROM'	=> array(USERS_TABLE => 'u'),
					'ON'	=> 't.'.$sortparam.'_id = u.user_id' 
				);
			$sql_array['GROUP_BY'] = 't.'.$sortparam.'_id';
		}

		$sql_array['WHERE'] = 'u.user_id = '.$rows[0];
		for ($i = 1, $end = sizeof($rows); $i < $end; ++$i)
		{
			$sql_array['WHERE'] .= ' OR u.user_id = '.$rows[$i];
		}

		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query_limit($sql, $top, $start);

		if (!$row = $db->sql_fetchrow($result))
		{
			trigger_error('NO_USER');
		}		
		else
		{
			do
			{
				$last_visit = $row['user_lastvisit'];
				$user_id = $row['user_id'];
				$rank_title = $rank_img = $rank_img_src = '';
				get_user_rank($row['user_rank'], (($user_id == ANONYMOUS) ? false : $row['user_posts']), $rank_title, $rank_img, $rank_img_src);
				$sthanks = true;
				$template->assign_block_vars('memberrow', array(
					'ROW_NUMBER'			=> $row_number + ($start + 1),
					'RANK_TITLE'			=> $rank_title,
					'RANK_IMG'				=> $rank_img,
					'RANK_IMG_SRC'			=> $rank_img_src,
					'GIVENS'				=> (!isset($givens[$user_id])) ? 0 : $givens[$user_id], 
					'RECEIVED'				=> (!isset($reseved[$user_id])) ? 0 : $reseved[$user_id],
					'JOINED'				=> $user->format_date($row['user_regdate']),
					'VISITED'				=> (empty($last_visit)) ? ' - ' : $user->format_date($last_visit),
					'POSTS'					=> ($row['user_posts']) ? $row['user_posts'] : 0,
					'USERNAME_FULL'			=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
					'USERNAME'				=> get_username_string('username', $row['user_id'], $row['username'], $row['user_colour']),
					'USER_COLOR'			=> get_username_string('colour', $row['user_id'], $row['username'], $row['user_colour']),
					'U_VIEW_PROFILE'		=> get_username_string('profile', $row['user_id'], $row['username'], $row['user_colour']),
					'U_SEARCH_USER'			=> ($auth->acl_get('u_search')) ? append_sid("{$phpbb_root_path}search.$phpEx", "author_id=$user_id&amp;sr=posts") : '',
					'U_SEARCH_USER_GIVENS'	=> ($auth->acl_get('u_search')) ? append_sid("{$phpbb_root_path}thankslist.$phpEx", "mode=givens&amp;author_id=$user_id&amp;give=true") : '',
					'U_SEARCH_USER_RECEIVED'=> ($auth->acl_get('u_search')) ? append_sid("{$phpbb_root_path}thankslist.$phpEx", "mode=givens&amp;author_id=$user_id&amp;give=false") : '',
					'L_VIEWING_PROFILE'		=> sprintf($user->lang['VIEWING_PROFILE'], $row['username']),
					'LOCATION'				=> ($row['user_from']) ? $row['user_from'] : '',
					'U_WWW'					=> (!empty($row['user_website'])) ? $row['user_website'] : '',
					'VISITED'				=> (empty($last_visit)) ? ' - ' : $user->format_date($last_visit),
				));
				$row_number++;
			}
			while ($row = $db->sql_fetchrow($result));
			$db->sql_freeresult($result);
			// www.phpBB-SEO.com SEO TOOLKIT BEGIN
			$seo_sep = strpos($sort_url, '?') === false ? '?' : '&amp;';
			// www.phpBB-SEO.com SEO TOOLKIT END
			$template->assign_vars(array(
				'PAGE_NUMBER'			=> on_page($total_users, $config['topics_per_page'], $start),
				'PAGINATION'			=> generate_pagination($pagination_url, $total_users, $config['topics_per_page'], $start),
				'U_SORT_POSTS'			=> $sort_url . $seo_sep . 'sk=d&amp;sd=' . (($sort_key == 'd' && $sort_dir == 'a') ? 'd' : 'a'),
				'U_SORT_USERNAME'		=> $sort_url . $seo_sep . 'sk=a&amp;sd=' . (($sort_key == 'a' && $sort_dir == 'a') ? 'd' : 'a'),
				'U_SORT_FROM'			=> $sort_url . $seo_sep . 'sk=b&amp;sd=' . (($sort_key == 'b' && $sort_dir == 'a') ? 'd' : 'a'),
				'U_SORT_JOINED'			=> $sort_url . $seo_sep . 'sk=c&amp;sd=' . (($sort_key == 'c' && $sort_dir == 'a') ? 'd' : 'a'),
				'U_SORT_THANKS_R'		=> $sort_url . $seo_sep . 'sk=e&amp;sd=' . (($sort_key == 'e' && $sort_dir == 'd') ? 'a' : 'd'),
				'U_SORT_THANKS_RT'		=> $sort_url . $seo_sep . 'sk=e&amp;sd=' . (($sort_key == 'e' && $sort_dir == 'd') ? 'a' : 'd') . '&amp;top=' . $config['thanks_top_number'],
				'U_SORT_THANKS_G'		=> $sort_url . $seo_sep . 'sk=f&amp;sd=' . (($sort_key == 'f' && $sort_dir == 'd') ? 'a' : 'd'),
				'U_SORT_THANKS_GT'		=> $sort_url . $seo_sep . 'sk=f&amp;sd=' . (($sort_key == 'f' && $sort_dir == 'd') ? 'a' : 'd') . '&amp;top=' . $config['thanks_top_number'],
				'U_SORT_ACTIVE'			=> ($auth->acl_get('u_viewonline')) ? $sort_url . $seo_sep . 'sk=l&amp;sd=' . (($sort_key == 'l' && $sort_dir == 'a') ? 'd' : 'a') : '',
			));
		}
		break;
}

// Output the page
$template->assign_vars(array(
	'TOTAL_USERS'		=> ($total_users == 1) ? $user->lang['LIST_USER'] : sprintf($user->lang['LIST_USERS'], $total_users),
	'U_THANKS'			=> append_sid("{$phpbb_root_path}thankslist.$phpEx"),
	'S_THANKS'			=> $sthanks,
));

page_header($page_title);

$template->set_filenames(array(
	'body' => $template_html));
	
make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();
?>