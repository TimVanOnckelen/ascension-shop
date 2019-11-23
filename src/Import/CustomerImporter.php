<?php


namespace AscensionShop\Import;

class CustomerImporter
{

    public function __construct()
    {

        add_action("init", array($this, "doCustomerDeleteEmptyDry"));

    }

    public function doCustomerDeleteEmptyDry(){

	    if (is_admin() && isset($_REQUEST["ascension-customer-input"])) {

		    require_once(ABSPATH.'wp-admin/includes/user.php' );


		    $args = array(
			    'date_query'    => array(
				    array(
					    'after'     => '2019-05-13 00:00:00',
				    ),
			    ),
			    'meta_query' => array(

				    'relation' => 'OR',
				    array(
					    'key' => 'first_name',
					    'value' => '',
					    'compare' => '=',
				    ),
				    array(
					    'key' => 'first_name',
					    'value' => '',
					    'compare' => 'NOT EXISTS',
				    )
			    ),
		    );
		    $users = get_users( $args );

		    foreach ($users as $u){
			    echo '#'.$u->ID.''.$u->display_name.' deleted <br />';
			    wp_delete_user( $u->ID );
		    }
		    exit;
	    }
    }

    public function doCustomerImportDry()
    {

        if (is_admin() && isset($_REQUEST["ascension-customer-input"])) {

            $amount_of_exsisting = 0;
            $failed = 0;

            $csv = array_map("str_getcsv", file(XE_ASCENSION_SHOP_PLUGIN_PATH . "/import/klanten_to_import_cy.csv", FILE_SKIP_EMPTY_LINES));
            $keys = array_shift($csv);



            foreach ($csv as $i => $row) {
                $csv[$i] = array_combine($keys, $row);
            }

            $count = 0;

            foreach ($csv as $user) {

                if (!isset($user["email"])) {
                    continue;
                }

                // Fix emails
                $user["email"] = str_replace(' ', '', $user["email"]);
                $user["email"] = str_replace('/\s+/', '', $user["email"]);

	            $user_id = email_exists($user["email"]);
                if ($user_id != false) {

                	$this->addCustomer($user,$user_id);

	                $amount_of_exsisting++;
                    continue;
                }


                if (!is_email($user["email"])) {
                    echo "failed:" . $user["email"] . "<br />";
                    $failed++;
                    $count;
                    continue;
                }

                $u = $this->addUser($user);

                if (!is_wp_error($u)) {
                    echo "Gebruiker " . $u . " toegevoegd. <br />";

                    // Add customer data
                    $this->addCustomer($user, $u);
                    $count++;
                } else {
                    echo "Gebruiker " . $user["email"] . " failed<br />" . " ERROR:" . $u->get_error_message();
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

    private function addUser($user)
    {

        $user_id = 0;

        if (!email_exists($user["email"])) {

            $user_name = ltrim($user["voornaam"] . '.' . $user["achternaam"]);
            $user_name = preg_replace(" ", "", $user_name);
            $user_name = preg_replace(".", "", $user_name);
            $user_name = preg_replace("/\s+/", "", $user_name);

            // Geen voornaam of achternaam? Dan is username = email
            if ($user_name == '') {
                $user_name = $user["email"];
            }

            $random_password = wp_generate_password($length = 12, $include_standard_special_chars = false);
            $user_id = wp_create_user($user_name, $random_password, $user["email"]);

            if (!is_wp_error($user_id)) {
                // Add meta data
                $this->addUserMeta($user, $user_id);
            }
        } else {
            echo "gebruiker bestaat al " . $user["email"];
        }

        return $user_id;

    }

    private function addUserMeta($user, $user_id)
    {

        $userObject = new \WP_User($user_id);
        $userObject->set_role("customer");

        wp_update_user(array(
            'ID' => $user_id, // this is the ID of the user you want to update.
            'first_name' => $user["voornaam"],
            'last_name' => $user["achternaam"],
        ));

        try {
            $customer = new \WC_Customer($user_id);
            $customer->set_billing_first_name($user["voornaam"]);
            $customer->set_billing_last_name($user["achternaam"]);
            $customer->set_billing_address_1($user["adres"]);
            $customer->set_billing_postcode($user["postcode"]);
            $customer->set_billing_city($user["stad"]);
            $customer->set_billing_state($user["stad"]);
            // $customer->set_billing_country( $user["land"] );
            $customer->set_billing_phone($user["telefoon"]);
            $customer->set_email($user["email"]);

            // Set discount
            $user["discount;"] = str_replace(";", "", $user["discount;"]);
            update_user_meta($user_id, "ascension_shop_affiliate_coupon", $user["discount;"]);

            $customer->save_data();
        } catch (\WC_Data_Exception $e) {
            echo "<br />" . $e->getMessage();
        }
    }

    private function addCustomer($user, $user_id)
    {

        // store the affiliate ID with the user.
        $customer = affwp_get_customer($user["email"]);

        if (!$customer) {
            // Add customer
            $customer_id = affwp_add_customer(
                array(
                    "first_name" => $user["voornaam"],
                    "last_name" => $user["achternaam"],
                    "email" => $user["email"],
                    "affiliate_id" => $user["affiliate_id"],
                    "user_id" => $user_id,
                    "date_created" => ""
                )
            );

            print_r("Klanten nummer:" . $customer_id);

            // Add affiliate id
            if ($customer_id != false) {
                affwp_add_customer_meta($customer_id, 'affiliate_id', $user["affiliate_id"], true);
            }

        }else{
	        affwp_add_customer_meta($customer->customer_id, 'affiliate_id', $user["affiliate_id"], true);

        }

    }
}