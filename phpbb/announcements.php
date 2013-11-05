<?php

define('IN_PHPBB', true);
$_phpbb_root_path = str_replace('announcements.php','',__FILE__);
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include_once($_phpbb_root_path . 'common.' . $phpEx);
include_once($_phpbb_root_path . 'includes/bbcode.' . $phpEx);
include_once($_phpbb_root_path . 'includes/functions_content.' . $phpEx);
include_once($_phpbb_root_path . 'includes/functions_convert.' . $phpEx);

if(!defined('SMILIES_PATH')) : define('SMILIES_PATH','images/');
endif;

$sql = "SELECT post_text, bbcode_uid, bbcode_bitfield FROM phpbb_posts WHERE topic_id = '15238' AND post_deleted = '0' ORDER BY post_id DESC LIMIT 0,1";
$post = mysql_fetch_assoc(mysql_query($sql));
$message = $post['post_text'];

$bbcode_bitfield = base64_decode($row['bbcode_bitfield']);
$bbcode = new bbcode(base64_encode($bbcode_bitfield));
$bbcode->bbcode_second_pass($message, $post['bbcode_uid'], $post['bbcode_bitfield']);

$message = convert_bbcode($message);
$message = censor_text($message);
$message = bbcode_nl2br($message);
$message = smiley_text($message);

echo $message;
?>