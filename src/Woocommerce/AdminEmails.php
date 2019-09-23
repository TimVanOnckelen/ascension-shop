<?php


namespace AscensionShop\Woocommerce;


class AdminEmails
{

    public function __construct()
    {
        add_filter('woocommerce_email_headers', array($this, "sendEmailToLangAdmin"), 10, 3);
    }

    public function sendEmailToLangAdmin($headers, $email_id, $order)
    {


        // Get lang of order
        $lang = get_post_meta($order->get_id(), "wpml_language", true);

        $temp_recipient = $this->getAdminsEmailsFromLang($lang);

        switch ($email_id) {
            case 'new_order':
                $headers .= 'Bcc: ' . implode(',', $temp_recipient) . "\r\n";
                break;

            default:
        }

        return $headers;

    }

    private function getAdminsEmailsFromLang($lang)
    {

        $email_list = array();

        $user_args = array(
            'meta_query' => array(
                array(
                    'key' => 'as_user_ln',
                    'value' => serialize($lang),
                    'compare' => 'LIKE',
                ),
            )
        );
        $users = new \WP_User_Query($user_args);

        if ($users->get_total() > 0) {
            foreach ($users->get_results() as $user) {
                $email_list[] = $user->user_email;
            }
        }

        return $email_list;

    }


}