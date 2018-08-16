<?php
require_once("vendor/autoload.php");

use theme\init\Nourish;
use theme\ajax\WP_AJAX;
use theme\orm\WP_Model;
use theme\cron\WP_Cron;
use theme\mail\WP_Mail;
use theme\api\WP_Route;


class ThemeInit extends Nourish{

    protected $nav_menus = array(
        'head-menu' => 'Head Menu',
    );

    protected $theme_support_custom = array(
        "title-tag"
    );
}
new ThemeInit();
