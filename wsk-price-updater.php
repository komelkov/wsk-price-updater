<?php
/*
Plugin Name: Update Product Prices by WSK
Description: Woocommerce plugin to update regular and sales prices in products.
Version: 1.0
Author: Pavel Komelkov
Author URI:
License:
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Text Domain: wsk-update-prices
Domain Path: /languages/
*/

// Load plugin text domain for internationalization
add_action('plugins_loaded', 'wsk_load_plugin_textdomain');
function wsk_load_plugin_textdomain()
{
  load_plugin_textdomain('wsk-update-prices', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

// Add plugin options page to the Woocommerce admin menu
add_action('admin_menu', 'wsk_add_custom_price_settings_page');
function wsk_add_custom_price_settings_page()
{
  add_submenu_page(
    'woocommerce',
    __('Update Product Prices', 'wsk-update-prices'),
    __('Update Prices', 'wsk-update-prices'),
    'manage_options',
    'wsk_custom_price_settings',
    'wsk_custom_price_settings_page'
  );
}

// Display the content of the plugin options page
function wsk_custom_price_settings_page()
{
  if (!current_user_can('manage_options')) {
    return;
  }

  if (isset($_GET['settings-updated'])) {
    add_settings_error('wsk_custom_price_settings', 'wsk_custom_price_settings_message', __('Settings saved', 'wsk-update-prices'), 'updated');
  }

  settings_errors('wsk_custom_price_settings');
?>
  <div class="wrap">
    <div id="preloader" class="hide">
      <img src="<?= plugin_dir_url(__FILE__) . 'assets/media/Spinner-1s-200px.svg' ?>" alt="" class="loader">
    </div>
    <h1><?= __('Update Product Prices', 'wsk-update-prices'); ?></h1>
    <form action="options.php" method="post">
      <?php
      settings_fields('wsk_custom_price_settings');
      do_settings_sections('wsk_custom_price_settings');
      submit_button(__('Save', 'wsk-update-prices'));
      ?>
    </form>
  </div>
<?php
}

// Register plugin options
add_action('admin_init', 'wsk_register_custom_price_settings');
function wsk_register_custom_price_settings()
{
  register_setting(
    'wsk_custom_price_settings',
    'wsk_custom_product',
    array(
      'type' => 'string',
      'sanitize_callback' => 'sanitize_text_field',
      'default' => '',
    )
  );

  register_setting(
    'wsk_custom_price_settings',
    'wsk_custom_variation',
    array(
      'type' => 'array',
      'sanitize_callback' => 'sanitize_text_field',
      'default' => array(),
    )
  );

  register_setting(
    'wsk_custom_price_settings',
    'wsk_custom_regular_price',
    array(
      'type' => 'number',
      'sanitize_callback' => 'sanitize_text_field',
      'default' => '',
    )
  );

  register_setting(
    'wsk_custom_price_settings',
    'wsk_custom_sales_price',
    array(
      'type' => 'number',
      'sanitize_callback' => 'sanitize_text_field',
      'default' => '',
    )
  );
}

// Add settings section to the plugin options page
add_action('admin_init', 'wsk_add_custom_price_settings_section');
function wsk_add_custom_price_settings_section()
{
  add_settings_section(
    'wsk_custom_price_section',
    __('Settings', 'wsk-update-prices'),
    'wsk_custom_price_section_callback',
    'wsk_custom_price_settings'
  );

  add_settings_field(
    'wsk_custom_product_field',
    __('Product', 'wsk-update-prices'),
    'wsk_custom_product_field_callback',
    'wsk_custom_price_settings',
    'wsk_custom_price_section'
  );

  add_settings_field(
    'wsk_custom_variation_field',
    __('Variations', 'wsk-update-prices'),
    'wsk_custom_variation_field_callback',
    'wsk_custom_price_settings',
    'wsk_custom_price_section'
  );

  add_settings_field(
    'wsk_custom_regular_price_field',
    __('Regular Price', 'wsk-update-prices'),
    'wsk_custom_regular_price_field_callback',
    'wsk_custom_price_settings',
    'wsk_custom_price_section'
  );

  add_settings_field(
    'wsk_custom_sales_price_field',
    __('Sales Price', 'wsk-update-prices'),
    'wsk_custom_sales_price_field_callback',
    'wsk_custom_price_settings',
    'wsk_custom_price_section'
  );
}

// Display the text for the settings section
function wsk_custom_price_section_callback()
{
  echo __('Select the product and variations (if applicable), enter the regular price, sales price to update the prices.', 'wsk-update-prices');
}

// Display the product selection field
function wsk_custom_product_field_callback()
{
  $products = wc_get_products(array('status' => 'publish', 'numberposts' => '-1', 'orderby' => 'title', 'order' => 'asc'));
?>
  <select name="wsk_custom_product" id="wsk-custom-product" required>
    <option value="" selected="selected"><?= __('Select a product', 'wsk-update-prices'); ?></option>
    <?php foreach ($products as $product) : ?>
      <option value="<?= $product->get_id(); ?>">
        <?= $product->get_name(); ?>
      </option>
    <?php endforeach; ?>
  </select>
<?php
}

// Display the variation selection field using AJAX
function wsk_custom_variation_field_callback()
{
?>
  <div id="wsk-variation-field">
    <p><?= __('Loading variations...', 'wsk-update-prices'); ?></p>
  </div>
<?php
}

// Display the regular price input field
function wsk_custom_regular_price_field_callback()
{
?>
  <input type="number" name="wsk_custom_regular_price" step="any" min="0" value="" required>
<?php
}

// Display the sales price input field
function wsk_custom_sales_price_field_callback()
{
?>
  <input type="number" name="wsk_custom_sales_price" step="any" min="0" value="" required>
<?php
}

// Enqueue JavaScript code and localize script
add_action('admin_enqueue_scripts', 'wsk_enqueue_scripts');
function wsk_enqueue_scripts($hook)
{
  if ($hook === 'woocommerce_page_wsk_custom_price_settings') {
    wp_enqueue_style('wsk-admin-style', plugin_dir_url(__FILE__) . 'assets/css/app.css', '1.0');
    wp_enqueue_script('wsk-admin-script', plugin_dir_url(__FILE__) . 'assets/js/app.js', array(), '1.0', true);
  }
}

// AJAX callback to load variations
add_action('wp_ajax_wsk_load_variations', 'wsk_load_variations');
function wsk_load_variations()
{
  $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
  $variations = array();

  if ($product_id > 0) {
    $product = wc_get_product($product_id);
    if ($product && $product->is_type('variable')) {
      $variation_data = $product->get_available_variations();
      $variations = [];
      foreach ($variation_data as $variation) {
        $variations[] = array(
          'variation' => $variation,
          'variation_id' => $variation['variation_id'],
          'attributes' => implode(', ', $variation['attributes']),
        );;
      }
    }
  }

  wp_send_json_success($variations);
  wp_die();
}

// Save product prices on form submission
add_action('admin_init', 'wsk_save_product_prices');
function wsk_save_product_prices()
{
  if (isset($_POST['wsk_custom_product'])) {
    $product_id = sanitize_text_field($_POST['wsk_custom_product']);
    $product = new WC_Product($product_id);
    $regular_price = sanitize_text_field($_POST['wsk_custom_regular_price']);
    $sales_price = sanitize_text_field($_POST['wsk_custom_sales_price']);
    $variations = isset($_POST['wsk_custom_variation']) ? array_map('sanitize_text_field', $_POST['wsk_custom_variation']) : array();

    if ($product_id && ($regular_price || $sales_price)) {
      if (!empty($variations)) {
        foreach ($variations as $variation_id) {
          update_post_meta($variation_id, '_regular_price', $regular_price * 3);
          update_post_meta($variation_id, '_sale_price', $sales_price * 3);
        }
      } else {
        update_post_meta($product_id, '_regular_price', $regular_price * 3);
        update_post_meta($product_id, '_sale_price', $sales_price * 3);
      }

      add_settings_error('wsk_custom_price_settings', 'wsk_custom_price_settings_message', sprintf(__('Prices for "%s" item have been updated', 'wsk-update-prices'), $product->get_name()), 'updated');
    }
  }
}
