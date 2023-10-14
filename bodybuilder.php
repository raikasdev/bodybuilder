<?php

/**
 * @package wp-bodybuilder
 *
 * Plugin Name:       WP Bodybuilder
 * Plugin URI:        https://github.com/raikasdev/bodybuilder
 * Description:       Build native WordPress blocks using PHP without having to leave your editor
 * Author:            Roni Äikäs
 * Author URI:        https://raikas.dev
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Version:           1.0.0
 */

namespace WP_Bodybuilder;

const PLUGIN_VERSION = '1.0.0';

require plugin_dir_path(__FILE__) . "vendor/autoload.php";
require 'helpers.php';

function bodybuilder_lift_up()
{
  require_once bodybuilder_base_path() . '/inc/block.php';

  require_once bodybuilder_base_path() . '/functions/render-block.php';
  require_once bodybuilder_base_path() . '/functions/register.php';

  do_action('bodybuilder_init');
}

add_action('init', __NAMESPACE__ . '\bodybuilder_lift_up'); // Start the engine
add_action('enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue_bodybuilder_js'); // Enqueue assets for Gutenberg

function enqueue_bodybuilder_js()
{
  wp_enqueue_script('bodybuilder-scripts', plugin_dir_url(__FILE__) . 'build/index.js', [], filemtime(plugin_dir_path(__FILE__) . 'build/index.js'), true);

  $blocks = apply_filters('bodybuilder_registered_blocks', []);
  wp_add_inline_script('bodybuilder-scripts', 'window.bodybuilder.register_blocks(' . json_encode($blocks) . ');');
}
