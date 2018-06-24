<?php

class RB_Customizer_API{
	public $wp_customize_manager;
	public $sections = array();
	public $configuration_function;

	public function __construct($wp_customize_manager, $configuration_function){
		$this->wp_customize_manager = $wp_customize_manager;
		$this->configuration_function = $configuration_function;
	}

	public function activate_selective_refresh(){

		foreach ( $this->sections as $section ) {
			$dependent_settings = $section->settings_without_selective_refresh();
			if ( $section->selective_refresh['activated'] && !empty($dependent_settings) ){
				$this->add_selective_refresh_partial(
					$section->id,
					$dependent_settings,
					$section->selective_refresh
				);
			}
			//echo __LINE__ . "\n"; print_r($section->settings_with_selective_refresh()); echo "\n";
			foreach( $section->settings_with_selective_refresh() as $setting ){
				$this->add_selective_refresh_partial(
					$setting->id,
					array($setting->id),
					$setting->selective_refresh
				);
			}

		}
	}
	/*
	$selective_refresh = array(
		'activated' 		=> true/false,
		'selector'  		=> string,
		'render_callback'	=> function(),
	)
	*/
	public function add_section($name, $options, $selective_refresh = array()){
		$this->wp_customize_manager->add_section($name,$options);
		$section = new RB_Customizer_Section($this->wp_customize_manager, $name, $options, $selective_refresh);
		$this->sections[] = $section;
		return $section;
	}

	public function add_panel($name, $options){
		$this->wp_customize_manager->add_panel( $name, $options);
		return $this;
	}

	public function add_selective_refresh_partial($id, $settings, $args = array()){
		$args['settings'] = $settings;

		$this->wp_customize_manager->selective_refresh->add_partial(
			$id,$args
		);
		/*print_r($selector);echo "\n";
		print_r($settings);echo "\n";
		print_r($render_callback);echo "\n";*/
	}

	public function get_section( $id ){
		foreach( $this->sections as $section ){
			if ( $section->id == $id )
				return $section;
		}
		return null;
	}

	public function initialize(){
		$this->run_configuration( $this->configuration_function );
		$this->activate_selective_refresh();
	}

	public function run_configuration( $config ){
		$config( $this );
	}

}

class RB_Customizer_Section{
	public $wp_customize_manager;
	public $id;
	public $options = array();
	public $controls = array();
	public $selective_refresh = array(
		'activated' => false,
	);

	public function __construct($wp_customize_manager, $id, $options, $selective_refresh = array()){
		$this->wp_customize_manager = $wp_customize_manager;
		$this->id = $id;
		$this->options = $options;
		$this->selective_refresh = array_merge($this->selective_refresh, $selective_refresh);
	}

	public function add_control($id, $control_class, $settings, $options){
		$options['section'] = $this->id;
		$settings_objects = $this->add_settings( $settings );
		$settings_ids = array_keys( $settings );
		$option_settings = $settings_ids;
		if( count($settings_ids) == 1 ){
			$option_settings = $settings_ids[0];
		}
		$options['settings'] = $option_settings;

		$this->wp_customize_manager->add_control( new $control_class($this->wp_customize_manager,$id,$options) );

		$this->controls[] = new RB_Customizer_Control($id, $settings_objects);
		return $this;
	}

	public function add_settings( $settings ){
		$settings_array = array();
		foreach ( $settings as $setting_id => $setting_data ){
			$this->wp_customize_manager->add_setting($setting_id,$setting_data['options']);
			$settings_selective_refresh = $setting_data['selective_refresh'] ? $setting_data['selective_refresh'] : array();
			$settings_array[] = new RB_Customizer_Setting($setting_id, $setting_data['options'], $settings_selective_refresh);
		}
		return $settings_array;
	}

	public function settings_without_selective_refresh(){
		$settings_final = array();
		foreach( $this->controls as $control ){
			$settings_final = array_merge( $settings_final, $control->settings_without_selective_refresh(true) );
		}
		return $settings_final;

	}

	public function settings_with_selective_refresh(){
		$settings_final = array();
		foreach( $this->controls as $control ){
			$settings_final = array_merge( $settings_final, $control->settings_with_selective_refresh() );
		}
		return $settings_final;
	}
}

class RB_Customizer_Control{
	public $id;
	public $settings = array();

	public function __construct($id, $settings){
		$this->id = $id;
		$this->settings = $settings;
	}

	public function settings_without_selective_refresh( $id_only = false ){

		$result = array_filter($this->settings, function ($setting) { return !$setting->selective_refresh['activated']; });
		if ( $id_only )
			$result = array_map( function($setting){ return $setting->id;}, $result  );
		return $result;
	}

	public function settings_with_selective_refresh( $id_only = false ){
		$result = array_filter($this->settings, function ($setting) {
			return ($setting->selective_refresh['activated'] && !$setting->selective_refresh['prevent']);
		});
		if ( $id_only )
			$result = array_map( $result, function($setting){ return $setting->id;} );
		//print_r($result);
		return $result;
	}

}

class RB_Customizer_Setting{
	public $id;
	public $options = array();
	public $selective_refresh = array( 'activated' => false, 'prevent' => false );

	public function __construct($id, $options, $selective_refresh = array()){
		$this->id = $id;
		$this->options = $options;
		$this->selective_refresh['render_callback'] = function(){ echo get_theme_mod( $this->id, ''); };
		$this->selective_refresh = array_merge($this->selective_refresh, $selective_refresh);
	}
}

?>
