/*

 $Id: oracle_schema.sql 143 2008-12-06 20:50:08Z lefty74 $

*/

/*
  This first section is optional, however its probably the best method
  of running phpBB on Oracle. If you already have a tablespace and user created
  for phpBB you can leave this section commented out!

  The first set of statements create a phpBB tablespace and a phpBB user,
  make sure you change the password of the phpBB user before you run this script!!
*/

/*
CREATE TABLESPACE "PHPBB"
	LOGGING
	DATAFILE 'E:\ORACLE\ORADATA\LOCAL\PHPBB.ora'
	SIZE 10M
	AUTOEXTEND ON NEXT 10M
	MAXSIZE 100M;

CREATE USER "PHPBB"
	PROFILE "DEFAULT"
	IDENTIFIED BY "phpbb_password"
	DEFAULT TABLESPACE "PHPBB"
	QUOTA UNLIMITED ON "PHPBB"
	ACCOUNT UNLOCK;

GRANT ANALYZE ANY TO "PHPBB";
GRANT CREATE SEQUENCE TO "PHPBB";
GRANT CREATE SESSION TO "PHPBB";
GRANT CREATE TABLE TO "PHPBB";
GRANT CREATE TRIGGER TO "PHPBB";
GRANT CREATE VIEW TO "PHPBB";
GRANT "CONNECT" TO "PHPBB";

COMMIT;
DISCONNECT;

CONNECT phpbb/phpbb_password;
*/
/*
	Table: 'phpbb_announcement_centre'
*/
CREATE TABLE phpbb_announcement_centre (
	announcement_forum_id number(8) DEFAULT '0' NOT NULL,
	announcement_topic_id number(8) DEFAULT '0' NOT NULL,
	announcement_post_id number(8) DEFAULT '0' NOT NULL,
	announcement_gopost number(1) DEFAULT '0' NOT NULL,
	announcement_first_last_post varchar2(4) DEFAULT '0' NOT NULL,
	announcement_draft clob DEFAULT '' ,
	announcement_draft_bbcode_uid varchar2(8) DEFAULT '' ,
	announcement_draft_bbcode_bitfield varchar2(255) DEFAULT '' ,
	announcement_draft_bbcode_options number(11) DEFAULT '7' NOT NULL,
	announcement_text clob DEFAULT '' ,
	announcement_text_bbcode_uid varchar2(8) DEFAULT '' ,
	announcement_text_bbcode_bitfield varchar2(255) DEFAULT '' ,
	announcement_text_bbcode_options number(11) DEFAULT '7' NOT NULL,
	announcement_text_guests clob DEFAULT '' ,
	announcement_text_guests_bbcode_uid varchar2(8) DEFAULT '' ,
	announcement_text_guests_bbcode_bitfield varchar2(255) DEFAULT '' ,
	announcement_text_guests_bbcode_options number(11) DEFAULT '7' NOT NULL,
	announcement_title varchar2(255) DEFAULT '' ,
	announcement_title_guests varchar2(255) DEFAULT '' 
)
/


