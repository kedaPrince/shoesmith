<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {
	var $loadedPlugins = array(); //Keep track of huge plugins
	var $uploaders = array();

	public function __construct() {
		parent::__construct();
		//Set language
		$this->set_language();
		header('Strict-Transport-Security: max-age=16070400; includeSubDomains');
	}

	private function set_language() {
		$languages = $this->config->item('languages');

		//Load language from based off second segment
		$code = $this->uri->segment(2, 'en');
		if ( ! empty($languages[$code])) {
			$this->lang->load('general', $languages[$code]);
		}
		else {
			//Could not determine language from second second, so trying the first segment
			$code = $this->uri->segment(1, 'en');
			if ( ! empty($languages[$code])) {
				$this->lang->load('general', $languages[$code]);
			}
			else {
				//Could not determine the language at all. Setting it to English.
				$this->lang->load('general', 'english');
			}
		}
	}

	/**
	 * Sluggifies a string
	 *
	 * @param string $str
	 * @param string $table
	 * @param int $id
	 *
	 * @return string
	 */
	function sluggify($str, $table = NULL, $id = 0) {
		if ( ! empty($str)) {

			//Format initial string
			$str = strtolower($str);
			$str = html_entity_decode($str);
			$str = strip_tags($str);
			$str = stripslashes($str);
			$str = str_replace('\'', '', $str);
			$str = preg_replace('/[^a-z0-9]+/', '-', $str);
			$str = trim($str, '-');

			$slug = $this->{$this->model}->check_unique_slug($str, $table, $id);

			return $slug;
		}

		return FALSE;
	}

	/**
	 * Fail
	 *
	 * A test callback function to force a serverside validation error.
	 * Use this in your validation rules by adding callback_fail.
	 *
	 * @return bool
	 */
	public function fail() {
		return FALSE;
	}

	public function boolean_field($str, $field) {

		$_POST[$field] = $str ? '1' : '0';
	}

	public function ajax_upload_blob() {
		//Get the file information
		$filename = $this->input->post('fname');
		$fieldname = $this->input->post('field_name');
		$sizes = $this->input->post('sizes');

		$imageData = array(
			'name' => $_FILES['imagefile']['name'],
			'temp_name' => $_FILES['imagefile']['tmp_name'],
			'size' => $_FILES['imagefile']['size'],
			'type' => $_FILES['imagefile']['type'],
			'basename' => $filename,
			'file_ext' => pathinfo($filename, PATHINFO_EXTENSION),
			'error' => array(),
			'field_name' => $fieldname
		);

		$folder = $this->pageName . '/' . date('ymd');
		$upload_path = 'resources/uploads/' . $folder . '/';
		dir_create($upload_path);
		$finalFileName = $this->unique_filename($upload_path, $filename);
		$imageData['file_name'] = $finalFileName;
		$imageData['folder'] = $folder;
		$upload_file_location = $upload_path . $imageData['file_name'];
		if (move_uploaded_file($imageData['temp_name'], $upload_file_location)) {
			$imageData['success'] = 1;
		}

		//Resize image into different sizes
		$this->load->library('image_uploader');
		$sizesArray = $sizes ? explode(',', $sizes) : array();
		$sizesArray[] = 200;

		foreach ($sizesArray as $s) {
			$s = trim($s);
			dir_create($upload_path . $s . '/');
			$thumb_file_name = $upload_path . $s . '/' . $finalFileName;
			$this->image_uploader->createThumbnailImage($thumb_file_name, $upload_file_location, $s);
		}

		ajax_return($imageData);
	}

	public function unique_filename($filepath, $filename) {

		$filename = safe_file_name($filename);

		while (TRUE) {
			if ( ! file_exists($filepath . '/' . $filename)) {
				break;
			}
			else {
				preg_match('/\_(\d+)\.\w*$/', $filename, $matches);

				if ( ! empty($matches[1])) {
					$numExt = $matches[0];
					$num = $matches[1];
					$num++;

					$newNumExt = preg_replace('/\_\d+/', '_' . $num, $numExt);
					$filename = preg_replace('/\_\d+\.\w*$/', $newNumExt, $filename);
				}
				else {
					$num = 1;
					$ext = pathinfo($filename, PATHINFO_EXTENSION);
					$newNumExt = '_1.' . $ext;
					$filename = preg_replace('/\.\w*$/', $newNumExt, $filename);
				}
			}
		}

		return $filename;
	}

	public function ajax_dz_upload() {
		//Set upload path
        $folder 					= 'temp/'.date('ymd').'/';
        $uploadPath                	= 'resources/uploads/'.$folder;
		dir_create($uploadPath);

		//Make sure filename is unique and upload to temp location
		$finalFileName 				= $this->unique_filename($uploadPath, $_FILES['file']['name']);
        if (!move_uploaded_file($_FILES['file']['tmp_name'],  $uploadPath.$finalFileName)) {
            ajax_error('Unable to upload file!');
			return;
		}

		$fileData = array(
            'file_name'     => $finalFileName,
            'size'          => $_FILES['file']['size'],
            'type'          => $_FILES['file']['type'],
            'ext'      		=> pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION),
			'upload_path'	=> $folder
        );
        
        ajax_return($fileData);
	}

	public function process_uploads($id) {
		//Check if any uploaders have been specified
		if (empty($this->uploaders)) {
			return;
		}

		//Foreach uploader field
		foreach ($this->uploaders as $field => $options) {
			if (in_array($options['type'], ['multi_image', 'multi_file'])) {
				$this->process_multi_uploads($id, $field, $options);
			}
			elseif (in_array($options['type'], ['single_image', 'single_file'])) {
				$this->process_single_uploads($id, $field, $options);
			}
		}

	}

	public function process_multi_uploads($id, $field, $options, $files = null) {
		// Use provided $files or fall back to POST data
		$files = $files ?? $this->input->post($field);
		if (!$files) {
			// No files provided or posted
			return;
		}

		//The type of uploader this is
		$type = !empty($options['type']) ? $options['type'] : 'file';

		//Determine module to be used as upload destination
		$module = !empty($options['module']) ? $options['module'] : $this->pageName;

		//Other options
		$table 			= $options['table'];
		$parentField	= $options['parent_field'];
		$extraFields 	= !empty($options['fields']) ? $options['fields'] : array();

		//Load image upload library if image
		if ($type == 'multi_image') {
			$this->load->library('image_uploader');

			//Determine the sizes
			$sizesArray 	= !empty($options['sizes']) ? $options['sizes'] : [];
			$sizesArray[] 	= 50;
			$sizesArray[] 	= 350;

			$folder = 'images';
		}
		else {
			$folder = 'files';
		}

		//If new file then move to correct folder 
		$resourcePath = 'resources/uploads/'.$folder.'/';
		$uploadPath = $module.'/'.date('Y').'/'.date('m').'/'.$id.'/';
		dir_create($resourcePath.$uploadPath);

		//Foreach File
		$insertData = array();
		$updateData = array();
		$removeData = array();
		foreach ($files as $f => $file) {
			if ($file['status'] == 'new') {

				//Make sure the filename is unique in that folder
				$finalFileName = $this->unique_filename($resourcePath.$uploadPath, $file['file']);

				//Move file to permanent location
				rename(abs_path().'resources/uploads/'.$file['path'].$file['file'], abs_path().$resourcePath.$uploadPath.$finalFileName);

				if ($type == 'single_image' || $type == 'multi_image') {
					//Create various sizes
					foreach ($sizesArray as $s) {
						$s = trim($s);
						dir_create($resourcePath.$uploadPath.$s.'/');
						$thumbFileName = $resourcePath.$uploadPath.$s.'/'.$finalFileName;
						$this->image_uploader->createThumbnailImage($thumbFileName, $resourcePath.$uploadPath.$finalFileName, $s);
					}
				}

				//Data to save to the DB
				$extra = !empty($file['fields']) ? $this->only_valid_fields($file['fields'], $extraFields) : array();
				$insertData[] = array_merge([
					$parentField 	=> $id,
					'created_at' 	=> date_now(),
					'position'		=> $file['position'],
					$field			=> $uploadPath.$finalFileName
				], $extra);
			}
			elseif ($file['status'] == 'updated') {
				//Data to save to the DB
				$extra = !empty($file['fields']) ? $this->only_valid_fields($file['fields'], $extraFields) : array();
				$updateData[$f] = array_merge([
					'position'		=> $file['position'],
					'updated_at' 	=> date_now()
				], $extra);
			}
			elseif ($file['status'] == 'removed') {
				//Data to save to the DB
				$removeData[$f] = array(
					'removed' 		=> 1,
					'deleted_at' 	=> date_now()
				);
			}
		}

		//Save new data to database
		if (!empty($insertData)) {
			$result = $this->db->insert_batch($table, $insertData);
			if (!$result) {
				anomalies::log('Failed to insert file data', $this->db->last_query());
			}
		}

		//Save updated data to database
		if (!empty($updateData)) {
			$result = bulk_update($table, $updateData);
			if (!$result) {
				anomalies::log('Failed to update file data', $this->db->last_query());
			}
		}

		//Save removed data to database
		if (!empty($removeData)) {
			$result = bulk_update($table, $removeData);
			if (!$result) {
				anomalies::log('Failed to remove file data', $this->db->last_query());
			}
		}
	}

	public function process_single_uploads($id, $field, $options) {
		$files = $this->input->post($field);
		if (!$files) {
			//No files posted
			return;
		}

		//The type of uploader this is
		$type = $options['type'];

		//Determine module to be used as upload destination
		$module = !empty($options['module']) ? $options['module'] : $this->pageName;

		//Other options
		$table = $options['table'];
		$extraFields = !empty($options['fields']) ? $options['fields'] : [];

		//Load image upload library if image
		if ($type == 'single_image') {
			$this->load->library('image_uploader');

			//Determine the sizes
			$sizesArray 	= !empty($options['sizes']) ? $options['sizes'] : [];
			$sizesArray[] 	= 50;
			$sizesArray[] 	= 350;

			$folder = 'images';
		}
		else {
			$folder = 'files';
		}

		// Resource and upload paths
		$resourcePath = 'resources/uploads/'.$folder.'/';
		$uploadPath = $module.'/'.date('Y').'/'.date('m').'/'.$id.'/';
		dir_create($resourcePath.$uploadPath);

		//Determine what the field is called in the db
		$saveField = !empty($options['dbField']) ? $options['dbField'] : $field;

		// Initialize update data
		$updateData = [];
		$hasNewImage = false;

		// Process all files
		foreach ($files as $f => $file) {
			if ($file['status'] == 'new') {
				$hasNewImage = true;

				// Handle field renaming for db
				if (!empty($options['dbField']) && !empty($file['fields'])) {
					$tempFileFields = $file['fields'];
					foreach ($tempFileFields as $k => $v) {
						if (strstr($k, $field)) {
							$file['fields'][str_replace($field, $options['dbField'], $k)] = $v;
							unset($file['fields'][$k]);
						}
					}
				}

				// Make sure the filename is unique
				$finalFileName = $this->unique_filename($resourcePath.$uploadPath, $file['file']);

				// Ensure the destination directory exists
				$destinationDirPath = abs_path() . $resourcePath . $uploadPath;
				if (!is_dir($destinationDirPath)) {
				    mkdir($destinationDirPath, 0755, true); // Attempt to create the directory
				}

				// Move file to permanent location
				$sourcePath = abs_path() . 'resources/uploads/' . $file['path'] . $file['file'];
				$destPath = abs_path() . $resourcePath . $uploadPath . $finalFileName;
				if (rename($sourcePath, $destPath)) {
					if ($type == 'single_image') {
						// Create various sizes
						foreach ($sizesArray as $s) {
							$s = trim($s);
							dir_create($resourcePath . $uploadPath . $s . '/');
							$thumbFileName = $resourcePath . $uploadPath . $s . '/' . $finalFileName;
							$this->image_uploader->createThumbnailImage($thumbFileName, $resourcePath . $uploadPath . $finalFileName, $s);
						}
					}

					// Data to save to the DB
					$extra = !empty($file['fields']) ? $this->only_valid_fields($file['fields'], $extraFields) : [];
					$updateData = array_merge([
						$saveField => $uploadPath . $finalFileName
					], $extra);
				} else {
					anomalies::log('Failed to move file: ' . $sourcePath . ' to ' . $destPath);
				}
			} elseif ($file['status'] == 'updated') {
				// Data to save to the DB
				$updateData = !empty($file['fields']) ? $this->only_valid_fields($file['fields'], $extraFields) : [];
			} elseif ($file['status'] == 'removed') {
				// Data to save to the DB
				$updateData = [
					$saveField => null,
				];

				// Empty the extra fields as well
				foreach ($extraFields as $extra) {
					$updateData[$extra] = null;
				}
			}
		}

		// If a new image was uploaded, clear any existing images
		if ($hasNewImage) {
			// Nullify the saveField for existing images
			$clearData = [
				$saveField => null,
			];
			foreach ($extraFields as $extra) {
				$clearData[$extra] = null;
			}
			$this->db->update($table, $clearData, ['id' => $id]);

			// Save new image data
			if (!empty($updateData)) {
				$result = $this->db->update($table, $updateData, ['id' => $id]);
				if (!$result) {
					anomalies::log('Failed to update single file data', $this->db->last_query());
				}
			}
		} elseif (!empty($updateData)) {
			// Save updates or removals
			$result = $this->db->update($table, $updateData, ['id' => $id]);
			if (!$result) {
				anomalies::log('Failed to update single file data', $this->db->last_query());
			}
		}
	}

	public function only_valid_fields($postedData, $fields) {
		if (empty($fields) || empty($postedData)) {
			return array();
		}

		$validFields = array();
		foreach ($fields as $field) {
			if (isset($postedData[$field])) {
				$validFields[$field] = $postedData[$field];
			}
		}

		return $validFields;
	}

	public function get_uploader_data($field, $row) {
		//Check if any uploaders have been specified
		if (empty($this->uploaders[$field])) {
			return array();
		}


		//Extra parent ID
		$id = is_object($row) ? $row->id : $row;

		if (empty($id)) {
			//ID is 0 or empty, no point in continuing
			return array();
		}

		$uploaderData = array();
		
		$options = $this->uploaders[$field];

		$extraFields 	= !empty($options['fields']) ? $options['fields'] : array();

		if (in_array($options['type'],  ['multi_image', 'multi_file'])) {
			$query = $this->{$this->model}->get_uploader_data($options['table'], $options['parent_field'], $id, $field, $extraFields);
			if ($query->num_rows() > 0) {
				$i = 0;
				foreach ($query->result_array() as $res) {
					$uploaderData[$i] 				= extract_values($res, ['id', 'position']);
					$uploaderData[$i]['path']		= $res[$field];
					$uploaderData[$i]['file_name'] 	= pathinfo($res[$field], PATHINFO_BASENAME);
					$uploaderData[$i]['fields'] 	= extract_values($res, $extraFields);
					$i++;
				}
			}
		}
		elseif (in_array($options['type'],  ['single_image', 'single_file'])) {
			if (is_object($row)) {
				$res = (array)$row;
			}
			else {
				//If just an ID then attempt to query for the data
				$query = $this->{$this->model}->get_uploader_data($options['table'], 'id', $id, $field, $extraFields, false);
				if ($query->num_rows() == 0) {
					//Can't find data
					return array();
				}
				$res = $query->row_array();
			}

			//Check if there is an image
			if (empty($res[$field])) {
				return array();
			}

			$uploaderData[0]['id'] 			= $res['id'];
			$uploaderData[0][$field] 		= $res[$field];
			$uploaderData[0]['path'] 		= $res[$field];
			$uploaderData[0]['file_name'] 	= !empty($res[$field]) ? pathinfo($res[$field], PATHINFO_BASENAME) : '';
			$uploaderData[0]['fields'] 		= !empty($options['fields']) ? extract_values($res, $options['fields']) : array();
		}

		return $uploaderData;
	}

	public function ajax_ck_upload() {
		//Set upload path
        $uploadPath = 'resources/ckuploads/'.date('ymd').'/';
		dir_create($uploadPath);

		//Make sure filename is unique and upload to temp location
		$finalFileName 				= $this->unique_filename($uploadPath, $_FILES['file']['name']);
        if (!move_uploaded_file($_FILES['file']['tmp_name'],  $uploadPath.$finalFileName)) {
            ajax_error('Unable to upload file!');
			return;
		}

        ajax_return([
			'url' => site_url($uploadPath.$finalFileName),
		]);
	}
}

require_once('CRUD_Controller.php');
require_once('Front_Controller.php');
