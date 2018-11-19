<?php
$rb_framework_uri = get_template_directory_uri()."/inc/rb-wordpress-framework";
$rb_customizer_uri = $rb_framework_uri."/customizer";
<<<<<<< HEAD
$rb_framework_directory = get_template_directory()."/inc/rb-wordpress-framework";
$rb_customizer_directory = $rb_framework_directory."/customizer";

function font_awesome_json_codes(){
    global $rb_customizer_directory;
    return file_get_contents("$rb_customizer_directory/json/fontawesome.json");
}

function font_awesome_codes(){
    return json_decode(font_awesome_json_codes(), true);
}
=======
>>>>>>> fb5d34d713776637ffa260c1541721b620bdc468

// =============================================================================
// Customizer STYLES
// =============================================================================
<<<<<<< HEAD
add_action( 'customize_controls_enqueue_scripts', function () use ($rb_customizer_uri, $rb_framework_uri){
    wp_enqueue_style( "normalize-css", "$rb_framework_uri/commons/libs/Skeleton-2.0.4/css/normalize.css", array() );
	wp_enqueue_style( "skeleton-css", "$rb_framework_uri/commons/libs/Skeleton-2.0.4/css/skeleton.css", array() );
	wp_enqueue_style( "font-awesome-css", "$rb_framework_uri/commons/libs/fontawesome-free-5.1.0-web/css/all.css", array() );
	wp_enqueue_style( "rb-customizer-css", "$rb_customizer_uri/css/rb-customizer.css" );
	wp_enqueue_style( "rb-customizer-image-selection-control", "$rb_customizer_uri/css/rb-customizer-image-selection-control.css" );
	wp_enqueue_style( "rb-customizer-color-scheme-control", "$rb_customizer_uri/css/rb-customizer-color-scheme-control.css" );
	wp_enqueue_style( "rb-customizer-sortable-list-control", "$rb_customizer_uri/css/rb-customizer-sortable-list-control.css" );
	wp_enqueue_style( "rb-customizer-lists-generator-control", "$rb_customizer_uri/css/rb-customizer-lists-generator-control.css" );
	wp_enqueue_style( "rb-customizer-textarea-generator-control", "$rb_customizer_uri/css/rb-customizer-textarea-generator-control.css" );
	wp_enqueue_style( "rb-customizer-multiple-inputs-control", "$rb_customizer_uri/css/rb-customizer-multiple-inputs-control.css" );
	wp_enqueue_style( "rb-customizer-image-gallery-control", "$rb_customizer_uri/css/rb-customizer-image-gallery-control.css" );
	wp_enqueue_style( "rb-customizer-tinymce-editor-control", "$rb_customizer_uri/css/rb-customizer-tinymce-editor-control.css" );
    wp_enqueue_style( "rb-customizer-fontawesome-control", "$rb_customizer_uri/css/rb-customizer-fontawesome-control.css" );
});
=======
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
>>>>>>> fb5d34d713776637ffa260c1541721b620bdc468

// =============================================================================
// Customizer SCRIPTS
// =============================================================================
<<<<<<< HEAD
add_action( 'customize_controls_enqueue_scripts', function () use ($rb_customizer_uri){
    wp_enqueue_script( "jquery-3", "https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js", true );
    wp_enqueue_script( "jquery-ui", $rb_customizer_uri . '/js/jquery-ui-1.12.1.custom/jquery-ui.min.js', array("jquery"), true );
    wp_enqueue_script( 'rb-wp-editor-customizer', $rb_customizer_uri . '/js/rb-customizer-panel.js', array( 'jquery' ), rand(), true );
    wp_enqueue_script( "rb-customizer-image-selection-control", $rb_customizer_uri."/js/rb-customizer-image-selection-control.js", array("jquery"), true );
    wp_enqueue_script( "rb-customizer-sortable-list-control", $rb_customizer_uri."/js/rb-customizer-sortable-list-control.js", array("jquery"), true );
    wp_enqueue_script( "rb-customizer-lists-generator", $rb_customizer_uri."/js/rb-customizer-lists-generator.js", array("jquery"), true );
    wp_enqueue_script( "rb-customizer-multiple-inputs-control", $rb_customizer_uri."/js/rb-customizer-multiple-inputs-control.js", array("jquery"), true );
    wp_enqueue_script( "rb-customizer-image-gallery-control", $rb_customizer_uri."/js/rb-customizer-image-gallery-control.js", array("jquery"), true );
    wp_enqueue_script( "rb-customizer-tinymce-editor-control", $rb_customizer_uri."/js/rb-customizer-tinymce-editor-control.js", array("jquery"), true );
    wp_enqueue_script( "rb-customizer-fontawesome-control", $rb_customizer_uri."/js/rb-customizer-fontawesome-control.js", array("jquery"), true );
    wp_localize_script( "rb-customizer-fontawesome-control", 'fa_vars', array(
        'fontawesomeCodes'  => font_awesome_json_codes(),
    ) );
});

// =============================================================================
// ON CUSTOMIZER PAGE
// =============================================================================
add_action( 'customize_register', 'my_customize_register', 0 );

function my_customize_register($wp_customize) {

    require get_template_directory() . '/inc/rb-wordpress-framework/customizer/inc/rb-customizer-panel-builder.php';
    require get_template_directory() . '/inc/rb-wordpress-framework/customizer/inc/customizer-controls.php';

}





=======
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
>>>>>>> fb5d34d713776637ffa260c1541721b620bdc468

?>
