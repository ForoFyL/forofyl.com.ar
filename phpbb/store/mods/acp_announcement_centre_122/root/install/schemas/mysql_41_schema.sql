#
# $Id: mysql_41_schema.sql 143 2008-12-06 20:50:08Z lefty74 $
#

# Table: 'phpbb_announcement_centre'
CREATE TABLE phpbb_announcement_centre (
	announcement_forum_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	announcement_topic_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	announcement_post_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	announcement_gopost tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
	announcement_first_last_post varchar(4) DEFAULT '0' NOT NULL,
	announcement_draft text NOT NULL,
	announcement_draft_bbcode_uid varchar(8) DEFAULT '' NOT NULL,
	announcement_draft_bbcode_bitfield varchar(255) DEFAULT '' NOT NULL,
	announcement_draft_bbcode_options int(11) UNSIGNED DEFAULT '7' NOT NULL,
	announcement_text text NOT NULL,
	announcement_text_bbcode_uid varchar(8) DEFAULT '' NOT NULL,
	announcement_text_bbcode_bitfield varchar(255) DEFAULT '' NOT NULL,
	announcement_text_bbcode_options int(11) UNSIGNED DEFAULT '7' NOT NULL,
	announcement_text_guests text NOT NULL,
	announcement_text_guests_bbcode_uid varchar(8) DEFAULT '' NOT NULL,
	announcement_text_guests_bbcode_bitfield varchar(255) DEFAULT '' NOT NULL,
	announcement_text_guests_bbcode_options int(11) UNSIGNED DEFAULT '7' NOT NULL,
	announcement_title varchar(255) DEFAULT '' NOT NULL,
	announcement_title_guests varchar(255) DEFAULT '' NOT NULL
) CHARACTER SET `utf8` COLLATE `utf8_bin`;


