<?php

namespace WP_Bodybuilder;

/**
 *  Wrapper function to get real base path for this plugin.
 *
 *  @since  0.1.0
 *  @return string  Path to this plugin
 */
function bodybuilder_base_path()
{
  return untrailingslashit(plugin_dir_path(__FILE__));
} // end bodybuilder_base_path
