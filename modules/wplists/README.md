# RB_Posts_List_Column

Adds a column on a posts list

````php
<?php
/**
*   @param string $column_id                                The id of the new column
*   @param string[]|string $post_types                      The post types where this column will be added
*   @param string $column_title                             The column header title
*   @param callback $render_callback                        Function that prints the column cell content
*       @param string $column                               The column id
*       @param WP_Post|int|null $post                       The row's post.
*   @param mixed $args                                      Extra settings for the column
*/
new RB_Posts_List_Column($column_id, $post_types, $column_title, function($column, $post){
    echo "Column content";
}, array(
    'position'      => 4,
    'column_class'  => 'test-class',
));
````

# RB_Posts_List_Column

Adds a column on a taxonomy terms list

````php
<?php
/**
*   @param string $column_id                                The id of the new column
*   @param string[]|string $tax_slugs                       The taxonomies where this column will be added
*   @param string $column_title                             The column header title
*   @param callback $render_callback                        Function that prints the column cell content
*       @param string $column                               The column id
*       @param WP_Post|int|null $post                       The row's term.
*   @param mixed $args                                      Extra settings for the column
*/
new RB_Terms_List_Column($column_id, $tax_slugs, $column_title, function($column, $term){
    echo "Column content";
}, array(
    'position'      => 4,
    'column_class'  => 'test-class',
));
````
