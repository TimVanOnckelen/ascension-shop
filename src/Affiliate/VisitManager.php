<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 9/08/2019
 * Time: 14:21
 */

namespace AscensionShop\Affiliate;


class VisitManager
{

    private $currentLang = ICL_LANGUAGE_CODE;

    public function __construct()
    {

        // create a shop ref, when no ref is set
        add_action("init", array($this, "createShopCookie"));

        // Recreate on lang switch
        add_action('wpml_switch_language', array($this, "switchLangRefCookie"));

    }

    public function createShopCookie()
    {

        if (!isset($_COOKIE['affwp_ref'])) {
            // Create a cookie for the user of the current shop lang
            return;
        }

        return;
    }

    public function switchLangRefCookie($language_code)
    {

        if (!isset($_COOKIE['affwp_ref'])) {
            // Create a cookie for the user of the current shop lang
            return $this->createShopCookie();
        }

        // Already set, so check if it is a affiliate dedicated to a shop
        return;
    }

    public function showShopManagers()
    {
        // wpml_active_languages();
    }
}