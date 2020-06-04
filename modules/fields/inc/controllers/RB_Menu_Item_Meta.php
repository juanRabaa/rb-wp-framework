<?php
/**
*   Manages the fields on a menu item of a post type.
*/
class RB_Menu_Item_Meta{
    public $meta_id;

    public function __construct($id, $metabox_settings, $control_settings ) {
        $this->metabox_settings = $metabox_settings;
        $this->meta_id = $this->metabox_settings['meta_id'] = $id;
        $this->control_settings = $control_settings;
        $this->metabox_setup();
    }

    public function metabox_setup(){
        /* Add meta boxes on the 'add_meta_boxes' hook. */
        add_action( 'wp_nav_menu_item_custom_fields', array($this, 'add_meta_field'), 10, 4 );
        /* Save post meta on the 'save_post' hook. */
        add_action( 'wp_update_nav_menu_item', array($this, 'save_meta_value'), 10, 3 );
    }

    /* Creates the metabox to be displayed on the post editor screen. */
    public function add_meta_field($item_id, $item, $depth, $args){
        if(!$this->is_on_admin_page($item->object))
            return false;
        $menu_item_manager = $this->get_item_field($item_id);
        $menu_item_manager->render_metabox($item);
    }

    /**
    *   Returns the field controller for an specific menu item
    *   @param int $item_id                                 The menu item ID
    */
    public function get_item_field($item_id){
        $field_id = "{$this->meta_id}__{$item_id}";
        return new RB_Menu_Item_Single($this->meta_id, $field_id, $this->metabox_settings, $this->control_settings);
    }

    public function save_meta_value( $menu_id, $post_id ) {
        $field_id = "{$this->meta_id}__{$post_id}";
        $item_field = $this->get_item_field($post_id);
        // /* Verify the nonce before proceeding. */
        // if ( !isset( $_POST[$this->id . '_nonce'] ) || !wp_verify_nonce( $_POST[$this->id . '_nonce'], basename( __FILE__ ) ) )
        //     return $post_id;

        //JSONS Values in the $_POST get scaped quotes. That makes json_decode
        //not recognize the content as jsons. THE PROBLEM is that it also eliminates
        //th the '\' in the values of the JSON.
        //$_POST = array_map( 'stripslashes_deep', $_POST );
        //echo "-----------METABOX SAVING PROCCESS----------------<br><br>";
        $new_meta_value = null;
        if(isset($_POST[$field_id])){
            $new_meta_value = $item_field->get_sanitized_value($_POST[$field_id], array(
                'unslash_group'                 => true,
                'escape_child_slashes'          => true,
                'unslash_repeater_slashes'      => true,
                'unslash_single_repeater'       => true,
            ));
        }

        // if($field_id == 'lr_encuesta_opciones'){
        //     echo "New value: "; var_dump($new_meta_value); echo "<br>";
        //     errr();
        // }

        /* Get the meta key. */
        $meta_key = $this->meta_id;

        /* Get the meta value of the custom field key. */
        $meta_exists = $this->meta_exists($post_id);
        $meta_value = get_post_meta( $post_id, $meta_key, true );

        //echo "Sanitized value: "; var_dump($new_meta_value); echo "<br>";

        // If the new value is not null
        if( isset($new_meta_value) ){
            /* If a new meta value was added and there was no previous value, add it. */
            if( !$meta_exists )
                add_post_meta( $post_id, $meta_key, $new_meta_value, true );
            /* If the new meta value does not match the old value, update it. */
            else if( $new_meta_value != $meta_value )
                update_post_meta( $post_id, $meta_key, $new_meta_value );
        }
        /* If there is no new meta value but an old value exists, delete it. */
        else if ( $meta_exists )
            delete_post_meta( $post_id, $meta_key, $meta_value );
    }

    /**
    *   Returns if the current menu item if of any of the types wanted.
    *   @param string $item_object                                  An object type from a menu item
    */
    public function is_on_admin_page($item_object){
        if(!isset($this->metabox_settings['admin_page']))
            return true;

        if(is_array($this->metabox_settings['admin_page'])){
            foreach($this->metabox_settings['admin_page'] as $admin_page){
                if($item_object == $admin_page)
                    return true;
            }
        }

        if($item_object == $this->metabox_settings['admin_page'])
            return true;

        return false;
    }

    protected function meta_exists($post_id){
        return metadata_exists( 'post', $post_id, $this->meta_id );
    }

}

class RB_Menu_Item_Single extends RB_Field_Factory{
    public $metabox_settings = array(
        'title'         => '',
        'admin_page'	=> 'post',
        'context'		=> 'advanced',
        'priority'		=> 'default',
        'classes'		=> '',
    );

    public function __construct($meta_id, $field_id, $metabox_settings, $control_settings ) {
        $this->metabox_settings = array_merge($this->metabox_settings, $metabox_settings);
        $this->meta_id = $meta_id;
        $this->field_id = $field_id;
        parent::__construct($this->field_id, null, $control_settings);
    }

    /**
    *   Returns the meta value for a post
    *   @param WP_Post|int $post                                Post id or instance from which to get the meta value from
    */
    public function get_value($post){
        $post = get_post($post);
        return $post && metadata_exists('post', $post->ID, $this->meta_id) ? get_post_meta( $post->ID, $this->meta_id, true ) : null;
    }

    //Returns the metabox title
    public function get_title(){
        return $this->metabox_settings['title'];
    }


    public function render_metabox($post){
        $this->value = $this->get_value($post);
        ?><div class="description description-wide"><?php
        $this->render($post);
        ?></div><?php
    }
}
