<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 30/07/2019
 * Time: 13:08
 */

namespace AscensionShop\Affiliate;


use AscensionShop\Lib\TemplateEngine;

class RateLevelsInit
{

    private static $levelKey = "xe-ascension-level-key";

    public function __construct()
    {
        // Load ConsolePage
        add_action("admin_menu", array($this, "addLevelRatesPage"));

        add_action("admin_post_xe_add_level", array($this, "saveLevel"));

    }

    public function saveLevel()
    {

        $levels = self::getRates();

        // error_log($levels[0]["rate"]);
        // Create array, when empty
        if (!is_array($levels)) {
            $levels = array();
        }

        $levels[$_POST["level"]]["rate"] = $_POST["rate"];

        update_option(self::$levelKey, $levels, true);

        wp_safe_redirect(urldecode($_POST['_wp_http_referer']));
    }

    /**
     * Get the rate of a specific level
     * @param $l
     *
     * @return int
     */
    public static function getLevelRate($l)
    {

        $levels = self::getRates();

        if (isset($levels[$l]["rate"])) {
            return $levels[$l]["rate"];
        }

        // Level rate not set
        return 0;
    }

    public static function getRates()
    {
        return get_option(self::$levelKey);
    }

    /**
     * Add the Level Rates page to admin
     */
    public function addLevelRatesPage()
    {
        add_submenu_page(
            'affiliate-wp',
            __('Levels', 'ascension-shop'),
            __('Levels', 'ascension-shop'),
            'manage_options',
            'rates-ml-ascension',
            array($this, 'loadRates')
        );
    }

    /**
     * Show page for adding rates
     */
    public function loadRates()
    {

        global $wp;

        $t = new TemplateEngine();

        $t->levels = self::getRates();

        echo $t->display("admin/levelRates.php");

    }


}