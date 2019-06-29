<?php
if(!class_exists('RB_Fields_Module')){
    define('RB_FORMS_FIELDS_MASTER_DIR', plugin_dir_path(__FILE__));
    
    class RB_Fields_Module{
        static private $initialized = false;

    	static public function initialize(){
    		if(self::$initialized)
    			return false;
            self::$initialized = true;

            require_once RB_FORMS_FIELDS_MASTER_DIR . 'inc/rb_fields_functions.php';

            if(is_admin()){
                //Screen
                add_action( 'current_screen', function(){
                    $screen = get_current_screen();
                    if( $screen && $screen->taxonomy )
                        add_action( 'admin_enqueue_scripts', array(self::class, 'on_taxonomies_screen_scripts') );
                });

                //ADMIN SCRIPTS
                add_action( 'admin_enqueue_scripts', array(self::class, 'rb_form_fields_scripts') );
                //Controller and core classes
                self::require_controller();
                self::require_core_controls();
                self::integrate_with_vc();
                self::require_environment_compability_classes();
            }

            //add_action( 'customize_controls_enqueue_scripts', array(self::class, 'rb_fields_module_customizer_scripts') );
            add_action( 'customize_register', array(self::class, 'rb_customizer_field_register') );
    	}

        static private function require_core_controls(){
            foreach (new DirectoryIterator(RB_FORMS_FIELDS_MASTER_DIR . 'controls/') as $control_info) {
    		    if($control_info->isDot()) continue;
    			$control_file = $control_info->getPathname() . '/control.php';
    			if(file_exists($control_file))
    			    require_once $control_file;
    		}
        }

        static private function require_core_controls_scripts(){
            foreach (new DirectoryIterator(RB_FORMS_FIELDS_MASTER_DIR . 'controls/') as $control_info) {
    		    if($control_info->isDot()) continue;
    			$control_file = $control_info->getPathname() . '/scripts.php';
    			if(file_exists($control_file))
    			    require_once $control_file;
    		}
        }

        // =====================================================================
        // VISUAL COMPOSER
        // =====================================================================
        static public function on_vc_plugins_loaded(){
            if(!function_exists('vc_add_shortcode_param'))
                return false;
            return vc_add_shortcode_param( 'rb_control', function ($param_settings, $value){
                if(!isset($param_settings['control_settings']) || !is_array($param_settings['control_settings']))
                    return 'Options to set up the RB Control should be passed through the param settings';
                $control_settings = $param_settings['control_settings'];
                $id = $param_settings['param_name'];
                $rb_control = new RB_Form_Field_Controller($id, $value, $control_settings);
                return $rb_control->get_as_html();// This is html markup that will be outputted in content elements edit form
            });
        }

        //Integrates the fields as a visual composer param
        static private function integrate_with_vc(){
            add_action( 'vc_plugins_loaded', array(self::class, 'on_vc_plugins_loaded') );
        }
        // =========================================================================
        // CLASSES
        // =========================================================================

        //Base classes
        static private function require_controller(){
            //Fields functionalities (fields creator - repeaters - groups)
            require_once RB_FORMS_FIELDS_MASTER_DIR . '/RB_Form_Field_Controller.php';
            require_once RB_FORMS_FIELDS_MASTER_DIR . '/RB_Field_Control.php';
        }

        //Clases that makes posible to use this controls on different environments
        static private function require_environment_compability_classes(){
            require_once RB_FORMS_FIELDS_MASTER_DIR . '/RB_Metabox.php';
            require_once RB_FORMS_FIELDS_MASTER_DIR . '/RB_Taxonomy_Meta.php';
            require_once RB_FORMS_FIELDS_MASTER_DIR . '/RB_Attachment_Meta.php';
        }

        // =========================================================================
        // SCRIPTS
        // =========================================================================
        static public function rb_form_fields_scripts($wp_customize){
            wp_enqueue_editor();
            // =====================================================================
            // COMMONS
            // =====================================================================
            //Collapsibles
            wp_enqueue_style( 'rb-collapsible', plugin_dir_url(__FILE__) . 'css/rb-collapsible.css' );
            wp_enqueue_script( 'rb-collapsible', plugin_dir_url(__FILE__) . 'js/rb-collapsible.js', array('jquery'), true );
            //Sortabe jQuery UI
            wp_enqueue_script( 'jquery-ui', plugin_dir_url(__FILE__) . 'js/libs/jquery-ui/jquery-ui.min.js', array('jquery'), true );
            //Main
            wp_enqueue_style( 'rb-form-fields-css', plugin_dir_url(__FILE__) . 'style.css' );
            wp_enqueue_script( 'rb-controls-values-manager', plugin_dir_url(__FILE__) . 'js/rb-controls.js', array('jquery'), true );
            //Font Aweasome
            wp_enqueue_style( "fontawesome", 'https://use.fontawesome.com/releases/v5.4.1/css/all.css', true );

            // =====================================================================
            // NATIVE CONTROLS SCRIPTS
            // =====================================================================
            self::require_core_controls_scripts();
        }

        static public function on_taxonomies_screen_scripts(){
            wp_enqueue_script( 'jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js', false );
            wp_enqueue_media();
        }

        // =============================================================================
        // CUSTOMIZER
        // =============================================================================
        //CUSTOMIZER SCRIPTS
        static public function rb_fields_module_customizer_scripts($wp_customize) {
            //wp_enqueue_script( 'jQuery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js', true );
            //wp_enqueue_script( 'rb-customizer-values-manager', plugin_dir_url(__FILE__) . 'js/customizerControlsValuesManager.js', array(), true );
        }

        static public function rb_customizer_field_register($wp_customize) {
            require_once RB_FORMS_FIELDS_MASTER_DIR . '/RB_Form_Field_Controller.php';
            require RB_FORMS_FIELDS_MASTER_DIR . '/RB_Customizer_Control.php';
        }

    }
    RB_Fields_Module::initialize();
}
