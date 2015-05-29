<?php

// What version of the API are we running?
//
if ( ! defined( 'HAPI_VERSION' ) ) {
    define( 'HAPI_VERSION' , '4.2.00' );
}

/**
 * Class Hoverboard_Object
 *
 * The base class on which all Hoverboard objects are built.
 *
 * 
 */
abstract class Hoverboard_Object {

    /**
     * Create me.
     *
     * All parameters should be passed as a named array.
     *
     * The key (name) must be a property defined by the class or the value will not be set.
     */
    public function __construct( $parameters = null ) {
        if ( $parameters !== null) {
            foreach ( (array) $parameters as $property => $value ) {
                if ( property_exists( $this , $property ) ) {
                    $this->$property = $value;
                }
            }
        }
    }

}
