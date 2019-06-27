<?php

function wp_query_is_on_last($query = null){
    global $wp_query;
    $query = $query ? $query : $wp_query;
    return (($query->current_post +1) == ($query->post_count));
}

// =============================================================================
//
// =============================================================================
function rb_is_customization_available(){
    global $pagenow;
    $on_editor_page = $pagenow == "admin.php" && isset($_GET['page']) && $_GET['page'] == 'test';
    return current_user_can('edit_theme_options') && ( (RB_CUSTOMIZER_FRONT_EDITION_ACTIVE || is_customize_preview()) || (!is_admin() || $on_editor_page) );
}

function rb_default_customizable($string, $default){
    if(!$string && rb_is_customization_available())
        echo $default;
    else
        echo $string;
}
