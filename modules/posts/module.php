<?php
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

        RB_Actions_Manager::add_action($action_id, 'init', $post_labels_callback, array(
            'priority'      => $priority,
        ));
    }

    /**
    *   Returns a post with a field that matches a value. Can be any field in the wp_posts table
    *   @param string $field_value                          The value that must have the field of the post we are searching for
    *   @param string $field                                The field for which we are searching the post
    *   @param string $post_type                            The post type of the post
    *   @param mixed $output                                Output that the 'get_post' function accepts
    */
    static public function get_post_by($field_value, $field = 'post_title', $post_type = "post", $output = OBJECT){
        global $wpdb;

        if ( is_array( $post_type ) ) {
            $post_type = esc_sql( $post_type );
            $post_type_in_string = "'" . implode( "','", $post_type ) . "'";
            $sql = $wpdb->prepare(
                "
                SELECT ID
                FROM $wpdb->posts
                WHERE $field = %s
                AND post_type IN ($post_type_in_string)
            ",
                $field_value
            );
        } else {
            $sql = $wpdb->prepare(
                "
                SELECT ID
                FROM $wpdb->posts
                WHERE $field = %s
                AND post_type = %s
            ",
                $field_value, $post_type
            );
        }

        $post = $wpdb->get_var( $sql );

        if ( $post ) {
            return get_post( $post, $output );
        }
    }
}
