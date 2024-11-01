<?php
/**
 * Created by PhpStorm.
 * User: Mattias
 * Date: 2018-06-27
 * Time: 08:53
 */

class DHLAutoOrderStatus
{
    function __construct()
    {
        add_action( 'init', array($this,'register_awaiting_shipment_order_status'),0 );
        add_filter( 'wc_order_statuses', array($this,'add_awaiting_shipment_to_order_statuses'),0 );
        add_action( 'wp_ajax_dhl_auto_set_awaiting', array($this,'dhl_auto_set_awaiting' ));
        add_filter( 'woocommerce_admin_order_actions', array($this,'add_custom_order_status_actions_button'), 100, 2 );
        add_action( 'admin_head', array($this,'add_custom_order_status_actions_button_css') );
    }
    function add_custom_order_status_actions_button( $actions, $order ) {
        // Display the button for all orders that have a 'processing' status
        if ( $order->has_status( array( 'processing' ) ) ) {

            // Get Order ID (compatibility all WC versions)
            $order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
            // Set the action button
            $actions['parcial'] = array(
                'url'       => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status=awaiting-shipment&order_id=' . $order_id ), 'woocommerce-mark-order-status' ),
                'name'      => __( 'Done packing', 'woo-dhlauto' ),
                'action'    => "view shipment", // keep "view" class for a clean button CSS
            );
        }
        return $actions;
    }

    function add_custom_order_status_actions_button_css() {
        echo '<style>.view.shipment::after { font-family: woocommerce; content: "\f164" !important; }
.view.shipment { color: #d40511;
    background-color: #ffd83f;
} 
mark.order-status.status-awaiting-shipment{color: #d40511;
    background-color: #ffd83f;}</style>';
    }

    function register_awaiting_shipment_order_status() {
        register_post_status( 'wc-awaiting-shipment', array(
            'label'                     => __('Awaiting shipment',"woo-dhlauto"),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( 'Awaiting shipment <span class="count">(%s)</span>', 'Awaiting shipment <span class="count">(%s)</span>', 'woo-dhlauto')
        ) );
    }
    function add_awaiting_shipment_to_order_statuses( $order_statuses ) {
        $new_order_statuses = array();
        // add new order status after processing
        foreach ( $order_statuses as $key => $status ) {
            $new_order_statuses[ $key ] = $status;
            if ( 'wc-processing' === $key ) {
                $new_order_statuses['wc-awaiting-shipment'] = __('Awaiting shipment',"woo-dhlauto");
            }
        }
        return $new_order_statuses;
    }
}