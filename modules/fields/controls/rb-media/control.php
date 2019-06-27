<?php

class RB_Media_Control extends RB_Field_Control{

    public function render_content(){
        extract($this->settings);
        ?>
        <?php $this->print_control_header(); ?>
        <div class="inputs-generator-inputs-holder">
            <div class="input-wp-media-image-holder">
                <img class="input-image-src" src="<?php echo esc_attr($this->value); ?>">
                <div class="input-image-placeholder">
                <p> Select an image </p>
                </div>
                <input <?php $this->print_input_link(); ?> class="rb-tax-value rb-sub-input <?php $this->print_input_classes(); ?>"  name="<?php echo $id; ?>" type="hidden" value="<?php echo esc_attr($this->value); ?>"></input>
            </div>
            <div class="remove-image-button"><i class="fas fa-times" title="Remove image"></i></div>
        </div>
        <?php
    }

}
