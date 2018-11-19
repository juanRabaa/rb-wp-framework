<?php
if( !class_exists('RB_Taxonomy_Form') ){

    if(!function_exists('wp_get_attachment')){
        function wp_get_attachment( $attachment_id ) {
            $attachment = get_post( $attachment_id );
            return array(
                'id'	=> $attachment_id,
                'thumbnail' => wp_get_attachment_thumb_url( $attachment_id ),
                'title' => $attachment->post_title,
                'caption' => $attachment->post_excerpt,
                'description' => $attachment->post_content,
                'link' => get_permalink( $attachment->ID ),
                'url' => $attachment->guid,
                'type' => get_post_mime_type( $attachment_id ),
                'alt' => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
                'video_url'	=> get_post_meta( $attachment->ID, 'rb_media_video_url', true ),
            );
        }
    }
    
    function rb_tax_scripts() {
        wp_enqueue_style( 'rb_taxonomies', get_template_directory_uri() . '/inc/rb-wordpress-framework/taxonomies/style.css' );
        wp_enqueue_script( "rb_taxonomies", get_template_directory_uri(). '/inc/rb-wordpress-framework/taxonomies/rb-tax-gallery-control.js', true );
    }
    add_action( 'admin_enqueue_scripts', 'rb_tax_scripts' );

    class RB_Taxonomy_Form{
        // =========================================================================
        // Singleton
        // =========================================================================

    	// Contenedor de la instancia del singleton
        private static $instancia;

        // Un constructor privado evita la creación de un nuevo objeto
        private function __construct() {
        }

        // método singleton
        public static function singleton(){
            if (!isset(self::$instancia)){
                $miclase = __CLASS__;
                self::$instancia = new $miclase;
            }
            return self::$instancia;
        }

    	// Evita que el objeto se pueda clonar
        public function __clone(){
            trigger_error('La clonación de este objeto no está permitida', E_USER_ERROR);
        }

        // =========================================================================
        //
        // =========================================================================
        public function add_field($id, $args = array(), $control_class = ''){
            //If a control class wasnt given
            if ( $control_class == '')
                $control_class = 'RB_Taxonomy_Form_Basic';
            $control = new $control_class($id, $args);
            $control->initialize();
        }

        public function add_group($id, $group_args = array(), $fields_args = array()){
            $control = new RB_Taxonomy_Fields_Group($id, $group_args, $fields_args);
            $control->initialize();
        }

        public function add_repeater($id, $repeater_args = array(), $fields_args = array()){
            $control = new RB_Taxonomy_Repeater($id, $repeater_args, $fields_args);
            $control->initialize();
        }
    }

    abstract class RB_Taxonomy_Form_Control{
        public $id = '';
        public $type = 'text';
        //If set to true, the control will expect a function that will echo the
        //html for the column content
        public $column_show_value = true;
        public $column_class = '';
        public $term_edit_container_attributes = array();
        public $term_edit_container_class = '';
        public $term_add_container_class = '';
        public $collapsible_class = '';
        public $add_form = false;
        public $actions_hooks = array();
        //Defaults settings, overwritten by $args in the contructor
        public $settings = array(
            'title'                 => '',
            'description'           => '',
            'default'               => '',
            'placeholder'           => '',
            'column'                => array(),//$id, $name
            'terms'                 => array(),
            'collapsible'           => false,
            'dinamic_title'         => false,
        );

    	public function __construct($id, $args = array()){
            $this->id = $id;
            $this->settings = array_merge($this->settings, $args);
            $this->terms = $this->settings['terms'];
            $this->title = $this->settings['title'];
            if( isset($this->settings['add_form']) )
                $this->add_form = $this->settings['add_form'];
            if( isset($this->settings['collapsible']) )
                $this->collapsible = $this->settings['collapsible'];
            if( isset($this->settings['dinamic_title']) )
                $this->dinamic_title = $this->settings['dinamic_title'];
    	}

        public function initialize(){
            if($this->terms)
                $this->add_control_actions();
        }

        // =========================================================================
        // COLUMNS
        // =========================================================================
        protected function get_column_name(){
            if( $this->settings['column'] && is_array( $this->settings['column'] ) && $this->settings['column'][1] )
                return $this->settings['column'][1];
            return false;
        }

        protected function get_column_id(){
            if( $this->settings['column'] && is_array( $this->settings['column'] ) && $this->settings['column'][0] )
                return $this->settings['column'][0];
            return false;
        }

        // =========================================================================
        // ACTIONS HOOKS
        // =========================================================================
        protected function add_control_actions(){
            $this->add_form_actions();
            $this->add_table_column_actions();
        }

        protected function add_form_actions(){
            foreach( $this->terms as $term_slug){
                add_action( $term_slug . "_edit_form_fields", array($this, 'term_edit_form_fields_row') );
                if( $this->add_form )
                    add_action( $term_slug . "_add_form_fields", array($this, 'term_add_form_fields_container') );
            }
            add_action('edited_term', array($this, 'save_extra_term_fields'), 10, 2);
            add_action ("created_term", array($this, 'save_extra_term_fields') );
        }

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
                $this->stablish_value( $this->get_value($term_id) );
            ?>
                <div class="rb-taxonomy-column-content <?php echo $this->column_class; ?>">
                <?php
                    if ($column_id == $this->get_column_id() ){
                        $this->manage_taxonomy_columns_fields($deprecated, $column_id, $term_id);
                    }
                ?>
                </div>
            <?php
            endif;
        }

        public function term_edit_form_fields_row($term_obj, $store_value = true){
            if($store_value)
                $this->stablish_value( $this->get_value($term_obj) );
            ?>
            <tr class="form-field rb-tax-form-field <?php echo $this->term_edit_container_row_class; ?>">
                <th scope="row" valign="top"><label for="<?php echo $this->id; ?>"><?php _e( $this->get_title() ); ?></label></th>
                <td>
                    <?php $this->term_edit_form_fields_container($term_obj); ?>
                </td>
            </tr>
            <?php
        }

        public function term_edit_form_fields_container($term_obj){
            $this->run_single_arg_action('before_edit_form_fields_container', $term_obj);
            $this->manage_dinamic_title();
            $collapsible_holder_class = '';
            if($this->collapsible)
                $collapsible_holder_class = 'rb-collapsible-holder';
            ?>
            <div class="rb-tax-field <?php echo $this->term_edit_container_class . ' ' . $collapsible_holder_class; ?>"
            <?php echo $this->get_edit_container_attributes(); ?>>
                <?php
                if($this->collapsible)
                    $this->generate_collapsible( $this->get_add_form_fields_container_markup($term_obj) );
                else
                    $this->term_edit_form_fields($term_obj);
                ?>
                <?php $this->run_single_arg_action('edit_form_fields_container_bottom', $term_obj); ?>
            </div>
            <?php
        }

        public function term_add_form_fields_container($term_obj, $store_value = true){
            if($store_value)
                $this->stablish_value( $this->get_value($term_obj) );
            $this->run_single_arg_action('before_add_form_fields_container', $term_obj);
            ?>
            <div class="form-field add-form-field <?php echo $this->term_add_container_class; ?>">
                <label for="tag-description"><?php echo $this->title; ?></label>
                <?php $this->term_add_form_fields($term_obj); ?>
            </div>
            <?php
        }

        public function save_extra_term_fields( $term_id ) {
            //if ( isset( $_POST[$this->id] ) ) {
                update_term_meta($term_id, $this->id, $_POST[$this->id]);
            //}
        }
        // =========================================================================
        // METHODS
        // =========================================================================
        public function get_value($term){
            $term_id;
            if(is_object($term))
                $term_id = $term->term_id;
            else
                $term_id = $term;
            $value = get_term_meta($term_id, $this->id, true);
            $value = $this->run_single_arg_action('filter_value', $value);
            return $value;
        }

        public function get_title(){
            return $this->settings['title'];
        }

        public function get_description(){
            return $this->settings['description'];
        }

        public function generate_collapsible($body){
            ?>
            <div class="rb-collapsible-title">
                <span class="rb-title"><?php echo $this->title; ?></span>
                <div class="rb-field-controls">
                    <span class="rb-collapsible-button"><i class="fas fa-chevron-down"></i></span>
                    <?php if( $this->delete_field_available ): ?>
                    <span class="rb-tax-delete-button"><i class="fas fa-trash"></i></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="rb-tax-fields-group rb-collapsible-body group <?php echo $this->collapsible_class; ?>">
                <?php  echo $body?>
            </div>
            <?php
        }

        public function get_add_form_fields_container_markup($term_obj){
            ob_start();
            $this->term_edit_form_fields($term_obj);
            return ob_get_clean();
        }

        protected function add_action($hook, $action){
            if ( is_callable($action) ){
                if ( !$this->actions_hooks[$hook] )
                    $this->actions_hooks[$hook] = array();
                array_push($this->actions_hooks[$hook], $action);
            }
        }

        protected function run_single_arg_action($hook, $a){
            $result = $a;
            if ( $this->actions_hooks[$hook] ){
                foreach( $this->actions_hooks[$hook] as $action ){
                    if ( is_callable($action) )
                        $result = $action($a);
                }
            }
            return $result;
        }

        protected function stablish_value($value){
            $this->value = $value;
        }

        protected function add_edit_container_attribute($name, $value){
            if( $name )
                $this->term_edit_container_attributes[$name] = $value;
        }

        protected function get_edit_container_attributes(){
            $result = '';
            if( is_array($this->term_edit_container_attributes) ){
                foreach($this->term_edit_container_attributes as $attr_name => $attr_value){
                    $result .= $attr_name . '="' . $attr_value . '" ';
                }
            }
            return $result;
        }

        public function manage_dinamic_title(){
            if($this->dinamic_title){
                $this->add_edit_container_attribute('rb-data-dinamic-title', $this->id);
                $this->add_edit_container_attribute('rb-data-base-title', $this->title);
            }
        }
        // =========================================================================
        // EXTENDABLE METHODS
        // =========================================================================
        abstract public function term_edit_form_fields($term_obj);
        public function term_add_form_fields($term_obj){
            $this->term_edit_form_fields_container($term_obj);
        }
        public function manage_taxonomy_columns_fields($deprecated, $column_id, $term_id){
            echo $this->value;
        }
    }

    // =============================================================================
    // FIELDS GROUP CLASS
    // =============================================================================
    class RB_Taxonomy_Fields_Group extends RB_Taxonomy_Form_Control {
        //Array (field_id => field_data)
        public $fields = array();
        //Group is collapsable as default, set to false to show all inputs at once
        public $collapsible = true;
        public $collapsible_class = 'rows-separator';

        public function __construct($id, $group_args = array(), $fields_args = array()){
            $this->settings['collapsible'] = true;
            $this->settings['rows_separator'] = true;
            parent::__construct($id, $group_args);
            $this->column_show_value = false;

            if( !$this->settings['rows_separator']  )
                $collapsible_class = '';

            if( is_string($this->dinamic_title) ){
                $this->dinamic_title = $this->id . '__' . $this->dinamic_title;
            }

            foreach($fields_args as $field_id => $field_args){
                $field_id = $id . '__' . $field_id;
                $field_args;
                $control_class = $field_args['rb_control'] ? $field_args['rb_control'] : '';

                if ( $control_class == '')
                    $control_class = 'RB_Taxonomy_Form_Basic';
                $control = new $control_class($field_id, $field_args);
                $control->column = $field_args['column'];
                $this->fields[$field_id] = $control;
            }
        }

        public function get_field_value($field_id, $term_obj){

            $group_fields_values = $this->value;
            //print_r($group_fields_values); echo str_replace($this->id . '__', '', $field_id);
            if( is_array($group_fields_values) ){
                return $group_fields_values[str_replace($this->id . '__', '', $field_id)];
            }
            return '';
        }

        public function term_edit_form_fields($term_obj){
                    //print_r( $this->value );
            foreach($this->fields as $field_id => $field):
                $field->value = $this->get_field_value($field_id, $term_obj);
                ?>
                    <div class="rb-tax-field-container">
                        <p class="rb-tax-field-title"><?php echo $field->title; ?></p>
                        <?php $field->term_edit_form_fields_container($term_obj); ?>
                    </div>
                <?php
            endforeach;
        }

        public function manage_taxonomy_columns_fields($deprecated, $column_id, $term_id)
        {
            foreach($this->fields as $field_id => $field):
                if($field->column):
                    $field->value = $this->get_field_value($field_id, $term_id);
                    ?>
                    <div class="rb-column-group-field-value">
                    <?php $field->manage_taxonomy_columns_fields($deprecated, $column_id, $term_id); ?>
                    </div>
            <?php
                endif;
            endforeach;
        }

        public function save_extra_term_fields( $term_id ) {
            $values = array();
            foreach( $this->fields as $field_id => $field_data ){
                if ( isset($_POST[$field_id]) )
                    $values[str_replace($this->id . '__', '', $field_id)] = $_POST[$field_id];
            }

            update_term_meta($term_id, $this->id, $values);
        }

        public function get_fields_ids(){
            $ids = array();
            foreach($this->fields as $field)
                array_push($ids, $field->id);
            return $ids;
        }

        public function manage_dinamic_title(){
            if($this->dinamic_title){
                $this->add_edit_container_attribute('rb-data-dinamic-title', $this->dinamic_title);
                $this->add_edit_container_attribute('rb-data-base-title', $this->title);
            }
        }
    }

    // =============================================================================
    // REPEATER GROUP CLASS
    // =============================================================================
    class RB_Taxonomy_Repeater extends RB_Taxonomy_Form_Control {
        public $control = array();
        public $placeholder_control = null;
        public $collapsible = false;
        public $collapsible_class = 'rows-separator';
        public $control_is_group = false;
        public $control_fields_args = array();
        public $repeater_args = array();
        public $group_settings = array(
            'title'         => '',
            'description'   => '',
            'dinamic_title' => false,
            'collapsible'   => true,
        );

        public function __construct($id, $repeater_args = array(), $fields_args = array()){
            $this->settings['collapsible'] = false;
            $this->settings['rows_separator'] = true;
            parent::__construct($id, $repeater_args);
            $this->column_show_value = false;
            $this->control_fields_args = $fields_args;
            $this->repeater_args = $repeater_args;

            if(is_array($repeater_args['group_settings'])){
                $this->group_settings['column'] = $this->settings['column'];
                $this->group_settings = array_merge($this->group_settings,$repeater_args['group_settings']);
            }

            if( !$this->settings['rows_separator']  )
                $collapsible_class = '';

            reset($fields_args);
            if( is_array(current($fields_args)) ){//Es grupo
                $this->control_is_group = true;
                $this->placeholder_control = new RB_Taxonomy_Fields_Group($id, $this->group_settings, $fields_args);
            }
            else if( is_string($fields_args[0]) ){//Es solo un field
                $field_id = $id . '__' . $fields_args[0];
                $args = $fields_args[1];
                $control_class = $fields_args[2] ? $fields_args[2] : '';

                if ( $control_class == '')
                    $control_class = 'RB_Taxonomy_Form_Basic';

                $this->placeholder_control = new $control_class($field_id, $args);
            }
        }

        //If control is a group, returns array with fields ids
        //If control is a single input, return its id string
        public function get_control_ids(){
            if( $this->control_is_group )
                return $this->placeholder_control->get_fields_ids();
            else
                return $this->placeholder_control->id;
        }

        public function get_control_value($term_obj, $nth){
            $controls_values = $this->value;
            $result = '';
            if( $this->control_is_group && is_array( $controls_values[$nth] ) ){
                $group_values = $controls_values[$nth];
                $saved = array();
                foreach($group_values as $old_id => $field_value){
                    $new_id = $this->reverse_repeater_id($old_id);
                    $new_id = $new_id . '__nth' . $nth;
                    //echo $new_id; echo "  "; echo $field_value; echo "<br>";
                    if( isset($group_values[$new_id]) ){
                        $saved[$old_id] = $new_id;
                    }
                    else {//we add the new id, and remove the old
                        $group_values[$new_id] = $field_value;
                        unset($group_values[$old_id]);
                    }
                }
                $result = $group_values;
            }
            else if (is_array( $controls_values )){
                $result = $controls_values[$nth];
            }
            //print_r($result);
            return $result;
        }

        public function term_edit_form_fields($term_obj){
            //print_r($this->value);
            $accordion_attr = '';
            if ( $this->placeholder_control->collapsible === 'accordion' )
                $accordion_attr = 'rb-collapsibles-accordion';
            ?>
            <div class="rb-tax-repeated" rb-data-ids="<?php echo $this->get_all_ids(); ?>" rb-data-control-placeholder="<?php echo $this->get_control_placeholder(); ?>">
                <div class="rb-tax-repeated-controls" <?php echo $accordion_attr; ?>>
                    <?php
                    $nth = 0;
                    if($this->value){
                        foreach($this->value as $control_value){
                            $this->generate_control_field($term_obj, $nth);
                            $nth++;
                        }
                    }
                    ?>
                </div>
                <div class="rb-add-item-container"><i class="fas fa-plus rb-add-item-button"></i></div>
                <input class="rb-tax-value rb-tax-repeater-order" type="hidden" name="<?php echo $this->id; ?>" id="<?php echo $this->id; ?>" value="<?php echo $this->get_initial_order(); ?>">
            </div>
            <?php
        }

        protected function get_initial_order(){
            $order = '';
            if($this->value){
                $amount_of_vals = count($this->value);
                for($i = 0; $i < $amount_of_vals; $i++){
                    $order .= $i;
                    if($i != ($amount_of_vals-1))
                        $order .= ',';
                }
            }
            return $order;
        }

        protected function generate_control_field($term_obj, $nth){
            $control = $this->generate_repeater_item($term_obj, $nth);
            $collapsible_class = $control->collapsible ? 'rb-tax-repeater-field-collapsible ' : 'rb-tax-repeater-field-no-collapsible ';
            $class = ' rb-tax-repeater-field rb-tax-countrol-' . $nth . ' ' . $collapsible_class;
            $control->term_edit_container_class .= $class;
            $control->add_edit_container_attribute('rb-tax-repeater-id', $nth);
            ?>
                <?php $control->term_edit_form_fields_container($term_obj); ?>

            <?php
        }

        protected function get_control_placeholder(){
            ob_start();
            $this->generate_control_field(null, '__rb_placeholder_replace');
            return htmlentities(ob_get_clean());
        }

        protected function generate_repeater_item($term_obj, $nth){
            $control = null;
            if( $this->control_is_group ){
                $control = new RB_Taxonomy_Fields_Group($this->id, $this->group_settings, $this->control_fields_args);
                $control = $this->adapt_control_to_repeater($control, $term_obj, $nth);
                //print_r($control->value);
                array_push( $this->control, $control );
                //$control->term_edit_form_fields_container($term_obj);
            }
            else{
                $field_id = $this->control_fields_args[0];
                $args = $this->control_fields_args[1];
                $control_class = $this->control_fields_args[2] ? $this->control_fields_args[2] : '';

                if ( $control_class == '')
                    $control_class = 'RB_Taxonomy_Form_Basic';

                $control = new $control_class($field_id, $args);
                $control = $this->adapt_control_to_repeater($control, $term_obj, $nth);
                array_push( $this->control, $control );
                //$control->term_edit_form_fields_container($term_obj);
                $nth++;
            }
            return $control;
        }

        protected function adapt_control_to_repeater($control, $term_obj, $nth){
            $control = $this->change_fields_id($control, $nth);
            $control->title = str_replace('/count/',$nth,$control->title);
            $control->dinamic_title .= '__nth' . $nth;
            $control->value = $this->get_control_value($term_obj, $nth);
            $control->delete_field_available = true;
            if(!$control->collapsible)
                $control->add_action('edit_form_fields_container_bottom', array($this, 'delete_field_control') );
            //$collapsible_class = $control->collapsible ? 'rb-tax-repeater-field-collapsible ' : '';
            //$control->term_edit_container_class .= 'rb-tax-repeater-field rb-tax-countrol-' . $nth . ' ' . $collapsible_class;
            //$control->add_edit_container_attribute('rb-tax-repeater-id', $nth);
            return $control;
        }

        protected function delete_field_control($term_obj){
            ?>
            <div class="rb-field-controls">
                <span class="rb-tax-delete-button rb-tax-repeater-delete-button"><i class="fas fa-trash"></i></span>
            </div>
            <?php
        }

        protected function change_fields_id($control, $nth){

            if( $this->control_is_group ){
                $saved = array();
                foreach( $control->fields as $old_id => $field_data ){
                    $new_id = $old_id . '__nth' . $nth;
                    //If there is already a field with this id, thing that is very odd,
                    //we cant change the current field id, it would be overwriting this other
                    //field, so we save it in a backup and add it later when is safe to do so
                    if( $control->fields[$new_id] ){
                        $saved[$old_id] = $new_id;
                    }
                    else {//we add the new id, and remove the old
                        $control->fields[$new_id] = $field_data;
                        $control->fields[$new_id]->id = $new_id;
                        unset($control->fields[$old_id]);
                    }
                }
                //Now we do the same with the ones backed up before, as there are no
                //possible repeted ids
                foreach( $saved as $old_id => $new_id ){
                    if( $control->fields[$old_id] ){
                        $control->fields[$new_id] = $control->fields[$old_id];
                        $control->fields[$new_id]->id = $new_id;
                        unset($control->fields[$old_id]);
                    }
                }
            }
            else{
                $control->id = $this->id . '__' . $control->id . '__nth' . $nth;
            }
            return $control;
        }

        public function manage_taxonomy_columns_fields($deprecated, $column_id, $term_id)
        {   //print_r($this->value);
            $nth = 0;
            if($this->value){
                foreach($this->value as $control_value){?>
                    <div class="rb-tax-repeater-column-group">
                    <?php
                        $control = $this->generate_repeater_item($term_id, $nth);
                        $control->manage_taxonomy_columns_fields($deprecated, $column_id, $term_id);
                        $nth++;
                    ?>
                    </div>
                    <?php
                }
            }
        }

        protected function transform_to_repeater_id($field_id, $nth, $only_nth = false){
            $id = $field_id . '__nth' . $nth;
            if( !$only_nth )
                $id = $this->id . '__' . $id;
            return $id;
        }

        protected function reverse_repeater_id($field_id){
            $clean_id = '';
            $clean_id = str_replace($this->id . '__', '', $field_id);
            $clean_id = preg_replace("/__nth\d*(?!.*__nth\d*)/", '', $clean_id);
            return $clean_id;
        }

        protected function get_all_ids(){
            $ids = array();
            if( $this->control_is_group ){
                foreach( $this->placeholder_control->fields as $field_id => $field_data ){
                    array_push($ids, $field_id);
                }
            }
            return implode(',', $ids);
        }

        public function save_extra_term_fields( $term_id ) {
            $values = array();
            $searching = true;
            $base_ids = $this->get_control_ids();
            print_r($base_ids);
            echo "<br>";
            echo "<br>";
            print_r($_POST);
            echo "<br>";
            echo "<br>";

            $order = array();
            if(isset($_POST[$this->id]))
                $order = str_getcsv($_POST[$this->id]);

            print_r($order);
            echo "<br>";
            echo "<br>";
            $i = 0;
            if($order){
                while($searching){
                    if(is_array($base_ids)){
                        foreach($base_ids as $field_id){
                            $processed_id = $field_id . '__nth' . $order[$i];
                            echo $processed_id . ': ' . $_POST[$processed_id];
                            echo "<br>";
                            echo "<br>";
                            if ( isset($_POST[$processed_id]) ){
                                if( !$values[$i] )
                                    $values[$i] = array();
                                $values[$i][$field_id] = $_POST[$processed_id];
                            }
                        }
                    }
                    else if(is_string($base_ids)){
                        $processed_id = $base_ids . '__nth' . $order[$i];
                        echo $processed_id . ': ' . $_POST[$processed_id];
                        echo "<br>";
                        echo "<br>";
                        if ( isset($_POST[$processed_id]) ){
                            if( !$values[$i] )
                                $values[$i] = $_POST[$processed_id];
                        }
                    }

                    if( !isset($order[$i + 1]) )
                        $searching = false;
                    else
                        $i++;
                }
            }
            print_r($values);
            //errereerererer();
            update_term_meta($term_id, $this->id, $values);
        }
    }

    // =============================================================================
    // =============================================================================
    // FIELDS CONTROLS
    // =============================================================================
    // =============================================================================

    // =============================================================================
    // BASICS INPUTS CONTROL
    // =============================================================================
    //This control is used as default if no other control class is given when padding
    //a field. For it to work properly, it needs no know the $type of the input, and
    //some other customizable options
    class RB_Taxonomy_Form_Basic extends RB_Taxonomy_Form_Control {
        public $type;
        public $choices = array();
        public $choice_none = array('None', false);//Option name, value
        public $max;
        public $min;

        public function __construct($id, $args = array()){
            $this->type = $args['type'];
            $this->choices = $args['choices'];
            $this->max = $args['max'];
            $this->min = $args['min'];
            if($args['choice_none'])
                $this->choice_none = $args['choice_none'];
            parent::__construct($id, $args);
        }


        public function term_edit_form_fields($term_obj){
            $value = $this->value;
            $extra_attr = '';
            if ( $this->type == 'text' || $this->type == 'number' || $this->type == 'email' || $this->type == 'date' || $this->type == 'checkbox' ):
                //Checkbox attributes
                if( ($this->type == 'checkbox') && $value )
                    $extra_attr .= 'checked ';
                //Number attributes
                if( ($this->type == 'number') ){
                    if(isset($this->min))
                        $extra_attr .= "min='$this->min' ";
                    if(isset($this->max))
                        $extra_attr .= "max='$this->max' ";
                }
            ?>
                <input name="<?php echo $this->id; ?>" id="<?php echo $this->id; ?>" value="<?php echo $value ?>" type='<?php echo $this->type; ?>' <?php echo $extra_attr; ?> size="40" aria-required="true">
                <p class="description"><?php _e( $this->get_description() ); ?></p>
            <?php
            elseif ( $this->type == 'textarea' ):
            ?>
            <textarea name="<?php echo $this->id; ?>" id="<?php echo $this->id; ?>" value="<?php echo $value ?>" <?php echo $extra_attr; ?> rows="5" cols="50"><?php echo $value ?></textarea>
            <p class="description"><?php _e( $this->get_description() ); ?></p>
            <?php
            elseif ( $this->type == 'select' ):
            ?>
                <select name="<?php echo $this->id; ?>" id="<?php echo $this->id; ?>" class="postform">
                	<option value="<?php echo $this->choice_none[1]; ?>"><?php echo $this->choice_none[0]; ?></option>
                    <?php
                    foreach($this->choices as $option_name => $option_value):
                        $selected = $value == $option_value ? 'selected' : '';
                    ?>
                	<option class="level-0" value="<?php echo $option_value; ?>" <?php echo $selected; ?>><?php echo $option_name; ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php _e( $this->get_description() ); ?></p>
            <?php
            elseif ( $this->type == 'radio' ):
                foreach($this->choices as $option_name => $option_value):
                    $checked = $value == $option_value ? 'checked' : '';
                ?>
                <div>
                    <input type="radio" name="<?php echo $this->id; ?>" value="<?php echo $option_value; ?>" <?php echo $checked; ?>/>
                    <label for="<?php echo $this->id; ?>"><?php echo $option_name; ?></label>
                </div>
            <?php endforeach;
            endif;
        }

        public function term_add_form_fields($term_obj){
            $this->term_edit_form_fields($term_obj);
        }

    }


    // =============================================================================
    // GALLERY FIELD CONTROL
    // =============================================================================
    class RB_Taxonomy_Gallery extends RB_Taxonomy_Form_Control {
        public $column_show_value = false;

        public function __construct($id, $args = array()){
            $this->term_edit_container_class = $this->term_add_container_class = 'rb-tax-images-control';
            parent::__construct($id, $args);
        }


        public function term_edit_form_fields($term_obj){
            $attachments_ids_csv = $this->value;
            $attachments_ids = $attachments_ids_csv ? str_getcsv($attachments_ids_csv) : array();
            ?>
            <input class="rb-tax-value" type="hidden" name="<?php echo $this->id; ?>" id="<?php echo $this->id; ?>" value="<?php echo $attachments_ids_csv ?>">
            <span class="description"><?php _e( $this->get_description() ); ?></span>
            <div class="rb-tax-images">
                <div class="rb-tax-images-boxes">
                    <?php
                    if ($attachments_ids):
                        foreach($attachments_ids as $attachment_id ):
                            $attachment = wp_get_attachment( $attachment_id );
                    ?>
                    <div class="rb-tax-image rb-gallery-box" rel="<?php echo $attachment_id; ?>" style="background-image: url(<?php echo $attachment['thumbnail']; ?>);">
                        <i class="fas fa-times rb-remove"></i>
                    </div>
                    <?php
                        endforeach;
                    endif;
                    ?>
                </div>
                <div class="rb-tax-add rb-gallery-box">
                    <i class="fas fa-plus rb-add"></i>
                </div>
            </div>
            <?php
        }

        public function term_add_form_fields($term_obj){
            $this->term_edit_form_fields($term_obj);
        }

        public function manage_taxonomy_columns_fields($deprecated, $column_id, $term_id)
        {
            if ($this->term_has_images($term_id)):
                $images_ids = $this->get_term_images_ids($term_id);
                ?>
                <div class="rb-term-column-images" rb-zoom-gallery>
                    <?php
                    foreach($images_ids as $image_id):
                        $thumb = wp_get_attachment_thumb_url( $image_id );
                        $url = wp_get_attachment_url( $image_id );
                    ?>
                        <div rb-zoom-src='<?php echo $url; ?>' class="rb-tax-image rb-gallery-box rb-image-zoom" rel="<?php echo $image_id; ?>" style="background-image: url(<?php echo $thumb; ?>);"></div>
                    <?php
                    endforeach;
                    ?>
                </div>
            <?php
            endif;
        }

        public function term_has_images($term_id){
            $attachments_ids_csv = $this->value;
            return $attachments_ids_csv != '';
        }

        public function get_term_images_ids($term_id){
            $attachments_ids_csv = $this->value;
            $attachments_ids = $attachments_ids_csv ? str_getcsv($attachments_ids_csv) : array();
            return $attachments_ids;
        }
    }
}
