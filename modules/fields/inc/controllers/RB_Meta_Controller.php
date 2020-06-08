<?php
class RB_Meta_Controller{
    public $meta_id;
    public $render_nonce = true;
    public $metabox_settings = array(
        'classes'		=> '',
    );

    public function __construct($id, $metabox_settings, $control_settings ) {
        $this->metabox_settings = array_merge($this->metabox_settings, $metabox_settings);
        $this->control_settings = $control_settings;
        $this->meta_id = $this->metabox_settings['meta_id'] = $id;
        $this->set_field_controller();
        $this->register_metabox();
    }

    public function set_field_controller($value = null){
        $this->field_controller = new RB_Field_Factory($this->meta_id, $value, $this->control_settings);
    }

    public function register_metabox(){
        /* Add field to attachment form. */
        add_filter( 'attachment_fields_to_edit', array($this, 'add_metabox'), 10, 2 );
        /* Saves the field value*/
        add_action( 'edit_attachment', array($this, 'save_metabox'), 20, 2 );
    }

    /* Adds a new field to the attachment form fields array */
    public function add_metabox($form_fields, $post){
        extract( $this->metabox_settings );
        $this->field_controller->set_value(get_post_meta( $post->ID, $this->meta_id, true ));

        $form_fields[$this->meta_id] = array(
            'label'  => __( $title ),
            'input'  => 'html',
            'html'   => $this->get_control_html_as_string($post),
        );

        return $form_fields;
    }

    public function get_control_html_as_string($post = null){
        ob_start();
        $this->field_controller->render($post);
        return ob_get_clean();
    }

    public function save_metabox( $post_id ) {
        // /* Verify the nonce before proceeding. */
        // if ( !isset( $_POST[$this->meta_id . '_nonce'] ) || !wp_verify_nonce( $_POST[$this->meta_id . '_nonce'], basename( __FILE__ ) ) )
        //     return $post_id;

        //JSONS Values in the $_POST get scaped quotes. That makes json_decode
        //not recognize the content as jsons. THE PROBLEM is that it also eliminates
        //th the '\' in the values of the JSON.
        //$_POST = array_map( 'stripslashes_deep', $_POST );

        $new_meta_value = null;
        if(isset($_POST[$this->meta_id])){
            $new_meta_value = $this->field_controller->get_sanitized_value($_POST[$this->meta_id], array(
                'unslash_group'                 => true,
                'escape_child_slashes'          => true,
                'unslash_repeater_slashes'      => true,
                'unslash_single_repeater'       => true,
            ));
        }

        /* Get the meta key. */
        $meta_key = $this->meta_id;

        /* Get the meta value of the custom field key. */
        $meta_exists = $this->meta_exists($post_id);
        $old_meta_value = get_post_meta( $post_id, $meta_key, true );

        // If the new value is not null
        if( isset($new_meta_value) ){
            /* If a new meta value was added and there was no previous value, add it. */
            if( !$meta_exists )
                add_post_meta( $post_id, $meta_key, $new_meta_value, true );
            /* If the new meta value does not match the old value, update it. */
            else if( $new_meta_value != $old_meta_value )
                update_post_meta( $post_id, $meta_key, $new_meta_value );
        }
        /* If there is no new meta value but an old value exists, delete it. */
        else if ( $meta_exists )
            delete_post_meta( $post_id, $meta_key, $old_meta_value );
    }

    protected function meta_exists($post_id){
        return metadata_exists( 'post', $post_id, $this->meta_id );
    }

    public function get_posted_value()

    public function update_value()
}
