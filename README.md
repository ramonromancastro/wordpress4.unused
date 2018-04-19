# wordpress-scripts
Scripts para realizar tareas de mantenimiento de WordPress

## wordpress4.unused.php

[![N|Solid](http://php.net/images/logos/php-power-white.gif)](http://www.php.net/)

**wordpress4.unused.php** es un script para eliminar los archivos en desuso de instalaciones de WordPress 4.x. Los archivos que este script revisa, identifica y elimina son:

  - Archvos subidos A WordPress que no están siendo utilizados por niguna página de WordPress.

### Requisitos
 - WordPress 4.x
 - PHP 5.3 o superior

### Instalación
```sh
$ wget https://raw.githubusercontent.com/ramonromancastro/wordpress-scripts/master/wordpress4.unused.php
```
### Ejecución
Para ver los comandos disponibles, ejecutar el comando
```sh
$ php /path/to/wordpress4.unused.php -h
```
### Ejemplos
```sh
$ cd /path/to/wordpress4/installation
$ php /path/to/wordpress4.unused.php
```
