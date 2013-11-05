/*

 $Id: postgres_schema.sql 143 2008-12-06 20:50:08Z lefty74 $

*/

BEGIN;

/*
	Domain definition
*/
CREATE DOMAIN varchar_ci AS varchar(255) NOT NULL DEFAULT ''::character varying;

/*
	Operation Functions
*/
CREATE FUNCTION _varchar_ci_equal(varchar_ci, varchar_ci) RETURNS boolean AS 'SELECT LOWER($1) = LOWER($2)' LANGUAGE SQL STRICT;
CREATE FUNCTION _varchar_ci_not_equal(varchar_ci, varchar_ci) RETURNS boolean AS 'SELECT LOWER($1) != LOWER($2)' LANGUAGE SQL STRICT;
CREATE FUNCTION _varchar_ci_less_than(varchar_ci, varchar_ci) RETURNS boolean AS 'SELECT LOWER($1) < LOWER($2)' LANGUAGE SQL STRICT;
CREATE FUNCTION _varchar_ci_less_equal(varchar_ci, varchar_ci) RETURNS boolean AS 'SELECT LOWER($1) <= LOWER($2)' LANGUAGE SQL STRICT;
CREATE FUNCTION _varchar_ci_greater_than(varchar_ci, varchar_ci) RETURNS boolean AS 'SELECT LOWER($1) > LOWER($2)' LANGUAGE SQL STRICT;
CREATE FUNCTION _varchar_ci_greater_equals(varchar_ci, varchar_ci) RETURNS boolean AS 'SELECT LOWER($1) >= LOWER($2)' LANGUAGE SQL STRICT;

/*
	Operators
*/
CREATE OPERATOR <(
  PROCEDURE = _varchar_ci_less_than,
  LEFTARG = varchar_ci,
  RIGHTARG = varchar_ci,
  COMMUTATOR = >,
  NEGATOR = >=,
  RESTRICT = scalarltsel,
  JOIN = scalarltjoinsel);

CREATE OPERATOR <=(
  PROCEDURE = _varchar_ci_less_equal,
  LEFTARG = varchar_ci,
  RIGHTARG = varchar_ci,
  COMMUTATOR = >=,
  NEGATOR = >,
  RESTRICT = scalarltsel,
  JOIN = scalarltjoinsel);

CREATE OPERATOR >(
  PROCEDURE = _varchar_ci_greater_than,
  LEFTARG = varchar_ci,
  RIGHTARG = varchar_ci,
  COMMUTATOR = <,
  NEGATOR = <=,
  RESTRICT = scalargtsel,
  JOIN = scalargtjoinsel);

CREATE OPERATOR >=(
  PROCEDURE = _varchar_ci_greater_equals,
  LEFTARG = varchar_ci,
  RIGHTARG = varchar_ci,
  COMMUTATOR = <=,
  NEGATOR = <,
  RESTRICT = scalargtsel,
  JOIN = scalargtjoinsel);

CREATE OPERATOR <>(
  PROCEDURE = _varchar_ci_not_equal,
  LEFTARG = varchar_ci,
  RIGHTARG = varchar_ci,
  COMMUTATOR = <>,
  NEGATOR = =,
  RESTRICT = neqsel,
  JOIN = neqjoinsel);

CREATE OPERATOR =(
  PROCEDURE = _varchar_ci_equal,
  LEFTARG = varchar_ci,
  RIGHTARG = varchar_ci,
  COMMUTATOR = =,
  NEGATOR = <>,
  RESTRICT = eqsel,
  JOIN = eqjoinsel,
  HASHES,
  MERGES,
  SORT1= <);

/*
	Table: 'phpbb_announcement_centre'
*/
CREATE TABLE phpbb_announcement_centre (
	announcement_forum_id INT4 DEFAULT '0' NOT NULL CHECK (announcement_forum_id >= 0),
	announcement_topic_id INT4 DEFAULT '0' NOT NULL CHECK (announcement_topic_id >= 0),
	announcement_post_id INT4 DEFAULT '0' NOT NULL CHECK (announcement_post_id >= 0),
	announcement_gopost INT2 DEFAULT '0' NOT NULL CHECK (announcement_gopost >= 0),
	announcement_first_last_post varchar(4) DEFAULT '0' NOT NULL,
	announcement_draft varchar(4000) DEFAULT '' NOT NULL,
	announcement_draft_bbcode_uid varchar(8) DEFAULT '' NOT NULL,
	announcement_draft_bbcode_bitfield varchar(255) DEFAULT '' NOT NULL,
	announcement_draft_bbcode_options INT4 DEFAULT '7' NOT NULL CHECK (announcement_draft_bbcode_options >= 0),
	announcement_text varchar(4000) DEFAULT '' NOT NULL,
	announcement_text_bbcode_uid varchar(8) DEFAULT '' NOT NULL,
	announcement_text_bbcode_bitfield varchar(255) DEFAULT '' NOT NULL,
	announcement_text_bbcode_options INT4 DEFAULT '7' NOT NULL CHECK (announcement_text_bbcode_options >= 0),
	announcement_text_guests varchar(4000) DEFAULT '' NOT NULL,
	announcement_text_guests_bbcode_uid varchar(8) DEFAULT '' NOT NULL,
	announcement_text_guests_bbcode_bitfield varchar(255) DEFAULT '' NOT NULL,
	announcement_text_guests_bbcode_options INT4 DEFAULT '7' NOT NULL CHECK (announcement_text_guests_bbcode_options >= 0),
	announcement_title varchar(255) DEFAULT '' NOT NULL,
	announcement_title_guests varchar(255) DEFAULT '' NOT NULL
);



COMMIT;