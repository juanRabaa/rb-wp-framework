<?php

//Library
wp_enqueue_style( 'grapick', rb_get_file_url(__FILE__) . '/css/grapick.min.css' );
wp_enqueue_script( 'grapick', rb_get_file_url(__FILE__) . '/js/grapick.min.js', true );
//Customs
wp_enqueue_style( 'rb-gradient-picker', rb_get_file_url(__FILE__) . '/css/rb-gradient-picker.css' );
wp_enqueue_script( 'rb-gradient-picker', rb_get_file_url(__FILE__) . '/js/rb-gradient-picker.js', array('jquery'), true );
