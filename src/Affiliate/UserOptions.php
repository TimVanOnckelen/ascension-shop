<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 30/07/2019
 * Time: 13:24
 */

namespace AscensionShop\Affiliate;


class UserOptions
{

    public function __construct()
    {

        add_action('affwp_update_affiliate', array($this, 'save_affiliate'), 0);

        // Affiliate Admin
        add_action('affwp_edit_affiliate_end', array($this, 'edit_affiliate'));
        add_action('affwp_new_affiliate_end', array($this, 'add_new_affiliate'));

        add_filter('manage_users_columns', array($this, "addAffiliateColumn"));
        add_filter('manage_users_custom_column', array($this, "addAffiliateData"), 10, 3);

    }

    /**
     * Save affiliate data
     * @param array $data
     *
     * @return bool
     */
    public function save_affiliate($data)
    {

        if (!isset($data['parent_affiliate_id']) && isset($data['xe_ascension_custom_rate'])) {
            return false;
        }

        if (!is_admin()) {
            return false;
        }

        if (!current_user_can('manage_affiliates')) {
            wp_die(__('You do not have permission to manage affiliates', 'affiliate-wp'));
        }


        // Get the current sub aff
        $sub_affiliate = new SubAffiliate($data["affiliate_id"]);

        // Save parent
        $sub_affiliate->saveParent($data["parent_affiliate_id"]);

        // Save custom rate
        $sub_affiliate->saveCustomRate($data["xe_ascension_custom_rate"]);


    }

    /**
     * Edit Affiliate
     *
     * @since 1.0
     * @param $affiliate
     * @return void
     */
    public function edit_affiliate($affiliate)
    {

        // Get all affiliates
        $all_affiliates = affiliate_wp()->affiliates->get_affiliates(array('number' => 0));

        // Get the current sub aff
        $sub_affiliate = new SubAffiliate($affiliate->affiliate_id);

        // Build an array of affiliate IDs and names for the drop down
        $affiliate_dropdown = array();

        if ($all_affiliates && !empty($all_affiliates)) {

            foreach ($all_affiliates as $a) {

                if ($affiliate_name = affiliate_wp()->affiliates->get_affiliate_name($a->affiliate_id)) {
                    $affiliate_dropdown[$a->affiliate_id] = $affiliate_name;
                }

            }

            // Make sure to remove current affiliate from the array so they can't be their own parent affiliate
            unset($affiliate_dropdown[$affiliate->affiliate_id]);

        }

        ?>
        <table class="form-table">

            <tr class="form-row form-required">

                <th scope="row">
                    <label for="parent_affiliate_id"><?php _e('Parent Affiliate', 'ascension-shop'); ?></label>
                </th>

                <td>
                    <select name="parent_affiliate_id" id="parent_affiliate_id">
                        <option value=""></option>
                        <?php foreach ($affiliate_dropdown as $affiliate_id => $affiliate_name) : ?>
                            <option value="<?php echo esc_attr($affiliate_id); ?>"<?php selected($sub_affiliate->getParentId(), $affiliate_id); ?>><?php echo esc_html($affiliate_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php _e('Enter the name of the affiliate to perform a search.', 'ascension-shop'); ?></p>
                </td>

            </tr>

            <tr class="form-row form-required">

                <th scope="row">
                    <label for="matrix_level"><?php _e('Matrix Level', 'affiliate-wp'); ?></label>
                </th>

                <td>
                    <input class="small-text" type="text" name="matrix_level" id="matrix_level"
                           value="<?php echo esc_attr($sub_affiliate->getLevel()); ?>" disabled="1"/>
                    <p class="description"><?php _e('The affiliate\'s level in the matrix. This cannot be changed.', 'affiliate-wp'); ?></p>
                </td>

            </tr>

        </table>
        <?php
        // show_sub_affiliates( $affiliate->affiliate_id, affiliate_wp()->settings->get( 'affwp_mlm_admin_view_subs' ) );

    }


    /**
     * Add Parent Affiliate Field to the Add New Affiliate Form
     *
     * @since 1.1
     * @return void
     */
    public function add_new_affiliate()
    {

        // Get all affiliates
        $all_affiliates = affiliate_wp()->affiliates->get_affiliates(array('number' => 0));

        // Build an array of affiliate IDs and names for the drop down
        $affiliate_dropdown = array();

        if ($all_affiliates && !empty($all_affiliates)) {

            foreach ($all_affiliates as $a) {

                if ($affiliate_name = affiliate_wp()->affiliates->get_affiliate_name($a->affiliate_id)) {
                    $affiliate_dropdown[$a->affiliate_id] = $affiliate_name;
                }

            }

            // Make sure to remove current affiliate from the array so they can't be their own parent affiliate
            // unset( $affiliate_dropdown[$affiliate->affiliate_id] );

        }

        ob_start(); ?>

        <tr class="form-row">

            <th scope="row">
                <label for="parent_affiliate_id"><?php _e('Parent Affiliate', 'ascension-shop'); ?></label>
            </th>

            <td>
                <select name="parent_affiliate_id" id="parent_affiliate_id">
                    <option value=""></option>
                    <?php foreach ($affiliate_dropdown as $affiliate_id => $affiliate_name) : ?>
                        <option value="<?php echo esc_attr($affiliate_id); ?>"><?php echo esc_html($affiliate_name); ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php _e('Enter the name of the affiliate to perform a search.', 'ascension-shop'); ?></p>
            </td>

        </tr>

        <?php
        $content = ob_get_contents();
        ob_end_clean();
        echo $content;

    }

    public function addAffiliateColumn($column)
    {
        $column['customerOf'] = __("Klant van", "ascension-shop");
        $column['affiliateOf'] = __("Sub affiliate van", "ascension-shop");
        return $column;
    }

    public function addAffiliateData($val, $column_name, $user_id)
    {
        switch ($column_name) {
            case 'customerOf' :
                $customer_id = $this->getCustomerByUserId($user_id);
                $parent = $this->getParentByCustomerId($customer_id);
                $username = affiliate_wp()->affiliates->get_affiliate_name($parent);
                return $username;
            case 'affiliateOf' :
                $sub_id = $this->getAffiliateIdByUserId($user_id);
                $sub = new SubAffiliate($sub_id);
                if ($sub->isSub() !== true) {
                    return "";
                }
                $parent = $sub->getParentId();
                $username = affiliate_wp()->affiliates->get_affiliate_name($parent);
                return $username;
            default:
        }
        return $val;
    }

    private function getAffiliateIdByUserId($user_id)
    {

        global $wpdb;
        $query = $wpdb->get_row("SELECT affiliate_id FROM {$wpdb->prefix}affiliate_wp_affiliates WHERE user_id='" . $user_id . "'");

        if (isset($query->affiliate_id)) {
            return $query->affiliate_id;
        }
        return 0;
    }

    private function getCustomerByUserId($user_id)
    {

        global $wpdb;
        $query = $wpdb->get_row("SELECT customer_id FROM {$wpdb->prefix}affiliate_wp_customers WHERE user_id='" . $user_id . "'");

        if (isset($query->customer_id)) {
            return $query->customer_id;
        }
        return 0;
    }

    private function getParentByCustomerId($customer_id)
    {
        global $wpdb;
        $query = $wpdb->get_row("SELECT meta_value FROM {$wpdb->prefix}affiliate_wp_customermeta WHERE affwp_customer_id='" . $customer_id . "' AND meta_key='affiliate_id'");

        if (isset($query->meta_value)) {
            return $query->meta_value;
        }
        return 0;
    }
}