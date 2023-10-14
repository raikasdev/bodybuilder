# WP Bodybuilder

Open source WordPress block engine for Gutenberg. Combines the best of both worlds: the ease of editing of native Gutenberg blocks and the easiness of ACF blocks.

WP Bodybuilder is currently an unstable prototype. I would not recommend to use it in production.

## How to install?

You need to git clone this plugin to your plugins directory, and then run `npm install` and `npm run build` to build the Gutenberg scripts.

## How to use?

Create a file in your theme's `template-parts/blocks` directory. For example: `template-parts/blocks/test.php`. Then add the following code:

```php
<section class="block block-test">
  <h1 wp-rich="title">Title placeholder</h1>
  <h2>Not a rich title</h2>

  <!-- Use wp-rich-formats to specify allowed formats, default none. -->
  <!-- Separated by a comma, no spaces. If a namespace (namespace/format) is not specified, by default using core -->
  <p wp-rich="description" wp-rich-formats="bold,italic,code,image,text-color,link,keyboard">Description placeholder</p>
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

The template file can access the `$data` variable to access the following values:

- attributes: The attributes of the block
- content: The content of the block
- block: The block instance
- is_editor: Whether the block is being rendered in the editor or not

## How does it work?

Most of the magic happens client side by using a HTML parser to turn the rendered block HTML to React using `html-to-react`.
In the process the text tags with `wp-rich` attribute are turned into RichText-elements.

On the backend the block is registered and the HTML is parsed to find the wp-rich attribute IDs to register.
