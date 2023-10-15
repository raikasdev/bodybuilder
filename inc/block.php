<?php
// Block global in template
$data = [
  'attributes' => [],
  'content' => '',
  'block' => null,
  'is_editor' => true,
];

class BodybuilderBlock
{
  public $attributes;
  public $content;
  public $block;
  public $is_editor;

  public $attr;
  public $register_attribute;

  function __construct($attributes, $content, $block, $is_editor)
  {
    $this->attributes = $attributes;
    $this->content = $content;
    $this->block = $block;
    $this->is_editor = $is_editor;
  }

  function attr($attribute_name)
  {
    return array_key_exists($attribute_name, $this->attributes) ? $this->attributes[$attribute_name] : null;
  }

  function register_attribute($name, $label, $type = 'string', $default = '', $args = [])
  {
  }
}

// Used during registration
class FakeBodybuilderBlock extends BodybuilderBlock
{
  public $callback;

  function __construct($callback)
  {
    $this->callback = $callback;

    $this->attributes = [];
    $this->content = '';
    $this->block = null;
    $this->is_editor = true;
  }

  function attr($attribute_name)
  {
    return null;
  }

  function register_attribute($name, $label, $type = 'string', $default = '', $args = [])
  {
    if (!isset($label)) {
      $label = $name;
    }

    call_user_func($this->callback, $name, $label, $type, $default, $args);
  }
}
