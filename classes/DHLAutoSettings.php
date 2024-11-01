<?php
/**
 * Created by PhpStorm.
 * User: Mattias
 * Date: 2018-06-27
 * Time: 08:43
 */

class DHLAutoSettings
{
    function __construct()
    {
        add_action('admin_menu', array($this,'woo_dhl_auto_plugin_create_menu'));
        add_action( 'admin_init', array($this,'woo_dhl_auto_plugin_settings') );
    }
    public function woo_dhl_auto_plugin_create_menu() {
        add_options_page('Woo DHL Auto Complete', 'Woo DHL Auto', 'administrator','woo_dhlauto' ,array($this,'woo_dhl_auto_settings_page') );
    }
    public function woo_dhl_auto_settings_page() {
        ?>
        <div class="wrap">
            <h1>DHL Auto Complete</h1>
<p><?php _e("Remember to setup a scheduele job to access","woo-dhlauto");?> <?php echo admin_url();?>admin-ajax.php?action=dhl_auto_complete <?php _e("in order to trigger the activation by API","woo-dhlauto")?></p>
            <form method="post" action="options.php">
                <?php settings_fields( 'woo_dhl_auto_settings-group' ); ?>
                <?php do_settings_sections( 'woo_dhl_autosettings-group' ); ?>

                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e("Api Username","woo-dhlauto"); ?></th>
                        <td><input type="text" name="dhl_auto_api_username" value="<?php echo esc_attr( get_option('dhl_auto_api_username') ); ?>" /></td>
                        <td><?php _e("What is your API username. Should be the same as your login to the myACT portal","woo-dhlauto"); ?> -> <a href="https://activetracing.dhl.com/DatPublic/login.do?"><?php _e("Click here for Portal","woo-dhlauto");?></a></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e("API Password","woo-dhlauto"); ?></th>
                        <td><input type="password" name="dhl_auto_api_password" value="<?php echo esc_attr( get_option('dhl_auto_api_password') ); ?>" /></td>
                        <td><?php _e("What is your API password. Should be the same as your password to the myACT portal","woo-dhlauto"); ?></td>

                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e("Create Debug log?","woo-dhlauto"); ?></th>
                        <td><input name="dhl_auto_should_log" type="checkbox" value="1" <?php checked( '1', get_option( 'dhl_auto_should_log' ) ); ?> /><?php _e("Yes","woo-dhlauto")?></td>
                    </tr>
                </table>

                <?php submit_button(); ?>

            </form>
        </div>
    <?php }
    public function woo_dhl_auto_plugin_settings()
    {
        $args = array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => NULL,
        );
        register_setting('woo_dhl_auto_settings-group', 'dhl_auto_api_password', $args);
        register_setting('woo_dhl_auto_settings-group', 'dhl_auto_api_username', $args);
        register_setting('woo_dhl_auto_settings-group', 'dhl_auto_should_log', $args);
    }


}