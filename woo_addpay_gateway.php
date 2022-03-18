<?php
/**
 * Plugin Name: WooCommerce AddPay Gateway
 * Plugin URI: http://github.com/addpay/addpay-woocommerce
 * Description: Receive payments using the AddPay payments provider.
 * Author: AddPay Pty Ltd
 * Author URI: https://www.addpay.co.za/
 * Developer: Richard Slabbert/Stephen Lake
 * Developer URI: https://www.addpay.co.za/
 * Version: 2.5.14
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Required functions
 */
if (! function_exists('woothemes_queue_update')) {
    require_once('woo-includes/woo-functions.php');
}

/**
 * Initialize the gateway.
 * @since 1.0.0
 */
function woocommerce_addpay_init()
{
    if (! class_exists('WC_Payment_Gateway')) {
        return;
    }
    require_once(plugin_basename('includes/class-wc-gateway-addpay.php'));
    load_plugin_textdomain('woocommerce-gateway-addpay', false, trailingslashit(dirname(plugin_basename(__FILE__))));
    add_filter('woocommerce_payment_gateways', 'woocommerce_addpay_add_gateway');
}
add_action('plugins_loaded', 'woocommerce_addpay_init', 0);

function woocommerce_addpay_plugin_links($links)
{
    $settings_url = add_query_arg(
        array(
            'page' => 'wc-settings',
            'tab' => 'checkout',
            'section' => 'wc_gateway_addpay',
        ),
        admin_url('admin.php')
    );

    $plugin_links = array(
        '<a href="' . esc_url($settings_url) . '">' . __('Settings', 'woocommerce-gateway-addpay') . '</a>',
        '<a href="https://support.woothemes.com/">' . __('Support', 'woocommerce-gateway-addpay') . '</a>',
        '<a href="https://docs.woothemes.com/document/addpay-payment-gateway/">' . __('Docs', 'woocommerce-gateway-addpay') . '</a>',
    );

    return array_merge($plugin_links, $links);
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'woocommerce_addpay_plugin_links');


/**
 * Add the gateway to WooCommerce
 * @since 1.0.0
 */
function woocommerce_addpay_add_gateway($methods)
{
    $methods[] = 'WC_Gateway_AddPay';
    return $methods;
}
