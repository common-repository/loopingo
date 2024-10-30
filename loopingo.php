<?php
/**
 *
 * loopingo Plugin
 *
 * @package loopingo Plugin
 * @author loopingo GmbH
 * @copyright 2019 - loopingo GmbH <service@loopingo.com>
 * @license GPL-2.0+

 * Plugin Name: loopingo
 * Author: loopingo GmbH
 * Description: Add loopingo to WooCommerce Thank You Page
 * Version: 1.2.0
 * Text Domain: loopingo
 * Author URI: https://www.loopingo.com/
 * Plugin Coopoeration: Simplify Everything OG, 6352 Ellmau, Austria
 * License: GPL2 http://www.gnu.org/licenses/gpl-2.0.html

 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

add_action( 'admin_menu', 'loo_add_admin_menu' );
add_action( 'admin_init', 'loo_settings_init' );
add_action( 'init', 'loopingo_textdomain' );
add_action( 'woocommerce_thankyou', 'loo_thank_you_page', 1, 1 );
add_action( 'woocommerce_thankyou', 'loo_thank_you_page_bottom', 20, 1 );
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'simplify_add_plugin_page_settings_link' );


/** Adds the text-domain for translation */
function loopingo_textdomain() {
    load_plugin_textdomain( 'loopingo', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
}
/** Adds the admin menu */
function loo_add_admin_menu() {

    add_options_page( 'loopingo', 'loopingo', 'manage_options', 'loopingo', 'loo_options_page' );

}
/** Adds settings tab to plugin page
 *
 * @param int $links Create the link.
 */
function simplify_add_plugin_page_settings_link( $links ) {
    // create link.
    $links[] = '<a href="' .
        admin_url( 'options-general.php?page=loopingo' ) .
        '">' . __( 'Settings' ) . '</a>';
    return $links;
}
/** Adds the backend menu */
function loo_settings_init() {

    register_setting( 'pluginPage', 'loo_settings' );

    add_settings_section(
        'loo_pluginPage_section',
        __( 'loopingo Settings', 'loopingo' ),
        'loo_settings_section_callback',
        'pluginPage'
    );

    add_settings_field(
        'loo_text_field_1',
        __( 'Token', 'loopingo' ),
        'loo_text_field_1_render',
        'pluginPage',
        'loo_pluginPage_section'
    );

    add_settings_field(
        'loo_select_field_2',
        __( 'Where should the widget be placed?', 'loopingo' ),
        'loo_select_field_2_render',
        'pluginPage',
        'loo_pluginPage_section'
    );
}

/** Adds options to the menu */
function loo_text_field_1_render() {

    $options       = get_option( 'loo_settings' );
    $options_value = $options['loo_text_field_1'];
    ?>
    <input type='text' name='loo_settings[loo_text_field_1]' value='<?php echo esc_attr( $options_value ); ?>' style="min-width:300px;">
    <?php

}

/** Adds options to the menu */
function loo_select_field_2_render() {

    $options = get_option( 'loo_settings' );
    ?>
    <select name='loo_settings[loo_select_field_2]'>
        <option value='1' <?php selected( $options['loo_select_field_2'], 1 ); ?>><?php echo esc_html__( 'Default', 'loopingo' ); ?></option>
        <option value='2' <?php selected( $options['loo_select_field_2'], 2 ); ?>><?php echo esc_html__( 'Bottom', 'loopingo' ); ?></option>
    </select>

    <?php

}

/** Adds options to the menu */
function loo_settings_section_callback() {

    echo esc_html__( 'This plugin places loopingo incentives on your WooCommerce thank you page.', 'loopingo' );
    echo '<br>';
    echo esc_html__( 'A token must be inserted in order to use loopingo.', 'loopingo' );
}

/** Adds options to the menu */
function loo_options_page() {

    ?>
    <form action='options.php' method='post'>

        <?php
        settings_fields( 'pluginPage' );
        do_settings_sections( 'pluginPage' );
        submit_button();
        ?>

    </form>

    <?php
    echo '<p class="loopingo_website">' . esc_html__( 'You can sign up to', 'loopingo' ) . '&nbsp; <a href="https://manager.loopingo.com/app-security/register" target="_blank">' . esc_html__( 'loopingo', 'loopingo' ) . '</a>&nbsp;' . esc_html__( 'to receive your Token.', 'loopingo' ) . '</p><br>';
    echo '<p class="copyright_loopingo">' . esc_html__( 'This plugin was created for', 'loopingo' ) . '&nbsp;<a href="https://www.loopingo.com/impressum/" target="_blank">loopingo GmbH</a>.&nbsp;' . esc_html__( 'loopingo GmbH is responsible for the functionality and updates.', 'loopingo' );
    echo '<br>' . esc_html__( 'Original development by', 'loopingo' ) . '&nbsp;<a href="https://simplify-everything.com" target="_blank">Simplify Everything OG</a></p>';

}
/** Runs the script
 *
 * @param int $order_id get the order.
 */
function loo_thank_you_page( $order_id, $bottom = false )
{
    $loopingo_settings = get_option( 'loo_settings' );
    $loopingo_position = $loopingo_settings['loo_select_field_2'];

    // 1 == default
    // 2 == bottom

    if ($bottom && '2' === $loopingo_position) {
        loo_create_loopingo($order_id);
        return;
    }

    if (!$bottom && '1' === $loopingo_position) {
        loo_create_loopingo($order_id);
        return;
    }
}
/** Runs the script for second priority
 *
 * @param int $order_id get the order.
 */
function loo_thank_you_page_bottom( $order_id ) {
    loo_thank_you_page( $order_id, true );
}

function loo_create_loopingo($order_id)
{
    $order             = new WC_Order( $order_id );
    $loopingo_settings = get_option( 'loo_settings' );
    $loopingo_token    = $loopingo_settings['loo_text_field_1'];
    $loo_order_id      = $order->get_id();
    $loo_voucher_code  = $order->get_coupon_codes();
    $loo_email         = $order->get_billing_email();
    $loo_country       = $order->get_billing_country();
    $loo_postal_code   = $order->get_billing_postcode();
    $loo_order_amount  = $order->get_total();
    $loo_order_first   = $order->get_billing_first_name();
    $loo_order_last    = $order->get_billing_last_name();
    $loo_order_city    = $order->get_billing_address_1();
    $loo_order_street  = $order->get_billing_city();

            ?>
<!-- LOOPINGO START -->
<div id="loopingo-integration-container">
    <script type="text/props">
    {
        "token"         : "<?php echo esc_attr( $loopingo_token ); ?>",
        "order_id"      : "<?php echo esc_attr( $loo_order_id ); ?>",
        "voucher_code"  : "<?php echo esc_attr( $loo_voucher_code ); ?>",
        "email"         : "<?php echo esc_attr( $loo_email ); ?>",
        "country"       : "<?php echo esc_attr( $loo_country ); ?>",
        "postal_code"   : "<?php echo esc_attr( $loo_postal_code ); ?>",
        "gender"        : "undefined",
        "order_amount"  : "<?php echo esc_attr( $loo_order_amount ); ?>",
        "first_name"    : "<?php echo esc_attr( $loo_order_first ); ?>",
        "last_name"     : "<?php echo esc_attr( $loo_order_last ); ?>",
        "city"          : "<?php echo esc_attr( $loo_order_city ); ?>",
        "street"        : "<?php echo esc_attr( $loo_order_street ); ?>",
        "house_number"  : "",
        "birthday"      : "",
        "is_test"       : false
    }
    </script>
</div>
<script async src="https://integration.loopingo.com/bundle_v1.js" type="text/javascript"></script>
<!-- LOOPINGO END -->
            <?php
}

// You made it to the bottom, well done! By the way, the Simplify Everything OG guys say hi.
