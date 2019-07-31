<?php

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
        <div
        id="rb-field-control-<?php echo $this->id; ?>"
        class="rb-form-control <?php echo $this->collapsible_class(); ?> <?php echo esc_attr($this->get_container_class()); ?>"
        data-dependencies="<?php echo esc_attr($this->get_field_dependencies_attr()); ?>" <?php echo $this->get_container_attr(); ?>>
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
