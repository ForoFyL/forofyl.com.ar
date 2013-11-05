/*

 $Id: _mssql_schema.sql 119 2010-04-18 14:37:22Z marc1706 $

*/

BEGIN TRANSACTION
GO

/*
	Table: 'phpbb_stats_addons'
*/
CREATE TABLE [phpbb_stats_addons] (
	[addon_classname] [varchar] (255) DEFAULT ('') NOT NULL ,
	[addon_enabled] [tinyint] DEFAULT (0) NOT NULL ,
	[addon_id] [int] DEFAULT (0) NOT NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

ALTER TABLE [phpbb_stats_addons] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_stats_addons] PRIMARY KEY  CLUSTERED 
	(
		[addon_classname]
	)  ON [PRIMARY] 
GO

/*
	Table: 'phpbb_stats_config'
*/
CREATE TABLE [phpbb_stats_config] (
	[config_name] [varchar] (255) DEFAULT ('') NOT NULL ,
	[config_value] [text] DEFAULT ('') NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

ALTER TABLE [phpbb_stats_config] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_stats_config] PRIMARY KEY  CLUSTERED 
	(
		[config_name]
	)  ON [PRIMARY] 
GO

/*
	Table: 'phpbb_stats_smilies'
*/
CREATE TABLE [phpbb_stats_smilies] (
	[smiley_url] [varchar] (255) DEFAULT ('') NOT NULL ,
	[smiley_count] [int] DEFAULT (0) NOT NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

ALTER TABLE [phpbb_stats_smilies] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_stats_smilies] PRIMARY KEY  CLUSTERED 
	(
		[smiley_url]
	)  ON [PRIMARY] 
GO

/*
	Table: 'phpbb_stats_bbcodes'
*/
CREATE TABLE [phpbb_stats_bbcodes] (
	[bbcode] [varchar] (255) DEFAULT ('') NOT NULL ,
	[bbcode_count] [int] DEFAULT (0) NOT NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

ALTER TABLE [phpbb_stats_bbcodes] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_stats_bbcodes] PRIMARY KEY  CLUSTERED 
	(
		[bbcode]
	)  ON [PRIMARY] 
GO



COMMIT
GO