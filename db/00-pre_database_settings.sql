/**
 * This file is intended to run specific tasks BEFORE main database creation.
 */

-- Adjust to the values you need --
SELECT @db_user := 'forofyl_admin', @db_password := 'forofyl';

/* Creating the developers user */
SET @create_for_local = CONCAT( 'CREATE USER "' , @db_user , '"@"localhost" IDENTIFIED BY "' , @db_password , '" ');
PREPARE stmt FROM @create_for_local; EXECUTE stmt; DEALLOCATE PREPARE stmt;

/* It's also created for remote access */
SET @create_for_remote = CONCAT( 'CREATE USER "' , @db_user , '"@"%" IDENTIFIED BY "' , @db_password , '" ');
PREPARE stmt FROM @create_for_remote; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Don't forget to run 01-forofyl_phpbb.sql now, and then 02-post_database_settings.sql! --