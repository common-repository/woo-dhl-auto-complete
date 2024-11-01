<?php
/**
 * Created by PhpStorm.
 * User: Mattias
 * Date: 2018-06-25
 * Time: 20:40
 */
/*
Plugin Name:  Woo DHL Auto Complete
Plugin URI:   https://developer.wordpress.org/plugins/the-basics/
Description:  Auto Completing orders through DHL API
Version:      1.2.0
Author:       mnording10
Author URI:   https://mnording.com
License:      MIT
License URI:  https://opensource.org/licenses/MIT
Text Domain:  woo-dhlauto
Domain Path:  /languages
*/
/**
 * Register new status
 **/
if(!class_exists('DhlLocatorWebservice') ){
    require_once 'classes/DhlLocatorWebservice.php';
}
if(!class_exists('DHLAutoSettings') ) {
    require_once 'classes/DHLAutoSettings.php';
}
    if(!class_exists('DHLAutoOrderStatus') ){
require_once 'classes/DHLAutoOrderStatus.php';
}
class DHLAutoComplete {
    function __construct()
    {
        register_deactivation_hook( __FILE__, array($this,'deactivate') );
        add_action( 'wp_ajax_dhl_auto_complete', array($this,'dhl_auto_complete' ));
        add_action( 'wp_ajax_nopriv_dhl_auto_complete', array($this, 'dhl_auto_complete' ));
        add_action("plugins_loaded",array($this,"init"));
        add_action( 'plugins_loaded', array($this,'dhl_auto_plugin_textdomain') );
    }

    /***
     * This method will ensure no orders are lost when plugin is de-activated.
     */
    function deactivate(){
       $orderswithstatus = wc_get_orders( array(
           'status' => 'wc-awaiting-shipment',
       ) );
       foreach($orderswithstatus as $order){
           $order->update_status('processing');
       }
    }

    function init(){
        if(!class_exists('WC_Logger')){
            return;
        }
        $this->logger = new WC_Logger();
        new DHLAutoSettings();
        new DHLAutoOrderStatus();
    }
    function dhl_auto_plugin_textdomain() {
        load_plugin_textdomain( 'woo-dhlauto', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
    }

    function dhl_auto_complete(){
        $username = get_option('dhl_auto_api_username');
        $password = get_option('dhl_auto_api_password');
        if(!$username || !$password){
            $this->logger->alert("Password and Username to DHL API Must be set in Settings panel",array("source"=>"dhl-auto-complete"));
            die();
        }
        $orders = wc_get_orders( array(
            'status' => 'wc-awaiting-shipment',
        ) );
        $this->logger->info("Got orders. ".count($orders),array("source" => "dhl-auto-complete"));
        $this->dhlService = new DhlLocatorWebservice($password,$username,"SV",get_option('dhl_auto_should_log'));

        foreach($orders as $order)
        {
            $orderid = $order->get_id();
            if($orderid == 0){
                continue;
            }
echo  $orderid." - ";

           $isAtTerminal=  $this->dhlService->IsShipmentAtTerminal($orderid);


           if($isAtTerminal){
                       $this->logger->info("Order with id ".$orderid." was at terminal",array( 'source' => 'dhl-auto-complete' ));
                       $order->update_status( 'completed' );
                       $order->add_order_note(__("Order activated automatically due to DHL API response.","woo-dhlauto"));
                       $this->logger->info("Order with id ".$orderid." was completed",array( 'source' => 'dhl-auto-complete' ));
                       continue;
               }

               $this->logger->info("Order with id ".$orderid.
                   " not yet at terminal",array( 'source' => 'dhl-auto-complete' ));
           }
        $this->logger->info("No more Orders were  at terminal",array( 'source' => 'dhl-auto-complete' ));
       }

    }
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    $t = new DHLAutoComplete();
}



