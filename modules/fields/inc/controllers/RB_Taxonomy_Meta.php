<?php
class RB_Taxonomy_Form_Field{
    public $terms;
    public $render_nonce = true;
    public $add_form = false;
    public $metabox_settings = array(
        'admin_page'	=> 'post',
        'context'		=> 'advanced',
        'priority'		=> 'default',
        'classes'		=> '',
        'terms'         => array('post_tag'),
        'column'        => null,
    );

    public function __construct($id, $metabox_settings, $control_settings) {
        $this->metabox_settings = array_merge($this->metabox_settings, $metabox_settings);
        $this->control_settings = $control_settings;
        $this->meta_id = $this->metabox_settings['meta_id'] = $id;
        $this->add_form = isset($this->metabox_settings['add_form']) ? $this->metabox_settings['add_form'] : null;
        $this->terms = isset($this->metabox_settings['terms']) ? $this->metabox_settings['terms'] : null;
        $this->set_field_controller();
        $this->register_form_field();
    }

    // Sets the instance of the controller for the field to display
    public function set_field_controller($value = null){
        $this->field_controller = new RB_Field_Factory($this->meta_id, $value, $this->control_settings);
    }

    // =========================================================================
    // REGISTER
    // =========================================================================
    public function register_form_field(){
        $this->add_form_actions();
        $this->add_table_column_actions();
    }

    // =========================================================================
    // ADD AND EDIT FORM ACTIONS
    // =========================================================================
    protected function add_form_actions(){
        foreach( $this->terms as $term_slug ){
            add_action( $term_slug . "_edit_form_fields", array($this, 'edit_form_fields_row') );
            if( $this->add_form )
                add_action( $term_slug . "_add_form_fields", array($this, 'add_form_fields_container') );
        }
        add_action('edited_term', array($this, 'save_extra_term_fields'), 10, 2);
        add_action("created_term", array($this, 'save_extra_term_fields') );
    }

    //Displays the table row for the edit form
    public function edit_form_fields_row($term_obj){
        $this->update_value($term_obj);
        ?>
        <tr class="form-field rb-tax-form-field">
            <th scope="row" valign="top"><label for="<?php echo $this->meta_id; ?>"><?php _e( $this->metabox_settings['title'] ); ?></label></th>
            <td>
                <?php $this->edit_form_fields_container($term_obj); ?>
            </td>
        </tr>
        <?php
    }

    //Renders the control
    public function edit_form_fields_container($term_obj){
        $this->update_value($term_obj);
        ?>
        <div class="rb-tax-field">
            <?php $this->field_controller->render($term_obj); ?>
        </div>
        <?php
    }

    //Displays the control on the add term form
    public function add_form_fields_container($term_obj){
        $this->update_value( $term_obj );
        ?>
        <div class="form-field add-form-field <?php echo $this->metabox_settings['term_add_container_class']; ?>">
            <label for="tag-description"><?php echo $this->metabox_settings['title']; ?></label>
            <?php $this->term_add_form_fields($term_obj); ?>
        </div>
        <?php
    }

    // =============================================================================
    // COLUMN FILTERS
    // =============================================================================

    // GETTERS
    // =========================================================================
    protected function get_column_name(){
        if( isset($this->metabox_settings['column']) && is_array( $this->metabox_settings['column'] ) && $this->metabox_settings['column'][1] )
            return $this->metabox_settings['column'][1];
        return false;
    }

    protected function get_column_id(){
        if( isset($this->metabox_settings['column']) && is_array( $this->metabox_settings['column'] ) && $this->metabox_settings['column'][0] )
            return $this->metabox_settings['column'][0];
        return false;
    }

    // FILTERS
    // =========================================================================
    protected function add_table_column_actions(){
        if( $this->get_column_id() ){
            foreach( $this->terms as $term_slug){
                add_filter('manage_edit-'.$term_slug.'_columns', array($this, 'manage_taxonomy_columns') );
                add_filter('manage_'.$term_slug.'_custom_column', array($this, 'manage_taxonomy_columns_fields_container') , 10, 3);
            }
        }
    }

    /*Adds the column of this control to the taxonomies list*/
    public function manage_taxonomy_columns($columns){
        return array_merge( $columns, array( $this->get_column_id() =>  $this->get_column_name() ) );
    }

    /*Wraps the control column content*/
    public function manage_taxonomy_columns_fields_container($deprecated, $column_id, $term_id){
        if ( $column_id == $this->get_column_id() ):
            $this->update_value($term_obj);
        ?>
            <div class="rb-taxonomy-column-content <?php echo $this->metabox_settings['column_class']; ?>">
            <?php
                if ($column_id == $this->get_column_id() ){
                    $this->manage_taxonomy_columns_fields($deprecated, $column_id, $term_id);
                }
            ?>
            </div>
        <?php
        endif;
    }

    // =============================================================================
    // SAVE
    // =============================================================================
    public function save_extra_term_fields( $term_id ) {
        /* Verify the nonce before proceeding. */
        // if ( !isset( $_POST[$this->meta_id . '_nonce'] ) || !wp_verify_nonce( $_POST[$this->meta_id . '_nonce'], basename( __FILE__ ) ) )
        //     return $term_id;

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
        $meta_exists = $this->meta_exists($term_id);
        $meta_value = get_term_meta( $term_id, $meta_key, true );

        // If the new value is not null
        if( isset($new_meta_value) ){
            /* If a new meta value was added and there was no previous value, add it. */
            if( !$meta_exists )
                add_term_meta( $term_id, $meta_key, $new_meta_value, true );
            /* If the new meta value does not match the old value, update it. */
            else if( $new_meta_value != $meta_value )
                update_term_meta( $term_id, $meta_key, $new_meta_value );
        }
        /* If there is no new meta value but an old value exists, delete it. */
        else if ( $meta_exists )
            delete_term_meta( $term_id, $meta_key, $meta_value );

    }
    // =========================================================================
    // METHODS
    // =========================================================================
    protected function update_value($term){
        $term_id;
        if(is_object($term))
            $term_id = $term->term_id;
        else
            $term_id = $term;
        if( $this->meta_exists($term_id) )
            $this->field_controller->set_value( get_term_meta($term_id, $this->meta_id, true) );
    }

    protected function meta_exists($term_id){
        return metadata_exists( 'term', $term_id, $this->meta_id );
    }

    // =========================================================================
    // EXTENDABLE METHODS
    // =========================================================================
    public function term_add_form_fields($term_obj){
        $this->edit_form_fields_container($term_obj);
    }
    public function manage_taxonomy_columns_fields($deprecated, $column_id, $term_id){
        echo $this->field_controller->value;
    }

}
