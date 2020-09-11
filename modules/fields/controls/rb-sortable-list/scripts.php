<?php

//Image Selection control
wp_enqueue_style( 'rb-sortable-list-control-css', rb_get_file_url(__FILE__) . '/css/rb-sortable-list-control.css' );
wp_enqueue_script( 'rb-sortable-list-control-js', rb_get_file_url(__FILE__) . '/js/rb-sortable-list-control.js', array('jquery'), true );
