<?php
class RB_Roles_Module{

    /**
    *   Adds or removes a set of capabilities
    *   Must be runned before the 'wp_roles_init' action takes place. It doesn't affect the database
    *   directly, so every change made will perdure only if the aaction hook is not removed
    *   @param string $action_id                    Action identifier to store the action
    *   @param string $role                         Role name
    *   @param string/array $cap                    Capability name or arrays of capabilities
    *   @param bool $action                         Wheter to add (true) or remove (false) the capabilities
    *   @param int $priority                        Action hook priority
    */
    static private function add_or_remove_cap($action_id, $role, $cap, $action, $priority = 10){
        $hook_callback = function($wp_roles) use ($role, $cap, $action, $action_id){
            if(!is_string($action_id) || !is_string($role) || (!is_string($cap) && !is_array($cap) && !empty($cap)) || !is_bool($action))
                return false;
            $role_object = $wp_roles->get_role($role);
            if($role_object){
                $capabilities = is_array($cap) ? $cap : [$cap];
                foreach($capabilities as $capability){
                    $role_object->capabilities[$capability] = $action;
                }
                return true;
            }
            return false;
        };
        add_action( 'wp_roles_init', $hook_callback );
        RB_Actions_Manager::store_action($action_id, 'wp_roles_init', $hook_callback, $priority);
    }

    /**
    *   Removes a role's capability
    *   @param string $action_id                    Action identifier to store the action
    *   @param string $role                         Role name
    *   @param string/array $cap                    Capability name or arrays of capabilities
    *   @param int $priority                        Action hook priority
    */
    static public function remove_cap($action_id, $role, $cap, $priority = 10){
        self::add_or_remove_cap($role, $cap, false, $action_id);
    }

    /**
    *   Adds a role's capability
    *   @param string $action_id                    Action identifier to store the action
    *   @param string $role                         Role name
    *   @param string/array $cap                    Capability name or arrays of capabilities
    *   @param int $priority                        Action hook priority
    */
    static public function add_cap($action_id, $role, $cap, $priority = 10){
        self::add_or_remove_cap($role, $cap, true, $action_id);
    }
}
