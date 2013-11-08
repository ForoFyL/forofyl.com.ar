/* Creating the developers user */
CREATE USER 'forofyl_admin'@'localhost' IDENTIFIED BY 'forofyl';

/* It's also created for remote access */
CREATE USER 'forofyl_admin'@'%' IDENTIFIED BY 'forofyl';

-- Don't forget to run 01-forofyl_phpbb.sql now, and then 02-post_database_settings.sql! --