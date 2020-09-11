<?php

//Double list control
wp_enqueue_style( 'rb-text-list-control', rb_get_file_url(__FILE__) . '/css/rb-doublelist-control.css' );
wp_enqueue_script( 'rb-double-list-control', rb_get_file_url(__FILE__) . '/js/rb-doublelist-control.js', array('jquery', 'jquery-ui-sortable'), true );
