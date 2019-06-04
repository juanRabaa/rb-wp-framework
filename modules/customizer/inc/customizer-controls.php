<?php

class RB_Extended_Control extends WP_Customize_Control {
	public $li_classes = "";
	public $label_classes = "";
	public $separator_content = "";
	public $dependents_controls = array(
		'controls'	=> [],//array of strings, with the names of the controls to hidde/show
		'hide_all'	=> false,//if true, it hides all the controls from his section, except self
		'reverse'	=> false,//if true, it hides the dependencies when the input value is true
	);

	public function __construct($manager, $id, $args = array())
	{
		parent::__construct($manager, $id, $args);


		if ( !empty($args["dependents_controls"]) )
			$this->dependents_controls = array_merge($this->dependents_controls, $args["dependents_controls"]);
	}

	public function pre_render(){}

	protected function input_id(){
		return '_customize-input-' . $this->id;
	}

	protected function dependencies_activated(){
		foreach( $this->dependents_controls as $dependencies_option ){
			if( !empty($dependencies_option) )
				return true;
		}
		return false;
	}

	protected function add_control_classes( $classes ){
		$this->li_classes .= ' ' . $classes;
	}

	protected function render_control_panel($title, $content, $options = array()){
		$defaults = array(
			'class'	=> '',
		);
		$settings = array_merge($defaults, $options);
	?>
		<div class="rb-control-panel <?php echo $settings['class']; ?>">
			<div class="panel-overlay"></div>
			<div class="rb-control-panel-title-container">
				<i class="fas fa-chevron-circle-left rb-control-panel-close-button"></i>
				<h5 class="rb-control-panel-title"><?php echo $title; ?></h5>
			</div>
			<?php echo $content; ?>
		</div>
	<?php
	}

	protected function render() {
		$this->pre_render();

		$id    = 'customize-control-' . str_replace( array( '[', ']' ), array( '-', '' ), $this->id );
		$class = 'customize-control customize-control-' . $this->type . " " . $this->li_classes;

		?>
		<li id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $class ); ?>">
			<?php if (!empty($this->separator_content)) : ?>
			<div class="controls-separator"><?php echo $this->separator_content; ?></div>
			<?php endif; ?>
			<?php $this->render_content(); ?>
		</li><?php

		if( $this->dependencies_activated() ):
			?>
			<script>
			$(document).ready(function(){
				var dependencies = JSON.parse('<?php echo json_encode($this->dependents_controls); ?>');
				wp.customize('<?php echo $this->id; ?>', function( value ) {
					value.bind( function ( value ) {
						toggle_dependencies('<?php echo $this->id; ?>', '<?php echo $this->input_id(); ?>', dependencies);
					});
				});
				toggle_dependencies('<?php echo $this->id; ?>', '<?php echo $this->input_id(); ?>', dependencies);
			});
			</script>
			<?php
		endif;
	}
}

class RB_Sortable_List_Control extends RB_Extended_Control {
    /**
    *	Array with the list items. Ex:
    *		array(
    *			'value' => 'List item content',
    *		)
    *	Commas on the 'value' will be replace with '-'
    */
    public $items = array();
    public $current_value;

    public function update_value(){
        $items_keys = array_keys ( $this->items );
        $current_order = $this->value() ? explode(',', $this->value()) : array();

        //Eliminates the items that are no longer in the items array

        foreach( $current_order as $item_key ){
            if ( !in_array ( $item_key, $items_keys ) )
                unset($current_order[ array_search ( $item_key, $current_order ) ]);
        }

        //Reindex the array ( unset deletes the element from the array but doesnt change the index of the rest)
        $current_order = array_values($current_order);

        //Push new items to the array
        foreach( $items_keys as $item_key ){
            if ( !in_array ( $item_key, $current_order ) )
                array_push ( $current_order, $item_key );
        }

        $this->current_value = $current_order;
        //print_r( $this->current_value );
    }

    public function array_replace_keys_commas( $strings, $replace_value = "-"){
        $temp_array = array_flip($strings);

        foreach( $temp_array as $item_key => $item_value )
            $temp_array[$item_key] = str_replace(",", $replace_value, $item_value);

        return array_flip($temp_array);
    }

    public function __construct($manager, $id, $args = array())
    {
        parent::__construct($manager, $id, $args);

        $this->items = $this->array_replace_keys_commas( $args["items"] );
        $this->update_value();
    }

    /**
     * Render the control's content.
     *
     * @since 3.4.0
    */
    public function render_content() {
        $name = '_customize-sortable-list-' . $this->id;
        $input_id = '_customize-sortable-item' . $this->id;
        $description_id = '_customize-description-' . $this->id;
        $describedby_attr = ( ! empty( $this->description ) ) ? ' aria-describedby="' . esc_attr( $description_id ) . '" ' : '';
        $amount_of_items = count($this->items);
        $ordered_items_values = $this->current_value;

        ?>
        <label class="customize-control-sortable-list <?php echo $this->label_classes; ?>">
            <span class="customize-control-title"><?php echo $this->label; ?></span>
            <span class="description customize-control-description"><?php echo $this->description; ?></span>
            <ul class="sortables-ul">
                <?php
                    for ( $i = 0; $i < $amount_of_items; $i++){
                        $item_value = $ordered_items_values[$i];
                        $item_nice_name = $this->items[$ordered_items_values[$i]];
                        $id = esc_attr( $input_id . '-' . str_replace( '', '-', $item_value ) );
                        ?>
                        <li
                            class="sortable-li"
                            id="<?php echo $id?>"
                            <?php echo $describedby_attr; ?>
                            name="<?php echo esc_attr( $item_value ); ?>"
                        >
                            <?php echo $item_nice_name ?>
                        </li>
                        <?php
                    }
                ?>
                <input type="hidden" value="<?php echo $this->value(); ?>" <?php $this->link(); ?>>
            </ul>
        </label>
        <?php
    }
}
