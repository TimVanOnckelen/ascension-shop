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

        if (is_admin() && isset($_REQUEST["ascension-discount-import"])) {

            $amount_of_exsisting = 0;
            $failed = 0;

            $csv = array_map("str_getcsv", file(XE_ASCENSION_SHOP_PLUGIN_PATH . "/import/partners_de.csv", FILE_SKIP_EMPTY_LINES));
            $keys = array_shift($csv);

            foreach ($csv as $i => $row) {
                $csv[$i] = array_combine($keys, $row);
            }


            $count = 0;

            foreach ($csv as $user) {


                if (!isset($user["Email"])) {
                    continue;
                }


                if (email_exists($user["Email"])) {
                    $the_user = get_user_by("email", $user["Email"]);
                    $user_id = $the_user->ID;

                    if(isset($user["voornaam"]) && $the_user->first_name == ''){
	                    update_user_meta($user_id,"first_name",$user["voornaam"]);
	                    update_user_meta($user_id,"last_name",$user["achternaam"]);
	                    update_user_meta($user_id,"first_name",$user["voornaam"].$user["achternaam"]);
	                    update_user_meta($user_id,"billing_first_name",$user["voornaam"]);
	                    update_user_meta($user_id,"billing_last_name",$user["achternaam"]);
	                    echo "Set user " . $user_id;
                    }

                    /*
                    if (isset($user["adres"])) {
                        // Set discount
                        echo "Set user " . $user_id . " adress to " . $user["adres"];

	                    update_user_meta($user_id,"billing_address_1",$user["adres"]);
	                    update_user_meta($user_id,"billing_city",$user["stad"]);
	                    update_user_meta($user_id,"billing_postcode",$user["postcode"]);
	                    update_user_meta($user_id,"billing_country",$user["land"]);
	                    update_user_meta($user_id,"billing_phone",$user["telefoon"]);

                    } else {
                        print_r($user);
                    }
                    */

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