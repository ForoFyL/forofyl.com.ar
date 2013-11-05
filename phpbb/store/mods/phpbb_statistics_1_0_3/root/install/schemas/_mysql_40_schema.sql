#
# $Id: _mysql_40_schema.sql 119 2010-04-18 14:37:22Z marc1706 $
#

CREATE TABLE phpbb_stats_addons (
	addon_classname varchar(255) DEFAULT '' NOT NULL,
	addon_enabled tinyint DEFAULT '0' NOT NULL,
	addon_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (addon_classname)
);

CREATE TABLE phpbb_stats_config (
	config_name varchar(255) DEFAULT '' NOT NULL,
	config_value mediumtext NOT NULL,
	PRIMARY KEY (config_name)
);

CREATE TABLE phpbb_stats_smilies (
	smiley_url varchar(255) DEFAULT '' NOT NULL,
	smiley_count mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (smiley_url)
);

CREATE TABLE phpbb_stats_bbcodes (
	bbcode varchar(255) DEFAULT '' NOT NULL,
	bbcode_count mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (bbcode)
);