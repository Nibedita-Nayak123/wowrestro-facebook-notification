<?php
/**
* Plugin Name: WoWRestro - Facebook Notifications
* Description: This plugin allows you to send facebook  notification when there is new order or order update
* Version: 1.0
* Author: MagniGenie
* Text Domain: wwro- facebook -noti
* Domain Path: /languages/
* WC tested up to: 8.1.1
*/
defined( 'ABSPATH' ) || exit;
if ( ! defined( 'WWRO_FACEBOOK_NOTIFICATION_FILE' ) ) {
  define( 'WWRO_FACEBOOK_NOTIFICATION_FILE', __FILE__ );
}
// Include the main class WWRO_Whatapp_Notification.
if ( ! class_exists( 'WWRO_Facebook_Notification_Loader', false ) ) {
  include_once dirname( __FILE__ ) . '/includes/class-wowrestro-facebook-notification-loader.php';
}
/**
 * Returns the main instance of WWRO_Facebook_Noti.
 *
 * @return WWRO_FACEBOOK_NOTIFICATION
 */
function WOWRESTRO_Facebook_Notification() {
  return WWRO_Facebook_Notification_Loader::instance();
}
WOWRESTRO_Facebook_Notification();