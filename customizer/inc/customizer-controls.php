<?php
/*
*
*	Custom controls for customizer
*
*/

if( ! function_exists( 'wp_dropdown_posts' ) ) {
	/**
	 * Create dropdown HTML content of posts
	 *
	 * The content can either be displayed, which it is by default or retrieved by
	 * setting the 'echo' argument. The 'include' and 'exclude' arguments do not
	 * need to be used; all published posts will be displayed in that case.
	 *
	 * Supports all WP_Query arguments
	 * @see https://codex.wordpress.org/Class_Reference/WP_Query
	 *
	 * The available arguments are as follows:
	 *
	 * @author Myles McNamara
	 * @website https://smyl.es
	 * @updated March 29, 2016
	 *
	 * @since 1.0.0
	 *
	 * @param array|string $args {
	 *     Optional. Array or string of arguments to generate a drop-down of posts.
	 *     {@see WP_Query for additional available arguments.
	 *
	 *     @type string       $show_option_all         Text to show as the drop-down default (all).
	 *                                                 Default empty.
	 *     @type string       $show_option_none        Text to show as the drop-down default when no
	 *                                                 posts were found. Default empty.
	 *     @type int|string   $option_none_value       Value to use for $show_option_non when no posts
	 *                                                 were found. Default -1.
	 *     @type array|string $show_callback           Function or method to filter display value (label)
	 *
	 *     @type string       $orderby                 Field to order found posts by.
	 *                                                 Default 'post_title'.
	 *     @type string       $order                   Whether to order posts in ascending or descending
	 *                                                 order. Accepts 'ASC' (ascending) or 'DESC' (descending).
	 *                                                 Default 'ASC'.
	 *     @type array|string $include                 Array or comma-separated list of post IDs to include.
	 *                                                 Default empty.
	 *     @type array|string $exclude                 Array or comma-separated list of post IDs to exclude.
	 *                                                 Default empty.
	 *     @type bool|int     $multi                   Whether to skip the ID attribute on the 'select' element.
	 *                                                 Accepts 1|true or 0|false. Default 0|false.
	 *     @type string       $show                    Post table column to display. If the selected item is empty
	 *                                                 then the Post ID will be displayed in parentheses.
	 *                                                 Accepts post fields. Default 'post_title'.
	 *     @type int|bool     $echo                    Whether to echo or return the drop-down. Accepts 1|true (echo)
	 *                                                 or 0|false (return). Default 1|true.
	 *     @type int          $selected                Which post ID should be selected. Default 0.
	 *     @type string       $select_name             Name attribute of select element. Default 'post_id'.
	 *     @type string       $id                      ID attribute of the select element. Default is the value of $select_name.
	 *     @type string       $class                   Class attribute of the select element. Default empty.
	 *     @type array|string $post_status             Post status' to include, default publish
	 *     @type string       $who                     Which type of posts to query. Accepts only an empty string or
	 *                                                 'authors'. Default empty.
	 * }
	 * @return string String of HTML content.
	 */
	function wp_dropdown_posts( $args = '' ) {
		$defaults = array(
			'selected'              => FALSE,
			'pagination'            => FALSE,
			'posts_per_page'        => - 1,
			'post_status'           => 'publish',
			'cache_results'         => TRUE,
			'cache_post_meta_cache' => TRUE,
			'echo'                  => 1,
			'select_name'           => 'post_id',
			'id'                    => '',
			'class'                 => '',
			'show'                  => 'post_title',
			'show_callback'         => NULL,
			'show_option_all'       => NULL,
			'show_option_none'      => NULL,
			'option_none_value'     => '',
			'multi'                 => FALSE,
			'value_field'           => 'ID',
			'order'                 => 'ASC',
			'orderby'               => 'post_title',
		);
		$r = wp_parse_args( $args, $defaults );
		$posts  = get_posts( $r );
		$output = '';
		$show = $r['show'];
		if( ! empty($posts) ) {
			$name = esc_attr( $r['select_name'] );
			if( $r['multi'] && ! $r['id'] ) {
				$id = '';
			} else {
				$id = $r['id'] ? " id='" . esc_attr( $r['id'] ) . "'" : " id='$name'";
			}
			$output = "<select name='{$name}'{$id} class='" . esc_attr( $r['class'] ) . "'>\n";
			if( $r['show_option_all'] ) {
				$output .= "\t<option value='0'>{$r['show_option_all']}</option>\n";
			}
			if( $r['show_option_none'] ) {
				$_selected = selected( $r['show_option_none'], $r['selected'], FALSE );
				$output .= "\t<option value='" . esc_attr( $r['option_none_value'] ) . "'$_selected>{$r['show_option_none']}</option>\n";
			}
			foreach( (array) $posts as $post ) {
				$value   = ! isset($r['value_field']) || ! isset($post->{$r['value_field']}) ? $post->ID : $post->{$r['value_field']};
				$_selected = selected( $value, $r['selected'], FALSE );
				$display = ! empty($post->$show) ? $post->$show : sprintf( __( '#%d (no title)' ), $post->ID );
				if( $r['show_callback'] ) $display = call_user_func( $r['show_callback'], $display, $post->ID );
				$output .= "\t<option value='{$value}'{$_selected}>" . esc_html( $display ) . "</option>\n";
			}
			$output .= "</select>";
		}
		/**
		 * Filter the HTML output of a list of pages as a drop down.
		 *
		 * @since 1.0.0
		 *
		 * @param string $output HTML output for drop down list of posts.
		 * @param array  $r      The parsed arguments array.
		 * @param array  $posts  List of WP_Post objects returned by `get_posts()`
		 */
		$html = apply_filters( 'wp_dropdown_posts', $output, $r, $posts );
		if( $r['echo'] ) {
			echo $html;
		}
		return $html;
	}
}



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


	class RB_Inputs_Generator_Control extends RB_Extended_Control {

		public $inputs_types = array();
		public $input_title = '';
		public $disable_generator = false;
		public $show_option_none = "None";
		public $dinamic_label = null;
		public $show_inputs_label = true;

		public function __construct($manager, $id, $args = array())
		{
			parent::__construct($manager, $id, $args);

			$this->input_title = $args["inputs_title"];
			$this->disable_generator = $args["disable_generator"] ? $args["disable_generator"] : $this->disable_generator;
			$this->disable_generator = $args["disable_generator"] ? $args["disable_generator"] : $this->disable_generator;
		}

		public function type_is_taxonomy ( $type ){
			switch ( $type ){
				case "categorie":
					return true;
				break;
				case "tag":
					return true;
				break;
				case "page":
					return true;
				break;
				case "post":
					return true;
				break;
				case "user":
					return true;
				break;
			}
			return false;
		}

		public function get_taxs_dropdown( $type, $id, $value ){
			$dropdown = null;
			switch ( $type ){
				case "categorie":
					$dropdown = wp_dropdown_categories(
						array(
							'name'              => $id,
							'echo'              => 0,
							'show_option_none'  => __( $this->show_option_none ),
							'option_none_value' => -1,
							'selected'          => $value,
						)
					);
				break;
				case "tag":
					$dropdown = wp_dropdown_categories(
						array(
							'name'              => $id,
							'echo'              => 0,
							'show_option_none'  => __( $this->show_option_none ),
							'option_none_value' => -1,
							'selected'          => $value,
							'taxonomy'			=> 'post_tag',
						)
					);
				break;
				case "page":
					$dropdown = wp_dropdown_pages(
						array(
							'name'              => $id,
							'echo'              => 0,
							'show_option_none'  => __( $this->show_option_none ),
							'option_none_value' => -1,
							'selected'          => $value,
						)
					);
				break;
				case "post":
					$dropdown = "<select
						name='$id'
					>";
					$dropdown .= '<option value="-1"'.selected($value, $post->ID, false).'>'.__( $this->show_option_none ).'</option>';
					$posts = get_posts(array(
						'posts_per_page'       	=> -1,
						'orderby'				=> 'title',
					));
					foreach ( $posts as $post ){
						$dropdown .= '<option value="'. $post->ID .'"'.selected($value, $post->ID, false).'>'.$post->post_title.'</option>';
					}

					$dropdown .= "</select>";
				break;
				case "user":
					$dropdown = "<select
						name='$id'
					>";
					$dropdown .= '<option value="-1"'.selected($value, $post->ID, false).'>'.__( $this->show_option_none ).'</option>';

					$users = get_users();
					foreach ( $users as $user ){
						$dropdown .= '<option value="'. $user->ID .'"'.selected($value, $user->ID, false).'>'.$user->user_nicename.'</option>';
					}

					$dropdown .= "</select>";
				break;
			}
			return $dropdown;
		}

		public function decode_json_value(){
			return json_decode($this->value(), true);
		}

		//Checks if the setting value is usable in this control
		public function is_safe_value(){
			$value = $this->decode_json_value();
			if ( is_array($value) && is_array($value[0]) )
				return true;
			else
				return false;
		}

		//Returns an inputs group HTML as a string
		public function inputs_as_string(){
			ob_start();
			$this->print_empty_group();
			return ob_get_clean();
		}

		public function get_inputs_group_title( $inputs ){
			$title = $this->label;

			$title_input_value = array_search($this->dinamic_label, $inputs);
			if ( !empty($title_input_value) )
				return $title_input_value;
			else if ($this->input_title)
				return $this->input_title;
			return $title;
		}

		public function get_input_data_by_id( $id ){
			return $this->inputs_types[$id];
		}

		//Prints an inputs group with all inputs emptys
		public function print_empty_group(){
			$inputs = $this->inputs_types;
			foreach( $inputs as $input_id => $value ){
				$inputs[$input_id] = "";
			}
			$this->print_single_inputs_group( $inputs );
		}

		public function print_inputs_groups(){
			$inputs_groups = $this->decode_json_value();
			if ( !empty($inputs_groups) && $this->is_safe_value() ){
				foreach( $inputs_groups as $inputs ){
					$this->print_single_inputs_group( $inputs );
					if ( $this->disable_generator )
						break;
				}
			}
			else
				$this->print_empty_group();
		}

		public function print_single_inputs_group( $inputs ){
			?>
			<li name="<?php echo $this->label; ?>" class="customizer-inputs-group sortable-li
			<?php if ( !$this->disable_generator ): ?>customizer-draggable-li bullet-draggable<?php endif; ?>">
				<?php if ( !$this->disable_generator ): ?>
				<span class="draggable-ball"></span>
				<div class="collapsible-title customizer-draggable-li-title">
					<span class="customize-control-arrow">
						<i class="fas fa-angle-down collapsible-arrow" aria-hidden="true"></i>
					</span>
					<span class="customize-control-title"><?php echo $this->get_inputs_group_title( $inputs ); ?></span>
				</div>
				<?php endif; ?>
				<div class="<?php if ( !$this->disable_generator ): ?>collapsible-body<?php endif; ?> inputs-holder">
				<?php
					$this->print_inputs( $inputs );
				?>
				</div>
			</li>
			<?php
		}

		public function print_inputs ( $inputs ){
			if ( !empty( $inputs ) ){
				foreach( $inputs as $input_id => $input_value ){
					$input_data = $this->get_input_data_by_id( $input_id );
					$this->print_single_input($input_id, $input_data["nice_name"], $input_data["type"], $input_data["dependencies"], $input_data["reverse_dependencies"], $input_value);
				}
			}
		}

		//Prints a single input, matching the markup with its type
		public function print_single_input($input_id, $nice_name, $type, $dependencies, $reverse_dependencies, $value){
			?>
				<div class="inputs-generator-inputs-holder" data-inputs-dependencies="<?php echo $dependencies; ?>"
				data-reverse-dependencies="<?php echo $reverse_dependencies; ?>" data-input-show="true">

				<?php if ( $type == "checkbox" ): ?>
				<input name="<?php echo $input_id; ?>" type="<?php  echo $type; ?>" value="<?php echo $value; ?>"
				<?php if ( $value ): ?> checked <?php endif; ?>>
				<?php endif; ?>

				<?php if ( $this->show_inputs_label ): ?>
				<span class="input-label"><?php echo $nice_name; ?></span>
				<?php endif; ?>

				<?php if ( $type == "textarea" ): ?>
				<textarea name="<?php echo $input_id; ?>" value="<?php echo $value; ?>"><?php echo $value; ?></textarea>
				<?php elseif ( $type == "image" ): ?>
				<div class="input-wp-media-image-holder">
					<img class="input-image-src" src="<?php echo $value; ?>">
					<div class="input-image-placeholder">
						<p> Select an image </p>
					</div>
					<input name="<?php echo $input_id; ?>" type="text" value="<?php echo $value; ?>">
				</div>
				<div class="remove-image-button"><i class="fas fa-times" title="Remove image"></i></div>
				<?php
					elseif ( $this->type_is_taxonomy( $type ) ):
						$dropdown = $this->get_taxs_dropdown( $type, $input_id, $value );
						echo $dropdown;

					elseif ( $type != "checkbox" ):
				?>
				<input name="<?php echo $input_id; ?>" type="<?php  echo $type; ?>" value="<?php echo $value; ?>">
				<?php endif; ?>
				</div>
			<?php
		}

		public function render_content() {
			?>
			<label class="customize-control-multiple-inputs customize-control-inputs-generator customizer-control-holder <?php echo $this->label_classes; ?>"
			data-base-inputs="<?php echo esc_html($this->inputs_as_string()); ?>"
			data-dinamic-label-id="<?php echo $this->dinamic_label; ?>">
				<div class="title-and-trash-holder">
					<span class="customize-control-title"><?php echo $this->label; ?></span>
					<?php if ( !$this->disable_generator ): ?>
					<i class="fas fa-trash-alt delete-item-on-drop" title="Drag item over to delete"></i>
					<?php endif; ?>
				</div>
				<span class="description customize-control-description"><?php echo $this->description; ?></span>
				<ul class="customizer-sortable-ul ui-sortable">
					<?php echo $this->print_inputs_groups(); ?>
				</ul>
				<?php if ( !$this->disable_generator ): ?>
				<div class="add-new-li customizer-add-new-button">
					<i class="fas fa-plus"></i>
				</div>
				<?php endif; ?>
				<input class="control-value" type="hidden" <?php $this->link(); ?>>
			</label>
			<?php
		}
	}

	class RB_Single_Input_Generator_Control extends RB_Inputs_Generator_Control {

		public function sanitize_inputs_array(){
			if( count( $this->inputs_types ) > 1 ){
				reset( $this->inputs_types );
				$first_key = key( $this->inputs_types );
				$this->inputs_types = array( $first_key => $this->inputs_types[$first_key] );
			}
			else if ( !count( $this->inputs_types ) )
				$this->inputs_types == array();
		}

		public function get_inputs_group_title( $inputs ){
			$title = $this->label;

			return $title;
		}

		public function is_safe_value(){
			$value = $this->decode_json_value();
			if ( is_array($value) )
				return true;
			else
				return false;
		}

		public function print_inputs ( $input_value ){
			$input_data = current($this->inputs_types);
			$input_id = key( $this->inputs_types );
			$this->print_single_input($input_id, $input_data["nice_name"], $input_data["type"], $input_data["dependencies"], $input_data["reverse_dependencies"], $input_value);
		}

		public function print_empty_group(){
			$this->print_single_inputs_group( '' );
		}

		public function pre_render(){
			$this->label_classes .= " single-input-generator-control";
			$this->sanitize_inputs_array();
		}

	}

	class RB_Single_Input_Control extends RB_Single_Input_Generator_Control {

		public $input_type = '';

		public function __construct($manager, $id, $args = array())
		{
			parent::__construct($manager, $id, $args);

			$this->input_type = $args["input_type"];
		}

		public function pre_render(){
			$this->label_classes .= " single-input-control";
			$this->disable_generator = true;
			$this->show_inputs_label = false;
		}

		public function print_inputs_groups(){
			$input_value = $this->value();
			$this->print_single_inputs_group( $input_value );
		}

		public function print_empty_group(){
			$this->print_single_inputs_group( '' );
		}

		public function print_inputs ( $input_value ){
			$this->print_single_input('single_input', '', $this->input_type, '', '', $input_value);
		}

	}

	/*Displays a group of inputs, returns as value a JSON
	/* Keys == inputs ID
	/*Same as the RB_Inputs_Generator_Control, but only allows one group, doesnt allows generation of new inputs groups.
	/*The difference is the value returned, instead of an array of inputs group is an array of inputs*/
	class RB_Inputs_Control extends RB_Inputs_Generator_Control {

		public function pre_render(){
			$this->label_classes .= " single-inputs-group-control";
			$this->disable_generator = true;
		}

		public function is_safe_value(){
			$value = $this->decode_json_value();
			if ( is_array($value) )
				return true;
			else
				return false;
		}

		public function print_inputs_groups(){
			$inputs = $this->decode_json_value();

			if ( !empty($inputs) && $this->is_safe_value() )
				$this->print_single_inputs_group( $inputs );
			else
				$this->print_empty_group();
		}

	}

	class RB_Gallery_Control extends RB_Extended_Control {

		public function print_base_input_str(){
			ob_start();
			$this->print_single_image( '' );
			return ob_get_clean();
		}

		public function decode_json_value(){
			return json_decode($this->value(), true);
		}

		public function print_single_image( $image_src ){
			?>
			<li class="customizer-gallery-image-li sortable-li customizer-draggable-li">
				<div class="gallery-image-holder" data-image-src="<?php echo $image_src; ?>" style="background-image: url(<?php echo $image_src; ?>);">
					<div class="gallery-image-controls">
						<div class="edit-image" title="Remove image"><i class="fas fa-pencil-alt"></i></div>
						<div class="drag-image"></div>
						<div class="remove-image" title="Remove image"><i class="fas fa-trash"></i></div>
					</div>
				</div>
			</li>
			<?php
		}

		public function print_images(){
			$images = $this->decode_json_value();

			if( !empty($images) ){
				foreach( $images as $image_src ){
					$this->print_single_image($image_src);
				}
			}
		}

		public function render_content() {
			?>
			<label class="customize-control-image-gallery customizer-control-holder <?php echo $this->label_classes; ?>"
			data-gallery-base-li="<?php print_r(esc_html( $this->print_base_input_str() )); ?>">
				<span class="customize-control-title"><?php echo $this->label; ?></span>
				<span class="description customize-control-description"><?php echo $this->description; ?></span>
				<ul class="customizer-sortable-ul ui-sortable">
					<?php echo $this->print_images(); ?>
				</ul>
				<div class="add-new-li customizer-add-new-button">
					<i class="fas fa-plus"></i>
				</div>
				<input class="control-value" type="hidden" <?php $this->link(); ?>>
			</label>
			<?php
		}

	}

	class RB_List_Generator_Control extends RB_Extended_Control {
		public $max_num_of_lists;
		public $button_content;

		public function __construct($manager, $id, $args = array())
		{
			parent::__construct($manager, $id, $args);

			$this->max_num_of_lists = $args["max_num_of_lists"];
			$this->button_content = $args["button_content"];
		}

		public function sanitized_value(){
			return str_replace('"',"'",$this->setting->value());
		}

		public function decoded_value(){
			return json_decode ( $this->setting->value(), true );
		}

		public function the_lists(){
			$lists = $this->decoded_value();
			$index = 0;

			foreach ( $lists as $list){
				$list_name = $list["name"];
				$list_items = $list["items"];
				?>
				<li
				data-list-name="<?php echo $list_name; ?>"
				data-list-id="list_<?php echo $index; ?>"
				data-list-items="<?php echo $this->stringify_items_array( $list_items ); ?>"
				class="sortable-li"
				>
					<span class="list-name"><?php echo $list_name; ?></span>
					<i class="fas fa-pencil-alt edit-button" title="Edit"></i>
					<i class="far fa-trash-alt delete-list" title="Delete List"></i>
				</li>
				<?php
				$index++;
			}
		}

		public function stringify_items_array( $items_array ){
			$array_length = count($items_array);
			$string = "";

			foreach ( $items_array as $index=>$item ){
				$string .= $item;
				if ( $index < ($array_length - 1) )
					$string .= ',';
			}

			return $string;
		}

		public function load_organize_lists_view(){
		?>
			<div class="lists-organization">
				<p>Organizate lists. Right click to edit that list</p>
				<div class="current-list">
					<ul class="sortables-ul">
						<?php
							if ( !empty($this->value()) )
								$this->the_lists();
						?>
						<input type="hidden" value="<?php echo $this->sanitized_value(); ?>" data-value="{'first_list':[],'second_list':[],'third_list':[]}" <?php $this->link(); ?>>
					</ul>
				</div>
				<div class="add-list">
					<i class="fas fa-plus-square" title="Add list"></i>
				</div>
			</div>
		<?php
		}

		public function load_edit_list_view(){
		?>
			<div data-list-id="" class="view-list" style="display: none;">
				<p> Editing list: <span class="the-list-name">name</span><i class="fas fa-pencil-alt edit-name"></i></p>
				<span class="list-fast-edition-button"> FAST EDITION </span>
				<div class="current-list">
					<ul class="sortables-ul">
					</ul>
				</div>
				<div class="add-list-item">
					<i class="fas fa-plus-square" title="Add list item"></i>
				</div>
			</div>
		<?php
		}

		public function load_list_item_edition(){
			?>
			<div class="insert-item-content ui-draggable">
				<h6> Editing list item </h6>
				<textarea class="item-edition-field"></textarea>
				<div class="item-edition-buttons awaiting-edition">
					<i class="far fa-save save-changes-button" title="Save changes"></i>
					<i class="fas fa-times discard-changes-button" title="Discard changes"></i>
				</div>
			</div>
			<?php
		}

		public function load_csv_list_edition(){
			?>
			<div class="cvs-list-edition ui-draggable">
				<h6> Editing list as CSV </h6>
				<textarea class="item-edition-field"></textarea>
				<div class="item-edition-buttons awaiting-edition">
					<i class="far fa-save save-changes-button" title="Save changes"></i>
					<i class="fas fa-times discard-changes-button" title="Discard changes"></i>
				</div>
			</div>
			<?php
		}

		public function list_control_panel_content(){
		ob_start();
		?>
			<!--<span> Number of lists: <?php echo $this->max_num_of_lists; ?></span>
			<span> Maximum number of lists possible: 3</span>-->
			<div class="list-selection">
				<span class="organize-button active">Organize/select list</span>
			</div>
			<div class="lists-visualization">
				<?php $this->load_edit_list_view(); ?>
				<?php $this->load_organize_lists_view(); ?>
				<?php $this->load_list_item_edition(); ?>
			</div>
		<?php
		return ob_get_clean();
		}

		public function render_content() {
			?>
			<label class="customize-control-list-edition <?php echo $this->label_classes; ?>">
				<span class="customize-control-title"><?php echo $this->label; ?></span>
				<span class="description customize-control-description"><?php echo $this->description; ?></span>
				<div class="list-edition-button">
					<span><?php echo $this->button_content; ?></span>
				</div>
				<?php
					$this->render_control_panel( 'Lists control panel', $this->list_control_panel_content(), array(
						'class' => 'list-edition-panel',
					));
				?>
			</label>
			<?php
		}
	}



	//Text editor control
	class RB_TinyMCE_Custom_Control extends RB_Extended_Control{
		public $type = 'textarea';
		/**
		** Render the content on the theme customizer page
		*/
		public function __construct($manager, $id, $args = array())
		{
			parent::__construct($manager, $id, $args);

			$this->add_control_classes('customize-tinymce-control');
		}

		public function render_content() { ?>
			<label class="<?php echo $this->label_classes; ?>">
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<div class="tinymce-placeholder">
					<textarea class="tinymce-content-preview"><?php echo $this->value(); ?></textarea>
					<div class="edit-button">Editar</div>
				</div>
				<input type="hidden" class="rb-control-value-input" value="<?php echo $this->value(); ?>" <?php $this->link(); ?>>
			</label>
		<?php
		}
	}

	/*TO ADD
	/***Option to not use zoom in
	/***Add the possibility to use MULTIPLE IMAGES per IMAGE CHOICE, and display a carousel on the 'zoom in' view, with all those images
	*/
	class RB_Select_By_Image extends RB_Extended_Control {
		/**
		*	Array with the images to use. Every image inside the array must respect this format:
		*		$file_without_ext => array(
		*			'value'			=> 'This option value',
		*			'src'			=> 'The image src',
		*			'nice_name'		=> 'A nice name'
		*		)
		*/
		public $images = array();

		/**
		*	String. Defines the max width of the image container. This defines how many images will be seen by row. Default 33%: shows
		*three images per row
		*/
		public $max_width = "33%";

		/**
		 * Render the control's content.
		 *
		 * @since 3.4.0
		 */

		public function __construct($manager, $id, $args = array())
		{
			parent::__construct($manager, $id, $args);

			$this->images = $args["images"];
			$this->max_width = $args["max_width"];
		/*	echo "aca\n";
			print_r($this->setting->value());*/
		}

		public function render_content() {
			$name = '_customize-image-radio-' . $this->id;
			$input_id = '_customize-input-' . $this->id;
			$description_id = '_customize-description-' . $this->id;
			$describedby_attr = ( ! empty( $this->description ) ) ? ' aria-describedby="' . esc_attr( $description_id ) . '" ' : '';

			?>
			<label class="customize-control-select <?php echo $this->label_classes; ?>">
				<span class="customize-control-title"><?php echo $this->label; ?></span>
				<span class="description customize-control-description"><?php echo $this->description; ?></span>
				<div class="image-selection-container">
					<span class="dbl-click-text">Double click on an image to zoom in</span>
					<?php foreach ( $this->images as $image_choice ) : ?>
					<?php $id = esc_attr( $input_id . '-radio-' . str_replace( '', '-', $image_choice["nice_name"] ) );?>
					<div class="image-choice" style="<?php if ( $this->max_width != '' ) echo "max-width: ".$this->max_width.';'; ?>">
						<input
							class="selection-with-image-input"
							id="<?php echo $id?>"
							type="radio"
							<?php echo $describedby_attr; ?>
							value="<?php echo esc_attr( $image_choice["value"] ); ?>"
							name="<?php echo esc_attr( $name ); ?>"
							<?php $this->link(); ?>
							<?php checked( $this->value(), $image_choice["nice_name"] ); ?>
							aria-label="<?php echo $image_choice["nice_name"] ?>"
							title="<?php echo $image_choice["nice_name"] ?>"
						/>
						<div class="image-selection-image">
							<img class="zoom-in-available" for="<?php echo $id; ?>" src="<?php echo $image_choice["src"]; ?>">
						</div>
					</div>
					<?php endforeach; ?>
				</div>
			</label>
			<?php

		}
	}

	class RB_Color_Scheme_Control extends RB_Extended_Control {
		public $colors_schemes;

		public function __construct($manager, $id, $args = array())
		{
			parent::__construct($manager, $id, $args);

			$this->colors_schemes = $args["colors_schemes"];
		}

		public function render_content() {
			$name = '_customize-image-radio-' . $this->id;
			$input_id = '_customize-input-' . $this->id;
			$description_id = '_customize-description-' . $this->id;
			$describedby_attr = ( ! empty( $this->description ) ) ? ' aria-describedby="' . esc_attr( $description_id ) . '" ' : '';
			?>
			<label class="customize-control-color-schemes <?php echo $this->label_classes; ?>">
				<span class="customize-control-title"><?php echo $this->label; ?></span>
				<span class="description customize-control-description"><?php echo $this->description; ?></span>
				<div class="colors-scheme">
					<div class="color-scheme-container">
					<?php if ( empty($this->colors_schemes)) : ?>
						<p> No colors schemes where supplied </p>
					<?php else : ?>
						<?php foreach ( $this->colors_schemes as $scheme_value => $scheme_colors ) : ?>
						<div class="color-scheme">
							<input
								class="color-scheme-input"
								id="<?php echo $id?>"
								type="radio"
								<?php echo $describedby_attr; ?>
								value="<?php echo esc_attr( $scheme_value ); ?>"
								name="<?php echo esc_attr( $name ); ?>"
								<?php $this->link(); ?>
								<?php checked( $this->value(), $scheme_value ); ?>
								aria-label="<?php echo $scheme_value ?>"
								title="<?php echo $scheme_value ?>"
							/>
							<?php foreach ( $scheme_colors as $color ) : ?>
							<div>
								<span style="background-color: <?php echo $color; ?>;"></span>
							</div>
							<?php endforeach; ?>
						</div>
						<?php endforeach; ?>
					<?php endif; ?>
					</div>
				</div>
			</label>
			<?php
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
