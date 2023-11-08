<?php
/**
 * WWRO_Facebook_Noti
 *
 * @package WWRO_Facebook_Notification
 * @since 1.0
 */
defined( 'ABSPATH' ) || exit;
/**
 * Main WWRO_Facebook_Notification Class.
 *
 * @class WWRO_Facebook_Notification
 */
class WWRO_Facebook_Notification_Loader {
  /**
   * WWRO_Facebook_Noti version.
   *
   * @var string
   */
  public $version = '1.0';
  /**
   * The single instance of the class.
   *
   * @var WWRO_Facebook_Notification
   * @since 1.0
   */
  protected static $_instance = null;
  /**
   * Main WWRO_Facebook_Noti Instance.
   *
   * Ensures only one instance of WWRO_Facebook_Noti is loaded or can be loaded.
   *
   * @since 1.0
   * @static
   * @return WWRO_Facebook_Notification - Main instance.
   */
  public static function instance() {

    if ( is_null( self::$_instance ) ) {

      self::$_instance = new self();
    }
    return self::$_instance;

  }
  /**
   * WWRO_Facebook_Noti Constructor.
   */
  public function __construct() {
    $this->define_constants();
    $this->includes();
    $this->init_hooks();

  }
  /**
   * Define constant if not already set.
   *
   * @param string      $name  Constant name.
   * @param string|bool $value Constant value.
   */
  private function define( $name, $value ) {
    if ( ! defined( $name ) ) {
      define( $name, $value );
    }
  }
  /**
   * Define Constants
   */
  private function define_constants() {
    $this->define( 'WWRO_FACEBOOK_NOTIFICATION_VERSION', $this->version );
    $this->define( 'WWRO_FACEBOOK_NOTIFICATION_PLUGIN_DIR', plugin_dir_path( WWRO_FACEBOOK_NOTIFICATION_FILE ) );
    $this->define( 'WWRO_FACEBOOK_NOTIFICATION_PLUGIN_URL', plugin_dir_url( WWRO_FACEBOOK_NOTIFICATION_FILE ) );
    $this->define( 'WWRO_FACEBOOK_NOTIFICATION_BASE', plugin_basename( WWRO_FACEBOOK_NOTIFICATION_FILE ) );
  }
  /**
   * Hook into actions and filters.
   *
   * @since 1.0
   */
  private function init_hooks() {
    add_action( 'admin_notices', array( $this, 'facebook_notification_required_plugins' ) );
    add_filter( 'plugin_action_links_' . WWRO_FACEBOOK_NOTIFICATION_BASE, array( $this, 'facebook_notification_settings_link' ) );
    add_action( 'plugins_loaded', array( $this, 'facebook_notification_load_textdomain' ) );
    add_filter( 'wowrestro_get_settings_pages', array( $this, 'wowrestro_get_facebook_notification_settings_page' ), 10, 1 );
  }
  /**
   * Include setting page
   * 
   * @since 1.0
   */
  public function wowrestro_get_facebook_notification_settings_page( $settings ) {
    $settings[] = include 'admin/wwro-fb-notification-settings.php';
    return $settings;
  }
  /**
   * Check plugin dependency
   *
   * @since 1.0
   */
  public function facebook_notification_required_plugins() {
    if ( ! is_plugin_active( 'wowrestro/wowrestro.php' ) ) {
      $plugin_link = 'https://wordpress.org/plugins/wowrestro/';
      /* translators: %1$s: plugin link for wowrestro */
      echo '<div id="notice" class="error"><p>' . sprintf( __( 'Delivery Fee requires <a href="%1$s" target="_blank"> WOWRestro </a> plugin to be installed. Please install and activate it', 'wwro-delivery-fee' ), esc_url( $plugin_link ) ).  '</p></div>';
      deactivate_plugins( '/wowrestro-delivery-fee/wowrestro-delivery-fee.php' );
    }
  }
  /**
   * Add settings link for the plugin
   *
   * @since 1.0
   */
  public function facebook_notification_settings_link( $links ) {

    $link = admin_url( 'admin.php?page=wowrestro-settings&tab=whatsapp_notification' );
    /* translators: %1$s: settings page link */
    $settings_link = sprintf( __( '<a href="%1$s">Settings</a>', 'wwro-delivery-fee' ), esc_url( $link ) );
    array_unshift( $links, $settings_link );
    return $links;
  }
  /**
   * Include required files for settings
   *
   * @since 1.0
   */
  private function includes() {
    require_once WWRO_FACEBOOK_NOTIFICATION_PLUGIN_DIR . 'includes/class-wowrestro-facebook-notification-loader.php';
    require_once WWRO_FACEBOOK_NOTIFICATION_PLUGIN_DIR . 'includes/admin/wwro-fb-admin-fields.php';
    require_once WWRO_FACEBOOK_NOTIFICATION_PLUGIN_DIR . 'includes/wowrestro-facebook-notification-services.php';
    require_once WWRO_FACEBOOK_NOTIFICATION_PLUGIN_DIR . 'includes/wwro-facebook-notification.php';
    require_once WWRO_FACEBOOK_NOTIFICATION_PLUGIN_DIR . '/vendor/autoload.php';  
  }
  /**
   * Load text domain
   *
   * @since 1.0
   */
  public function facebook_notification_load_textdomain() {
    load_plugin_textdomain( 'wwro-facebook-notification', false, dirname( plugin_basename( WWRO_FACEBOOK_NOTIFICATION_FILE ) ) . '/languages/' );
  }
}
