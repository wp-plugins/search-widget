<?php
/*
 * Plugin Name: Search Widget
 * Plugin URI: http://www.hoverboard.tools/product/search-widget/
 * Description: A basic search widget for WordPress sites.
 * Author: Charleston Software Associates
 * Version: 4.2.01
 * Author URI: http://www.charlestonsw.com
 * License: GPL2+
 * Text Domain: searchwidget
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly, dang hackers

define( 'SEARCHWIDGET_VERSION'      ,   '4.2.01'                        );
define( 'SEARCHWIDGET__PLUGIN_DIR'  ,   plugin_dir_path( __FILE__ )     );
define( 'SEARCHWIDGET__PLUGIN_FILE' ,   __FILE__                        );
define( 'SEARCHWIDGET_HAPI_DIR'     , SEARCHWIDGET__PLUGIN_DIR . 'hapi/'    );

// Admin Features
//
if ( is_admin() ) {
    require_once( SEARCHWIDGET__PLUGIN_DIR . 'include/class.admin.php'  );
}

// Widgets
//
require_once( SEARCHWIDGET__PLUGIN_DIR . 'include/class.widget.php'  );


// Dad. Explorer. Rum Lover. Code Geek.  Not Necessarily In That Order. //