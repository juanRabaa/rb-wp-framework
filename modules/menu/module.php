<?php

if(!class_exists('RB_Menu_Module')){
    define('RB_MENU_MASTER_DIR', plugin_dir_path(__FILE__));

    class RB_Menu_Module{
        static private $initialized = false;

    	static public function initialize(){
    		if(self::$initialized)
    			return false;
            self::$initialized = true;

            require_once RB_MENU_MASTER_DIR . 'inc/RB_Menu_Item_Type.php';
            require_once RB_MENU_MASTER_DIR . 'inc/RB_Menu.php';
    	}
    }

    RB_Menu_Module::initialize();
}
