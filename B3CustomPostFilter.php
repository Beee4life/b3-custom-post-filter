<?php
    /*
    Plugin Name:        B3 Custom post filter
    Plugin URI:         https://github.com/Beee4life/b3-custom-post-filter
    Description:        ...
    Version:            0.1
    Requires at least:  4.3
    Tested up to:       5.7
    Requires PHP:       5.6
    Author:             Beee
    Author URI:         https://berryplasman.com
    Tags:               post type, filtering, admin, filter, custom, meta, query
    Text-domain:        b3-cpf
    License:            GPL v2 (or later)
    License URI:        https://www.gnu.org/licenses/gpl-2.0.html
    Domain Path:        /languages
       ___  ____ ____ ____
      / _ )/ __/  __/  __/
     / _  / _/   _/   _/
    /____/___/____/____/

    */

    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }

    if ( ! class_exists( 'B3CustomPostFilter' ) ) {

        class B3CustomPostFilter {

            /**
             * Initializes the plugin.
             *
             * To keep the initialization fast, only add filter and action
             * hooks in the constructor.
             */
            function __construct() {
            }


            /**
             * This initializes the whole shabang
             */
            public function init() {
                $this->settings = array(
                    'path'    => trailingslashit( dirname( __FILE__ ) ),
                    'version' => '0.1',
                );

                // actions
                register_activation_hook( __FILE__,     array( $this, 'b3_plugin_activation' ) );
                register_deactivation_hook( __FILE__,   array( $this, 'b3_plugin_deactivation' ) );
    
                add_action( 'restrict_manage_posts',        array( $this, 'b3_add_extra_filter' ), 20, 2 );
                add_action( 'pre_get_posts',                array( $this, 'b3_pre_get_posts' ) );
                add_filter( 'query_vars',                   array( $this, 'b3_add_query_vars' ) );
    
            }


            /*
             * Do stuff upon plugin activation
             *
             * @since 2.0.0
             */
            public function b3_plugin_activation() {
            }


            /**
             * Do stuff upon plugin deactivation
             */
            public function b3_plugin_deactivation() {
            }
    
            function b3_add_extra_filter( $post_type, $which ) {
                // @TODO: get allowed post types
                
                if ( 'bp_news' != $post_type ) {
                    return;
                }
        
                // create filter options
                // output html for taxonomy dropdown filter
                echo '<select name="custom_filter" id="custom_filter" class="postform">';
                echo '<option value="">' . __( 'Your filters', 'boilerplate' ) . '</option>';
                echo '<option value="xxx">XXX</option>';
                echo '</select>';
            }
    
            function b3_pre_get_posts( $query ) {
                if ( is_admin() && $query->is_main_query() ) {
                    // echo '<pre>'; var_dump($query->query_vars); echo '</pre>'; exit;
                    if ( ! empty( $query->query['custom_filter'] ) ) {
                        $new_query = get_new_query( $query->get( 'custom_filter' ) );
                
                        if ( $new_query ) {
                            $query->set( 'meta_query', $new_query );
                        }
                    }
                }
            }
    
            function b3_add_query_vars( $vars ) {
                $vars[] = 'custom_filter';
        
                return $vars;
            }
    
            function get_new_query( $value ) {
                if ( ! $value ) {
                    return false;
                }
        
                $meta_query = [
                    [
                        'key'   => 'test_item',
                        'value' => $value,
                    ],
                ];
        
                return $meta_query;
            }
    
        }

        /**
         * The main function responsible for returning the one true B3CustomPostFilter instance to functions everywhere.
         *
         * @return \B3CustomPostFilter
         */
        function init_b3_custom_filter() {
            global $b3_custom_filter;

            if ( ! isset( $b3_custom_filter ) ) {
                $b3_custom_filter = new B3CustomPostFilter();
                $b3_custom_filter->init();
            }

            return $b3_custom_filter;
        }

        init_b3_custom_filter();

    }
