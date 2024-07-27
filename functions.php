<?php
    function b3_default_excluded_post_types() {
        $default_types = [
            'attachment',
            'revision',
            'nav_menu_item',
            'custom_css',
            'customize_changeset',
            'oembed_cache',
            'user_request',
            'wp_block',
            'wp_template',
            'wp_template_part',
            'wp_global_styles',
            'wp_navigation',
            'wp_font_family',
            'wp_font_face',
            'acf-taxonomy',
            'acf-post-type',
            'acf-ui-options-page',
            'acf-field-group',
            'acf-field'
        ];
        sort( $default_types );
        
        if ( class_exists( 'acf' ) ) {
            $exclude_acf = [
                'acf-taxonomy',
                'acf-post-type',
                'acf-ui-options-page',
                'acf-field-group',
                'acf-field',
            ];
            
            $types = array_merge( $default_types, $exclude_acf );
            sort( $types );
            
            return $types;
        }
        
        return $default_types;
    }
