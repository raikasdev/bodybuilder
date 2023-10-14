<?php

/**
 * Register WP Bodybuilder settings
 */

/**
 * Add the settings
 */
add_action('admin_init', 'bodybuilder_settings_init');
function bodybuilder_settings_init()
{
  // Register a new setting for "bodybuilder" page.
  register_setting('bodybuilder', 'bodybuilder_options');

  // Register a new section in the "bodybuilder" page.
  add_settings_section(
    'bodybuilder_section_developers',
    __('The Matrix has you.', 'bodybuilder'),
    'bodybuilder_section_developers_callback',
    'bodybuilder'
  );

  // Register a new field in the "bodybuilder_section_developers" section, inside the "bodybuilder" page.
  add_settings_field(
    'bodybuilder_field_pill', // As of WP 4.6 this value is used only internally.
    // Use $args' label_for to populate the id inside the callback.
    __('Pill', 'bodybuilder'),
    'bodybuilder_field_pill_cb',
    'bodybuilder',
    'bodybuilder_section_developers',
    array(
      'label_for'         => 'bodybuilder_field_pill',
      'class'             => 'bodybuilder_row',
      'bodybuilder_custom_data' => 'custom',
    )
  );
}

/**
 * Developers section callback function.
 *
 * @param array $args  The settings array, defining title, id, callback.
 */
function bodybuilder_section_developers_callback($args)
{
?>
  <p id="<?php echo esc_attr($args['id']); ?>"><?php esc_html_e('Follow the white rabbit.', 'bodybuilder'); ?></p>
<?php
}

/**
 * Pill field callbakc function.
 *
 * WordPress has magic interaction with the following keys: label_for, class.
 * - the "label_for" key value is used for the "for" attribute of the <label>.
 * - the "class" key value is used for the "class" attribute of the <tr> containing the field.
 * Note: you can add custom key value pairs to be used inside your callbacks.
 *
 * @param array $args
 */
function bodybuilder_field_pill_cb($args)
{
  // Get the value of the setting we've registered with register_setting()
  $options = get_option('bodybuilder_options');
?>
  <select id="<?php echo esc_attr($args['label_for']); ?>" data-custom="<?php echo esc_attr($args['bodybuilder_custom_data']); ?>" name="bodybuilder_options[<?php echo esc_attr($args['label_for']); ?>]">
    <option value="red" <?php echo isset($options[$args['label_for']]) ? (selected($options[$args['label_for']], 'red', false)) : (''); ?>>
      <?php esc_html_e('red pill', 'bodybuilder'); ?>
    </option>
    <option value="blue" <?php echo isset($options[$args['label_for']]) ? (selected($options[$args['label_for']], 'blue', false)) : (''); ?>>
      <?php esc_html_e('blue pill', 'bodybuilder'); ?>
    </option>
  </select>
  <p class="description">
    <?php esc_html_e('You take the blue pill and the story ends. You wake in your bed and you believe whatever you want to believe.', 'bodybuilder'); ?>
  </p>
  <p class="description">
    <?php esc_html_e('You take the red pill and you stay in Wonderland and I show you how deep the rabbit-hole goes.', 'bodybuilder'); ?>
  </p>
<?php
}

/**
 * Add the top level menu page.
 */
function bodybuilder_options_page()
{
  add_submenu_page(
    'options-general.php',
    'WP Bodybuilder',
    'WP Bodybuilder',
    'manage_options',
    'bodybuilder',
    'bodybuilder_options_page_html'
  );
}


/**
 * Register our bodybuilder_options_page to the admin_menu action hook.
 */
add_action('admin_menu', 'bodybuilder_options_page');


/**
 * Top level menu callback function
 */
function bodybuilder_options_page_html()
{
  // check user capabilities
  if (!current_user_can('manage_options')) {
    return;
  }

  // add error/update messages

  // check if the user have submitted the settings
  // WordPress will add the "settings-updated" $_GET parameter to the url
  if (isset($_GET['settings-updated'])) {
    // add settings saved message with the class of "updated"
    add_settings_error('bodybuilder_messages', 'bodybuilder_message', __('Settings Saved', 'bodybuilder'), 'updated');
  }

  // show error/update messages
  settings_errors('bodybuilder_messages');
?>
  <div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <form action="options.php" method="post">
      <?php
      // output security fields for the registered setting "bodybuilder"
      settings_fields('bodybuilder');
      // output setting sections and their fields
      // (sections are registered for "bodybuilder", each field is registered to a specific section)
      do_settings_sections('bodybuilder');
      // output save settings button
      submit_button('Save Settings');
      ?>
    </form>
  </div>
<?php
}
