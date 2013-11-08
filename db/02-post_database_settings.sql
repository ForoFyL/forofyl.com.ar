/** 
 * This file is intended to run specific tasks AFTER main database creation.
 * Don't run this file if you didn't run 00-pre_database_settings.sql before, 
 * or you don't have a user called 'forofyl_admin'.
 */

/**
 * Adjust to the values you need.
 * Make sure @db_user is the same as in 00-pre_database_settings.sql.
 */
SELECT @db_user := 'forofyl_admin';

-- Grant all privileges on databases to devs user.

USE `forofyl_phpbb`;

SET @grant_local_option = CONCAT( 'GRANT ALL ON `forofyl_phpbb`.* TO \'' , @db_user , '\'@\'localhost\' WITH GRANT OPTION' );
PREPARE stmt FROM @grant_local_option; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @grant_local = CONCAT( 'GRANT ALL ON `forofyl_phpbb` TO \'' , @db_user , '\'@\'localhost\'' );
PREPARE stmt FROM @grant_local; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @grant_local_create = CONCAT( 'GRANT CREATE ON `forofyl_phpbb` TO \'' , @db_user , '\'@\'localhost\'' );
PREPARE stmt FROM @grant_local_create; EXECUTE stmt; DEALLOCATE PREPARE stmt;
FLUSH PRIVILEGES;

SET @grant_remote_option = CONCAT( 'GRANT ALL ON `forofyl_phpbb`.* TO \'' , @db_user , '\'@\'%\' WITH GRANT OPTION' );
PREPARE stmt FROM @grant_remote_option; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @grant_remote = CONCAT( 'GRANT ALL ON `forofyl_phpbb` TO \'' , @db_user , '\'@\'%\'' );
PREPARE stmt FROM @grant_remote; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @grant_remote_create = CONCAT( 'GRANT CREATE ON `forofyl_phpbb` TO \'' , @db_user , '\'@\'%\'' );
PREPARE stmt FROM @grant_remote_create; EXECUTE stmt; DEALLOCATE PREPARE stmt;
FLUSH PRIVILEGES;