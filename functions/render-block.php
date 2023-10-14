<?php

function render_bodybuilder_block($attributes, $content, $block)
{
  $is_editor = defined('REST_REQUEST') && true === REST_REQUEST && 'edit' === filter_input(INPUT_GET, 'context', FILTER_SANITIZE_STRING);

  $block_id = explode('/', $block->name)[1];
  $template_path = locate_template("template-parts/blocks/{$block_id}.php");

  if (empty($template_path)) {
    if ($is_editor) {
      return "<section class=\"block\">Block {$block_id}.php file was not found</section>";
    }
    return '';
  }

  $block = new BodybuilderBlock($attributes, $content, $block, $is_editor);

  ob_start();
  require $template_path;
  return ob_get_clean();
}
