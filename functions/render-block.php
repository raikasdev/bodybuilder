<?php

use PHPHtmlParser\Dom;
use PHPHtmlParser\Dom\Node\TextNode;

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
  $html = ob_get_clean();

  if ($is_editor) {
    return $html;
  }

  $dom = new Dom;
  $dom->loadStr($html);

  $rich_texts = $dom->find('[wp-rich]');

  // Remove rich text attributes
  foreach ($rich_texts as $element) {
    // get the class attr
    $element->removeAttribute('wp-rich');
    $element->removeAttribute('wp-placeholder');
    $element->removeAttribute('wp-rich-formats');
  }

  // Replace inner_blocks with content
  $inner_blocks = $dom->find('innerblocks');
  foreach ($inner_blocks as $inner_block) {
    // get the class attr
    $reflection = new \ReflectionProperty(get_class($inner_block), 'outerHtml');
    $reflection->setAccessible(true);
    $reflection->setValue($inner_block, $block->content);
  }

  return $dom;
}
