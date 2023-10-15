<?php

use PHPHtmlParser\Dom;

function register_bodybuilder_block($args = array())
{
  if (!isset($args['name'])) {
    _doing_it_wrong('register_bodybuilder_block', 'Block name must be supplied', '1.0.0');
    return;
  }
  if (!isset($args['title'])) {
    $args['title'] = $args['name'];
  }

  // We parse the HTML from the template file to get the rich text the block uses
  // TODO: add filter
  $template_path = locate_template("template-parts/blocks/{$args['name']}.php");

  $attributes = [];

  if (!empty($template_path)) {
    $block = new FakeBodybuilderBlock(function ($name, $label, $type, $default, $args) use (&$attributes) {
      switch ($type) {
        case 'enum':
          $attributes[$name] = [
            'type'       => 'string',
            'enum'       => array_keys($args),
            'default'    => $default,
            'bb-type'    => 'sidebar',
            'bb-label'   => $label,
            'bb-options' => $args,
          ];
          break;
        default:
          $attributes[$name] = [
            'type'     => $type,
            'default'  => $default,
            'bb-type'  => 'sidebar',
            'bb-label' => $label,
          ];
          break;
      }
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
  $block = new WP_Block_Type('bodybuilder/' . $args['name'], array_merge($args, [
    "api_version"     => 3,
    "title"           => $args['title'],
    "render_callback" => 'render_bodybuilder_block',
    "attributes"      => $attributes,
  ]));

  add_filter('bodybuilder_registered_blocks', function ($blocks) use ($block) {
    $blocks[$block->name] = $block->attributes;
    return $blocks;
  });

  $block = register_block_type($block);
}
