<?php
class RB_Customizer_Module extends RB_Framework_Module{
    static public $dependencies = array('adminpages');

    static public function initialize(){
        if(!self::are_dependencies_loaded(self::$dependencies))
            self::load_dependencies(self::$dependencies);

        self::define_constants();
        self::hook_customizer_scripts();
        self::hook_customize_register();
        self::frontend_edition();
    }

    // =============================================================================
    // CONSTANTS
    // =============================================================================
    static private function define_constants(){
        define('RB_CUSTOMIZER_FRAMEWORK_PATH', RB_Wordpress_Framework::get_module_path("customizer") );
        define('RB_CUSTOMIZER_FRAMEWORK_URI',  RB_Wordpress_Framework::get_module_uri("customizer") );
        define('RB_CUSTOMIZER_FRAMEWORK_ASSETS_URI',  RB_Wordpress_Framework::get_module_uri("customizer") . '/assets' );
        if(!defined('RB_CUSTOMIZER_FRONT_EDITION_ACTIVE'))
            define('RB_CUSTOMIZER_FRONT_EDITION_ACTIVE',  false );
        if(!defined('RB_CUSTOMIZER_FRONT_EDITION_BUTTONS_ACTIVE'))
            define('RB_CUSTOMIZER_FRONT_EDITION_BUTTONS_ACTIVE',  true );
    }

    // =============================================================================
    // Customizer STYLES
    // =============================================================================
    static public function enqueue_customizer_scripts(){
        //STYLES
        wp_enqueue_style( "normalize-css", RB_WORDPRESS_FRAMEWORK_COMMONS_URI . "/libs/Skeleton-2.0.4/css/normalize.css", array() );
        wp_enqueue_style( "skeleton-css", RB_WORDPRESS_FRAMEWORK_COMMONS_URI ."/libs/Skeleton-2.0.4/css/skeleton.css", array() );
        wp_enqueue_style( "font-awesome-css", RB_WORDPRESS_FRAMEWORK_COMMONS_URI. "/libs/fontawesome-free-5.1.0-web/css/all.css", array() );
        wp_enqueue_style( "rb-customizer-css", RB_CUSTOMIZER_FRAMEWORK_URI . "/css/rb-customizer.css" );
        //SCRIPTS
        wp_enqueue_script( "jquery", "https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js", true );
        //wp_enqueue_script( "jquery-3", "https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js", true );
        wp_enqueue_script( "jquery-ui", RB_WORDPRESS_FRAMEWORK_COMMONS_URI . '/js/libs/jquery-ui-1.12.1.custom/jquery-ui.min.js', array("jquery"), true );
    }

    static private function hook_customizer_scripts(){
        add_action( 'customize_controls_enqueue_scripts', array('RB_Customizer_Module', 'enqueue_customizer_scripts'));
    }

    // =============================================================================
    // Customizer PAGE
    // =============================================================================
    static public function on_customizer_page($wp_customize){
        require RB_CUSTOMIZER_FRAMEWORK_PATH . '/inc/rb-customizer-panel-builder.php';
        require RB_CUSTOMIZER_FRAMEWORK_PATH . '/inc/customizer-controls.php';
    }

    static private function hook_customize_register(){
        add_action( 'customize_register', array('RB_Customizer_Module', 'on_customizer_page') );
    }

    // =========================================================================
    // Front end edition
    // =========================================================================
    static private function frontend_edition(){
        require_once RB_CUSTOMIZER_FRAMEWORK_PATH . '/inc/rb-customizer-front-edition.php';
        //require_once RB_CUSTOMIZER_FRAMEWORK_PATH . '/inc/rb-edition-panel.php';

        // if(rb_customizer_front_edition_is_active() && isset($_GET['rb_edition'])){
        //     add_filter( 'template_include', 'portfolio_page_template', 99 );
        //     function portfolio_page_template( $template ) {
        //         print_r($template);
        //     	return RB_CUSTOMIZER_FRAMEWORK_PATH . '/templates/front-edition-save-box.php';
        //     }
        // }
    }
}

// =============================================================================
// COMMONS - MOVE TO COMMONS FOLDER
// =============================================================================
function rb_customizer_front_edition_is_active(){
    global $pagenow;
    $on_editor_page = $pagenow == "admin.php" && isset($_GET['page']) && $_GET['page'] == 'test';
    return RB_CUSTOMIZER_FRONT_EDITION_ACTIVE && current_user_can('edit_theme_options') && !is_customize_preview() && (!is_admin() || $on_editor_page);
}

RB_Customizer_Module::initialize();
?>
