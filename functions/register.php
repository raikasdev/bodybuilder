<?php

use PHPHtmlParser\Dom;

function register_bodybuilder_block($id, $args = array())
{
  // We parse the HTML from the template file to get the rich text the block uses
  $template_path = locate_template("template-parts/blocks/{$id}.php");

  $attributes = [];

  if (!empty($template_path)) {
    $block = new FakeBodybuilderBlock(function ($name, $type, $default) use (&$attributes) {
      $attributes[$name] = [
        'type'    => $type,
        'default' => $default,
        'bb-type' => 'sidebar',
      ];
    });

    ob_start();
    require $template_path;
    $html = ob_get_clean();
    $dom = new Dom;
    $dom->loadStr($html);

    $rich_texts = $dom->find('[wp-rich]');

    foreach ($rich_texts as $element) {
      // get the class attr
      $attributeName = $element->getAttribute('wp-rich');
      $attributes[$attributeName] = [
        'type'    => 'string',
        'default' => '',
        'bb-type' => 'rich-text',
      ];
    }
  }


  // Then, we parse the get_field() calls to get the programmatical key for the attributes

  // Then, we register the block
  $block = new WP_Block_Type('bodybuilder/' . $id, array_merge([
    "api_version"     => 3,
    "title"           => "Bodybuilder Block: " . $id,
    "render_callback" => 'render_bodybuilder_block',
    "attributes"      => $attributes,
  ], $args));

  add_filter('bodybuilder_registered_blocks', function ($blocks) use ($block) {
    $blocks[$block->name] = $block->attributes;
    return $blocks;
  });

  $block = register_block_type($block);
}
