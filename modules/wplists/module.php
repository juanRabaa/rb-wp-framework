<?php

if(!class_exists('RB_Objects_Lists')){
    define('RB_OBJECT_LISTS_MASTER_DIR', plugin_dir_path(__FILE__));

    class RB_Objects_Lists{
        static private $initialized = false;

    	static public function initialize(){
    		if(self::$initialized)
    			return false;
            self::$initialized = true;

            require_once RB_OBJECT_LISTS_MASTER_DIR . 'inc/RB_Objects_List_Column.php';
            require_once RB_OBJECT_LISTS_MASTER_DIR . 'inc/RB_Terms_List_Column.php';
            require_once RB_OBJECT_LISTS_MASTER_DIR . 'inc/RB_Posts_List_Column.php';
    	}
    }

    RB_Objects_Lists::initialize();

    // /**
    // *   @param string $column_id                                The id of the new column
    // *   @param string[]|string $tax_slugs                       The taxonomies where this column will be added
    // *   @param string $column_title                             The column header title
    // *   @param callback $render_callback                        Function that prints the column cell content
    // *       @param string $column                               The column id
    // *       @param WP_Post|int|null $post                       The row's term.
    // */
    // new RB_Terms_List_Column('test_column', 'lr-article-author', 'Tax Col', function($column, $term){
    //     var_dump($column);
    // }, array(
    //     'position'      => 4,
    //     'column_class'  => 'test-class',
    // ));
    //
    // /**
    // *   @param string $column_id                                The id of the new column
    // *   @param string[]|string $post_types                      The post types where this column will be added
    // *   @param string $column_title                             The column header title
    // *   @param callback $render_callback                        Function that prints the column cell content
    // *       @param string $column                               The column id
    // *       @param WP_Post|int|null $post                       The row's post.
    // */
    // new RB_Posts_List_Column('test_column', 'lr-article', 'Post Column', function($column, $post){
    //     var_dump($post->post_title);
    // }, array(
    //     'position'      => 4,
    //     'column_class'  => 'test-class',
    // ));
}
