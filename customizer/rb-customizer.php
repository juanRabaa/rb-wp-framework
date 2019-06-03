<?php
define('RB_WORDPRESS_FRAMEWORK_PATH',  get_template_directory(). "/inc/rb-wordpress-framework" );
define('RB_WORDPRESS_FRAMEWORK_URI',  get_template_directory_uri(). "/inc/rb-wordpress-framework" );
define('RB_CUSTOMIZER_FRAMEWORK_PATH', dirname(__FILE__));
define('RB_CUSTOMIZER_FRAMEWORK_URI',  RB_WORDPRESS_FRAMEWORK_URI . "/customizer" );
define('RB_CUSTOMIZER_FRONT_EDITION_ACTIVE', true);

// =============================================================================
// Customizer STYLES
// =============================================================================
add_action( 'customize_controls_enqueue_scripts', function(){
    wp_enqueue_style( "normalize-css", RB_CUSTOMIZER_FRAMEWORK_URI . "/commons/libs/Skeleton-2.0.4/css/normalize.css", array() );
	wp_enqueue_style( "skeleton-css", RB_CUSTOMIZER_FRAMEWORK_URI ."/commons/libs/Skeleton-2.0.4/css/skeleton.css", array() );
	wp_enqueue_style( "font-awesome-css", RB_CUSTOMIZER_FRAMEWORK_URI. "/commons/libs/fontawesome-free-5.1.0-web/css/all.css", array() );
	wp_enqueue_style( "rb-customizer-css", RB_CUSTOMIZER_FRAMEWORK_URI . "/css/rb-customizer.css" );
	wp_enqueue_style( "rb-customizer-image-selection-control", RB_CUSTOMIZER_FRAMEWORK_URI . "/css/rb-customizer-image-selection-control.css" );
	wp_enqueue_style( "rb-customizer-color-scheme-control", RB_CUSTOMIZER_FRAMEWORK_URI . "/css/rb-customizer-color-scheme-control.css" );
	wp_enqueue_style( "rb-customizer-sortable-list-control", RB_CUSTOMIZER_FRAMEWORK_URI . "/css/rb-customizer-sortable-list-control.css" );
	wp_enqueue_style( "rb-customizer-lists-generator-control", RB_CUSTOMIZER_FRAMEWORK_URI . "/css/rb-customizer-lists-generator-control.css" );
	wp_enqueue_style( "rb-customizer-textarea-generator-control", RB_CUSTOMIZER_FRAMEWORK_URI . "/css/rb-customizer-textarea-generator-control.css" );
	wp_enqueue_style( "rb-customizer-multiple-inputs-control", RB_CUSTOMIZER_FRAMEWORK_URI . "/css/rb-customizer-multiple-inputs-control.css" );
	wp_enqueue_style( "rb-customizer-image-gallery-control", RB_CUSTOMIZER_FRAMEWORK_URI . "/css/rb-customizer-image-gallery-control.css" );
	wp_enqueue_style( "rb-customizer-tinymce-editor-control", RB_CUSTOMIZER_FRAMEWORK_URI . "/css/rb-customizer-tinymce-editor-control.css" );
    wp_enqueue_style( "rb-customizer-fontawesome-control", RB_CUSTOMIZER_FRAMEWORK_URI . "/css/rb-customizer-fontawesome-control.css" );
});

// =============================================================================
// Customizer SCRIPTS
// =============================================================================
add_action( 'customize_controls_enqueue_scripts', function(){
    wp_enqueue_script( "jquery", "https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js", true );
    //wp_enqueue_script( "jquery-3", "https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js", true );
    wp_enqueue_script( "jquery-ui", RB_CUSTOMIZER_FRAMEWORK_URI . '/js/jquery-ui-1.12.1.custom/jquery-ui.min.js', array("jquery"), true );
    wp_enqueue_script( 'rb-wp-editor-customizer', RB_CUSTOMIZER_FRAMEWORK_URI . '/js/rb-customizer-panel.js', array( 'jquery' ), rand(), true );
    wp_enqueue_script( "rb-customizer-image-selection-control", RB_CUSTOMIZER_FRAMEWORK_URI."/js/rb-customizer-image-selection-control.js", array("jquery"), true );
    wp_enqueue_script( "rb-customizer-sortable-list-control", RB_CUSTOMIZER_FRAMEWORK_URI."/js/rb-customizer-sortable-list-control.js", array("jquery"), true );
    wp_enqueue_script( "rb-customizer-lists-generator", RB_CUSTOMIZER_FRAMEWORK_URI."/js/rb-customizer-lists-generator.js", array("jquery"), true );
    wp_enqueue_script( "rb-customizer-multiple-inputs-control", RB_CUSTOMIZER_FRAMEWORK_URI."/js/rb-customizer-multiple-inputs-control.js", array("jquery"), true );
    wp_enqueue_script( "rb-customizer-image-gallery-control", RB_CUSTOMIZER_FRAMEWORK_URI."/js/rb-customizer-image-gallery-control.js", array("jquery"), true );
    wp_enqueue_script( "rb-customizer-tinymce-editor-control", RB_CUSTOMIZER_FRAMEWORK_URI."/js/rb-customizer-tinymce-editor-control.js", array("jquery"), true );
    wp_enqueue_script( "rb-customizer-fontawesome-control", RB_CUSTOMIZER_FRAMEWORK_URI."/js/rb-customizer-fontawesome-control.js", array("jquery"), true );
    wp_localize_script( "rb-customizer-fontawesome-control", 'fa_vars', array(
        'fontawesomeCodes'  => font_awesome_json_codes(),
    ) );
});

// =============================================================================
// ON CUSTOMIZER PAGE
// =============================================================================
add_action( 'customize_register', function($wp_customize){
    require get_template_directory() . '/inc/rb-wordpress-framework/customizer/inc/rb-customizer-panel-builder.php';
    require get_template_directory() . '/inc/rb-wordpress-framework/customizer/inc/customizer-controls.php';
});

// =============================================================================
// FRONT END EDITION
// =============================================================================
require_once RB_CUSTOMIZER_FRAMEWORK_PATH . '/inc/rb-customizer-front-edition.php';

?>
