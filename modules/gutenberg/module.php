<?php
class RB_Gutenberg_Module extends RB_Framework_Module{
    /**
    *   Parse a gutenberg content string into an HTML string
    */
    static public function parse_content($content){
        $blocks = parse_blocks($content);
        $html = '';
        foreach($blocks as $block)
            $html .= render_block($block);
        return $html;
    }
}
