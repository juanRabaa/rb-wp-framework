<?php

//Library
wp_enqueue_style( 'grapick', plugin_dir_url(__FILE__) . 'css/grapick.min.css' );
wp_enqueue_script( 'grapick', plugin_dir_url(__FILE__) . 'js/grapick.min.js', true );
//Customs
wp_enqueue_style( 'rb-gradient-picker', plugin_dir_url(__FILE__) . 'css/rb-gradient-picker.css' );
wp_enqueue_script( 'rb-gradient-picker', plugin_dir_url(__FILE__) . 'js/rb-gradient-picker.js', array('jquery'), true );
