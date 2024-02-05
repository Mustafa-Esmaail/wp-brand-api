<?php
/*
Plugin Name: Brand API
Plugin URI: https://github.com/Mustafa-Esmaail/wp-Brand-Category-clone
Description: Custom API Brand with product Category id .
Version: 1.0
Author: Mustafa Esmaail
Author URI: https://github.com/Mustafa-Esmaail
*/

if (!function_exists('wp_crop_image')) {
  include(ABSPATH . 'wp-admin/includes/image.php');
}
// Prevent direct access to the plugin file


// Register API endpoint
add_action('rest_api_init', 'product_api');



function product_api()
{
  register_rest_route(
    'brand-api/v1',
    '/brand',
    array(
      'methods' => 'GET',
      'callback' => 'product_api_handler',
      'permission_callback' => '__return_true',
    )
  );
}





function product_api_handler(WP_REST_Request $request)
{



  $consumer_key = 'ck_27e0107113e03925d3d1c8f5957f2332db3d55f1';
  $consumer_secret = 'cs_c2344f43b38675b30e96f7f177c18c52b11b3a67';

  // WooCommerce API URL
  $url = 'https://toolsworld.ivalleytraining.com/wp-json/wc/v3/products';

  $category_id = isset($_GET['category_id']) ? $_GET['category_id'] : null;


  // $attribute_id = wc_get_attribute_id_by_name($attribute_value);

  // Add parameters for category and attribute
  $parameters = array(
    'category' => $category_id,
    'consumer_key' => $consumer_key,
    'consumer_secret' => $consumer_secret,
  );

  $response = wp_remote_get(add_query_arg($parameters, $url));


  // Check for errors
  if (is_wp_error($response)) {
    echo 'Error: ' . $response->get_error_message();
  } else {
    $brand = [];
    // Parse the JSON response
    $products = json_decode(wp_remote_retrieve_body($response));
    $terms = get_terms('pa_brand');

    // Loop through the products
    foreach ($products as $product) {
      foreach ($product->attributes as $attribute) {
        if ($attribute->name == "Brand") {
          $string_version = implode(' ', $attribute->options);
          foreach ($terms as $term) {
            if ($term->name == $string_version) {

              $termmeta = get_term_meta($term->term_id, 'image');
              $imgUrl = str_replace('/home/toolsivv/public_html', 'https://tools.ivalleytraining.com', $termmeta[0]['url']);
              $brand[$term->term_id]['name'] = $string_version;
              $brand[$term->term_id]['img'] = $imgUrl;
            }
          }
        }
      }
    }
  }
  return  $brand;
}
