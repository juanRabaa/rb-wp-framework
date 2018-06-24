<?php
$rb_framework_uri = get_template_directory_uri()."/inc/rb-wordpress-framework";
$rb_customizer_uri = $rb_framework_uri."/customizer";

// =============================================================================
// Customizer STYLES
// =============================================================================
function load_styles_customizer() {
    wp_enqueue_style( "normalize-css", $rb_customizer_uri."/commons/libs/Skeleton-2.0.4/css/normalize.css", array() );
	wp_enqueue_style( "skeleton-css", $rb_customizer_uri."/commons/libs/Skeleton-2.0.4/css/skeleton.css", array() );
	wp_enqueue_style( "font-awesome-css", "https://use.fontawesome.com/releases/v5.1.0/css/all.css", array() );
	wp_enqueue_style( "rb-customizer-css", $rb_customizer_uri."/css/src/rb-customizer/rb-customizer.css" );
	wp_enqueue_style( "rb-customizer-image-selection-control", $rb_customizer_uri."/css/src/rb-customizer/rb-customizer-image-selection-control.css" );
	wp_enqueue_style( "rb-customizer-color-scheme-control", $rb_customizer_uri."/css/src/rb-customizer/rb-customizer-color-scheme-control.css" );
	wp_enqueue_style( "rb-customizer-sortable-list-control", $rb_customizer_uri."/css/src/rb-customizer/rb-customizer-sortable-list-control.css" );
	wp_enqueue_style( "rb-customizer-lists-generator-control", $rb_customizer_uri."/css/src/rb-customizer/rb-customizer-lists-generator-control.css" );
	wp_enqueue_style( "rb-customizer-textarea-generator-control", $rb_customizer_uri."/css/src/rb-customizer/rb-customizer-textarea-generator-control.css" );
	wp_enqueue_style( "rb-customizer-multiple-inputs-control", $rb_customizer_uri."/css/src/rb-customizer/rb-customizer-multiple-inputs-control.css" );
	wp_enqueue_style( "rb-customizer-image-gallery-control", $rb_customizer_uri."/css/src/rb-customizer/rb-customizer-image-gallery-control.css" );
	wp_enqueue_style( "rb-customizer-tinymce-editor-control", $rb_customizer_uri."/css/src/rb-customizer/rb-customizer-tinymce-editor-control.css" );
}
add_action( 'customize_controls_enqueue_scripts', 'load_styles_customizer' );

// =============================================================================
// Customizer SCRIPTS
// =============================================================================
function load_script_customizer() {
        wp_enqueue_script( "jquery-3", "https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js", true );
		wp_enqueue_script( "jquery-ui", "https://code.jquery.com/ui/1.10.3/jquery-ui.js", array("jquery"), true );
		wp_enqueue_script( 'rb-wp-editor-customizer', $rb_customizer_uri . '/js/src/rb-customizer/rb-customizer-panel.js', array( 'jquery' ), rand(), true );
		wp_enqueue_script( "rb-customizer-image-selection-control", $rb_customizer_uri."/js/src/rb-customizer/rb-customizer-image-selection-control.js", array("jquery"), true );
		wp_enqueue_script( "rb-customizer-sortable-list-control", $rb_customizer_uri."/js/src/rb-customizer/rb-customizer-sortable-list-control.js", array("jquery"), true );
		wp_enqueue_script( "rb-customizer-lists-generator", $rb_customizer_uri."/js/src/rb-customizer/rb-customizer-lists-generator.js", array("jquery"), true );
		wp_enqueue_script( "rb-customizer-multiple-inputs-control", $rb_customizer_uri."/js/src/rb-customizer/rb-customizer-multiple-inputs-control.js", array("jquery"), true );
		wp_enqueue_script( "rb-customizer-image-gallery-control", $rb_customizer_uri."/js/src/rb-customizer/rb-customizer-image-gallery-control.js", array("jquery"), true );
		wp_enqueue_script( "rb-customizer-tinymce-editor-control", $rb_customizer_uri."/js/src/rb-customizer/rb-customizer-tinymce-editor-control.js", array("jquery"), true );
}
add_action( 'customize_controls_enqueue_scripts', 'load_script_customizer' );

require $rb_customizer_uri . '/inc/rb-customizer-panel-builder.php';
require $rb_customizer_uri . '/inc/customizer-controls.php';

?>
