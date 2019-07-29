<?php

// =============================================================================
// RB FIELD CONTROLLER
// =============================================================================
class RB_Form_Field_Controller{
    public $settings = array();
    public $controls = null;

    public function __construct($id, $value, $settings = array()) {
        $this->settings = array_merge($this->settings, $settings);
        $this->id = $this->settings['id'] = $id;
        $this->controls = isset($this->settings['controls']) && is_array($this->settings['controls']) ? $this->settings['controls'] : null;
        $this->value = $value;
        $this->generate_control();
    }

    //Renders the controller accordingly to the settings passed
    public function render($post = null){
        //print_r($this->controls);
        $rb_control = $this->generate_control();
        if($rb_control)
            $rb_control->render();
    }

    public function generate_control(){
        $this->control = null;
        if($this->is_repeater()){
            $this->control = new RB_Form_Repeater_Field($this->id, $this->value, $this->get_repeater_settings(), $this->settings, $this->controls);
        }
        else if( $this->is_group() ){
            $this->control = new RB_Form_Group_Field($this->id, $this->value, $this->settings, $this->controls);
        }
        //Generates the controler when only one control was provided
        else{
            $this->control = new RB_Form_Single_Field($this->id, $this->value, $this->settings, $this->get_first_control());
        }
        return $this->control;
    }

    // =============================================================================
    // GETTERS
    // =============================================================================

    //Get the first control in the $controls array. The one that would be used in a single field
    public function get_first_control(){ foreach($this->controls as $control) return $control; }

    //Returns the value of one of the settings
    public function get_setting( $name, $default = null ){
        return isset($this->settings[$name]) ? $this->settings[$name] : $default;
    }

    public function get_repeater_settings(){
        return $this->is_repeater() && is_array($this->settings['repeater']) && !empty($this->settings['repeater']) ? $this->settings['repeater'] : array();
    }

    public function get_sanitized_value($value){
        return $this->control->get_sanitized_value($value);
    }

    final static function get_input_class_link(){
        return 'wpb_vc_param_value';
    }

    final static function get_control_input_link(){
        return "rb-control-value";
    }

    // =========================================================================
    // METHODS
    // =========================================================================
    public function is_group(){
        return is_array($this->controls) && count($this->controls) > 1;
    }

    public function is_repeater(){
        return isset($this->settings['repeater']) &&
        ( $this->settings['repeater'] === true || (is_array($this->settings['repeater']) && !empty($this->settings['repeater'])) );
    }
}

// =============================================================================
// RB FIELD ABSTRACT
// =============================================================================
abstract class RB_Form_Field_Control{
    public $settings = array();

    public function __construct($id, $value, $settings = array()) {
        $this->id = $id;
        $this->value = $value;
        $this->settings = wp_parse_args($this->settings, $settings);
    }

    public function render($post = null){
        $title = $this->get_title();
        ?>
        <div class="rb-form-control <?php echo $this->collapsible_class(); ?> <?php echo esc_attr($this->get_container_class()); ?>"
        data-dependencies="<?php echo esc_attr($this->get_field_dependencies_attr()); ?>" <?php echo esc_attr($this->get_container_attr()); ?>>
            <?php if($title): ?>
            <div class="control-header rb-collapsible-header">
                <h1 data-title="Red Social 3" class="title"><?php echo esc_html($title); ?></h1>
            </div>
            <?php endif; ?>
            <div class="control-body rb-collapsible-body">
                <?php echo $this->print_description(); ?>
                <?php $this->render_field($post); ?>
            </div>
        </div>
        <?php
    }

    abstract public function render_field($post = null);

    abstract public function get_container_class();

    public function collapsible_class(){
        if(isset($this->settings['collapsible']) && ( $this->settings['collapsible'] === true || is_array($this->settings['collapsible']) && !empty($this->settings['collapsible']) ) )
            echo 'rb-collapsible';
    }

    public function get_container_attr(){ return ""; }

    public function print_description(){
        $description = $this->get_description();
        if($description):
        ?><p class="control-description"><?php echo esc_html($description); ?></p><?php
        endif;
    }

    public function get_description(){
        return isset($this->settings['description']) && is_string($this->settings['description']) ? $this->settings['description'] : '';
    }

    public function get_title(){
        return isset($this->settings['title']) && is_string($this->settings['title']) ? $this->settings['title'] : '';
    }

    //Returns the value of one of the settings
    public function get_setting( $name, $default = null ){
        return isset($this->settings[$name]) ? $this->settings[$name] : $default;
    }

    public function get_value(){ return $this->value; }

    public function get_field_dependencies_attr(){
        if(!isset($this->control_settings['dependencies']))
            return '';

        $dependecies = array();
        $has_operator = isset($this->control_settings['dependencies'][1]) && is_array($this->control_settings['dependencies'][1]) && is_string($this->control_settings['dependencies'][0]);
        $dependecies[0] = $has_operator ? $this->control_settings['dependencies'][0] : 'AND';
        $dependecies[1] = $has_operator ? $this->control_settings['dependencies'][1] : $this->control_settings['dependencies'];

        return json_encode($dependecies);
    }
}

// =============================================================================
// SINGLE FIELD
// =============================================================================
class RB_Form_Single_Field extends RB_Form_Field_Control{
    public $default_type = 'RB_Input_Control';

    public function __construct($id, $value, $settings = array(), $control = array()) {
        parent::__construct($id, $value, $settings);
        $this->control = $control;
        $this->value = $this->get_value();
    }

    public function render_field($post = null){
        ?>
        <div class="control-content">
        <?php
            $this->generate_renderer();
            if($this->renderer)
                $this->renderer->print_control($post);
        ?>
        </div>
        <?php
    }

    public function get_container_class(){ return "rb-form-control-single-field"; }

    public function get_value(){
        if( !isset($this->value) && is_array($this->control) && isset($this->control['default']) )
            return $this->control['default'];
        return $this->value;
    }

    public function get_id(){ return $this->id; }

    //Saves in $this->renderer the object that renders the control
    public function generate_renderer(){
        $control_class = is_array($this->control) && isset($this->control['type']) && is_string($this->control['type']) ? $this->control['type'] : $this->default_type;
        if($control_class && class_exists($control_class) && method_exists($control_class, 'render_content')){
            $this->control['type'] = $control_class;
            $this->control['id'] = $this->get_id();
            $this->renderer = new $control_class( $this->get_value(), $this->control);
        }
        else
            $this->renderer = null;
    }

    public function get_sanitized_value($value){
        return is_string($value) ? $value : '';
    }

}

// =============================================================================
// GROUP FIELD
// =============================================================================
class RB_Form_Group_Field extends RB_Form_Field_Control{

    /**
    * @param array $value
    *   Controls values.
    *   array( $control_id => $control_value, ... )
    * @param array $controls
    *   Controls information. One value for each control.
    *   array( $control_id => $control_settings, ... )
    */
    public function __construct($id, $value, $settings = array(), $controls = array()) {
        parent::__construct($id, $value, $settings);
        $this->controls = $controls;
        $this->sanitize_value();
    }

    // =========================================================================
    // GETTERS
    // =========================================================================

    /*Creates and returns the renderer for one of the child fields*/
    public function get_child_field_renderer($control_ID, $control_settings){
        $child_id = $this->get_input_id($control_ID);
        $child_value = $this->get_child_field_value($control_ID);
        $controller_settings = isset($control_settings['controller']) ? $control_settings['controller'] : array();
        $controller_settings['controls'] = isset($control_settings['controls']) ? $control_settings['controls'] : array($control_ID => $control_settings);
        //print_r($controller_settings);
        return new RB_Form_Field_Controller($child_id, $child_value, $controller_settings);
    }

    //Gets one of the group controls id, sufixing the control_id to the repeater_id
    public function get_input_id($control_id){ return $this->id . '-' . $control_id; }

    public function get_child_field_value($control_id, $default = null){
        return is_array($this->value) && isset($this->value[$control_id]) ? $this->value[$control_id] : $default;
    }

    public function get_container_class(){ return "rb-form-control-group-field"; }

    public function get_container_attr(){ return 'data-id="$this->id"'; }

    // =========================================================================
    // METHODS
    // =========================================================================

    /*The value of a group must be an array of controls values. This is taken
    *care of once the group value has been submited, when the environment compability
    *functions (customizer,taxonomy,attachment) sanitize the value before storing it.
    *When the control is used outside of a registered environment, the value doesn't get
    *sanitized, wich causes it to be a json string*/
    public function sanitize_value(){
        $this->value = $this->get_sanitized_value($this->value);
    }

    public function get_sanitized_value($value){
        if(is_string($value)){
            $json_value = json_decode($value, true);
            if(json_last_error() == JSON_ERROR_NONE)
                $value = $json_value;
        }
        return $value;
    }

    public function print_group_value_input(){
        ?>
        <input
        class="<?php echo RB_Form_Field_Controller::get_input_class_link(); ?>"
        rb-control-group-value
        name="<?php echo $this->id; ?>"
        value="<?php echo esc_attr(json_encode($this->value, JSON_UNESCAPED_UNICODE)); ?>"
        type="hidden"></input>
        <?php
    }

    public function render_field($post = null){
        if(is_array($this->controls)):
        ?>
        <div class="controls">
            <?php
            foreach($this->controls as $control_ID => $control){
                $rb_child_field = $this->get_child_field_renderer($control_ID, $control);
                ?>
                <div class="group-child-control" data-id="<?php echo $control_ID; ?>">
                    <?php $rb_child_field->render($post); ?>
                </div>
                <?php
            }
            ?>
        </div>
        <?php $this->print_group_value_input(); ?>
        <?php
        endif;
    }
}

// =============================================================================
// REPEATER
// =============================================================================
class RB_Form_Repeater_Field extends RB_Form_Field_Control{
    public $repeater_settings = array(
        'collapsible'   => true,
        'accordion'     => false,
    );

    public function __construct($id, $value, $repeater_settings, $controller_settings, $controls){
        parent::__construct($id, $value, $controller_settings);
        $this->repeater_settings = is_array($repeater_settings) ? array_merge($this->repeater_settings, $repeater_settings) : $this->repeater_settings;
        $this->controls = $controls;
        $this->sanitize_value();
    }

    public function render_field($post = null){
        if(is_array($this->controls)):
        ?>
        <div class="repeater-container">
            <div class="rb-repeater-items <?php $this->accordion_class(); ?>">
                <?php
                if(is_array($this->value) && !empty($this->value)):
                    $i = 1;
                    foreach($this->value as $item_value):
                        $this->print_item($item_value, $i, $post);
                        $i++;
                    endforeach;
                else://There is no value
                    $this->print_item(null, 1, $post);
                endif;
                ?>
            </div>
            <div class="repeater-add-button">
                <i class="add-button fas fa-plus"></i>
            </div>
        </div>
        <?php $this->print_repeater_value_input(); ?>
        <?php
        endif;
    }

    public function print_item($value, $index, $post){
        //Check if it has controller prop
        $item_settings = $this->get_item_controller_settings();
        $item_settings['controls'] = $this->controls;
        $item = new RB_Repeater_Item($this->get_item_id($index), $value, $index, $item_settings, $this->repeater_settings);
        $item->render($post);
    }

    public function get_item_controller_settings(){
        return isset($this->repeater_settings['item_controller']) && is_array($this->repeater_settings['item_controller']) ? $this->repeater_settings['item_controller'] : array();
    }

    public function get_item_id($index){ return "$this->id-$index"; }

    public function accordion_class(){ echo $this->is_accordion() ? 'rb-accordion' : ''; }

    public function is_accordion(){
        return isset($this->repeater_settings['accordion']) && $this->repeater_settings['accordion'];
    }

    public function print_repeater_value_input(){
        ?>
        <input
        class="<?php echo RB_Form_Field_Controller::get_input_class_link(); ?>"
        rb-control-repeater-value
        name="<?php echo $this->id; ?>"
        value="<?php echo esc_attr(json_encode($this->value, JSON_UNESCAPED_UNICODE)); ?>"
        type="hidden" ></input>
        <?php
    }

    public function sanitize_value(){
        $this->value = $this->get_sanitized_value($this->value);
    }

    public function get_sanitized_value($value){
        if(is_string($value)){
            $json_value = json_decode($value, true);
            if(json_last_error() == JSON_ERROR_NONE)
                $value = $json_value;
        }
        return $value;
    }

    public function get_container_class(){ return "rb-form-control-repeater-field"; }

    public function get_container_attr(){ return "data-id='$this->id'"; }
}

class RB_Repeater_Item{
    public function __construct($id, $value, $index, $item_settings, $repeater_settings){
        $this->id = $id;
        $this->value = $value;
        $this->index = $index;
        $this->repeater_settings = is_array($repeater_settings) ? $repeater_settings : array();
        $this->item_settings = $item_settings;
    }

    public function render($post = null){
        $item_controller = new RB_Form_Field_Controller($this->id, $this->value, $this->item_settings);
        $item_title = $this->get_item_title($this->index);
        ?>
        <div class="repeater-item <?php echo $this->collapsible_class(); ?>">
            <div class="item-header rb-collapsible-header">
                <?php if($item_title): ?>
                <h2 class="item-title title"><?php echo $item_title; ?></h2>
                <?php endif; ?>
                <?php $this->print_action_controls(); ?>
            </div>
            <div class="item-content rb-collapsible-body">
                <?php $item_controller->render($post); ?>
            </div>
        </div>
        <?php
    }

    public function collapsible_class(){
        return $this->is_collapsible() ? 'rb-collapsible' : '';
    }

    public function is_collapsible(){
        return isset($this->repeater_settings['collapsible']) && $this->repeater_settings['collapsible'];
    }

    public function get_item_title(){
        $base_title = isset($this->repeater_settings['item_title']) ? $this->repeater_settings['item_title'] : 'Item';
        return str_replace('($n)', $this->index, $base_title);
    }

    public function print_action_controls(){
        ?>
        <div class="action-controls">
            <div class="control delete-button red">
                <i class="fas fa-trash-alt"></i>
            </div>
        </div>
        <?php
    }
}
