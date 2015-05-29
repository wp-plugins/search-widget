<?php
require_once( SEARCHWIDGET_HAPI_DIR . 'hoverboard.admin.php' );

/**
 * Class SearchWidget_Admin
 *
 * Text Domain: searchwidget
 */
class SearchWidget_Admin extends Hoverboard_Admin {

    /**
     * @var SearchWidget_Admin
     **/
    private static $instance = null;

    /**
     * Create a singleton of this admin object.
     *
     * @return MCServer_Admin
     */
    static function init() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new SearchWidget_Admin;
        }
        return self::$instance;
    }

}

SearchWidget_Admin::init();
