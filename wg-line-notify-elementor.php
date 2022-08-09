<?php
/**
 * Plugin Name: LINE Notify for Elementor Pro Form
 * Version: 0.1
 * Description: This plugin send a message to LINE via LINE Notify when Elementor Pro Form send a mail.
 * Author: Chris Lin
 * Author URI: https://github.com/yclin925
 * Plugin URI: https://github.com/yclin925
 * License: MIT
 */

// Refer: https://github.com/OsukeUesugi/cf7-line-notify

class Epf_Line_Notify
{
  function __construct() {
    register_activation_hook( __FILE__, array( $this, 'check_dependency') );
    add_action( 'admin_menu', array( $this, 'add_plugin_menu' ) );
    add_action( 'elementor_pro/forms/new_record', array( $this, 'line_notify' ) );
    add_action( 'admin_init', array( $this, 'plugin_menu_init' ) );
  }

  public function check_dependency() {
    $active_plugins = get_option( 'active_plugins' );
    if ( !array_search( 'elementor-pro/elementor-pro.php', $active_plugins ) ) {
      echo 'Please activate Elementor Pro plugin before use this plugin.';
      exit();
    }
  }

  public function plugin_menu_init() {
    if( isset( $_POST['epf-line-notify'] ) && $_POST['epf-line-notify'] ) {
      if( check_admin_referer( 'epf-line-notify-nonce-key', 'epf-line-notify' ) ) {

        if( isset( $_POST['epf-line-notify-token'] ) && $_POST['epf-line-notify-token'] ) {
          update_option( 'epf-line-notify-token', $_POST['epf-line-notify-token'] );
        } else {
          update_option( 'epf-line-notify-token', '' );
        }
      }
    }
  }

  public function add_plugin_menu() {
    add_options_page(
      'EPF LINE Notify',
      'LINE Notify for Elementor Pro Form ',
      'administrator',
      'epf-line-notify',
      array($this, 'display_plugin_admin_page')
    );
  }

  function display_plugin_admin_page() {
    $access_token = stripslashes( get_option( 'epf-line-notify-token' ) );
?>
      <div class="wrap">
        <h2>LINE Notify for Contact Form 7</h2>
        <form action="" method="post">
        <table class="form-table">
        <tr>
        <th scope="row">ACCESS TOKEN</th>
        <td>
        <input id="" class="" name="epf-line-notify-token" value="<?php echo $access_token; ?>">
        </td>
        </tr>
        </table>
        <p><input type="submit" value="SAVE" class="button button-primary button-large" /></p>
<?php
  wp_nonce_field( 'epf-line-notify-nonce-key', 'epf-line-notify' );
?>
        </form>
      </div>
<?php
  }

  public function line_notify( $record ) {
    $access_token = stripslashes( get_option( 'epf-line-notify-token' ) );

    if ( empty( $access_token ) ) return;

    $headers = 'Authorization: Bearer ' . $access_token;

    /*取得Elementor表單原始資料*/
    $raw_fields = $record->get( 'fields' );
    $fields = [];

    foreach ( $raw_fields as $id => $field ) { //$id為form_id，暫時用不到
        $fields[$field['title']] = $field['value'];
    }
    
    $mail_body = ""; //待發送給Line Notify字串
    foreach ( $fields as $label => $data ) {
      $mail_body .= $label . "：" . $data . "\r\n";
    }

    $data = array(
      'headers' => $headers,
      'body' => array( 'message' => $mail_body )
    );
    wp_remote_post('https://notify-api.line.me/api/notify', $data);
  }
}

new Epf_Line_Notify();

?>