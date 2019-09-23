<?php


namespace AscensionShop\Woocommerce;


class Optimalizations
{

    public function __construct()
    {

        add_filter('woocommerce_variable_sale_price_html', array($this, "disablePriceRange"), 10, 2);
        add_filter('woocommerce_variable_price_html', array($this, "disablePriceRange"), 10, 2);
        add_filter('woo_discount_rules_remove_event_woocommerce_before_calculate_totals', array($this, 'woo_discount_rules_remove_event_woocommerce_before_calculate_totals_method'));
        add_filter('woo_discount_rules_has_price_override', array($this, 'woo_discount_rules_has_price_override_method'), 10, 2);
        add_filter('woo_discount_rules_exclude_woocommerce_bundled_item', array($this, 'woo_discount_rules_exclude_woocommerce_bundled_item_method'), 10, 2);

        // edit user vat
        add_action('show_user_profile', array($this, "editVat"));
        add_action('edit_user_profile', array($this, "editVat"));
        add_action('personal_options_update', array($this, "saveVat"));
        add_action('edit_user_profile_update', array($this, "saveVat"));

    }

    public function editVat($user)
    {

        ?>

        <h3><?php _e("BTW nummer", "ascension-shop"); ?></h3>

        <table class="form-table">
            <tr>
                <th><label for="vat_number"><?php _e("Btw nummer"); ?></label></th>
                <td>
                    <input type="text" name="vat_number" id="vat_number"
                           value="<?php echo esc_attr(get_the_author_meta('vat_number', $user->ID)); ?>"
                           class="regular-text"/><br/>
                    <span class="description"><?php _e("BTW nummer van de gebruiker."); ?></span>
                </td>
            </tr>
        </table>
        <?php

    }

    public function saveVat($user_id)
    {
        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }
        update_user_meta($user_id, 'address', $_POST['vat_number']);

    }

    /**
     * Disable price range in woocommerce
     * @param $price
     * @param $product
     *
     * @return string
     */
    public function disablePriceRange($price, $product)
    {

        // Main Price
        $prices = array($product->get_variation_price('min', true), $product->get_variation_price('max', true));
        $price = $prices[0] !== $prices[1] ? sprintf(__('%1$s', 'woocommerce'), wc_price($prices[0])) : wc_price($prices[0]);

        // Sale Price
        $prices = array($product->get_variation_regular_price('min', true), $product->get_variation_regular_price('max', true));
        sort($prices);
        $saleprice = $prices[0] !== $prices[1] ? sprintf(__('%1$s', 'woocommerce'), wc_price($prices[0])) : wc_price($prices[0]);

        if ($price !== $saleprice) {
            $price = '<del>' . $saleprice . '</del> <ins>' . $price . '</ins>';
        }
        return $price;
    }


    public function woo_discount_rules_remove_event_woocommerce_before_calculate_totals_method($remove_event)
    {
        return true;
    }


    public function woo_discount_rules_has_price_override_method($hasPriceOverride, $product)
    {
        return true;
    }

    public function woo_discount_rules_exclude_woocommerce_bundled_item_method($exclude_bundled_item, $product)
    {
        return false;
    }




}