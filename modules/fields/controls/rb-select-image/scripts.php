<?php
//Image Selection control
wp_enqueue_style( 'rb-image-selection-control', rb_get_file_url(__FILE__) . '/css/rb-image-selection-control.css' );
wp_enqueue_script( 'rb-select-image', rb_get_file_url(__FILE__) . '/js/rb-select-image-control.js', array('jquery'), true );
