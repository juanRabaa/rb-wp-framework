<?php

//Image Selection control
wp_enqueue_style( 'rb-image-selection-control', plugin_dir_url(__FILE__) . 'css/rb-image-selection-control.css' );
wp_enqueue_script( 'rb-select-image', plugin_dir_url(__FILE__) . 'js/rb-select-image-control.js', array('jquery'), true );
