<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 30/08/2019
 * Time: 9:46
 */

namespace AscensionShop\Affiliate;


use AscensionShop\Lib\TemplateEngine;

class StandardRefferal
{

    public function __construct()
    {
        add_filter("affwp_tracking_is_valid_affiliate", array($this, "allowOwnAffiliate"), 10, 2);
        add_action("init", array($this, "setCustomRef"));
        add_action("admin_menu", array($this, "addSettingsPage"));
        add_action("admin_post_ascension-save_standard_ref", array($this, "saveStandards"));
        add_action("init", array($this, "changeStandardRef"));
    }

    /**
     * Add standard ref if not
     */
    public function setCustomRef()
    {
        $ref_id = affiliate_wp()->tracking->get_affiliate_id();
        $aff = $this->getStandardLangAffiliate();

        // Setup standard affiliate
        if ( ! $ref_id > 0 && ! is_user_logged_in() ) {
	        $this->setSelfRef( $aff );
        } elseif ( $ref_id > 0 && is_user_logged_in() ) {
	        // Setup the affiliate id
	        $own_aff_id = affwp_get_affiliate_id( get_current_user_id() );

	        if ( $own_aff_id > 0 && $own_aff_id != $ref_id ) {
		        $this->setSelfRef( $own_aff_id );
	        }
        } else {
	        $ref = $this->getStandardLangAffiliate();

	        if ( $ref != $ref_id ) {
		        $this->setSelfRef( $ref );
	        }
        }

        return;

    }

    /**
     * Change the standard ref on lang switch
     */
    public function changeStandardRef()
    {

        $current_ref = affiliate_wp()->tracking->get_affiliate_id();

        if (in_array($current_ref, $this->getArrayOfStandards()) === true) {
            // Current aff id is a standard, so set the new one based on lang
            $ref = $this->getStandardLangAffiliate();

            if ($ref != $current_ref) {
                $this->setSelfRef($ref);
            }
        }

        return;


    }

    private function getArrayOfStandards()
    {
        $langs = icl_get_languages();
        $return_array = array();

        foreach ($langs as $l) {
            $return_array[] = get_option("ascension-shop_standard_ref_" . $l["code"]);
        }

        return $return_array;

    }

    private function getStandardLangAffiliate()
    {

        $current_lang = ICL_LANGUAGE_CODE;
        return get_option("ascension-shop_standard_ref_" . $current_lang);

    }

    private function setSelfRef($own)
    {
        affiliate_wp()->tracking->set_affiliate_id($own);
        global $wp;
        // error_log("Ref set to:" . $own);
        // wp_safe_redirect(affwp_get_affiliate_referral_url($own));
    }

    /**
     * Allow own Affiliate id as Ref
     * @param $ret
     * @param $affiliate_id
     *
     * @return bool
     */
    public function allowOwnAffiliate($ret, $affiliate_id)
    {

        if ($ret === true) {
            return true;
        }

        error_log("the reffering aff id:" . $affiliate_id);

        $affiliate = affwp_get_affiliate($affiliate_id);
        $is_self = is_user_logged_in() && get_current_user_id() == $affiliate->user_id;
        $active = 'active' === $affiliate->status;
        $ret = $is_self OR $active;

        return $ret;

    }

    public function addSettingsPage()
    {
        add_submenu_page(
            'affiliate-wp',
            'Standard Affiliate',
            'Standard Affiliate',
            'manage_options',
            'ascension-shop-standard-ref',
            array($this, 'viewSettingsPage')
        );
    }

    public function viewSettingsPage()
    {

        $t = new TemplateEngine();
        $t->langs = icl_get_languages();
        $t->affiliates = affiliate_wp()->affiliates->get_affiliates(array('number' => 99999, 'orderby' => 'name', 'order' => 'ASC'));

        echo $t->display("admin/standardRefferal.php");

    }

    /**
     * Save Standards
     */
    public function saveStandards()
    {

        $langs = icl_get_languages();

        foreach ($langs as $l) {

            if (isset($_REQUEST[$l["code"]])) {
                // Save the options
                update_option("ascension-shop_standard_ref_" . $l["code"], $_REQUEST[$l["code"]]);
            }
        }

        wp_safe_redirect($_REQUEST["_wp_http_referer"]);
    }
}