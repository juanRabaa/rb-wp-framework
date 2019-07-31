<?php

// =============================================================================
// RB REPEATER ITEM
// =============================================================================
class RB_Repeater_Item{
    public function __construct($id, $value, $index, $item_settings, $repeater_settings){
        $this->id = $id;
        $this->value = $value;
        $this->index = $index;
        $this->repeater_settings = is_array($repeater_settings) ? $repeater_settings : array();
        $this->item_settings = $item_settings;
        $this->controller = new RB_Form_Field_Controller($this->id, $this->value, $this->item_settings);
    }

    public function render($post = null){
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
                <?php $this->controller->render($post); ?>
            </div>
        </div>
        <?php
    }

    public function get_sanitized_value($value){
        return $this->controller->get_sanitized_value($value);
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
            <?php if(!$this->is_collapsible()): ?>
            <div class="control drag-button blue" title="Delete item">
                <i class="fas fa-arrows-alt"></i>
            </div>
            <?php endif; ?>
            <div class="control delete-button red" title="Move item">
                <i class="fas fa-trash-alt"></i>
            </div>
        </div>
        <?php
    }
}
