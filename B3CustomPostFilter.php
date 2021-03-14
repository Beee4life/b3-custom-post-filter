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
                register_activation_hook( __FILE__,     array( $this, 'b3cpf_plugin_activation' ) );
                register_deactivation_hook( __FILE__,   array( $this, 'b3cpf_plugin_deactivation' ) );
    
                add_action( 'admin_menu',                   array( $this, 'b3cpf_add_admin_pages' ) );
                add_action( 'admin_init',                   array( $this, 'b3cpf_errors' ) );
                add_action( 'admin_init',                   array( $this, 'b3cpf_form_handling' ) );
                add_action( 'admin_enqueue_scripts',        array( $this, 'b3cpf_add_scripts' ) );
    
                add_action( 'restrict_manage_posts',        array( $this, 'b3cpf_add_extra_filter' ), 20, 2 );
                add_action( 'pre_get_posts',                array( $this, 'b3cpf_pre_get_posts' ) );
                add_filter( 'query_vars',                   array( $this, 'b3cpf_add_query_vars' ) );
    
                add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'b3cpf_settings_link' ) );
    
            }


            /*
             * Do stuff upon plugin activation
             *
             * @since 2.0.0
             */
            public function b3cpf_plugin_activation() {
                // set default post types
                update_option( 'b3cpf_post_types', [ 'post', 'page' ], true );
            }


            /**
             * Do stuff upon plugin deactivation
             */
            public function b3cpf_plugin_deactivation() {
            }
    
            public function b3cpf_add_admin_pages() {
                include 'b3-dashboard.php';
                add_options_page( 'Post Filters', 'Post Filters', 'manage_options', 'b3cpf-dashboard', 'b3cpf_dashboard' );
            }
    
            public function b3cpf_add_scripts() {
                if ( is_admin() ) {
                    wp_enqueue_style( 'b3cpf', plugins_url( 'admin.css', __FILE__ ), [] );
                }
            }
    
            public static function b3cpf_errors() {
                static $wp_error; // Will hold global variable safely
        
                return isset( $wp_error ) ? $wp_error : ( $wp_error = new WP_Error( null, null, null ) );
            }
    
            /*
             * Displays error messages
             */
            public static function b3cpf_show_admin_notices() {
                if ( $codes = B3CustomPostFilter::b3cpf_errors()->get_error_codes() ) {
                    if ( is_wp_error( B3CustomPostFilter::b3cpf_errors() ) ) {
                        $span_class = false;
                        foreach ( $codes as $code ) {
                            if ( strpos( $code, 'success' ) !== false ) {
                                $span_class = 'notice-success ';
                            } elseif ( strpos( $code, 'error' ) !== false ) {
                                $span_class = 'error ';
                            } elseif ( strpos( $code, 'warning' ) !== false ) {
                                $span_class = 'notice-warning ';
                            } elseif ( strpos( $code, 'info' ) !== false ) {
                                $span_class = 'notice-info ';
                            }
                        }
                        echo '<div id="message" class="notice ' . $span_class . 'is-dismissible">';
                        foreach ( $codes as $code ) {
                            $message = B3CustomPostFilter::b3cpf_errors()->get_error_message( $code );
                            echo '<p class="">';
                            echo $message;
                            echo '</p>';
                        }
                        echo '</div>';
                    }
                }
            }

            public function b3cpf_form_handling() {
                // Set post types
                if ( isset( $_POST[ 'b3cpf_set_post_types_nonce' ] ) ) {
                    if ( ! wp_verify_nonce( $_POST[ 'b3cpf_set_post_types_nonce' ], 'b3cpf-set-post-types-nonce' ) ) {
                        $this->b3cpf_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'b3cpf' ) );
                    } else {
                        if ( empty( $_POST[ 'b3cpf_post_type' ] ) ) {
                            delete_option( 'b3cpf_post_types' );
                        } else {
                            update_option( 'b3cpf_post_types', $_POST[ 'b3cpf_post_type' ], true );
                        }
                        $this->b3cpf_errors()->add( 'success_post_types_saved', esc_html__( 'Post types saved.', 'b3cpf' ) );
                    }
                }
    
                // Add filter
                if ( isset( $_POST[ 'b3cpf_set_filters_nonce' ] ) ) {
                    if ( ! wp_verify_nonce( $_POST[ 'b3cpf_set_filters_nonce' ], 'b3cpf-set-filters-nonce' ) ) {
                        $this->b3cpf_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'b3cpf' ) );
                        return;
                    } else {
    
                        $filter_key      = ( isset( $_POST[ 'b3cpf_filter_key' ] ) ) ? $_POST[ 'b3cpf_filter_key' ] : false;
                        $filter_label    = ( isset( $_POST[ 'b3cpf_filter_label' ] ) ) ? $_POST[ 'b3cpf_filter_label' ] : false;
                        $filter_operator = ( isset( $_POST[ 'b3cpf_filter_operator' ] ) ) ? $_POST[ 'b3cpf_filter_operator' ] : false;
                        
                        if ( strpos( $_POST[ 'b3cpf_filter_key' ], ' ' ) !== false ) {
                            $this->b3cpf_errors()->add( 'error_space_in_key', esc_html__( 'You can\'t have a space in your key.', 'b3cpf' ) );
                            return;
                        }
                        if ( empty( $_POST[ 'b3cpf_filter_key' ] ) || empty( $_POST[ 'b3cpf_filter_label' ] ) ) {
                            if ( empty( $_POST[ 'b3cpf_filter_key' ] ) ) {
                                $this->b3cpf_errors()->add( 'error_empty_key', esc_html__( 'You need to enter a filter key (no spaces).', 'b3cpf' ) );
                            }
                            if ( empty( $_POST[ 'b3cpf_filter_label' ] ) ) {
                                $this->b3cpf_errors()->add( 'error_empty_value', esc_html__( 'You need to enter a filter label.', 'b3cpf' ) );
                            }
                            return;
                        } else {
                            $option[ $_POST[ 'b3cpf_filter_key' ] ] = $_POST[ 'b3cpf_filter_label' ];
                
                            $all_filters = get_option( 'b3cpf_post_filters', [] );
                            if ( empty( $all_filters ) ) {
                                $new_options = $option;
                            } else {
                                if ( ! array_key_exists( $_POST[ 'b3cpf_filter_key' ], $all_filters ) ) {
                                    $new_options = array_merge( $all_filters, $option );
                                } else {
                                    $this->b3cpf_errors()->add( 'error_key_exists', sprintf( __( 'The key "%s" already exists.', 'b3cpf' ), $_POST[ 'b3cpf_filter_key' ] ) );
                                    return;
                                }
                            }
                
                            update_option( 'b3cpf_post_filters', $new_options, true );
                        }
                    }
                }
    
                // Remove filter
                if ( isset( $_POST[ 'b3cpf_remove_filters_nonce' ] ) ) {
                    if ( ! wp_verify_nonce( $_POST[ 'b3cpf_remove_filters_nonce' ], 'b3cpf-remove-filters-nonce' ) ) {
                        $this->b3cpf_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'b3cpf' ) );
                        return;
                    } else {
                        if ( ! empty( $_POST[ 'b3cpf_filters' ] ) ) {
                            $stored_post_filters = get_option( 'b3cpf_post_filters', [] );
                            foreach( $_POST[ 'b3cpf_filters' ] as $filter ) {
                                if ( array_key_exists( $filter, $stored_post_filters ) ) {
                                    unset( $stored_post_filters[ $filter ] );
                                }
                            }
                            if ( ! empty( $stored_post_filters ) ) {
                                update_option( 'b3cpf_post_filters', $stored_post_filters, true );
                            } else {
                                delete_option( 'b3cpf_post_filters' );
                            }
                        }
                    }
                }
            }
    
            public function b3cpf_add_extra_filter( $post_type, $which ) {
                $allowed_post_types  = get_option( 'b3cpf_post_types', [ 'post', 'page' ] );
                $stored_post_filters = get_option( 'b3cpf_post_filters', [] );
                
                if ( ! in_array( $post_type, $allowed_post_types ) ) {
                    return;
                }
        
                // create filter options / output html for taxonomy dropdown filter
                echo '<select name="b3_custom_filter" id="b3_custom_filter" class="postform">';
                echo '<option value="">' . __( 'Your filters', 'b3-cpf' ) . '</option>';
                foreach( $stored_post_filters as $key => $label ) {
                    echo '<option value="' . $key . '">' . $label . '</option>';
                }
                echo '</select>';
            }
    
            public function b3cpf_pre_get_posts( $query ) {
                if ( is_admin() && $query->is_main_query() ) {
                    if ( ! empty( $query->query['b3_custom_filter'] ) ) {
                        $new_query = $this->b3_get_new_query( $query->query['b3_custom_filter'] );
                
                        if ( $new_query ) {
                            $query->set( 'meta_query', $new_query );
                        }
                    }
                }
            }
    
            public function b3cpf_add_query_vars( $vars ) {
                $vars[] = 'b3_custom_filter';
        
                return $vars;
            }
    
            public function b3_get_new_query( $value ) {
                if ( ! $value ) {
                    return false;
                }
        
                $meta_query = [
                    [
                        'key'     => $value,
                        'value'   => '',
                        'compare' => '!=',
                    ],
                ];
        
                return $meta_query;
            }
    
            /*
             * Add settings link on plugin page
             *
             * @param $links
             *
             * @return array
             */
            public function b3cpf_settings_link( $links ) {
                $settings_link = [ 'settings' => '<a href="options-general.php?page=b3cpf-dashboard">' . esc_html__( 'Settings', 'b3-cpf' ) . '</a>' ];
                $links         = array_merge( $settings_link, $links );
        
                return $links;
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
