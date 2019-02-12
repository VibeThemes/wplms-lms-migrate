<?php
/*
Plugin Name:WPLMS LMS Migration
Plugin URI: http://www.Vibethemes.com
Description: WMigrate courses from LMS theme to WPLMS
Version: 1.0
Author: VibeThemes
Author URI: http://www.vibethemes.com
Text Domain: wplms-lms
*/
/*
Copyright 2016  VibeThemes  (email : vibethemes@gmail.com)

WPLMS Learndash Migration program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

WPLMS Learndash Migration program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with WPLMS Learndash Migration program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

include_once 'includes/init.php';

add_action( 'plugins_loaded', 'wplms_learndash_migrate_language_setup' );
function wplms_learndash_migrate_language_setup(){
    $locale = apply_filters("plugin_locale", get_locale(), 'wplms-lms');
    
    $lang_dir = dirname( __FILE__ ) . '/languages/';
    $mofile        = sprintf( '%1$s-%2$s.mo', 'wplms-lms', $locale );
    $mofile_local  = $lang_dir . $mofile;
    $mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;

    if ( file_exists( $mofile_global ) ) {
        load_textdomain( 'wplms-lms', $mofile_global );
    } else {
        load_textdomain( 'wplms-lms', $mofile_local );
    }   
}
