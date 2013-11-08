/* 
 * Don't run this file if you didn't run 00-pre_database_settings.sql before, 
 * or you don't have a user called 'forofyl_admin'.
 */

-- Grant all privileges on databases to devs user.

GRANT ALL ON forofyl_phpbb.* TO 'forofyl_admin'@'localhost' WITH GRANT OPTION;
GRANT ALL ON forofyl_phpbb TO 'forofyl_admin'@'localhost';
GRANT CREATE ON forofyl_phpbb TO 'forofyl_admin'@'localhost';
FLUSH PRIVILEGES;

GRANT ALL ON forofyl_phpbb.* TO 'forofyl_admin'@'%' WITH GRANT OPTION;
GRANT ALL ON forofyl_phpbb TO 'forofyl_admin'@'%';
GRANT CREATE ON forofyl_phpbb TO 'forofyl_admin'@'%';
FLUSH PRIVILEGES;