<?php
/**
 * AddPay Payment Gateway
 *
 * Provides the AddPay Payment Gateway.
 *
 * @class 		woocommerce_addpay
 * @package		WooCommerce
 * @category	Payment Gateways
 * @author		AddPay
 */
define('WC_GATEWAY_ADDPAY_VERSION', '2.5.15');

include('class-wc-gateway-addpay-assets.php');

class WC_Gateway_AddPay extends WC_Payment_Gateway
{

    /**
     * Version
     */
    public $version;

    /**
     * HTTP Payload
     */
    protected $payload = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->version            = WC_GATEWAY_ADDPAY_VERSION;
        $this->id                 = 'addpay';
        $this->method_title       = __('AddPay', 'woocommerce-gateway-addpay');

        $this->method_description = sprintf(__('AddPay works by sending the user to %1$sAddPay%2$s to enter their payment information.', 'woocommerce-gateway-addpay'), '<a href="http://addpay.co.za/">', '</a>');
        $this->icon               = WP_PLUGIN_URL . '/' . plugin_basename(dirname(dirname(__FILE__))) . '/assets/images/logo.png';
        $this->debug_email        = get_option('admin_email');

        $this->supports = array(
            'products',
        );

        $this->init_form_fields();
        $this->init_settings();

        $this->client_id            = $this->get_option('client_id');
        $this->client_secret        = $this->get_option('client_secret');
        $this->environment          = $this->get_option('environment');
        $this->title                = $this->get_option('title');
        $this->description          = $this->get_option('description');
        $this->payment_url          = $this->get_option('payment_url');
        $this->enabled              = 'yes';


        if ($this->environment == 'yes') {
            $this->url              = 'https://secure.addpay.co.za/v2/transactions';
        } else {
            $this->url              = 'https://secure-test.addpay.co.za/v2/transactions';
        }
        
        add_action('woocommerce_update_options_payment_gateways', [$this, 'process_admin_options']);
        add_action('woocommerce_update_options_payment_gateways_addpay', [$this, 'process_admin_options']);

        $this->check_result();

        add_action('woocommerce_api_wc_gateway_addpay', [$this, 'check_result']);
    }

    /**
     * Initialise Gateway Settings Form Fields
     *
     * @since 1.0.0
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title'       => __('Enable/Disable', 'woocommerce-gateway-addpay'),
                'label'       => __('Enable AddPay', 'woocommerce-gateway-addpay'),
                'type'        => 'checkbox',
                'description' => __('This controls whether or not this gateway is enabled within WooCommerce.', 'woocommerce-gateway-addpay'),
                'default'     => 'yes',
                'desc_tip'    => true,
            ),
            'title' => array(
                'title'       => __('Title', 'woocommerce-gateway-addpay'),
                'type'        => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'woocommerce-gateway-addpay'),
                'default'     => __('AddPay', 'woocommerce-gateway-addpay'),
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => __('Description', 'woocommerce-gateway-addpay'),
                'type'        => 'text',
                'description' => __('This controls the description which the user sees during checkout.', 'woocommerce-gateway-addpay'),
                'default'     => __('Proceed via AddPay suite of payment methods.', 'woocommerce-gateway-addpay'),
                'desc_tip'    => true,
            ),
            'client_id' => array(
                'title'       => __('Client ID', 'woocommerce-gateway-addpay'),
                'type'        => 'text',
                'description' => __('This is the Client ID generated on the AddPay merchant console.', 'woocommerce-gateway-addpay'),
                'default'     => 'CHANGE ME',
            ),
            'client_secret' => array(
                'title'       => __('Client Secret', 'woocommerce-gateway-addpay'),
                'type'        => 'text',
                'description' => __('This is the Client Secret generated on the AddPay merchant console.', 'woocommerce-gateway-addpay'),
                'default'     => 'CHANGE ME',
            ),
            'environment' => array(
                'title'       => __('Environment', 'woocommerce-gateway-addpay'),
                'label'       => __('Live AddPay API Credentials', 'woocommerce-gateway-addpay'),
                'type'        => 'checkbox',
                'description' => __('This controls whether or not this gateway is using sandbox or live credentials.', 'woocommerce-gateway-addpay'),
                'default'     => 'no',
                'desc_tip'    => true,
            ),
            'payment_url' => array(
                'title'       => __('Payment URL', 'woocommerce-gateway-addpay'),
                'type'        => 'text',
                'description' => __('The URL of your custom payment page. AddPay Plus Customers only.  ', 'woocommerce-gateway-addpay'),
                'default'     => __('', 'woocommerce-gateway-addpay'),
            )

        );
    }

    /**
     * Process the payment and return the result.
     *
     * @since 1.0.0
     */
    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);

        $this->payload = json_encode(array(
            'reference'   => $order->get_order_number(),
            'description' => get_bloginfo('name'),
            'customer' => array(
                'firstname' => self::get_order_prop($order, 'billing_first_name'),
                'lastname'  => self::get_order_prop($order, 'billing_last_name'),
                'email'     => self::get_order_prop($order, 'billing_email'),
                'mobile'    => self::get_order_prop($order, 'billing_phone'),
            ),
            'amount'  => array(
              'value'         => $order->get_total(),
              'currency_code' => get_woocommerce_currency(),
            ),
            'service' => array(
              'key'     => 'DIRECTPAY',
              'intent'  => 'SALE'
            ),
            'return_url'    => $this->get_return_url($order),
            'notify_url'    => str_replace('https:', 'http:', add_query_arg('wc-api', 'WC_Gateway_Addpay', home_url('/')))
        ));

        $this->result = wp_remote_post($this->url, array(
              'method'       => 'POST',
              'timeout'      => 45,
              'redirection'  => 5,
              'httpversion'  => '1.0',
              'blocking'     => true,
              'headers'      => [
                'content-type'  => 'application/json',
                'accept'        => 'application/json',
                'Authorization' => 'Token ' . base64_encode("{$this->client_id}:{$this->client_secret}"),
              ],
              'body'         => $this->payload,
              'cookies'      => array()
        ));

        if ($this->result['response']['code'] == 201 || $this->result['response']['code'] == 200) {
            $result = json_decode($this->result['body'])->data;

            if (strlen($this->payment_url) == 0){
                return array(
                    'result'     => 'success',
                    'redirect'   => $result->direct,
                    );
                }
            else {
                return array(
                    'result'     => 'success',
                    'redirect'   => $this->payment_url .'&transaction_id=' . $result->id,
                );
            }

        } else {
            try {
                $result = json_decode($this->result['body']);

                wc_add_notice(__('<strong>Payment Error</strong><br/>', 'woothemes') . $result->meta->message . '', 'error');
                return;
            } catch (\Exception $e) {
                wc_add_notice(__('<strong>Payment Error:</strong><br/>', 'woothemes') . 'System error', 'error');
                return;
            }
        }
    }

    /**
     * Check payment result.
     *
     * Check the result of the transaction and mark the order appropriately
     *
     * @since 1.0.0
     */
    public function check_result($order_id = '')
    {
        $transaction_id = isset($_GET['transaction_id']) ? $_GET['transaction_id'] : false;

        if ($transaction_id) {
            $this->result = wp_remote_get("{$this->url}/{$transaction_id}", array(
              'method'       => 'GET',
              'timeout'      => 45,
              'redirection'  => 5,
              'httpversion'  => '1.0',
              'blocking'     => true,
              'headers'      => [
                'Content-Type'  => 'application/json',
                'accept'        => 'application/json',
                'Authorization' => 'Token ' . base64_encode("{$this->client_id}:{$this->client_secret}"),
              ],
              'cookies'      => array()
          ));

            $transaction = json_decode($this->result['body'])->data;
            $order          = new WC_Order($transaction->reference);
            $status         = $transaction->status;

            if ($status == 'COMPLETE') {
                $order->update_status('processing');
                $order->payment_complete();

                if (!isset($_POST['transaction_id'])) {
                    wc_add_notice(__('<strong>Payment successfully processed via AddPay</strong> ', 'woothemes'), 'success');
                }
            } elseif ($status == 'FAILED' || $status == 'CANCELLED') {
                if (!isset($_POST['transaction_id'])) {
                    $state = ucFirst(strtolower($status));

                    wc_add_notice(__("<strong>Payment {$state}</strong><br/>{$transaction->status_reason}", 'woothemes'), 'error');
                }

                $order->update_status('failed');
            }
        }
    }

    /**
     * Get order property with compatibility check on order getter introduced
     * in WC 3.0.
     *
     * @since 1.0.0
     *
     * @param WC_Order $order Order object.
     * @param string   $prop  Property name.
     *
     * @return mixed Property value
     */
    public static function get_order_prop($order, $prop)
    {
        switch ($prop) {
            case 'order_total':
                $getter = array( $order, 'get_total' );
                break;
            default:
                $getter = array( $order, 'get_' . $prop );
                break;
        }

        return is_callable($getter) ? call_user_func($getter) : $order->{ $prop };
    }
}
