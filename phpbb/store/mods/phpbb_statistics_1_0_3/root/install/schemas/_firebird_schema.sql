#
# $Id: _firebird_schema.sql 122 2010-04-24 21:02:07Z marc1706 $
#


# Table: 'phpbb_stats_addons'
CREATE TABLE phpbb_stats_addons (
	addon_classname VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	addon_enabled SMALLINT CHARACTER SET UTF8 DEFAULT '' NOT NULL,
	addon_id INTEGER DEFAULT 0 NOT NULL,
);;

ALTER TABLE phpbb_stats_config ADD PRIMARY KEY (addon_classname);;

# Table: 'phpbb_stats_config'
CREATE TABLE phpbb_stats_config (
	config_name VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	config_value BLOB SUB_TYPE TEXT CHARACTER SET UTF8 DEFAULT '' NOT NULL,
);;

ALTER TABLE phpbb_stats_config ADD PRIMARY KEY (config_name);;

# Table: 'phpbb_stats_smilies'
CREATE TABLE phpbb_stats_smilies (
	smiley_url VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	smiley_count INTEGER DEFAULT 0 NOT NULL,
);;

ALTER TABLE phpbb_stats_smilies ADD PRIMARY KEY (smiley_url);;

# Table: 'phpbb_stats_bbcodes'
CREATE TABLE phpbb_stats_bbcodes (
	bbcode VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	bbcode_count INTEGER DEFAULT 0 NOT NULL,
);;

ALTER TABLE phpbb_stats_bbcodes ADD PRIMARY KEY (bbcode);;
