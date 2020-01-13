<?php
class RB_Metabox extends RB_Field_Factory{
    public $meta_id;
    public $render_nonce = true;
    public $metabox_settings = array(
        'title'         => '',
        'admin_page'	=> 'post',
        'context'		=> 'advanced',
        'priority'		=> 'default',
        'classes'		=> '',
        'column'        => null,
    );

    public function __construct($id, $metabox_settings, $control_settings ) {
        $this->metabox_settings = array_merge($this->metabox_settings, $metabox_settings);
        $this->meta_id = $this->metabox_settings['meta_id'] = $id;
        parent::__construct($id, null, $control_settings);
        $this->register_metabox();
    }

    //Returns the metabox title
    public function get_title(){
        return $this->metabox_settings['title'];
    }

    /**
    *   Registers the metabox to be used on the post edition page.
    *   Setups the column in the posts list page
    */
    public function register_metabox(){
        add_action( 'load-post.php', array($this, 'metabox_setup') );
        add_action( 'load-post-new.php', array($this, 'metabox_setup') );
        $this->column_setup();
    }

    /**
    *   Sets up the column to show on the posts list.
    */
    private function column_setup(){
        if(!$this->metabox_settings['column'])
            return false;
        foreach($this->get_admin_pages() as $admin_page){
            RB_Filters_Manager::add_filter( "rb_metabox-$this->meta_id-column_base", "manage_{$admin_page}_posts_columns", array($this, 'add_column_base') );
            RB_Filters_Manager::add_filter( "rb_metabox-$this->meta_id-column_content", "manage_{$admin_page}_posts_custom_column", array($this, 'add_column_content') );
        }
    }

    /**
    *   Adds the metabox column to the posts list. The content is then setted by add_column_content
    *   @param string[] $columns                            Columns names array
    */
    public function add_column_base($columns){
        $title = $this->get_title();
        if(is_array($this->metabox_settings['column']) && isset($this->metabox_settings['column']['title']))
            $title = $this->metabox_settings['column']['title'];
        $columns[$this->meta_id] = $title;
        return $columns;
    }

    /**
    *   Adds content to the metabox column cell on the posts list page
    *   @param string $columns                              Column name
    *   @param WP_Post|int|null $post                       ID or instances of the post. If null, global $post is used
    */
    public function add_column_content($column, $post = null){
        if($column != $this->meta_id)
            return '';
        $meta_value = $this->get_value($post);
        $title = $this->get_title();
        if(is_array($this->metabox_settings['column']) && isset($this->metabox_settings['column']['content']) && is_callable($this->metabox_settings['column']['content']))
            call_user_func($this->metabox_settings['column']['content'], $meta_value, get_post($post));
        else
            echo $meta_value;
    }

    public function metabox_setup(){
        /* Add meta boxes on the 'add_meta_boxes' hook. */
        add_action( 'add_meta_boxes', array($this, 'add_metabox') );
        /* Save post meta on the 'save_post' hook. */
        add_action( 'save_post', array($this, 'save_metabox'), 10, 2 );
    }

    /* Creates the metabox to be displayed on the post editor screen. */
    public function add_metabox(){
        extract( $this->metabox_settings );
        foreach($this->get_admin_pages() as $admin_page){
            add_meta_box( $this->id, $title, array($this, 'render_metabox'), $admin_page, $context, $priority);
        }
        $this->add_metabox_classes();
    }

    /**
    *   Returns the meta value for a post
    *   @param WP_Post|int $post                                Post id or instance from which to get the meta value from
    */
    public function get_value($post){
        $post = get_post($post);
        return $post && metadata_exists('post', $post->ID, $this->meta_id) ? get_post_meta( $post->ID, $this->meta_id, true ) : null;
    }

    public function render_metabox($post){
        $this->value = $this->get_value($post);
        $this->render($post);
    }

    public function save_metabox( $post_id, $post ) {

        // /* Verify the nonce before proceeding. */
        // if ( !isset( $_POST[$this->id . '_nonce'] ) || !wp_verify_nonce( $_POST[$this->id . '_nonce'], basename( __FILE__ ) ) )
        //     return $post_id;

        //JSONS Values in the $_POST get scaped quotes. That makes json_decode
        //not recognize the content as jsons. THE PROBLEM is that it also eliminates
        //th the '\' in the values of the JSON.
        //$_POST = array_map( 'stripslashes_deep', $_POST );
        //echo "-----------METABOX SAVING PROCCESS----------------<br><br>";
        $new_meta_value = null;
        if(isset($_POST[$this->id])){
            $new_meta_value = $this->get_sanitized_value($_POST[$this->id], array(
                'unslash_group'                 => true,
                'escape_child_slashes'          => true,
                'unslash_repeater_slashes'      => true,
                'unslash_single_repeater'       => true,
            ));
        }

        // if($this->id == 'lr_encuesta_opciones'){
        //     echo "New value: "; var_dump($new_meta_value); echo "<br>";
        //     errr();
        // }

        /* Get the meta key. */
        $meta_key = $this->meta_id;

        /* Get the meta value of the custom field key. */
        $meta_exists = $this->meta_exists($post_id);
        $meta_value = get_post_meta( $post_id, $meta_key, true );

        //echo "Sanitized value: "; var_dump($new_meta_value); echo "<br>";
        //asdasd3453();

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
    *   Returns the admin pages where this metabox will be added
    *   @return string[] admin pages post types
    */
    public function get_admin_pages(){
        return is_array($this->metabox_settings['admin_page']) ? $this->metabox_settings['admin_page'] : [$this->metabox_settings['admin_page']];
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
            $admin_pages = $this->get_admin_pages();
            foreach($admin_pages as $admin_page){
                add_filter( "postbox_classes_{$admin_page}_{$this->id}", function( $classes = array() ){
                    foreach ( $this->metabox_settings['classes'] as $class ) {
                        if ( ! in_array( $class, $classes ) ) {
                            $classes[] = sanitize_html_class( $class );
                        }
                    }
                    return $classes;
                });
            }
        }
    }

    protected function meta_exists($post_id){
        return metadata_exists( 'post', $post_id, $this->id );
    }
}
