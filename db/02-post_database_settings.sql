-- Grant all privileges on databases to devs user.

GRANT ALL ON forofyl_phpbb.* TO 'forofyl_admin'@'localhost' WITH GRANT OPTION;
GRANT ALL ON forofyl_phpbb TO 'forofyl_admin'@'localhost';
GRANT CREATE ON forofyl_phpbb TO 'forofyl_admin'@'localhost';
FLUSH PRIVILEGES;

GRANT ALL ON forofyl_phpbb.* TO 'forofyl_admin'@'%' WITH GRANT OPTION;
GRANT ALL ON forofyl_phpbb TO 'forofyl_admin'@'%';
GRANT CREATE ON forofyl_phpbb TO 'forofyl_admin'@'%';
FLUSH PRIVILEGES;