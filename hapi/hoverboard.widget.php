<?php

/**
 * Class Hoverboard_Widget
 *
 * The class on which your widget interface should be built.
 *
 * It always includes a facility for a title entry as that just seems like the right thing to do.
 *
 * If you don't want/need a title interface you can use the filters/parameters here or build a new
 * class not based on this abstract definition.
 *
 * You will want to create the widget(), form(), and update() methods in your class, as a minimum.
 *
 */
abstract class Hoverboard_Widget extends WP_Widget {

    /*-------------------------
     * PROPERTIES
     *-------------------------*/

    /**
     * A unique base ID (slug) for this widget.
     *
     * @var null
     */
    public $base_id = null;

    /**
     * The name for this widget, appears in the WP Admin UI in the widget title box.
     *
     * @var null
     */
    public $name = null;

    /**
     * The description for this widget in the widgets admin UI appears under the widget title box.
     *
     * @var null|string
     */
    public $description = '';

    /**
     * If the site admin leaves the title field blank, what should it be?
     *
     * @var string
     */
    public $default_title = '';

    /**
     * Additional admin form content.
     *
     * @var string
     */
    public $form_content = '';

    /**
     * What text should precede the widget title label in the admin UI?
     *
     * @var string
     */
    private $title_field_label = '';

    /*-------------------------
     * METHODS
     *-------------------------*/

    /**
     * Create me.
     *
     * Pass in the following named array (as a minimum)
     *
     *
     */

    /**
     * Pass in the following named array (as a minimum)
     *
     * $my_widget = new MyApp_Widget(
     *      array(
     *          'base_id'       => 'my_special_widget'                ,   // SLUG for this widget
     *          'name'          => __('Widget Title' , 'text_domain') ,   // Widget Title
     *      )
     * );
     *
     * Recommended options:
     *
     * 'title_field_label'  => __('Title:', 'text_domain')         , // what to show on the admin UI before the title field
     *
     * More options:
     *
     * 'default_title'      => __('Default title' , 'text_domain') , // what to use if the user leaves it blank
     * 'description'        => __('Describe it.' , 'text_domain')  , // Description
     * 'form_content'       => 'HTML string for form entries'      , // HTML for building the form content, other than the title (we do that for you)
     *
     * For more info
     * @see https://codex.wordpress.org/Widgets_API
     *
     * @param mixed[] $parameters
     */
    public function __construct( $parameters = null ) {
        if ( $parameters !== null) {
            foreach ( (array) $parameters as $property => $value ) {
                if ( property_exists( $this , $property ) ) {
                    $this->$property = $value;
                }
            }
        }

        // Sanity Checks
        //
        if ( is_null( $this->base_id) ) { return; }
        if ( is_null( $this->name   ) ) { return; }

        // Widget instantiation via WordPress WP_Widget API
        //
        parent::__construct(
            $this->base_id,
            $this->name,
            array(
                'description'   => $this->description,
            )
        );

        $this->add_hooks_and_filters();
    }

    /**
     * This is where you add your hooks and filters.
     *
     * Override in your class.
     */
    protected function add_hooks_and_filters() {

    }

    /**
     * Outputs the content of the widget to the user.
     *
     * @param array $args widget arguments passed in real time.
     * @param array $instance the settings saved in the widget system.
     *
     * @return null;
     */
    public function widget( $args, $instance ) {
        $widget_output = '';

        if ( ! isset( $args['base_id'] ) ) {
            $args['base_id'] = $this->base_id;
        }

        echo $args['before_widget'];

        // Show Title
        //
        $title = $this->set_title( $instance );
        if ( ! empty( $title ) ) {
            echo $args['before_title'];

            echo apply_filters( 'widget_title' , $instance['title'] );

            echo $args['after_title' ];
        }


        // FILTER: searchwidget_widget_output - modify the widget output of the Search Widget widget, , gets current output [1] , args array [2] , and instance array [3]
        echo apply_filters('hapi_widget_output', $widget_output , $args , $instance );

        echo $args['after_widget'];

    }

    /**
     * Outputs the options form on admin widget manager.
     *
     * @param array $instance The widget options
     *
     * @return null
     */
    public function form( $instance ) {
        // FILTER: hapi_widget_form_content - modify the admin form content, gets HTML string of 'form_content' [1]
        $form_content = apply_filters( 'hapi_widget_form_content' , $this->form_content , $instance );

        echo
            '<p>' .

                $this->form_add_field(
                    array(
                        'slug'  => 'title' ,
                        'value' => esc_attr( $this->set_title( $instance ) ) ,
                        'label' => $this->title_field_label ,
                    )
                ) .

                $form_content                           .

            '</p>'
        ;
    }

    /**
     * Add a field to the admin form.
     *
     * $parameters is a named array that dictates how the field is built.
     *
     * Required:
     * $parameters['slug'] = the slug for the field, i.e. 'title'.
     *
     * Optional:
     * $parameters['type']  = the type of field to render, defaults to 'text'.
     * $parameters['value'] = the field value, defaults to blank ''.
     * $parameters['label'] = the preceding label for the field, defaults to no label displayed.
     * $parameters['help_link'] = a URL to link to with a help icon next to the title
     * $parameters['help_text'] = help text to display below the input
     *
     * @param array $parameters
     *
     * @return string
     */
    public function form_add_field ( $parameters = array() ) {

        // Required Parameters
        //
        if ( ! isset( $parameters['slug']   ) || empty( $parameters['slug']   ) ) { return ''; }

        // Optional Parameters
        //
        if ( ! isset( $parameters['type']  ) || empty( $parameters['type'] ) ) { $parameters['type']  = 'text'; }
        if ( ! isset( $parameters['value'] )                                 ) { $parameters['value'] = '';     }

        $html = '';

        $field_id   = $this->get_field_id( $parameters['slug'] );
        $field_name = $this->get_field_name( $parameters['slug'] );
        $field_value = $parameters['value'];

        // Add the label to the HTML if set
        //
        if ( isset( $parameters['label'] ) && ! empty( $parameters['label'] ) ) {
            $html .=
                "<label for='{$field_id}'>"       .
                $parameters['label']              .
                ': </label>'
                ;
        }

        // Add a help icon next to the label if the help_link is set.
        //
        if ( isset( $parameters['help_link'] ) && ! empty( $parameters['help_link'] ) ) {
            $html .=
                "<a href='{$parameters['help_link']}' target='hoverboard'>( ? )</a>"
                ;
        }

        // Output the selected form field type
        //
        switch ( strtolower( $parameters['type'] ) ) {

            // Text Field
            //
            case 'text':
                $html .=
                    '<input class="widefat" type="text" '   .
                    "id='{$field_id}' "                     .
                    "name='{$field_name}' "                 .
                    "value='{$field_value}' "               .
                    '/>'
                    ;
                break;

        }

        // Add help text below the input if set
        //
        if ( isset( $parameters['help_text'] ) && ! empty( $parameters['help_text'] ) ) {
            $html .=
                '<small>'               .
                $parameters['help_text']    .
                '</small>'
            ;
        }

        // Wrap HTML in paragraph tags.
        //
        if ( ! empty ( $html ) ) {
            $html = "<p>{$html}</p>";
        }

        return $html;

    }


    /**
     * Set the title to be displayed, returning the default_title if the title
     * is empty for this instance.
     *
     * @param $instance
     * @return mixed|void
     */
    private function set_title( $instance ) {
        $return_title = '';

        // Set the title for output in the widget manager.
        //
        if ( ! empty( $instance['title'] ) ) {
            $return_title = $instance['title'];
        } elseif ( ! empty( $this->default_title ) ) {
            $return_title = $this->default_title;
        }

        // FILTER: hapi_widget_title - modify the title, gets current title [1] and instance array [2]
        return apply_filters( 'hapi_widget_title' , $return_title , $instance );
    }

    /**
     * Processing widget options on save.
     *
     * @param array $new_instance The new options
     * @param array $old_instance The previous options
     *
     * @return null
     */
    public function update( $new_instance, $old_instance ) {

        $save_this_instance = array();
        $new_title = $this->set_title( $new_instance );
        $save_this_instance['title'] = ! empty( $new_title ) ? strip_tags( $new_title ) : '';

        // FILTER: hapi_widget_update - set what we are going to save for the form settings, gets $save_this_instance named array[1], $new_instance[2], $old_instance[3]
        return apply_filters( 'hapi_widget_update' , $save_this_instance , $new_instance, $old_instance );
    }

}
