<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(defined('RB_WORDPRESS_FRAMEWORK_VERSION'))
    return;

// =============================================================================
// CONSTANTS
// =============================================================================
define('RB_WORDPRESS_FRAMEWORK_VERSION', '0.1.0');
define('RB_WORDPRESS_FRAMEWORK_PATH',  dirname(__FILE__) );
define('RB_WORDPRESS_FRAMEWORK_URI',  plugins_url("", __FILE__) );
define('RB_WORDPRESS_FRAMEWORK_COMMONS_URI', plugins_url("commons", __FILE__));

// =============================================================================
// COMMONS
// =============================================================================
require_once RB_WORDPRESS_FRAMEWORK_PATH . '/inc/rb-functions.php';
require_once RB_WORDPRESS_FRAMEWORK_PATH . '/inc/RB_Globals.php';

// =============================================================================
// FRAMEWORK
// =============================================================================
if(!class_exists('RB_Wordpress_Framework')){
    class RB_Wordpress_Framework{
        static private $modules_loaded = array();

        static public function get_framework_path($path = ''){
            return RB_WORDPRESS_FRAMEWORK_PATH . $path;
        }

        static public function get_framework_uri($path = ''){
            return RB_WORDPRESS_FRAMEWORK_URI . $path;
        }

        static public function module_exists($module_name){
            return file_exists(self::get_module_path($module_name));
        }

        static public function get_module_path($module_name){
            return self::get_framework_path('/modules') . "/$module_name";
        }

        static public function get_module_uri($module_name){
            return self::get_framework_uri('/modules') . "/$module_name";
        }

        static public function module_is_loaded($module_name = ''){
            return isset($modules_loaded[$module_name]);
        }

        static public function load_module($module_name){
            if(self::module_is_loaded($module_name))
                return false;
            $module_file = self::get_module_path($module_name) . "/module.php";
            if(!file_exists($module_file))
                return false;
            require_once $module_file;
            $modules_loaded[$module_name] = true;
            return true;
        }
    }

    require RB_WORDPRESS_FRAMEWORK_PATH . '/inc/RB_Framework_Module.php';
	require RB_WORDPRESS_FRAMEWORK_PATH . '/inc/RB_Filters_Manager.php';
	RB_Wordpress_Framework::load_module('wplists');
}
