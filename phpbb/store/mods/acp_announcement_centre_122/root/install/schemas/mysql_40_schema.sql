#
# $Id: mysql_40_schema.sql 143 2008-12-06 20:50:08Z lefty74 $
#

# Table: 'phpbb_announcement_centre'
CREATE TABLE phpbb_announcement_centre (
	announcement_forum_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	announcement_topic_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	announcement_post_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	announcement_gopost tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
	announcement_first_last_post varbinary(4) DEFAULT '0' NOT NULL,
	announcement_draft blob NOT NULL,
	announcement_draft_bbcode_uid varbinary(8) DEFAULT '' NOT NULL,
	announcement_draft_bbcode_bitfield varbinary(255) DEFAULT '' NOT NULL,
	announcement_draft_bbcode_options int(11) UNSIGNED DEFAULT '7' NOT NULL,
	announcement_text blob NOT NULL,
	announcement_text_bbcode_uid varbinary(8) DEFAULT '' NOT NULL,
	announcement_text_bbcode_bitfield varbinary(255) DEFAULT '' NOT NULL,
	announcement_text_bbcode_options int(11) UNSIGNED DEFAULT '7' NOT NULL,
	announcement_text_guests blob NOT NULL,
	announcement_text_guests_bbcode_uid varbinary(8) DEFAULT '' NOT NULL,
	announcement_text_guests_bbcode_bitfield varbinary(255) DEFAULT '' NOT NULL,
	announcement_text_guests_bbcode_options int(11) UNSIGNED DEFAULT '7' NOT NULL,
	announcement_title varbinary(255) DEFAULT '' NOT NULL,
	announcement_title_guests varbinary(255) DEFAULT '' NOT NULL
);


