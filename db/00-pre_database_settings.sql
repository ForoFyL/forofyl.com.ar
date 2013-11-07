/* Creating the developers user */
CREATE USER 'forofyl_admin'@'localhost' IDENTIFIED BY 'forofyl';

/* It's also created for remote access */
CREATE USER 'forofyl_admin'@'%' IDENTIFIED BY 'forofyl';

