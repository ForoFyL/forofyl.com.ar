/*

 $Id: _oracle_schema.sql 119 2010-04-18 14:37:22Z marc1706 $

*/


/*
	Table: 'phpbb_stats_addons'
*/
CREATE TABLE phpbb_stats_addons (
	addon_classname varchar2(255) DEFAULT '' ,
	addon_enabled number(5) DEFAULT 0 ,
	addon_id number(8) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_stats_addons PRIMARY KEY (addon_classname)
)
/

/*
	Table: 'phpbb_stats_config'
*/
CREATE TABLE phpbb_stats_config (
	config_name varchar2(255) DEFAULT '' ,
	config_value clob DEFAULT '' ,
	CONSTRAINT pk_phpbb_stats_config PRIMARY KEY (config_name)
)
/

/*
	Table: 'phpbb_stats_smilies'
*/
CREATE TABLE phpbb_stats_smilies (
	smiley_url varchar2(255) DEFAULT '' ,
	smiley_count number(8) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_stats_smilies PRIMARY KEY (smiley_url)
)
/

/*
	Table: 'phpbb_stats_bbcodes'
*/
CREATE TABLE phpbb_stats_bbcodes (
	bbcode varchar2(255) DEFAULT '' ,
	bbcode_count number(8) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_stats_bbcodes PRIMARY KEY (bbcode)
)
/
