<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class PayFast {
	function generateForm($bidder, $package, $page) {
		$ci =& get_instance();
		if (empty($bidder) || empty($package)) {
			return;
		}

		$sandbox = $ci->config->item('payfast_sandbox_mode');
		if ($sandbox) {
			$merchantID = $ci->config->item('payfast_sandbox_merchant_id');
			$merchantKey = $ci->config->item('payfast_sandbox_merchant_key');
			$passPhrase = $ci->config->item('payfast_sandbox_passphrase');
		} else {
			$merchantID = $ci->config->item('payfast_merchant_id');
			$merchantKey = $ci->config->item('payfast_merchant_key');
			$passPhrase = $ci->config->item('payfast_passphrase');
		}


		$data = array(
			// Merchant details
			'merchant_id' => $merchantID,
			'merchant_key' => $merchantKey,
			'return_url' => $page . '?payfast_return=1',
			'cancel_url' => $page . '?payfast_cancel=1',
			'notify_url' => site_url() . 'buy_biddies/notify',
			// Buyer details
			'name_first' => $bidder['first_name'],
			'name_last' => $bidder['last_name'],
			'email_address' => $bidder['email'],
			// Transaction details
			'm_payment_id' => md5(date('Y-m-d H:i:s')),
			'amount' => number_format(sprintf('%.2f', ($package->price / 100)), 2, '.', ''),
			'item_name' => $package->biddies . ' Biddies',
			'custom_int1' => $bidder['id'],
			'custom_int2' => $package->id,

		);

		$signature = $this->generateSignature($data, $passPhrase);
		$data['signature'] = $signature;

		$pfHost = $sandbox ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';
		$htmlForm = '<form id="payfast-form" action="https://' . $pfHost . '/eng/process" method="post">';
		foreach ($data as $name => $value) {
			$htmlForm .= '<input name="' . $name . '" type="hidden" value=\'' . $value . '\' />';
		}

		return $htmlForm;
	}

	/**
	 * @param array $data
	 * @param null $passPhrase
	 * @return string
	 */
	function generateSignature($data, $passPhrase = NULL) {
		// Create parameter string
		$pfOutput = '';
		foreach ($data as $key => $val) {
			if ($val !== '') {
				$pfOutput .= $key . '=' . urlencode(trim($val)) . '&';
			}
		}
		// Remove last ampersand
		$getString = substr($pfOutput, 0, -1);
		if ($passPhrase !== NULL) {
			$getString .= '&passphrase=' . urlencode(trim($passPhrase));
		}
		return md5($getString);
	}

	function log_api($action, $headers, $body, $response) {
		$ci =& get_instance();

		$ci->db->set('action', $action);
		$ci->db->set('headers', json_encode($headers));
		$ci->db->set('body', json_encode($body));
		$ci->db->set('response', is_array($response) ? json_encode($response) : $response);
		$ci->db->set('created', date('Y-m-d H:i:s'));

		$ci->db->insert('log_payfast_api');
	}
}
