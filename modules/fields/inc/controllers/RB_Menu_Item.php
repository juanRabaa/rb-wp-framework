<?php
class RB_Menu_Item extends RB_Field_Factory{
    public $meta_id;
    public $render_nonce = true;
    public $metabox_settings = array(
        'priority'		=> 'default',
        'classes'		=> '',
        'tabs'          => ''
    );

    public function __construct($id, $metabox_settings, $control_settings ) {
        $this->metabox_settings = array_merge($this->metabox_settings, $metabox_settings);
        $this->meta_id = $this->metabox_settings['meta_id'] = $id;
        parent::__construct($id, null, $control_settings);
        $this->register_metabox();
    }

    public function register_metabox(){
        add_action( 'admin_init', array($this, 'add_metabox'), 10, 1);
    }

    // public function metabox_setup($object){
    //     add_action( 'add_meta_boxes', array($this, 'add_metabox') );
    //     return $object;
    // }

    /* Creates the metabox to be displayed on the custom menu item */
    public function add_metabox($object){
        extract( $this->metabox_settings );
        add_meta_box( $this->id, $title, array($this, 'render_metabox'), 'nav-menus', 'side', $priority);
        //$this->add_metabox_classes();
        return $object;
    }

    public function render_metabox($post){
        // $value = get_post_meta( $post->ID, $this->meta_id, true );
        // $this->value = $value;
        ?>
        <div id="authorarchive" class="categorydiv">
            <?php
            $this->render_header($post);
            $this->render_body($post);
            $this->render_submit($post);
            ?>
        </div>
        <?php
    }

    public function render_header($post){
        global $nav_menu_selected_id;
        $removed_args = array( 'action', 'customlink-tab', 'edit-menu-item', 'menu-item', 'page-tab', '_wpnonce' );

        $this->current_tab = 'testtab';
        if ( isset( $_REQUEST['authorarchive-tab'] ) && 'admins' == $_REQUEST['authorarchive-tab'] ) {
        	$this->current_tab = 'admins';
        }elseif ( isset( $_REQUEST['authorarchive-tab'] ) && 'all' == $_REQUEST['authorarchive-tab'] ) {
        	$this->current_tab = 'all';
        }

        ?>
        <ul id="authorarchive-tabs" class="authorarchive-tabs add-menu-item-tabs">
            <li <?php echo ( 'all' == $this->current_tab ? ' class="tabs"' : '' ); ?>>
                <a class="nav-tab-link" data-type="tabs-panel-authorarchive-all" href="<?php if ( $nav_menu_selected_id ) echo esc_url( add_query_arg( 'authorarchive-tab', 'all', remove_query_arg( $removed_args ) ) ); ?>#tabs-panel-authorarchive-all">
                    <?php _e( 'View All' ); ?>
                </a>
            </li><!-- /.tabs -->

            <li <?php echo ( 'admins' == $this->current_tab ? ' class="tabs"' : '' ); ?>>
                <a class="nav-tab-link" data-type="tabs-panel-authorarchive-admins" href="<?php if ( $nav_menu_selected_id ) echo esc_url( add_query_arg( 'authorarchive-tab', 'admins', remove_query_arg( $removed_args ) ) ); ?>#tabs-panel-authorarchive-admins">
                    <?php _e( 'Admins' ); ?>
                </a>
            </li><!-- /.tabs -->
        </ul>
        <?php
    }

    public function render_body($post){
        ?>
        <div id="tabs-panel-authorarchive-admins" class="tabs-panel tabs-panel-view-admins <?php echo ( 'testtab' == $this->current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' ); ?>">
        	<ul id="authorarchive-checklist-admins" class="categorychecklist form-no-clear">
                <?php $this->render($post); ?>
        	</ul>
        </div><!-- /.tabs-panel -->
        <?php
    }

    public function render_submit(){

    }

    // public function save_metabox( $post_id, $post ) {
    //
    //     // /* Verify the nonce before proceeding. */
    //     // if ( !isset( $_POST[$this->id . '_nonce'] ) || !wp_verify_nonce( $_POST[$this->id . '_nonce'], basename( __FILE__ ) ) )
    //     //     return $post_id;
    //
    //     //JSONS Values in the $_POST get scaped quotes. That makes json_decode
    //     //not recognize the content as jsons. THE PROBLEM is that it also eliminates
    //     //th the '\' in the values of the JSON.
    //     //$_POST = array_map( 'stripslashes_deep', $_POST );
    //     //echo "-----------METABOX SAVING PROCCESS----------------<br><br>";
    //     $new_meta_value = null;
    //     if(isset($_POST[$this->id])){
    //         $new_meta_value = $this->get_sanitized_value($_POST[$this->id], array(
    //             'unslash_group'             => true,
    //             'escape_child_slashes'      => true,
    //             'unslash_repeater_slashes'   => true,
    //         ));
    //     }
    //
    //
    //     // if($this->id == 'rb-test-groups-repeater'){
    //     //     echo "New value: "; var_dump($new_meta_value); echo "<br>";
    //     //     errr();
    //     // }
    //
    //     /* Get the meta key. */
    //     $meta_key = $this->meta_id;
    //
    //     /* Get the meta value of the custom field key. */
    //     $meta_exists = $this->meta_exists($post_id);
    //     $meta_value = get_post_meta( $post_id, $meta_key, true );
    //
    //     //echo "Sanitized value: "; var_dump($new_meta_value); echo "<br>";
    //     //asdasd3453();
    //
    //     // If the new value is not null
    //     if( isset($new_meta_value) ){
    //         /* If a new meta value was added and there was no previous value, add it. */
    //         if( !$meta_exists )
    //             add_post_meta( $post_id, $meta_key, $new_meta_value, true );
    //         /* If the new meta value does not match the old value, update it. */
    //         else if( $new_meta_value != $meta_value )
    //             update_post_meta( $post_id, $meta_key, $new_meta_value );
    //     }
    //     /* If there is no new meta value but an old value exists, delete it. */
    //     else if ( $meta_exists )
    //         delete_post_meta( $post_id, $meta_key, $meta_value );
    //
    // }

    // public function add_metabox_classes(){
    //     /**
    //      * {post_type_name}     The name of the post type
    //      * {metabox_id}         The ID attribute of the metabox
    //      *
    //      * @param   array   $classes    The current classes on the metabox
    //      * @return  array               The modified classes on the metabox
    //     */
    //     if( is_array($this->metabox_settings['classes']) ){
    //         add_filter( "postbox_classes_{$this->metabox_settings['admin_page']}_{$this->id}", function( $classes = array() ){
    //             foreach ( $this->metabox_settings['classes'] as $class ) {
    //                 if ( ! in_array( $class, $classes ) ) {
    //                     $classes[] = sanitize_html_class( $class );
    //                 }
    //             }
    //             return $classes;
    //         });
    //     }
    // }

    // protected function meta_exists($post_id){
    //     return metadata_exists( 'post', $post_id, $this->id );
    // }
}
