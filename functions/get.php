<?php
function get_attribute($attribute_name)
{
  if (!isset($attributes)) {
    $message = __('Trying to access attributes outside block.', 'bodybuilder');
    _doing_it_wrong(__FUNCTION__, $message, '6.3.1'); //phpcs:ignore -- escape not required.
    return null;
  }

  return $attributes[$attribute_name];
}
