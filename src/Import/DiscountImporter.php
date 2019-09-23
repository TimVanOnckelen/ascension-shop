<?php


namespace AscensionShop\Import;


use mysql_xdevapi\Exception;

class DiscountImporter
{

    public function __construct()
    {

        add_action("init", array($this, "doCustomerImportDry"));

        set_time_limit(300);


    }

    public function doCustomerImportDry()
    {

        if (is_admin() && isset($_REQUEST["ascension-discount-inmport"])) {

            $amount_of_exsisting = 0;
            $failed = 0;

            $csv = array_map("str_getcsv", file(XE_ASCENSION_SHOP_PLUGIN_PATH . "/import/klanten_to_import.csv", FILE_SKIP_EMPTY_LINES));
            $keys = array_shift($csv);

            foreach ($csv as $i => $row) {
                $csv[$i] = array_combine($keys, $row);
            }


            $count = 0;

            foreach ($csv as $user) {

                if (!isset($user["email"])) {
                    continue;
                }


                if (email_exists($user["email"])) {

                    $user_id = get_user_by("email", $user["email"]);
                    $user_id = $user_id->ID;

                    if (isset($user["discount"])) {
                        // Set discount
                        $user["discount"] = str_replace(";", "", $user["discount"]);
                        echo "Set user " . $user_id . " to " . $user["discount"];

                        update_user_meta($user_id, "ascension_shop_affiliate_coupon", $user["discount"]);
                    } else {
                        print_r($user);
                    }
                    $amount_of_exsisting++;
                    continue;
                }


                if (!is_email($user["email"])) {
                    echo "failed:" . $user["email"] . "<br />";
                    $failed++;
                    $count;
                    continue;
                }


            }

            $total = count($csv);
            $amount_to_add = $total - $amount_of_exsisting;

            echo "Aantal records:" . $total;
            echo "<br />Aantal reeds bestaande customers:" . $amount_of_exsisting;
            echo "Aantal toegevoegd:" . $count;
            echo "Aantal mislukt:" . $failed;
            echo "<br />Aantal toe te voegen customers" . $amount_to_add;

            // print_r($csv);
            die();
        }
    }


}