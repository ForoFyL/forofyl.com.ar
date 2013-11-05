/*

 $Id: mssql_schema.sql 143 2008-12-06 20:50:08Z lefty74 $

*/

BEGIN TRANSACTION
GO

/*
	Table: 'phpbb_announcement_centre'
*/
CREATE TABLE [phpbb_announcement_centre] (
	[announcement_forum_id] [int] DEFAULT (0) NOT NULL ,
	[announcement_topic_id] [int] DEFAULT (0) NOT NULL ,
	[announcement_post_id] [int] DEFAULT (0) NOT NULL ,
	[announcement_gopost] [int] DEFAULT (0) NOT NULL ,
	[announcement_first_last_post] [varchar] (4) DEFAULT (0) NOT NULL ,
	[announcement_draft] [varchar] (4000) DEFAULT ('') NOT NULL ,
	[announcement_draft_bbcode_uid] [varchar] (8) DEFAULT ('') NOT NULL ,
	[announcement_draft_bbcode_bitfield] [varchar] (255) DEFAULT ('') NOT NULL ,
	[announcement_draft_bbcode_options] [int] DEFAULT (7) NOT NULL ,
	[announcement_text] [varchar] (4000) DEFAULT ('') NOT NULL ,
	[announcement_text_bbcode_uid] [varchar] (8) DEFAULT ('') NOT NULL ,
	[announcement_text_bbcode_bitfield] [varchar] (255) DEFAULT ('') NOT NULL ,
	[announcement_text_bbcode_options] [int] DEFAULT (7) NOT NULL ,
	[announcement_text_guests] [varchar] (4000) DEFAULT ('') NOT NULL ,
	[announcement_text_guests_bbcode_uid] [varchar] (8) DEFAULT ('') NOT NULL ,
	[announcement_text_guests_bbcode_bitfield] [varchar] (255) DEFAULT ('') NOT NULL ,
	[announcement_text_guests_bbcode_options] [int] DEFAULT (7) NOT NULL ,
	[announcement_title] [varchar] (255) DEFAULT ('') NOT NULL ,
	[announcement_title_guests] [varchar] (255) DEFAULT ('') NOT NULL 
) ON [PRIMARY]
GO



COMMIT
GO

