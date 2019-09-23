<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 12/07/2019
 * Time: 14:39
 */

namespace AscensionShop\Woocommerce;


class AdminOrders
{

    private $currentLang = ICL_LANGUAGE_CODE;
    private $user_id = 0;

    function __construct()
    {

        if (!is_admin()) {
            return;
        }

        // Filter out right orders by lang
        add_filter('pre_get_posts', array($this, 'filterOutOrders'));

        // Show lang options on user edit
        add_action('show_user_profile', array($this, 'addShopLangToUser'));
        add_action('edit_user_profile', array($this, 'addShopLangToUser'));

        // Save on edit
        add_action('personal_options_update', array($this, "saveShopLangFromAdmin"));
        add_action('edit_user_profile_update', array($this, "saveShopLangFromAdmin"));

        // Add lang of order to as colomn
        add_filter('manage_edit-shop_order_columns', array($this, "addOrderLangTitletoColumn"), 10, 1);
        add_action('manage_posts_custom_column', array($this, "addOrderLangtoColumn"), 10, 1);

        // Get & set the current user
        $this->user_id = get_current_user_id();

    }

    /**
     * Filter out posts by ID
     * @param $query
     *
     * @return mixed
     */
    public function filterOutOrders($query)
    {

        global $pagenow;
        $qv = &$query->query_vars;

        if ($pagenow == 'edit.php' &&
            isset($qv['post_type']) && $qv['post_type'] == 'shop_order') {

            // Get current user id
            $user_id = get_current_user_id();
            // Get current user langs as shop manager
            $userln = get_the_author_meta('as_user_ln', $user_id);

            // THe relations array
            $user_ln_relation_array = $this->createMetaArrayLang($userln);

            // Add meta query
            $meta_query = $user_ln_relation_array;

            // Set the query
            $query->set('meta_query', $meta_query);
        }


        return $query;

    }

    /**
     * Add the capability to edit user langs.
     * @param $user
     */
    public function addShopLangToUser($user)
    {
        // Only show if current user can edit
        if (!current_user_can('edit_user', $user->ID)) return;

        if (!is_admin()) return;

        // Get admin langs
        $lang = get_user_meta($user->ID, 'as_user_ln');
        $lang = $lang[0];

        // Get all available langs
        $all_website_langs = icl_get_languages();

        ?>

        <table class="form-table">
            <tr>
                <th><label for="as_user_ln"><?php _e("Winkel eigenaar taal") ?></label></th>
                <td>
                    <?php
                    foreach ($all_website_langs as $l) {
                        ?>
                        <input type="checkbox" name="as_user_ln[]"
                               value="<?php echo $l["code"]; ?>" <?php if (in_array($l["code"], $lang)) echo 'checked'; ?>><?php echo $l["native_name"]; ?>
                        <Br>
                        <?php
                    }
                    ?>

                    </select></td>
            </tr>

        </table>
        <?php
    }

    /**
     * Create a meta query ready array for langs
     * @param $lang_array
     *
     * @return array
     */
    private function createMetaArrayLang($lang_array)
    {

        $user_ln_relation_array = array(
            'relation' => 'OR');

        // Langs In Array
        if (is_array($lang_array)) {
            // Add multiple langs
            foreach ($lang_array as $l) {

                array_push($user_ln_relation_array, array(
                    'key' => 'wpml_language',
                    'value' => $l,
                ));
            }
        } else { // Add single lang
            array_push($user_ln_relation_array, array(
                'key' => 'wpml_language',
                'value' => $lang_array,
            ));
        }

        return $user_ln_relation_array;

    }


    /**
     * @param $user_id
     */
    public function saveShopLangFromAdmin($user_id)
    {
        $return = update_user_meta($user_id, 'as_user_ln', $_POST['as_user_ln']);
        return $return;
    }

    public function addOrderLangTitletoColumn($my_columns)
    {
        $my_columns['storeln'] = __("Taal", "ascension-shop");
        return $my_columns;
    }


    public function addOrderLangtoColumn($column)
    {
        global $post;
        switch ($column) {
            case 'storeln':
                $v = '';
                $x = get_post_meta($post->ID, 'wpml_language', true);
                if (isset($x)) {
                    $v = $x;
                }
                echo ($v) ?: __("Geen waarde", "ascesion-shop");
                break;
        }
    }


}