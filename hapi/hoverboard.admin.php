<?php
require_once('hoverboard.object.php');

/**
 * Class Hoverboard_Admin
 *
 * The class on which your admin interface should be built.
 *
 * Create an Admin Class specific to your plugin in ./include/class.admin.php
 * then
 * Use if ( is_admin() ) { require_once( YOURAPP_PLUGIN_DIR . '/include/class.admin.php'; } in your main plugin php file.
 *
 * You will need to define YOURAPP_PLUGIN_DIR (and rename YOURAPP) first.
 *
 * @see http://www.hoverboard.tools/product/hoverboard-api-demo/
 */
abstract class Hoverboard_Admin extends Hoverboard_Object {


}
