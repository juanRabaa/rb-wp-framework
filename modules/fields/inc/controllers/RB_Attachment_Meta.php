<?php
class RB_Attachment_Meta extends RB_Field_Factory{
    public $meta_id;
    public $render_nonce = true;
    public $metabox_settings = array(
        'classes'		=> '',
    );

    public function __construct($id, $metabox_settings, $control_settings ) {
        $this->metabox_settings = array_merge($this->metabox_settings, $metabox_settings);
        $this->meta_id = $this->metabox_settings['meta_id'] = $id;
        parent::__construct($id, null, $control_settings);
        $this->register_metabox();
    }

    public function register_metabox(){
        // add_action( 'load-post.php', array($this, 'metabox_setup') );
        // add_action( 'load-post-new.php', array($this, 'metabox_setup') );
        $this->metabox_setup();
    }

    public function metabox_setup(){
        /* Add meta boxes on the 'add_meta_boxes' hook. */
        add_filter( 'attachment_fields_to_edit', array($this, 'add_metabox'), 10, 2 );
        /* Save post meta on the 'save_post' hook. */
        add_action( 'edit_attachment', array($this, 'save_metabox'), 20, 2 );
    }

    /* Creates the metabox to be displayed on the post editor screen. */
    public function add_metabox($form_fields, $post){
        extract( $this->metabox_settings );
        $this->value = get_post_meta( $post->ID, $this->meta_id, true );

        $form_fields[$this->id] = array(
            'label'  => __( $title ),
            'input'  => 'html',
            'html'   => $this->get_control_html_as_string($post),
        );
        //$this->add_metabox_classes();

        return $form_fields;
    }

    public function get_control_html_as_string($post = null){
        ob_start();
        $this->render($post);
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    public function save_metabox( $post_id ) {
        // /* Verify the nonce before proceeding. */
        // if ( !isset( $_POST[$this->id . '_nonce'] ) || !wp_verify_nonce( $_POST[$this->id . '_nonce'], basename( __FILE__ ) ) )
        //     return $post_id;

        //JSONS Values in the $_POST get scaped quotes. That makes json_decode
        //not recognize the content as jsons. THE PROBLEM is that it also eliminates
        //th the '\' in the values of the JSON.
        //$_POST = array_map( 'stripslashes_deep', $_POST );

        $new_meta_value = null;
        if(isset($_POST[$this->id])){
            $new_meta_value = $this->get_sanitized_value($_POST[$this->id], array(
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

    public function add_metabox_classes(){
        /**
         * {post_type_name}     The name of the post type
         * {metabox_id}         The ID attribute of the metabox
         *
         * @param   array   $classes    The current classes on the metabox
         * @return  array               The modified classes on the metabox
        */
        if( is_array($this->metabox_settings['classes']) ){
            add_filter( "postbox_classes_{$this->metabox_settings['admin_page']}_{$this->id}", function( $classes = array() ){
                foreach ( $this->metabox_settings['classes'] as $class ) {
                    if ( ! in_array( $class, $classes ) ) {
                        $classes[] = sanitize_html_class( $class );
                    }
                }
                return $classes;
            });
        }
    }

    protected function meta_exists($post_id){
        return metadata_exists( 'post', $post_id, $this->id );
    }
}
