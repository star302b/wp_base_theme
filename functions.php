<?php
require_once("vendor/autoload.php");

use theme\init\Nourish;
use theme\ajax\WP_AJAX;

class ThemeInit extends Nourish{

    protected $nav_menus = array(
        'head-menu' => 'Head Menu',
    );

    protected $theme_support_custom = array(
        "title-tag"
    );
}
new ThemeInit();

