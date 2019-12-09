<?php

/*
*   It helps managing actions hooks, storing information about them in order to
*   allow third parties to remove them
*/
class RB_Actions_Manager{
    static private $stored_actions = array();

    /**
    *   Stores an action id and its callback
    *   @param string $action_id                        Actions identifier
    *   @param string $tag                              The action hook to which the function to be removed is hooked
    *   @param callback $callback                       The callable for the function used when registring the action
    */
    static public function store_action($action_id, $tag, $callback){
        if(!is_string($action_id) || !is_string($tag))
            return false;
        if(self::get_stored_action($action_id))
            throw new Exception("Trying to store an action with an ID that already exists: '$action_id'");
        self::$stored_actions[$action_id] = array(
            'tag'       => $tag,
            'callback'  => $callback,
        );
    }

    /**
    *   Returns an actions stored data
    *   @param string $action_id                        Actions identifier
    */
    static public function get_stored_action($action_id){
        if(!is_string($action_id))
            return null;
        return isset(self::$stored_actions[$action_id]) ? self::$stored_actions[$action_id] : null;
    }

    /**
    *   Removes an action, preventing the callback from being called
    *   @param string $action_id                        Actions identifier
    *   @param string $priority                         How soon should the action hook be removed
    */
    static public function remove_stored_action($action_id, $priority = 10){
        if(!is_string($action_id))
            return false;
        $action = self::get_stored_action($action_id);
        if(!$action)
            return false;
        remove_action($action['tag'], $action['callback'], $priority );
        unset(self::$stored_actions[$action_id]);
    }
}
