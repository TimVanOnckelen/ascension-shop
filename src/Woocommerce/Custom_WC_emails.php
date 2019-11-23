<?php


namespace AscensionShop\Woocommerce;


use AscensionShop\Woocommerce\mails\PaymentReminderEmail;

class Custom_WC_emails {

	public function __construct()
	{
		// Filtering the emails and adding our own email.
		add_action('woocommerce_email_classes', array($this, 'register_email'), 10, 1);

	}

	/**
	 * @param array $emails
	 *
	 * @return array
	 */
	public function register_email($emails)
	{

		$emails['WC_paymentreminder'] = new PaymentReminderEmail();
		return $emails;
	}

}