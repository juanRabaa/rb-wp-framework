<?php
// =============================================================================
// CONSTANTS
// =============================================================================
define('RB_CUSTOMIZER_FRAMEWORK_PATH', RB_Wordpress_Framework::get_module_path("customizer") );
define('RB_CUSTOMIZER_FRAMEWORK_URI',  RB_Wordpress_Framework::get_module_uri("customizer") );
if(!defined('RB_CUSTOMIZER_FRONT_EDITION_ACTIVE'))
    define('RB_CUSTOMIZER_FRONT_EDITION_ACTIVE',  false );

// =============================================================================
// COMMONS - MOVE TO COMMONS FOLDER
// =============================================================================
function rb_customizer_front_edition_is_active(){
    return RB_CUSTOMIZER_FRONT_EDITION_ACTIVE && current_user_can('edit_theme_options') && !is_customize_preview();
}

// =============================================================================
// Customizer STYLES
// =============================================================================
add_action( 'customize_controls_enqueue_scripts', function(){
    wp_enqueue_style( "normalize-css", RB_WORDPRESS_FRAMEWORK_URI . "/commons/libs/Skeleton-2.0.4/css/normalize.css", array() );
	wp_enqueue_style( "skeleton-css", RB_WORDPRESS_FRAMEWORK_URI ."/commons/libs/Skeleton-2.0.4/css/skeleton.css", array() );
	wp_enqueue_style( "font-awesome-css", RB_WORDPRESS_FRAMEWORK_URI. "/commons/libs/fontawesome-free-5.1.0-web/css/all.css", array() );
	wp_enqueue_style( "rb-customizer-css", RB_CUSTOMIZER_FRAMEWORK_URI . "/css/rb-customizer.css" );
	wp_enqueue_style( "rb-customizer-sortable-list-control", RB_CUSTOMIZER_FRAMEWORK_URI . "/css/rb-customizer-sortable-list-control.css" );
});

// =============================================================================
// Customizer SCRIPTS
// =============================================================================
add_action( 'customize_controls_enqueue_scripts', function(){
    wp_enqueue_script( "jquery", "https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js", true );
    //wp_enqueue_script( "jquery-3", "https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js", true );
    wp_enqueue_script( "jquery-ui", RB_CUSTOMIZER_FRAMEWORK_URI . '/js/jquery-ui-1.12.1.custom/jquery-ui.min.js', array("jquery"), true );
    wp_enqueue_script( "rb-customizer-sortable-list-control", RB_CUSTOMIZER_FRAMEWORK_URI."/js/rb-customizer-sortable-list-control.js", array("jquery"), true );
});

// =============================================================================
// ON CUSTOMIZER PAGE
// =============================================================================
add_action( 'customize_register', function($wp_customize){
    require RB_CUSTOMIZER_FRAMEWORK_PATH . '/inc/rb-customizer-panel-builder.php';
    require RB_CUSTOMIZER_FRAMEWORK_PATH . '/inc/customizer-controls.php';
});

// =============================================================================
// FRONT END EDITION
// =============================================================================
require_once RB_CUSTOMIZER_FRAMEWORK_PATH . '/inc/rb-customizer-front-edition.php';

?>
