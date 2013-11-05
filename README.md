ForoFyL
=======

[ForoFyL](http://www.forofyl.com.ar/) es un foro basado en phpBB y destinado a estudiantes de la Facultad de Filosofía y Letras de la Universidad de Buenos Aires (aunque tenemos unos cuantos agradables intrusos).

A través de este repositorio podés colaborar con el desarrollo del foro, ayundándonos a mejorarlo y a resolver eventuales bugs.

### ¿Cuáles son los requisitos básicos?

1. Conocimiento de [PHP](http://php.net/) y [MySQL](http://www.mysql.com/).
2. Manejo de [phpBB](https://www.phpbb.com/).
3. Manejo de [Git/Github](http://try.github.io/levels/1/challenges/1).
4. Un servidor web local como [Apache](http://httpd.apache.org/) o [NGINX](http://nginx.org/). Usamos NGINX en producción, por lo cual sería ideal que tengas instalado lo mismo, pero un Apache corriendo sobre [XAMPP](http://www.apachefriends.org/en/xampp.html) basta y sobra. De hecho, generalmente es la opción más fácil de instalar.

### ¿Cómo empezar?

1. Solicitá una base de datos de prueba a [admin@forofyl.com.ar](mailto:admin@forofyl.com.ar), y cuando la tengas importala desde tu instalación de MySQL.
2. Cloná este repositorio a tu máquina.
3. Copiá el archivo `local-config-example.php` y renombralo como `local-config.php`.
4. Ajustá las variables del archivo `local-config.php` según la configuración de tu base de datos local.
4. Accedé por medio de cualquier browser a la URL correspondiente a tu instalación local.

### Cosas a tener en cuenta

* La ruta `/phpbb/images/avatars` consiste en un link simbólico a la carpeta `/shared/content/uploads/images/avatars`, la cual está fuera de la instalación de phpBB e incluida en `.gitignore` para evitar que los avatares que se vayan cargando se suban al repositorio.
* Las discusiones acerca de issues y la documentación que vaya surgiendo van a estar escritas en castellano, pero a la hora de documentar en el código, a fin de mantener la coherencia con el desarrollo original, vamos a escribir en inglés. Si se encuentra algo escrito en castellano correspondiente a código antiguo, debería ser traducido.
* La base de datos no puede descargarse del repositorio actualmente, ya que todavía no pudimos asegurarnos de tener una versión sin datos sensibles. Cuando hayamos podido hacerlo, vamos a incluirla acá.

### ¿Cómo enviar mi código?

Simplemente creá un fork de este proyecto, commiteá tu propio branch y hacé un pull request. Una vez hecho eso, revisamos tu código, y si está todo bien lo integramos y lo ponemos en producción.

Si no sabés programar y querés reportar un issue, eso también es más que bienvenido.
