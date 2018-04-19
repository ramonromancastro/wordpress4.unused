<?php

# wordpress4.unused.php is a PHP scripts to delete orphans files in WordPress
# 4.x installations.
#
# Copyright (C) 2018 Ramon Roman Castro <ramonromancastro@gmail.com>
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.

define("RRC_VERSION","1.9");

# ---------------------------------------------------------------------------------------
# FUNCIONES
# ---------------------------------------------------------------------------------------

$progress_value=0;
$progress_total=0;
$progress_time=null;

function rrc_start_progress($total){
	global $progress_value,$progress_total,$progress_time;
	
	$progress_value=1;
	$progress_total=$total;
	$progress_time=microtime(true);
}
function rrc_print_progress(){
	global $progress_value,$progress_total,$progress_time;
	
	$time_ahora = microtime(true);
	$time_total = $time_ahora - $progress_time;
	$time_restante = ($time_total / $progress_value) * ($progress_total - $progress_value + 1);
	$time_restante = sprintf("%02d:%02d",$time_restante/60,$time_restante%60);
	
	printf("%6.2f%%  [%6d/%6d] Tiempo estimado: %s\r",($progress_value*100/$progress_total),$progress_value,$progress_total,$time_restante);
	$progress_value++;
}

function rrc_print_copyright(){
	global $rrc_options;
	echo "\nwordpress4.delete.unused.php - Calculate unused attachments\n";
	echo "Ramón Román Castro <ramon.roman.c@juntadeandalucia.es>\n";
	echo "Versión ".RRC_VERSION."\n\n";
	if (!isset($rrc_options['f'])){
		echo "Modo simulación [\033[01;92m ACTIVADO \033[0m]\n";
	}
	else{
		echo "Modo simulación [\033[01;93m DESACTIVADO \033[0m]\n";
	}
	if (isset($rrc_options['v'])){
		echo "Modo detallado  [\033[01;92m ACTIVADO \033[0m]\n\n";
	}
	else{
		echo "Modo detallado  [\033[01;90m DESACTIVADO \033[0m]\n\n";
	}
}

function rrc_print_site(){
	echo "\033[01;36m";
	echo "WordPress version : ".get_bloginfo('version')."\n";
	echo "Nombre del sitio  : ".get_bloginfo('name')."\n";
	echo "Descripción       : ".get_bloginfo('description')."\n";
	echo "Site URL          : ".get_site_url()."\n";
	echo "Admin URL         : ".get_admin_url()."\n";
	echo "\033[0m\n";
}

function rrc_print_help(){
	rrc_print_copyright();
	echo "Usage: wordpress4.delete.unused.php -f -v\n\n";
	echo "\t-f ... Desactivar modo simulación (por defecto): los cambios son definitivos\n";
	echo "\t-v ... Modo detallado\n";
	echo "\n";
	exit;
}

function rrc_verbose($text){
	global $rrc_options;
	if (isset($rrc_options['v'])) echo "[ \033[01;36mDEBUG\033[0m ] $text\n";
}

# ---------------------------------------------------------------------------------------
# Lectura de parámetros y carga de la configuración
# ---------------------------------------------------------------------------------------

$rrc_options = getopt("fv");
if (!is_array($rrc_options) ) {
	rrc_print_help();
}

# ---------------------------------------------------------------------------------------
/** Sets up the WordPress Environment. */
# ---------------------------------------------------------------------------------------
require( 'wp-load.php' );

global $wpdb;

# ---------------------------------------------------------------------------------------
# Cuerpo principal del script
# ---------------------------------------------------------------------------------------

rrc_print_copyright();
rrc_print_site();

echo "Extrayendo los attachments ({$wpdb->prefix}posts) no utilizados ...\n";

$sqlUnusedAttachments = "SELECT ID,guid FROM {$wpdb->prefix}posts WHERE post_parent = 0 AND post_type = 'attachment' AND NOT EXISTS (SELECT meta_id FROM {$wpdb->prefix}postmeta WHERE meta_value = {$wpdb->prefix}posts.ID AND meta_key IN ('_thumbnail_id','_product_image_gallery'))";
$post_ids = $wpdb->get_results($sqlUnusedAttachments);

echo "Eliminando los posts de la aplicación ...\n";

$post_total = count($post_ids);
$files_total = 0;
$post_free = 0;
$post_delete = 0;
$post_error = 0;

rrc_start_progress($post_total);
foreach ( $post_ids as $item ) {
	rrc_print_progress();
	$path = get_attached_file($item->ID);
	
	// Calculo de espacio en disco
	$files_total++;
	if (file_exists($path)){
		$file = stat($path);
		$post_free += $file['size'];
	}
	rrc_verbose($path);
	$image_meta = wp_get_attachment_metadata( $item->ID );
	if (isset($image_meta['sizes'])){
		foreach($image_meta['sizes'] as $value){
			$files_total++;
			$dirname = dirname($path);
			if (file_exists($dirname."/".$value['file'])){
				$file = stat($dirname."/".$value['file']);
				$post_free += $file['size'];
			}
			rrc_verbose("\t".$dirname."/".$value['file']);
		}
	}
	
	// Eliminacion de los registros
	if (isset($rrc_options['f'])) $result = wp_delete_attachment($item->ID,true); else $result = FALSE;
	if ($result !== false) {
		$post_delete++;
	}
	else{
		if (isset($rrc_options['f'])) echo "[ \033[01;31mERROR\033[0m ] $path\n";
		$post_error++;
	}
}

echo "\n\nBASE DE DATOS\n";
echo "{$wpdb->prefix}posts::Huérfanos/Procesados/Errores : \033[01;33m".$post_total."\033[0m / \033[01;32m".$post_delete." / \033[01;31m".$post_error."\033[0m\n";
echo "\nSISTEMA DE ARCHIVOS\n";
echo "Archivos asociados {$wpdb->prefix}posts::Huérfanos: \033[01;33m".$files_total." [".intval($post_free/1024/1024)." MB]\033[0m\n";
echo "\n"
?>