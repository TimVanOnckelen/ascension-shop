<?php
/**
 * Created by PhpStorm.
 * User: Tim Van Onckelen
 * Date: 8/09/2019
 * Time: 16:58
 */

namespace AscensionShop\Affiliate;


class Mails
{

    function __construct()
    {
        add_filter("affwp_notify_on_new_referral", array($this, "checkIfRefferalIsNotZero"), 10, 1);
    }

	/**
	 * Do not send mail on zero
	 * @param $status
	 * @param $referral
	 *
	 * @return bool
	 */
    public function checkIfRefferalIsNotZero($status,$referral)
    {
        // Don't send zero commision mails
        if($referral->amount <= 0) {
            error_log("Zero commission mail for ".$object->refferal->referral_id." cancelled");
            return false;
        }

        return $status;

    }
}