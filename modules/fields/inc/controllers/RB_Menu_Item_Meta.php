<?php
/**
*   Manages the fields on a menu item of a post type.
*/
class RB_Menu_Item_Meta{
    public $meta_id;
    public $metabox_settings = array(
        'title'         => '',
        'admin_page'	=> 'post',
        'context'		=> 'advanced',
        'priority'		=> 'default',
        'classes'		=> '',
    );

    public function __construct($id, $metabox_settings, $control_settings ) {
        $this->metabox_settings = array_merge($this->metabox_settings, $metabox_settings);
        $this->control_settings = $control_settings;
        $this->meta_id = $this->metabox_settings['meta_id'] = $id;
        $this->set_field_controller();
        $this->register_metabox();
    }

    // Sets the instance of the controller for the field to display
    public function set_field_controller($value = null){
        $this->field_controller = new RB_Field_Factory($this->meta_id, $value, $this->control_settings);
    }

    /**
    *   Returns the meta value for a post
    *   @param WP_Post|int $post                                Post id or instance from which to get the meta value from
    */
    public function get_value($post){
        $post = get_post($post);
        return $post && metadata_exists('post', $post->ID, $this->meta_id) ? get_post_meta( $post->ID, $this->meta_id, true ) : null;
    }

    // Registers the metabox render and save
    public function register_metabox(){
        /* Hook the fields to the menu item of the post type. */
        add_action( 'wp_nav_menu_item_custom_fields', array($this, 'render_metafield'), 10, 4 );
        /* Save post meta on the 'save_post' hook. */
        add_action( 'wp_update_nav_menu_item', array($this, 'save_meta_value'), 10, 3 );
    }

    /* Renders the metabox */
    public function render_metafield($item_id, $item, $depth, $args){
        if(!$this->is_on_admin_page($item->object))
            return false;
        $this->field_controller->set_value($this->get_value($item));
        ?><div class="description description-wide"><?php
        $this->field_controller->render($item);
        ?></div><?php
    }

    /**
    *   Sets the id for the current item being processed
    *   @param int $item_id                                 The menu item ID
    */
    public function set_current_item_id($item_id){
        $this->item_id = $item_id;
        $this->field_id = "{$this->meta_id}__{$this->item_id}";
        $this->field_controller->set_id($this->field_id);
    }

    public function save_meta_value( $menu_id, $post_id ) {
        $this->set_current_item_id($post_id);
        // /* Verify the nonce before proceeding. */
        // if ( !isset( $_POST[$this->id . '_nonce'] ) || !wp_verify_nonce( $_POST[$this->id . '_nonce'], basename( __FILE__ ) ) )
        //     return $post_id;

        //JSONS Values in the $_POST get scaped quotes. That makes json_decode
        //not recognize the content as jsons. THE PROBLEM is that it also eliminates
        //th the '\' in the values of the JSON.
        //$_POST = array_map( 'stripslashes_deep', $_POST );
        //echo "-----------METABOX SAVING PROCCESS----------------<br><br>";
        $new_meta_value = null;
        if(isset($_POST[$this->field_id])){
            $new_meta_value = $this->field_controller->get_sanitized_value($_POST[$this->field_id], array(
                'unslash_group'                 => true,
                'escape_child_slashes'          => true,
                'unslash_repeater_slashes'      => true,
                'unslash_single_repeater'       => true,
            ));
        }

        // if($this->field_id == 'lr_encuesta_opciones'){
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
