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
    $is_customize_preview = is_customize_preview();
    return current_user_can('edit_theme_options') && RB_CUSTOMIZER_FRONT_EDITION_ACTIVE && (!is_admin() || ($is_customize_preview || $on_editor_page));
}

function rb_default_customizable($string, $default){
    if(!$string && rb_is_customization_available())
        echo $default;
    else
        echo $string;
}

function rb_get_default_customizable($string, $default){
    ob_start();
    rb_default_customizable($string, $default);
    return ob_get_clean();
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
    $index = 0;
    foreach($array as $key => $item){
        if( $condition($item, $key) )
            return $index;
        $index++;
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

/**
*   Performs a deep merge of $array1 and $array2. If an item of type array is found in both arrays with the same
*   item key, a deep merge will be performed with them.
*/
function array_merge_deep(array $array1, array $array2){
    $new_array = $array1;
    foreach($array2 as $item_key => $array2_item_value){
        if(!isset($array1[$item_key]))
            $new_array[$item_key] = $array2_item_value;
        else{
            $item_old_value = $array1[$item_key];
            if(is_array($item_old_value) && is_array($array2_item_value))
                $new_array[$item_key] = array_merge_deep($item_old_value, $array2_item_value);
            else
                $new_array[$item_key] = $array2_item_value;
        }
    }
    return $new_array;
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

/**
*   Simple Tax query to be used in a WP_Query
*   @param string   $relation                                   Query relation. Accepts 'OR' or 'AND'
*   @param array    $tems                                       Array of terms to query for. Format must be (taxonomy_name => terms_array)
*   @param string   $fields                                     Fields to check terms against in the query. Can be any field that a WP_Term have.
*/
function rb_get_tax_query($relation, $terms, $field = 'term_id'){
    if(!is_array($terms) || empty($terms))
        return null;
    $tax_query = array();
    foreach($terms as $taxonomy => $tax_terms){
        if(!$tax_terms || empty($tax_terms))
            continue;
        $tax_query[] = array(
            'taxonomy' => $taxonomy,
            'field'    => $field,
            'terms'    => $tax_terms,
        );
    }
    if(empty($tax_query))
        return null;
    if(count($tax_query) > 1)
        $tax_query['relation'] = 'OR';
    return $tax_query;
}

// =============================================================================
//
// =============================================================================
function print_if($content, $condition){
    $condition ? print_r($content) : false;
}

// =============================================================================
// TEMPLATE PART
// =============================================================================
function rb_get_template_part($slug, $name = '', $args = array()){
    $previous_query_vars = array();
    if(is_array($args) && count($args)){
        foreach($args as $arg_name => $arg_value){
            $previous_query_vars[$arg_name] = get_query_var($arg_name, null);
            set_query_var($arg_name, $arg_value);
        }
    }

    get_template_part($slug, $name);

    //Return the queries var to their previous value
    foreach($previous_query_vars as $var_name => $var_value){
        set_query_var($var_name, $var_value);
    }
}

/**
*   @param string/array     $vars                       Name of the query var to retrieve, or array of (var_name => default_value)
*   @param mixed            $default                    Default value if $vars is string. If both the query var value and $default
*                                                       are arrays, the returned value will be a deep merge between them
*/
function rb_get_query_var($vars, $default = null){
    if( !$vars && !is_string($vars) && ( !is_array($vars) || empty($vars) ) )
        return null;

    if(is_string($vars)){
        return rb_parsed_query_var($vars, $default);
    }
    //no string then array
    $result_vars = array();
    foreach($vars as $var_name => $var_default){
        // var_dump($var_name);
        // var_dump(rb_parsed_query_var($var_name, $var_default));
        // echo "<br>";
        $result_vars[$var_name] = rb_parsed_query_var($var_name, $var_default);
    }
    return $result_vars;
}

/**
*   @param string           $var                        Name of the query var to retrieve.
*   @param mixed            $default                    Default value. If both the query var value and $default
*                                                       are arrays, the returned value will be a deep merge between them
*/
function rb_parsed_query_var($var, $default){
    if(!is_string($var))
        return null;
    $query_var_value = get_query_var($var, null);
    $final_value = null;
    if(is_array($default) && is_array($query_var_value))
        $final_value = array_merge_deep($default, $query_var_value);
    else
        $final_value = $query_var_value;
    if($final_value === null)
        $final_value = $default;
    return $final_value;
}
