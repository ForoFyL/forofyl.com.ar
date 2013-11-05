#
# $Id: sqlite_schema.sql 143 2008-12-06 20:50:08Z lefty74 $
#

BEGIN TRANSACTION;

# Table: 'phpbb_announcement_centre'
CREATE TABLE phpbb_announcement_centre (
	announcement_forum_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	announcement_topic_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	announcement_post_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	announcement_gopost INTEGER UNSIGNED NOT NULL DEFAULT '0',
	announcement_first_last_post varchar(4) NOT NULL DEFAULT '0',
	announcement_draft text(65535) NOT NULL DEFAULT '',
	announcement_draft_bbcode_uid varchar(8) NOT NULL DEFAULT '',
	announcement_draft_bbcode_bitfield varchar(255) NOT NULL DEFAULT '',
	announcement_draft_bbcode_options INTEGER UNSIGNED NOT NULL DEFAULT '7',
	announcement_text text(65535) NOT NULL DEFAULT '',
	announcement_text_bbcode_uid varchar(8) NOT NULL DEFAULT '',
	announcement_text_bbcode_bitfield varchar(255) NOT NULL DEFAULT '',
	announcement_text_bbcode_options INTEGER UNSIGNED NOT NULL DEFAULT '7',
	announcement_text_guests text(65535) NOT NULL DEFAULT '',
	announcement_text_guests_bbcode_uid varchar(8) NOT NULL DEFAULT '',
	announcement_text_guests_bbcode_bitfield varchar(255) NOT NULL DEFAULT '',
	announcement_text_guests_bbcode_options INTEGER UNSIGNED NOT NULL DEFAULT '7',
	announcement_title varchar(255) NOT NULL DEFAULT '',
	announcement_title_guests varchar(255) NOT NULL DEFAULT ''
);



COMMIT;