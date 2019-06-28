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

// =============================================================================
// ARRAY FUNCTIONS
// =============================================================================
//Remueve el primer elemento que cumpla la condicion
function remove_from_array($array, $condition){
    $index = is_in_array($array, $condition);
    if( $index != -1 ){
        array_splice($array, $index, 1);
    }
    return $array;
}

//Agrega los items del segundo array que no esten en el primero, comparando con
//la $key enviada
function add_missing($array, $array2, $key){
    foreach($array2 as $item_to_add){
        if(is_in_array($array, function($item) use ($key, $item_to_add){
            $item->$key == $item_to_add->$key;
        }) == -1){
            array_push($array, $item_to_add);
        }
    }
    return $array;
}

//Retorna el index del primer elemento que cumpla la condicion. -1 si no se encontro
function is_in_array($array, $condition){
    foreach($array as $key => $item){
        if( $condition($item) )
            return $key;
    }
    return -1;
}

//Crea un arbol a partir de un array y una funcion que determina si un elemento
//es hijo de otro
function array_to_tree($array, $is_child){
    if( !isset($array) || !is_array($array) || !isset($array) || !is_callable($is_child) )
        return null;

    $charge_childs = function($item, $items, $removed = array(), $is_child) use(&$charge_childs){
        $item->menu_item_childs = array();
        foreach($items as $key => $possible_child){
            if( $is_child($possible_child, $item) ){//Si es hijo
                $removed[$possible_child->ID] = $possible_child;//Agregamos el hijo a los removidos
                $result = $charge_childs($possible_child, $items, $removed, $is_child);//Cargamos los hijos al hijo
                $possible_child = $result["item"];
                $removed = $result["removed"];//Agregamos los hijos de los hijos a los removidos
                array_push($item->menu_item_childs, $possible_child);//Lo agregamos al array de hijos
            }
        }
        return array(
            "item"  => $item,
            "removed"   => $removed,
        );
    };

    $make_tree = function($items, $is_child) use(&$charge_childs){
        $removed = array();
        foreach($items as $key => $item){
            //Not removed
            if(is_in_array($removed, function($a) use ($item){
                return $a->ID == $item->ID;
            }) == -1 ){
                $result = $charge_childs($item, $items, $removed, $is_child);
                $items[$key] = $result["item"];
                $removed = $result["removed"];
            }
        };

        foreach($removed as $item){
            $items = remove_from_array($items, function($a) use ($item){
                return $a->ID == $item->ID;
            });
        }

        return $items;
    };

    return $make_tree($array, $is_child);
}

// =============================================================================
//
// =============================================================================
function rb_get_wp_object_id(){

}

// =============================================================================
// TEMPLATE PART TO STRING
// =============================================================================
function rb_catch_template_part($generic,$specialised){
    ob_start();
    get_template_part($generic, $specialised);
    return ob_get_clean();
}

// =============================================================================
// WORDPRESS POSTS FUNCTIONS
// =============================================================================
function get_most_recent_post( $args = array() ){
	$most_recent_post = null;
	//The query to get the latest post
	$the_query = new WP_Query($args);
	//If there is a post

	if ( $the_query->have_posts() ){
		$the_query->the_post();
		$most_recent_post = $the_query->post;
	}
	//reset wordpress post globals
	wp_reset_postdata();
	return $most_recent_post;
}
