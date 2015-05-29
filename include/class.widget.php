<?php
require_once( SEARCHWIDGET_HAPI_DIR . 'hoverboard.widget.php' );

/**
 * Class SearchWidget_Widget
 *
 * Text Domain: searchwidget
 */
class SearchWidget_Widget extends Hoverboard_Widget {

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

        $hidden_fields .=
            "<input type='hidden' id='post_parent' name='post_parent' value='{$instance['parent_id']}' /> ";

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

        $new_form .= $this->form_add_field(
            array(
                'slug'  => 'parent_id' ,
                'value' => isset( $instance['parent_id'] ) ? $instance['parent_id'] : '',
                'label' => __( 'Parent ID' , 'searchwidget' ) ,
                'help_link' => 'http://hoverboard.tools/product/search-widget/' ,
                'help_text' => __( 'Enter a parent page ID to limit search results to children of this ID.' , 'searchwidget' )
            )
        );

        return $new_form;
    }

    /**
     * Modify the search query.
     *
     * @param $search_object
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
        // post_parent : blank = everything, 0 = top pages only, # = under that specific post id
        //
        $search_object->query_vars = array_merge(
            $search_object->query_vars ,
            array(
                'post_parent'   => isset( $_REQUEST['post_parent'] ) ? $_REQUEST['post_parent'] : '',
            )
        );

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
        $to_be_saved['parent_id'] = ! empty( $new_instance['parent_id'] ) ? (int) $new_instance['parent_id'] : '';
        return $to_be_saved;
    }

}

add_action( 'widgets_init' , create_function( '' , 'return register_widget("SearchWidget_Widget");' ) );

