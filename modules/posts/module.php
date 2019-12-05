<?php
class RB_WP_Actions_Manager{
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
            throw new Exception("Trying to store an action with an ID that already exists: 'action_id'");
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


class RB_Posts_Module extends RB_Framework_Module{
    static public $dependencies = array();
    static private $track_views = false;
    static private $posts_views_meta_key = 'rb_post_views_count';
    static private $stored_actions = array();

    /**
    *   Tracks the amount of views a post have and stores it in the rb_post_views_count post meta
    */
    static public function track_views(){
        if(self::$track_views)
            return false;
        self::$track_views = true;
        //To keep the count accurate, lets get rid of prefetching
        remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
        add_action('save_post', array(self::class, 'set_default_post_views_meta'), 10, 3);
        return add_action( 'wp_head', array(self::class, 'track_post_views'));
    }

    /**
    *   Sets a default value of 0 on post creation/update to the posts views meta
    */
    static public function set_default_post_views_meta($postID){
        $meta_value = get_post_meta($postID, self::$posts_views_meta_key, true);
        if( !wp_is_post_revision($postID) && $meta_value !== '0' && (!$meta_value || $meta_value == '')  )
            update_post_meta( $postID, 'rb_post_views_count', '0');
    }

    /**
    *
    */
    static public function track_post_views($postID){
        if ( !is_single() ) return;
        if ( empty($post_id) ) {
            global $post;
            $post_id = $post->ID;
        }
        // if(get_post_type($post_id) == 'lr-article')
        self::update_post_views($post_id);
    }

    /**
    *
    */
    static private function update_post_views($postID){
        $count = get_post_meta($postID, self::$posts_views_meta_key, true);
        $count = !$count && $count !== 0 ? 0 : $count;
        update_post_meta($postID, self::$posts_views_meta_key, $count + 1);
    }

    /**
    *   Sets new labels for a post type
    *   @param string $action_id                        An string with which to identify the action hook.
    *                                                   It can be used to remove the hook if necessary, avoiding any
    *                                                   changes to the posts labels.
    *   @param string $post_type                        The post type to which to change the labels
    *   @param string[] $labels                         Array of labels to set
    *   @param int $priority                            Indicates how soon on the action hooks is the callback runned
    */
    static public function change_post_type_labels($action_id, $post_type, $new_labels, $priority = 10){
        if(!is_string($action_id) || !is_array($new_labels) || empty($new_labels) || !is_string($post_type))
            return false;

        $post_labels_callback = function() use ($post_type, $new_labels){
            $post_type_object = get_post_type_object($post_type);
            if(!$post_type_object)
                return false;
            $labels = $post_type_object->labels;
            foreach($new_labels as $label_name => $label){
                $labels->$label_name = $label;
            }
            return true;
        };
        add_action( 'init', $post_labels_callback, $priority );
        RB_WP_Actions_Manager::store_action($action_id, 'init', $post_labels_callback);
    }
}
