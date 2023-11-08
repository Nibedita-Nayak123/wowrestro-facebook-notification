<?php

/**
 * WOWRestro Facebook_Noti Settings
 *
 * @package WOWRestro/Admin
 */

defined('ABSPATH') || exit;

if (class_exists('WWRO_Settings_Facebook_Notification', false)) {
  return new WWRO_Settings_Facebook_Notification();
}

/**
 * WWRO_Settings_Delivery_Fee.
 */
class WWRO_Settings_Facebook_Notification extends WWRO_Settings_Page
{

  /**
   * Constructor.
   */
  public function __construct()
  {

    $this->id    = 'facebook_notification';
    $this->label = __('Facebook Notification', 'wwro-facebook-notification');

    add_action('wowrestro_admin_field_button', array($this, 'wowrestro_admin_field_button_html'), 10, 1);
    add_action('wowrestro_update_option_facebook_notification', array($this, 'save_facebook_notification_options'), 10, 1);
    add_action('admin_enqueue_scripts', array($this, 'wowrestro_facebook_notification_admin_script'));
    add_action( 'admin_enqueue_scripts', array( $this, 'facebook_notification_admin_styles' ) );
    add_action( 'wp_ajax_wwro_facebook_test_notification_service', array( $this, 'wwro_facebook_test_notification_service' ), 10 );
    add_action('woocommerce_new_order', array($this, 'wr_api_new_order_notification'), 10, 2);
    parent::__construct();
  }

 public function wr_api_new_order_notification($payment_id, $order)
  {
      $enable_admin_notification = get_option('enable_admin_facebook_notification');
      if ($enable_admin_notification === 'yes') {
          $order_number = $order->get_order_number();
          $order_status = $order->get_status();
          $service_date = $order->get_date_created()->format('Y-m-d H:i:s');
          $shop_name = get_bloginfo('name');
          $service_time = get_post_meta($order->get_id(), '_wowrestro_service_time', true);
          $service_type = get_post_meta($order->get_id(), '_wowrestro_service_type', true);
          $billing_fname = $order->get_billing_first_name();
          $full_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
          $phone = $order->get_billing_phone();
          $price = $order->get_total();
  
          $notification_message = get_option('admin_facebook_text');
          // Replace placeholders with actual order data
          $notification_message = str_replace('{ORDER_NUMBER}', $order_number, $notification_message);
          $notification_message = str_replace('{ORDER_STATUS}', $order_status, $notification_message);
          $notification_message = str_replace('{SHOP_NAME}', $shop_name, $notification_message);
          $notification_message = str_replace('{SERVICE_DATE}', $service_date, $notification_message);
          $notification_message = str_replace('{SERVICE_TIME}', $service_time, $notification_message);
          $notification_message = str_replace('{SERVICE_TYPE}', $service_type, $notification_message);
          $notification_message = str_replace('{BILLING_FNAME}', $billing_fname, $notification_message);
          $notification_message = str_replace('{FULLNAME}', $full_name, $notification_message);
          $notification_message = str_replace('{PHONE}', $phone, $notification_message);
          $notification_message = str_replace('{PRICE}', $price, $notification_message);
  
          // Create an instance of your Facebook notification service
          $facebook_notification_service = new WWRO_Facebook_Notification_Service();
          // Send the Facebook notification with the customized message
          $response = $facebook_notification_service->send_text_notification($notification_message);
      }
  }
public function wwro_facebook_test_notification_service() {
  // Get the recipient user ID and test message from the AJAX request
  $recipient_user_id = sanitize_text_field($_POST[ 'recipient_user_id' ]);
  $test_message = sanitize_text_field($_POST[ 'test_message' ]);
  $facebook_page_id = get_option( 'facebook_page_id' );
  $facebook_app_id = get_option( 'facebook_app_id' );
  $facebook_app_secret = get_option( 'facebook_app_secret' );
  $facebook_page_token = get_option( 'facebook_page_token' );
  $admin_facebook_text = get_option( 'admin_facebook_text' );
  // Create an instance of your Facebook notification service
  $facebook_notification_service = new WWRO_Facebook_Notification_Service();
  // Send the test Facebook notification
  $response = $facebook_notification_service->send_text_notification($test_message);
  if ($response) {
      // Notification sent successfully
      wp_send_json_success(
        array(
          'message' => 'Test message sent successfully!',
          'response' => $response
        )
      );
  } else {
      // Handle errors
      wp_send_json_error(array('message' => 'Failed to send the test message.'));
  }
  wp_die();
}
  public function save_facebook_notification_options($options)
  {
    if ($options['type'] == 'facebook_notification') {
      update_option('_wowrestro_customer_facebook_notification', $_POST['facebook_notification']);
    }
  }
  public function wowrestro_admin_field_button_html($value)
  {
?>
    <tr valign="top" class="<?php echo $value['row_class'] ?>">
      <th scope="row" class="titledesc">
      </th>
      <td class="forminp forminp-<?php echo esc_attr(sanitize_title($value['type'])); ?>">
        <input id="<?php echo esc_attr($value['id']); ?>" class="button button-primary wowrestro-facebook-test-notification" type="<?php echo esc_attr(sanitize_title($value['type'])); ?>" value="<?php echo esc_html($value['title']); ?>">
      </td>
    </tr>
  <?php

  }
 
  /**
   *
   * Add necessary js for the admin
   *
   * @since 1.1
   * @return mixed
   */
  public function wowrestro_facebook_notification_admin_script() {
    wp_register_script('wwro-facebook-sweetalert2', 'https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.9.0/sweetalert2.min.js', array('jquery'), '11.9.0', true);
    // Enqueue SweetAlert script
    wp_enqueue_script('wwro-facebook-sweetalert2');
    // Enqueue your custom JavaScript file that uses SweetAlert
    wp_enqueue_script('wwro-facebook-admin-script', WWRO_FACEBOOK_NOTIFICATION_PLUGIN_URL . 'assets/js/wowrestro-facebook-admin.js', array('jquery', 'wwro-facebook-sweetalert2'), WWRO_FACEBOOK_NOTIFICATION_VERSION);
    $params = array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'message_sent' => __('Message sent', 'wwro_facebook_notification'),
        'message_sent_text' => __('Message has been sent to', 'wwro_facebook_notification'),
        'message_error' => __('Error', 'wwro_facebook_notification'),
    );
    wp_localize_script('wwro-facebook-admin-script', 'wwrofacebookAdmin', $params);

    if (isset($_GET['tab']) && $_GET['tab'] == 'facebook_notification') {
        wp_enqueue_script('jquery-ui-accordion');
        wp_enqueue_script('wwro-facebook-sweetalert2');
        wp_enqueue_script('wwro-facebook-admin-script');
    }
}
  public function facebook_notification_admin_styles() {
      wp_register_style( 'wwro-facebook-admin-style' , WWRO_FACEBOOK_NOTIFICATION_PLUGIN_URL . 'assets/css/wowrestro-facebook-admin-style.css', array(), WWRO_FACEBOOK_NOTIFICATION_VERSION );
      wp_register_style( 'wwro-facebook-sweetalert2-style' , WWRO_FACEBOOK_NOTIFICATION_PLUGIN_URL . 'assets/css/sweetalert2.min.css', array(), WWRO_FACEBOOK_NOTIFICATION_VERSION );
      if ( isset( $_GET['tab'] ) 
        && $_GET['tab'] == 'facebook_notification'  ) {
        wp_enqueue_style( 'wwro-facebook-admin-style' );
        wp_enqueue_style( 'wwro-facebook-sweetalert2-style' );
      }
    }    
  /**
   * Get settings array.
   *
   * @param string $current_section Current section name.
   * @return array
   */
  public function get_settings($current_section = '')
  { 
    $settings = apply_filters(
        'wowrestro_facebook_noti_settings',
        array(
          array(
            'id'            => 'admin_settings',
            'title'          => __('Admin Settings', 'wowrestro_facebook_notification'),
            'type'          => 'title',
            'tooltip_title' => __('Admin Facebook Settings', 'wowrestro_facebook_notification'),
          ),
          array(
            'id'   => 'enable_admin_facebook_notification',
            'name' => __('Enable Admin Notification', 'wowrestro_facebook_notification'),
            'desc' => sprintf(
              __('Enable this option to get admin notification', 'wowrestro_facebook_notification')
            ),
            'type' => 'checkbox',
          ),
          array(
            'id'   => 'facebook_app_id',
            'name' => __( 'Facebook App ID', 'wowrestro-fb-notification' ),
            'std'  => '',
            'type' => 'text',
            'desc' => __( 'Enter your Facebook App ID. You can obtain this ID by creating a Facebook App in the Facebook Developer Portal.', 'wowrestro-fb-notification' ),
          ),
          array(
           'id'   => 'facebook_app_secret',
           'name' => __( 'Facebook App Secret', 'wowrestro-fb-notification' ),
           'std'  => '',
           'type' => 'text',
           'desc' => __( 'Enter your Facebook App Secret. You can find this in your Facebook App settings in the Facebook Developer Portal.', 'wowrestro-fb-notification' ),
          ),
          array(
            'id'   => 'facebook_page_token',
            'name' => __( 'Facebook Page Token', 'wowrestro-fb-notification' ),
            'std'  => '',
            'type' => 'text',
            'desc' => __( 'Enter your Facebook Page Token. This token is used to access your Facebook Page for sending notifications. You can generate this token in the Facebook Developer Portal.', 'wowrestro-fb-notification' ),
          ),
          array(
            'id'   => 'facebook_page_id',
            'name' => __( 'Facebook Page Id', 'wowrestro-fb-notification' ),
            'std'  => '',
            'type' => 'text',
            'desc' => __( 'Enter your Facebook Page id. This id is used to access your Facebook Page.', 'wowrestro-fb-notification' ),
          ),
          array(
            'id'   => 'admin_facebook_user_id',
            'name' => __( 'Admin Facebook User ID', 'wowrestro-fb-notification' ),
            'std'  => 'enter user id.',
            'type' => 'text',
            'desc' =>__( 'Enter the User ID of the admin\'s Facebook account. You can find this ID in your Facebook profile settings.', 'rp-fb-notification' )
          ),
          array(
            'title'       => __('Admin Facebook text', 'wowrestro_facebook_notification'),
            'id' => 'admin_facebook_text',
            'css'         => 'width:400px; height: 75px;',
            'placeholder' => __('N/A', 'wowrestro_facebook_notification'),
            'type'        => 'textarea',
            'default'     => '#{ORDER_NUMBER} is updated with status {ORDER_STATUS} on {SERVICE_DATE} at {SHOP_NAME} ',
            'desc'        => __('Available placeholders are {ORDER_NUMBER}, {ORDER_STATUS}, {SERVICE_DATE}, {SHOP_NAME}, {SERVICE_TIME}, {SERVICE_TYPE}, {BILLING_FNAME}, {FULLNAME}, {PHONE}, {PRICE}', 'wowrestro_facebook_notification'),
            'autoload'    => false,
            'desc_tip'    => false,
          ),
          array(
            'type' => 'sectionend',
            'id'   => 'account_endpoint_options',
          ),
          array(
            'id'            => 'test_settings',
            'title'          => __('Test Settings', 'wowrestro_facebook_notification'),
            'desc'          => '',
            'type'          => 'title',
            'tooltip_title' => __('Test Facebook Settings', 'wowrestro_facebook_notification'),
          ),
          array(
            'id'   => 'test_facebook_text',
            'class'   => 'test_facebook_text',
            'name' => __('Test Facebook text', 'wowrestro_facebook_notification'),
            'desc' => sprintf(
              __('Enter the text which you want to send as the test message', 'wowrestro_facebook_notification')
            ),
            'type' => 'textarea',
          ),
          array(
            'id'   => 'test_facebook_button',
            'row_class'   => 'wwro-test-options',
            'type' => 'button',
            'name' => __('Test Notification', 'wowrestro_facebook_notification'),
            'button_label' => __('Test Notification', 'wowrestro_facebook_notification'),
            'desc' => __('Click the button to send a test Facebook notification.', 'wowrestro_facebook_notification'),
          ),
          array(
            'type' => 'sectionend',
            'id'   => 'account_settings_options',
          ),
        ),
      );
      return apply_filters('wowrestro_get_settings_' . $this->id, $settings, $current_section);
    }
    //Admin custom button
    public function wowrestro_settings_sanitize_facebook_notification($input)
    {
        if ( isset( $_POST['facebook_notification'] ) ) {
          update_option( '_wwro_customer_facebook_notification', $_POST['facebook_notification'] );
        }
        return $input;
      }
  }
return new WWRO_Settings_Facebook_Notification();
