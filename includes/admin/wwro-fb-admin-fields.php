<?php
function wowrestro_test_facebook_notifications_callback( $args ) {
  ob_start();
  ?>
  <input type="button" class="button button-primary wowrestro-facebook-test-notification" value="<?php esc_html_e( 'Test Notification', 'rp-facebook-notification' ); ?>">
  <?php
  $html = ob_get_clean();
  echo $html;
}

