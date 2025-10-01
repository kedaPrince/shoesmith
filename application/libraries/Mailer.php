<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Mailer {
	
	public function queue($to, $from, $subject, $body, $force=false, $cc=array(), $bcc=array(), $files=array()) {
		$ci =& get_instance();

		//Add mail to queue
		$ci->db->set('email_to', $to);
		$ci->db->set('email_from', $from);
		$ci->db->set('email_cc', implode(', ', $cc));
		$ci->db->set('email_bcc', implode(', ', $bcc));
		$ci->db->set('email_subject', $subject);
		$ci->db->set('email_body', $body);
		$ci->db->set('email_attachments', implode("\n", $files));
		$ci->db->set('created_at', date("Y-m-d H:i:s"));
		$result = $ci->db->insert('sys_emails_queue');

		if ($result) {
			if ($force) {
				$id = $ci->db->insert_id();
				$this->process_queue($id);
			}
		}
		else {
			$message = 'Failed to insert mail to queue';
			Anomalies::log(trim($message), $ci->db->last_query());
		}
	}

	public function process_queue($id=0) {
		$ci =& get_instance();

		//Get site name
		$siteName = $ci->config->item('site_name');

		//Get limit of mails to process
		$limit = $ci->config->item('mail_process_limit');

		//Get mails in queue
		$ci->db->where('failed', 0);
		$ci->db->order_by('id', 'asc');
		$ci->db->limit($limit);
		if ($id) {
			$ci->db->where('id', $id);
		}
		$query = $ci->db->get('sys_emails_queue');

		if ($query->num_rows() > 0) {
			$ci->load->library('email');

			//Load SMTP if given
			$mailConfig = $ci->config->item('mail_config');
			if (!empty($mailConfig)) {
				$ci->email->initialize($mailConfig);
			}

			$f = 0;
			foreach ($query->result() as $row) {
				$ci->email->clear(TRUE);

				//Setup email
				$ci->email->to($row->email_to);
				$ci->email->from($row->email_from, $siteName);
				$ci->email->subject($row->email_subject);
				$ci->email->message($row->email_body);

				//Add BCCs and CCs
				!empty($row->email_cc) && $ci->email->cc($row->email_cc);
				!empty($row->email_bcc) && $ci->email->bcc($row->email_bcc);

				//Add attachments
				if (!empty($row->email_attachments)) {
					$files = explode("\n", $row->email_attachments);
					foreach ($files as $file) {
						$ci->email->attach($file);
					}
				}
				$result = $ci->email->send(false);

				//If successful
				if ($result) {
					//Add to sent table
					$ci->db->set('email_to', $row->email_to);
					$ci->db->set('email_from', $row->email_from);
					$ci->db->set('email_cc', $row->email_cc);
					$ci->db->set('email_bcc', $row->email_bcc);
					$ci->db->set('email_subject', $row->email_subject);
					$ci->db->set('email_body', $row->email_body);
					$ci->db->set('email_attachments', $row->email_attachments);
					$ci->db->set('sent_at', date("Y-m-d H:i:s"));
					$ci->db->insert('sys_emails_sent');

					//Remove entry from queue
					$ci->db->delete('sys_emails_queue', array('id' => $row->id));
				}
				else {
					//Increment fail counter
					$f++;

					//log error
					$ci->db->set('failed', 1);
					$ci->db->set('failed_date', date("Y-m-d H:i:s"));
					$ci->db->set('email_errors', $ci->email->print_debugger());
					$ci->db->where('id', $row->id);
					$ci->db->update('sys_emails_queue');
				}
			}

			//If any errors occurred then notify the users
			if ($f) {
				//TODO: Send to users
			}
		}
	}
}