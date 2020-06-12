<?php

abstract class RB_Objects_List_Column{
    /**
    *   @property string $id
    *   Id of the column
    */
    public $id;

    /**
    *   @property string[]|string $admin_pages
    *   String or array of strings representing the slug of the wp object screens where to add the new column
    */
    public $admin_pages;

    /**
    *   @property string $title
    *   Title to show on the column header
    */
    public $title;

    /**
    *   @property callback $render_callback
    *   The callback that renders the column content. This arguments are passed through
    *   @param string $column                               The column id
    *   @param mixed|int|null $wp_object                    The wordpress object
    */
    public $render_callback;

    /**
    *   @property string $cell_class
    *   Class for the div containing the cell content
    */
    public $cell_class = '';

    /**
    *   @property int $position
    *   Position of the column on the list. Defaults to the last position posible during runtime
    *   First position is 0
    */
    public $position = null;

    public function __construct($id, $admin_pages, $title, $render_callback, $args = array()) {
        $this->id = $id;
        $this->admin_pages = $admin_pages;
        $this->title = $title;
        $this->render_callback = $render_callback;
        $this->cell_class = isset($args['cell_class']) && is_string($args['cell_class']) ? $args['cell_class'] : $this->cell_class;
        $this->position = isset($args['position']) && is_int($args['position']) ? $args['position'] : $this->position;
        $this->column_setup();
    }

    /**
    *   Sets up the column to show on the list of wp objects.
    */
    protected function column_setup(){
        foreach($this->get_admin_pages() as $admin_page){
            $this->setup_screen_column($admin_page);
        }
    }

    abstract protected function setup_screen_column($admin_page);
    abstract protected function get_object($wp_object);

    /**
    *   Adds the metabox column to the wp objects list. The content is then setted by render_content
    *   @param string[] $columns                            Columns names array
    */
    public function add_column_base($columns){
        $original_columns = $columns;
        $columns_amount = count($original_columns);
        $position = is_int($this->position) && $this->position >= 0 && $this->position <= $columns_amount ? $this->position : $columns_amount;

        if($position == $columns_amount){
            $columns[$this->id] = $this->title;
        }
        else{
            $last_half = array_slice($original_columns, $position);
            var_dump($position);
            $last_half = array_merge(array( $this->id => $this->title), $last_half);
            array_splice( $columns, $position, 0, $last_half );
        }
        return $columns;
    }

    /**
    *   Render the content for this column
    *   @param string $columns                              Column name
    *   @param mixed|int|null $wp_object                    ID or instances of the wp object.
    */
    public function render_content($column, $wp_object = null){
        if( is_callable($this->render_callback) ):
            ?>
            <div class="rb-object-column <?php echo esc_attr($this->filter_cell_class()); ?>">
                <?php call_user_func($this->render_callback, $column, $this->get_object($wp_object)); ?>
            </div>
            <?php
        endif;
    }

    /**
    *   Returns the admin pages where this metabox will be added
    *   @return string[]
    */
    public function get_admin_pages(){
        return is_array($this->admin_pages) ? $this->admin_pages : [$this->admin_pages];
    }

    /**
    *
    */
    public function filter_cell_class(){
        return $this->cell_class;
    }
}
