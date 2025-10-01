<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class BulkSMS {
	public function request($path = "") {
		$ci =& get_instance();

		$apiKey = $ci->config->item('bulksms_api_key');
		$apiSecret = $ci->config->item('bulksms_api_secret');
		$authKey = base64_encode($apiKey . ':' . $apiSecret);
		$authorization = 'Authorization: Basic ' . $authKey;
		$url = 'https://api.bulksms.com/v1/';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url . $path);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array($authorization));
		curl_setopt($ch, CURLOPT_HEADER, FALSE);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		if (($data = curl_exec($ch)) === FALSE) {
			$result = FALSE;
		} else {
			$result = json_decode($data);
		}
		curl_close($ch);

		return $result;
	}

	public function post($path = "", $params = array(), $method = "") {
		$ci =& get_instance();

		$apiKey = $ci->config->item('bulksms_api_key');
		$apiSecret = $ci->config->item('bulksms_api_secret');
		$authKey = base64_encode($apiKey . ':' . $apiSecret);
		$authorization = 'Authorization: Basic ' . $authKey;
		$url = 'https://api.bulksms.com/v1/';

		$data_string = json_encode($params);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url . $path);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array($authorization));
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		if ( ! empty($method)) {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		}
		else {
			curl_setopt($ch, CURLOPT_POST, TRUE);
		}

		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			$authorization,
			'Content-Type: application/json',
			'Content-Length: ' . strlen($data_string)
		));

		if (($data = curl_exec($ch)) === FALSE) {
			$result = FALSE;
		} else {
			$result = json_decode($data);
		}
		curl_close($ch);

		return $result;
	}

	public function process_queue($smsID = 0) {
		$ci =& get_instance();

		//Get items in queue
		$ci->db->where('sms_sent', 0);
		$ci->db->where('failed', 0);
		$smsID && $ci->db->where('id', $smsID);
		$ci->db->order_by('id', 'ASC');
		$query = $ci->db->get('sys_sms_queue');

		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$data = array(
					'body' => $row->message,
					'to' => json_decode($row->recipient_data)
				);

				$result = $this->post('messages', $data);
				if (empty($result->status)) {
					$ci->db->set('sms_sent', 1);
					$ci->db->set('sent_datetime', date('Y-m-d H:i:s'));
					$ci->db->where('id', $row->id);
					$ci->db->update('sys_sms_queue');
				} else {
					$ci->db->set('error_output', json_encode((array)$result));
					$ci->db->set('failed', 1);
					$ci->db->set('failed_datetime', date('Y-m-d H:i:s'));
					$ci->db->where('id', $row->id);
					$ci->db->update('sys_sms_queue');

					$emailData = array(
						'site_url' => site_url(),
						'error' => ! empty($result->title) ? $result->title : 'Unknown Error',
						'date' => date('j M H:i'),
						'message' => preg_replace('/\#*/', '', $row->message),
						'queue_id' => $row->id
					);

					//Notify admins
					notify_admins('failed_sms', 'Failed to send SMS (#' . $row->id . ')', $emailData);
				}
			}
		}
	}

	public function add_to_queue($message, $recipientData, $extraData = array()) {
		$ci =& get_instance();
		$tagData = array();

		preg_match_all('/\{[^\{\}]+\}/', $message, $tags);
		if ( ! empty($tags[0])) {
			//The message expects tags to be substituted
			foreach ($tags[0] as $k => $tag) {
				$tagData[] = preg_replace('/[\{\}]/', '', $tag);
				$message = str_replace($tag, '{F' . $k . '########################################################################}', $message);
			}
		}

		$fields = array();
		foreach ($tagData as $tag) {
			$fields[] = ! empty($row[$tag]) ? $row[$tag] : (! empty($extraData[$tag]) ? $extraData[$tag] : '');
		}


		//Format number
		$mobile = preg_replace('/^0/', '27', $recipientData->telephone);

		$formattedData[] = array(
			'address' => $mobile,
			'fields' => $fields
		);

		//Save to SMS queue
		$ci->db->set('message', $message);
		$ci->db->set('recipient_data', json_encode($formattedData));
		$ci->db->set('created_at', date('Y-m-d H:i:s'));
		$ci->db->insert('sys_sms_queue');
	}
}
