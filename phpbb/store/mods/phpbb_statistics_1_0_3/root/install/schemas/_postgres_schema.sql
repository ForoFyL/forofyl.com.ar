/*

 $Id: _postgres_schema.sql 123 2010-04-24 21:04:53Z marc1706 $

*/

BEGIN;


/*
	Table: 'phpbb_stats_addons'
*/
CREATE TABLE phpbb_stats_addons (
	addon_classname varchar(255) DEFAULT '' NOT NULL,
	addon_enabled smallint DEFAULT 0 NOT NULL,
	addon_id INT4 DEFAULT 0 NOT NULL,
	PRIMARY KEY (addon_classname)
);

/*
	Table: 'phpbb_stats_config'
*/
CREATE TABLE phpbb_stats_config (
	config_name varchar(255) DEFAULT '' NOT NULL,
	config_value TEXT DEFAULT '' NOT NULL,
	PRIMARY KEY (config_name)
);

/*
	Table: 'phpbb_stats_smilies'
*/
CREATE TABLE phpbb_stats_smilies (
	smiley_url varchar(255) DEFAULT '' NOT NULL,
	smiley_count INT4 DEFAULT '0' NOT NULL,
	PRIMARY KEY (smiley_url)
);

/*
	Table: 'phpbb_stats_bbcodes'
*/
CREATE TABLE phpbb_stats_bbcodes (
	bbcode varchar(255) DEFAULT '' NOT NULL,
	bbcode_count INT4 DEFAULT '0' NOT NULL,
	PRIMARY KEY (bbcode)
);


COMMIT;