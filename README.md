# wordpress4.unused.php [![N|Solid](http://php.net/images/logos/php-power-white.gif)](http://www.php.net/)
**wordpress4.unused.php** es un script para eliminar los archivos en desuso de instalaciones de WordPress 4.x. Los archivos que este script revisa, identifica y elimina son:
  - Archivos subidos a WordPress que no están siendo utilizados por niguna página de WordPress.
```sql
SELECT *
FROM posts
WHERE post_parent = 0 AND post_type = 'attachment'
AND NOT EXISTS ( SELECT meta_id FROM postmeta WHERE meta_value = posts.ID AND meta_key IN ('_thumbnail_id','_product_image_gallery') )
```
## Requisitos
 - WordPress 4.x
 - PHP 5.3 o superior

## Instalación
Descargar el archivo wordpress4.unused.php del repositorio de GitHub https://github.com/ramonromancastro/wordpress4.unused.
## Ejecución
Para ver los comandos disponibles, ejecutar el comando
```sh
$ php /path/to/wordpress4.unused.php -h
```
## Ejemplos
```sh
$ cd /path/to/wordpress4/installation
$ php /path/to/wordpress4.unused.php
```
