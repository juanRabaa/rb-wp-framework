<?php
if(class_exists('RB_WP_Rest_API_Extended'))
    return;

require_once RB_WORDPRESS_FRAMEWORK_PATH . '/rest/rb-wp-rest-api.php';

// =============================================================================
// ADMINISTRATOR ONLY ROUTES
// A estas rutas solo puede acceder un usuario logeado con rol de administrador
// =============================================================================
//RB_WP_Rest_API_Extended::group(['role'   =>  'administrator'], function(){
    // =========================================================================
    // ROUTES
    // =========================================================================
    RB_WP_Rest_API_Extended::post('rb-customizer/v1', '/setting/update', function($request){
        return set_theme_mod( $request['settingID'], $request['value'] );
    });
//});
