<?php
class RB_Attachment_Meta extends RB_Metabox_Base{
    use RB_Post_Methods;
    public $metabox_settings = array(
        'classes'		=> '',
    );

    public function __construct($id, $metabox_settings, $control_settings ) {
        parent::__construct($id, $metabox_settings, $control_settings);
        $this->register();
    }

    protected function set_metafield(){
        $this->meta_field = new RB_Post_Meta_Control($this->meta_id, $this->control_settings);
    }

    public function register_metabox(){
        /* Add field to attachment form. */
        add_filter( 'attachment_fields_to_edit', array($this, 'add_metabox'), 10, 2 );
        /* Saves the field value*/
        add_action( 'edit_attachment', array($this->meta_field, 'save_metabox'), 20, 2 );
    }

    /* Adds a new field to the attachment form fields array */
    public function add_metabox($form_fields, $post){
        $form_fields[$this->meta_id] = array(
            'label'  => __( $this->get_title() ),
            'input'  => 'html',
            'html'   => $this->get_control_html_as_string($post),
        );

        return $form_fields;
    }

    // Return the render of the control as a string
    public function get_control_html_as_string($post = null){
        ob_start();
        $this->render_meta_field($post);
        return ob_get_clean();
    }

    /**
    *   Returns the meta value for a post
    *   @param WP_Post|int $post                                Post id or instance from which to get the meta value from
    */
    public function get_value($post){
        $post = get_post($post);
        return $post && !is_wp_error($post) && metadata_exists('post', $post->ID, $this->meta_id) ? get_post_meta( $post->ID, $this->meta_id, true ) : null;
    }

    public function meta_exists($post_id){
        return metadata_exists( 'post', $post_id, $this->meta_id );
    }

}
