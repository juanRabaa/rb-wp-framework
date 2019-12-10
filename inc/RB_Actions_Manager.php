<?php

/*
*   It helps managing actions hooks, storing information about them in order to
*   allow third parties to remove them and other functionalities
*/
class RB_Actions_Manager{
    static private $stored_actions = array();

    /**
    *   Stores an action data
    *   @param string $action_id                        Actions identifier.
    */
    static private function store_action_data($action_id, $action_data){
        self::$stored_actions[$action_id] = $action_data;
    }

    /**
    *   Adds an action and stores its data for easier actions manipulation
    *   @param string $action_id                        Actions identifier. Used by the RB_Actions_Manager to facilitate
    *                                                   operations with existing actions, like removing.
    *   @param string $tag                              The name of the action to which the $function_to_add is hooked.
    *   @param callable $function_to_add                The callable of the function to be runned on this action.
    *   @param mixed[] (optional) $args
    *   Optional argumentes array
    *       @property int $priority                    Action priority. Used by the manager to add actions before of after
    *                                                   this one. Might be overriden if passed any $dependencies.
    *       @param int $accepted_args                   The number of arguments the function accepts.
    *       @param string[] $dependencies               Actions ids of those this one depends on. The priority of this
    *                                                   action will be setted inmediatly higher than the latest action passed, so that
    *                                                   the callback may be called after all of them had been already invoked.
    *       @param bool $await_dependencies             ****TO BE IMPLEMENTED****
    *                                                   Indicates wheter the action hook must be put on hold if dependencies where not found.
    *                                                   The action will be hooked once the dependencies gets hooked or if the action hook is
    *                                                   reached.
    */
    static public function add_action($action_id, $tag, $function_to_add, $args = array()){
        // VALIDATION
        if(!is_string($action_id) || !is_string($tag))
            return false;
        if(self::get_stored_action($action_id))// If and action with that id already exists
            throw new Exception("Trying to store an action with an ID that already exists: '$action_id'");

        // ARGS SETUP
        $default_args = array(
            'priority'              => 10,
            'accepted_args'         => 1,
            'dependencies'          => null,
            'await_dependencies'    => true,
        );
        $args = array_merge($default_args, $args);
        extract($args);

        // ACTION DATA CONSTRUCTION
        // Set priority based on dependencies passed
        if(is_array($dependencies) && !empty($dependencies))
            $priority = self::generate_action_priority($tag, $dependencies, $priority);

        // Add action
        add_action( $tag, $function_to_add, $priority, $accepted_args );
        // Store the action data
        $action_data = array(
            'tag'           => $tag,
            'callback'      => $function_to_add,
            'priority'      => $priority,
            'accepted_args' => $accepted_args,
        );
        self::store_action_data($action_id, $action_data);
    }

    /**
    *   Returns an action stored data
    *   @param string $action_id                        Actions identifier
    *   @return mixed[]|null                            The action if found, null otherwise
    */
    static private function get_stored_action($action_id){
        if(!is_string($action_id))
            return null;
        return isset(self::$stored_actions[$action_id]) ? self::$stored_actions[$action_id] : null;
    }

    /**
    *   Removes an action, preventing the callback from being called
    *   @param string $action_id                        Actions identifier
    *   @param string $priority                         How soon should the action hook be removed
    */
    static public function remove_action($action_id, $priority = 10){
        if(!is_string($action_id))
            return false;
        $action = self::get_stored_action($action_id);
        if(!$action)
            return false;
        remove_action($action['tag'], $action['callback'], $priority );
        unset(self::$stored_actions[$action_id]);
    }

    /**
    *   Returns the latest action to be called, being the one with the biggest priority
    *   @param string[] $tag                            Action hook tag of the hook we want the latest action from.
    *   @param string[] (optional)$actions_ids          A set of actions to which reduce the search of the latest.
    *   @return mixed[]|null                            The latest action, or null if not found.
    */
    static public function get_latest_action($tag, $actions_ids = null){
        $latest_action = null;
        $check_and_replace = function($current_action) use (&$latest_action, $tag){
            if($current_action['tag'] != $tag)//Check if the action is hooked to the action hook we want
                return;
            if(!$latest_action)//First action data found
                $latest_action = $current_action;
            else //if this action is runned after the current latest action found
                $latest_action = $current_action['priority'] >= $current_action['priority'] ? $action_data : $latest_action;
        };

        if(is_array($actions_ids) && !empty($actions_ids)){
            foreach($actions_ids as $action_id){
                if(!isset(self::$stored_actions[$action_id])) //action not found
                    continue;
                $check_and_replace(self::$stored_actions[$action_id]);
            }
        }
        else {
            foreach(self::$stored_actions as $stored_action_data){
                $check_and_replace($stored_action_data);
            }
        }

        return $latest_action;
    }

    /**
    *   Generates an action priority based on the dependencies passed, and the action hook.
    *   @param string[] $tag                                Action hook tag of the hook we want the latest action from.
    *   @param string[] $dependencies                       Array of dependencies actions ids.
    *   @param int $default                                 Default priority.
    */
    static public function generate_action_priority($tag, $dependencies, $default = 10){
        $latest_dependency = self::get_latest_action($tag, $dependencies);
        return $latest_dependency ? $latest_dependency['priority'] : $default;
    }
}
