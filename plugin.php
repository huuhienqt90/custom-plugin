<?php
/*
Plugin Name: Custom Login
Plugin URI: https://github.com/huuhienqt90/custom-login
Description: Custom Register/ Login
Version: 1.0.0
Author: Hien(Hamilton) H.HO
Author URI: https://github.com/huuhienqt90
Text Domain: custom-login
*/
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
if( !class_exists( 'CustomLogin' ) ){
    class CustomLogin{

        /**
         * Constructor
         */
        public function __construct(){
            $this->defines();
            $this->hooks();
        }

        /**
         * Define all constants
         */
        public function defines(){
            define( '__COMPLETE_REGISTER__', __('Register successfully. You must purchase a membership product to use this site.') );
        }

        /**
         * Add all hooks if need
         */
        public function hooks(){
            add_action( 'tml_new_user_registered', array( $this, 'add_option_subscription' ), 10, 2 );
            add_action( 'init', array( $this, 'show_mes' ) );
            $pending_role = get_role( 'pending' );
            if(!$pending_role){
                add_role( 'pending','Pending User', array('read'=> true));
            }
            add_action( 'woocommerce_order_status_changed', array( $this, 'change_user_role' ), 10, 3 );
        }

        /**
         * Add option subscription
         */
        public function add_option_subscription( $user_id, $user_pass ){
            update_user_meta( $user_id, 'hh-subscription', 'no' );
            $user = new WP_User( $user_id );
            $user->set_role( 'pending' );
            wp_redirect( add_query_arg( array('hh_action'=>'register-complete'), get_permalink( woocommerce_get_page_id( 'shop' ) ) ) );
            exit();
        }

        /**
         * Show message
         */
        public function show_mes(  ){
            if( isset( $_REQUEST['hh_action']) && $_REQUEST['hh_action'] == 'register-complete')
                wc_add_notice( __COMPLETE_REGISTER__ );

        }

        /**
         * Change user role
         */
        public function change_user_role( $order_id, $old_status, $new_status){
            if( $new_status == 'completed' ){
                $order = new WC_Order( $order_id );
                if( WC_Subscriptions_Order::order_contains_subscription( $order ) ){
                    $user = new WP_User( $order->get_user_id() );
                    if( in_array( 'pending', $user->roles )){
                        $user->remove_role('pending');
                        $user->add_role( 'subscriber' );
                    }
                }
            }
        }
    }
    new CustomLogin();
}