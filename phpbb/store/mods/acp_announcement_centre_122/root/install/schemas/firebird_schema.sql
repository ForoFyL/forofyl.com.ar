#
# $Id: firebird_schema.sql 143 2008-12-06 20:50:08Z lefty74 $
#


# Table: 'phpbb_announcement_centre'
CREATE TABLE phpbb_announcement_centre (
	announcement_forum_id INTEGER DEFAULT 0 NOT NULL,
	announcement_topic_id INTEGER DEFAULT 0 NOT NULL,
	announcement_post_id INTEGER DEFAULT 0 NOT NULL,
	announcement_gopost INTEGER DEFAULT 0 NOT NULL,
	announcement_first_last_post VARCHAR(4) CHARACTER SET NONE DEFAULT 0 NOT NULL,
	announcement_draft BLOB SUB_TYPE TEXT CHARACTER SET UTF8 DEFAULT '' NOT NULL,
	announcement_draft_bbcode_uid VARCHAR(8) CHARACTER SET NONE DEFAULT '' NOT NULL,
	announcement_draft_bbcode_bitfield VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	announcement_draft_bbcode_options INTEGER DEFAULT 7 NOT NULL,
	announcement_text BLOB SUB_TYPE TEXT CHARACTER SET UTF8 DEFAULT '' NOT NULL,
	announcement_text_bbcode_uid VARCHAR(8) CHARACTER SET NONE DEFAULT '' NOT NULL,
	announcement_text_bbcode_bitfield VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	announcement_text_bbcode_options INTEGER DEFAULT 7 NOT NULL,
	announcement_text_guests BLOB SUB_TYPE TEXT CHARACTER SET UTF8 DEFAULT '' NOT NULL,
	announcement_text_guests_bbcode_uid VARCHAR(8) CHARACTER SET NONE DEFAULT '' NOT NULL,
	announcement_text_guests_bbcode_bitfield VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	announcement_text_guests_bbcode_options INTEGER DEFAULT 7 NOT NULL,
	announcement_title VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	announcement_title_guests VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL
);;


