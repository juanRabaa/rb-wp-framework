<?php

// =============================================================================
// REPEATER
// =============================================================================
class RB_Form_Repeater_Field extends RB_Form_Field_Control{
    public $repeater_settings = array(
        'collapsible'   => true,
        'accordion'     => false,
        'empty_message' => 'Start adding items!',
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
        <div class="repeater-container <?php echo $this->empty_class(); ?>">
            <?php $this->print_empty_message(); ?>
            <div class="rb-repeater-items <?php $this->accordion_class(); ?>">
                <?php
                if(!$this->is_empty()):
                    $i = 1;
                    foreach($this->value as $item_value):
                        $this->print_item($item_value, $i, $post);
                        $i++;
                    endforeach;
                // else://There is no value
                //     $this->print_item(null, 1, $post);
                endif;
                ?>
            </div>
            <div class="repeater-add-button">
                <i class="add-button fas fa-plus"></i>
            </div>
            <div class="repeater-empty-control">
                <?php echo esc_attr($this->print_placeholder_item($post)); ?>
            </div>
        </div>
        <?php $this->print_repeater_value_input(); ?>
        <?php
        endif;
    }

    public function print_placeholder_item($post = null){
        $this->print_item('', '__($RB_REPEATER_PLACEHOLDER)', $post);
    }

    public function get_item_controller($value, $index){
        //Check if it has controller prop
        $item_settings = $this->get_item_controller_settings();
        $item_settings['controls'] = $this->controls;
        return new RB_Repeater_Item($this->get_item_id($index), $value, $index, $item_settings, $this->repeater_settings);
    }

    public function print_item($value, $index, $post){
        $item = $this->get_item_controller($value, $index);
        $item->render($post);
    }

    public function is_empty(){ return !is_array($this->value) || empty($this->value); }

    public function empty_class(){ return $this->is_empty() ? 'empty' : ''; }

    public function get_item_controller_settings(){
        $item_controller = $this->get_repeater_setting('item_controller');
        return is_array($item_controller) ? $this->repeater_settings['item_controller'] : array();
    }

    public function get_item_id($index){ return "$this->id-$index"; }

    public function accordion_class(){ echo $this->is_accordion() ? 'rb-accordion' : ''; }

    public function is_accordion(){ return true && $this->get_repeater_setting('accordion'); }

    public function get_repeater_setting($name){ return isset($this->repeater_settings[$name]) ? $this->repeater_settings[$name] : null; }

    public function print_empty_message(){
        $message = $this->get_repeater_setting('empty_message');
        ?>
        <div class="rb-repeater-empty-message">
            <?php
            //If the message is a function
            if( is_callable($message) )
                $message($message);
            //If the message is a string
            else if ( is_string($message) ):
            ?>
                <p class="message"><?php echo $message; ?></p>
            <?php
            endif;
            ?>
        </div>
        <?php
    }

    public function print_repeater_value_input(){
        ?>
        <input
        class="<?php echo RB_Form_Field_Controller::get_input_class_link(); ?>"
        rb-control-repeater-value
        rb-control-value
        name="<?php echo $this->id; ?>"
        value="<?php echo esc_attr(json_encode($this->value, JSON_UNESCAPED_UNICODE)); ?>"
        type="hidden" ></input>
        <?php
    }

    public function sanitize_value(){
        $this->value = $this->get_sanitized_value($this->value);
    }

    public function get_sanitized_value($value){
        $item_controller = $this->get_item_controller('', '0');
        if(is_string($value) || is_array($value)){
            $decoded_value = is_string($value) ? json_decode($value, true) : $value;

            // Sanitize item value using child controller sanitization function
            // if(is_array($decoded_value)){
            //     foreach($decoded_value as $key => $item_value){
            //         $decoded_value[$key] = $item_controller ? $item_controller->get_sanitized_value($item_value) : null;
            //     }
            // }

            if(json_last_error() == JSON_ERROR_NONE)
                $value = $decoded_value;
        }
        else
            $value = null;

        return $value;
    }

    public function get_item_base_title(){
        $base_title = $this->get_repeater_setting('item_title');
        return $base_title ? $base_title : 'Item';
    }

    public function get_container_class(){ return "rb-form-control-repeater-field"; }

    public function get_container_attr(){
        return 'data-id="'.esc_attr($this->id).'" data-base-title="'. esc_attr($this->get_item_base_title()) .'"';
    }
}
