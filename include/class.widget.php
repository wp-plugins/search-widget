<?php
require_once( SEARCHWIDGET_HAPI_DIR . 'hoverboard.widget.php' );

/**
 * Class SearchWidget_Widget
 *
 * Text Domain: searchwidget
 */
class SearchWidget_Widget extends Hoverboard_Widget {

    /*-------------------------
     * PROPERTIES
     *-------------------------*/

    /**
     * @var mixed[]
     */
    private $filter_fields = array( );

    /*-------------------------
     * METHODS
     *-------------------------*/

    /**
     * Build the search widget.
     */
    function __construct() {
        parent::__construct(
            array(
                'base_id'           => 'search_widget'                          ,
                'name'              => __('Search Widget' , 'searchwidget' )  ,
                'title_field_label' => __( 'Title'        , 'searchwidget' )  ,
                'description'       =>
                    __( 'A search form for your site with limits on scope.' , 'searchwidget' )
            )
        );

        $this->setup_filter_fields();
    }

    /**
     * Add our custom hooks and filters that modify the class output.
     */
    function add_hooks_and_filters() {

        // Hoverboard Admin Interface (*form*)
        //
        add_filter( 'hapi_widget_form_content' , array( $this , 'extend_admin_form'    ) , 10 , 2   );
        add_filter( 'hapi_widget_update'       , array( $this , 'save_widget_settings' ) , 10 , 3   );

        // Hoverboard User Interface (*widget*)
        //
        add_filter( 'hapi_widget_output' , array( $this , 'output_search_widget') , 10 , 3);

        // WordPress Search Interface
        //
        add_action( 'pre_get_posts' , array( $this , 'modify_search_query') , 10 );

    }

    /**
     * Add hidden form fields to the built-in search form.
     *
     * @return string
     */
    private function add_hidden_widget_fields( $instance ) {
        $hidden_fields = wp_nonce_field( 'search_widget' , 'search_widget_nonce' );

        foreach ( $this->filter_fields as $field_slug => $field_properties ) {
            if ( isset( $instance[ $field_slug ] ) ) {
                $hidden_fields .=
                    "<input type='hidden' id='{$field_slug}' name='{$field_slug}' value='{$instance[ $field_slug ]}' /> ";
            }
        }

        return $hidden_fields;
    }

    /**
     * Extend the admin form.
     *
     * @param $current_form
     *
     * @return string
     */
    function extend_admin_form( $current_form , $instance ) {

        $new_form = $current_form;

        foreach ( $this->filter_fields as $field_slug => $field_properties ) {
            $new_form .= $this->form_add_field(
                array(
                    'slug'      => $field_slug ,
                    'value'     => isset( $instance[ $field_slug ] ) ? $instance[ $field_slug ] : '',
                    'label'     => $field_properties['label']     ,
                    'help_link' => $field_properties['help_link'] ,
                    'help_text' => $field_properties['help_text']
                )
            );
        }

        return $new_form;
    }

    /**
     * Modify the search query.
     *
     * This modifies the current WP_Query.
     * @see https://codex.wordpress.org/Class_Reference/WP_Query
     *
     * @param $search_object
     *
     * @return mixed
     */
    function modify_search_query( $search_object) {

        // Only modify a valid search widget search form.
        //
        if (! isset( $_REQUEST['search_widget_nonce'] ) ||
            ! wp_verify_nonce( $_REQUEST['search_widget_nonce'] , 'search_widget')
        ) {
            return $search_object;
        }

        // Modify the search object query.
        //
        $new_query_vars = array();
        foreach ( $this->filter_fields as $field_slug => $field_properties ) {

            // Set a new query var if field slug exists in a POST/GET variable.
            //
            if ( isset( $_REQUEST[ $field_slug ] ) ) {
                $new_query_vars[ $field_slug ] =  trim( $_REQUEST[ $field_slug ] );

                // Force the variable type if 'var_type' is defined
                // If invalid, obliterate the new query var setting
                //
                if ( isset( $field_properties['var_type'] ) ) {
                    if ( ! settype( $new_query_vars[ $field_slug ] , $field_properties['var_type'] ) ) {
                        unset( $new_query_vars[ $field_slug ] );
                    }
                }

            }
        }

        $search_object->query_vars = array_merge( $search_object->query_vars ,  $new_query_vars );

        return $search_object;
    }

    /**
     * Output the search form.
     *
     * @param $output
     * @param $args
     *
     * @return string search form output.
     */
    function output_search_widget( $output , $args , $instance ) {
        $output .= get_search_form( false );

        $inject_html = $this->add_hidden_widget_fields( $instance );
        $close_form_snippet = '</form>';

        $output = str_replace( $close_form_snippet , $inject_html . $close_form_snippet , $output);

        return $output;
    }

    /**
     * Set the widget parameters to be saved.
     *
     * @param $to_be_saved
     * @param $new_instance
     * @param $old_instance
     *
     * @return mixed
     */
    function save_widget_settings( $to_be_saved , $new_instance , $old_instance ) {

        // Go through all the filter fields and make sure they are saved.
        //
        foreach ( $this->filter_fields as $field_slug => $field_properties ) {

            // Set a new query var if field slug exists in a POST/GET variable.
            //
            if ( isset( $new_instance[ $field_slug ] ) ) {
                $to_be_saved[ $field_slug ] =  $new_instance[ $field_slug ];

                // Force the variable type if 'var_type' is defined
                // If invalid, obliterate the new query var setting
                //
                if ( isset( $field_properties['var_type'] ) ) {
                    if ( ! settype( $to_be_saved[ $field_slug ] , $field_properties['var_type'] ) ) {
                        unset( $to_be_saved[ $field_slug ] );
                    }
                }
            }
        }

        return $to_be_saved;
    }

    /**
     * Setup the filter fields metadata.
     */
    private function setup_filter_fields() {

        // Link all of these to the Hoverboard Search Widget page.
        //
        $help_link = 'http://hoverboard.tools/product/search-widget/';

        // - POSTS

        // category_name : string, slug of category
        //
        // fetches search matches for posts in the category and all the children
        //
        $this->filter_fields['category_name'] = array(
            'label'     => __( 'Category Slug' , 'searchwidget' )                                                               ,
            'help_link' => $help_link ,
            'help_text' => __( "Enter a post category slug to limit searches to that category and it's children." , 'searchwidget' )  ,
            'var_type'  => 'string'
        );

        // tag : string, slug of tag
        //
        // fetches search matches for posts that have the given tag
        //
        $this->filter_fields['tag'] = array(
            'label'     => __( 'Tag Slug' , 'searchwidget' )                                                               ,
            'help_link' => $help_link ,
            'help_text' => __( "Enter a post tag slug to limit searches to that tag." , 'searchwidget' )  ,
            'var_type'  => 'string'
        );

        // - PAGES

        // post_parent : blank = everything, 0 = top pages only, # = under that specific post id
        //
        $this->filter_fields['post_parent'] = array(
            'label'     => __( 'Parent ID' , 'searchwidget' )                                                               ,
            'help_link' => $help_link ,
            'help_text' => __( 'Enter a parent page ID to limit search results to children of this ID.' , 'searchwidget' )  ,
            'var_type'  => 'integer'
            );

        /**
         * Filter the list of fields that are processed by the widget.
         *
         * @since 4.2.03
         *
         * @param mixed[] $filter_fields a parameter_slug=>properties array
         *
         * @returns mixed[] modified filter fields.
         */
        apply_filters( 'search_widget_filter_fields' , $this->filter_fields );
    }

}

add_action( 'widgets_init' , create_function( '' , 'return register_widget("SearchWidget_Widget");' ) );

