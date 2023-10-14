# WP Bodybuilder

Open source WordPress block engine for Gutenberg. Combines the best of both worlds: the ease of editing of native Gutenberg blocks and the easiness of ACF blocks.

WP Bodybuilder is currently an **unstable prototype**. I would not recommend to use it in production.

## How to install?

You need to git clone this plugin to your plugins directory, and then run `npm install` and `npm run build` to build the Gutenberg scripts.

## How to use?

Create a file in your theme's `template-parts/blocks` directory. For example: `template-parts/blocks/test.php`. Then add the following code:

```php
<?php
$block->register_attribute('test-attribute', 'string', 'Heading, just not rich text');
$block->register_attribute('another-test-attribute', 'boolean', false);
?>

<section class="block block-testi">
  <h1 wp-rich="title" wp-placeholder="Gutenberg heading placeholder">
    <?php echo $block->attr('title'); ?>
  </h1>
  <h2><?php echo $block->attr('test-attribute'); ?></h2>

  <!-- Use wp-rich-formats to specify allowed formats, default none. -->
  <!-- Separated by a comma, no spaces. If a namespace (namespace/format) is not specified, by default using core -->
  <p wp-rich="description" wp-rich-formats="bold,italic,code,image,text-color,link,keyboard"><?php echo $block->attr('description'); ?></p>

  <?php if ($block->attr('another-test-attribute')) : ?>
    <p>The test attribute is true</p>
  <?php endif; ?>
</section>
```

Then register the block in your theme's `functions.php` file:

```php
function block_register()
{
  register_bodybuilder_block('test');
}

add_action('bodybuilder_init', __NAMESPACE__ . '\block_register');
```

### Using attributes and stuff inside block template

The template file can access the `$block` variable to access the block being rendered.

- `$block->attributes`: Attributes of the block (recommended to use `$block->attr()` instead)
- `$block->content`: The content of the block
- `$block->block`: The WP_Block instance
- `$block->is_editor`: Is the block being rendered in the editor

- `$block->attr('attribute_name')`: Get an attribute value, or return null
- `$block->register_attribute('attribute_name', 'type (string/boolean)', 'default value')`: Register an attribute

## How does it work?

Most of the magic happens client side by using a HTML parser to turn the rendered block HTML to React using `html-to-react`.
In the process the text tags with `wp-rich` attribute are turned into RichText-elements.

On the backend the block is registered and the HTML is parsed to find the wp-rich attribute IDs to register.
