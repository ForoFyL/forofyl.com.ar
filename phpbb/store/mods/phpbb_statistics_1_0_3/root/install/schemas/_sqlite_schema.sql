#
# $Id: _sqlite_schema.sql 122 2010-04-24 21:02:07Z marc1706 $
#

BEGIN TRANSACTION;

# Table: 'phpbb_stats_addons'
CREATE TABLE phpbb_stats_addons (
	addon_classname varchar(255) NOT NULL DEFAULT '',
	addon_enabled tinyint(1) NOT NULL DEFAULT 0,
	addon_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (addon_classname)
);

# Table: 'phpbb_stats_config'
CREATE TABLE phpbb_stats_config (
	config_name varchar(255) NOT NULL DEFAULT '',
	config_value mediumtext(16777215) NOT NULL DEFAULT '',
	PRIMARY KEY (config_name)
);

# Table: 'phpbb_stats_smilies'
CREATE TABLE phpbb_stats_smilies (
	smiley_url varchar(255) NOT NULL DEFAULT '',
	smiley_count INTEGER UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (smiley_url)
);

# Table: 'phpbb_stats_bbcodes'
CREATE TABLE phpbb_stats_bbcodes (
	bbcode varchar(255) NOT NULL DEFAULT '',
	bbcode_count INTEGER UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (bbcode)
);


COMMIT;