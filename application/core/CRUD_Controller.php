<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CRUD_Controller extends MY_Controller {
	public $page                    = '';
	public $listFields              = array();
	public $listActions             = array();
	public $listExpanded            = '';
	public $filters                 = array();
	public $formFields              = array();
	public $formLabels              = array();
	public $listing                 = TRUE;
	public $adding                  = TRUE;
	public $editing                 = TRUE;
	public $abling                  = TRUE;
	public $deleting                = TRUE;
	public $loginData               = '';
	public $zone                    = array();
	public $subMenu                 = array();
	public $userNotifications;
	public $folder                  = '';
	public $singular                = '';
	public $plural                  = '';
	public $siteMap                 = array();
	public $identifierField         = 'name';
	public $pageCSS                 = array();
	public $pageJS                  = array();
	public $hideSubNav              = FALSE;
	public $itemCount               = FALSE;
	public $quickManage             = FALSE;
	public $quickManageSize         = 1;
	public $noXSS                   = array();
	public $dfNoXSS                 = array();
	public $dfSluggify              = array();
	public $sluggify                = false;
	public $extraPageActions        = array();

	public function __construct() {
		parent::__construct();
		is_logged_in();
		$this->set_singular_plural();

		$this->zone = array(
			'url'   => redir('dashboard', TRUE),
			'title' => 'My Dashboard'
		);

		$this->loginData = loginData();

		$this->folder = empty($this->folder) ? $this->loginData['group'] : $this->folder;
		$this->load->model('../core/CRUD_Model');

		$this->set_site_structure();

		//Default image sizes
		$this->imageSizes = array(
			'thumbs' => array(
				'width'     => 150,
				'height'    => 135
			),
			'big_thumbs' => array(
				'width'     => 201,
				'height'    => 201
			)
		);
	}

	public function ajax_quick_manage($id = FALSE) {
		$row = $this->{$this->model}->get_by_id($id);
		if ($row == FALSE) {
			$row = '';
		}

		$identifier = !empty($row) && !empty($row->{$this->identifierField}) ? $row->{$this->identifierField} : '';
		$data = array(
			'row' 			=> $row,
			'identifier' 	=> $identifier
		);

		//If you want to modify the existing post data
		$extra = $this->quick_manage_extra($id, $row);
		$data = array_merge($data, $extra);

		$html = $this->load->view($this->folder . '/' . $this->pageName . '/ajax_manage', $data, TRUE);

		//Setup dynamic fields
		$df = $this->get_all_dynamic_field_data($id);
		$html .= setup_dynamic_fields($df);

		$this->output->set_output($html);
	}

	public function quick_manage_extra($id, $row) {
		return array();
	}

	public function add() {
		if ($this->quickManage) {
			redir($this->pageName . '/#add');
		} else {
			$this->breadcrumbs = array(
				array(
					'title' => lang($this->pageName . '_heading'),
					'url' => url($this->pageName)
				),
				array(
					'title' => lang($this->pageName . '_add_heading'),
					'url' => redir($this->pageName . '/add', TRUE)
				)
			);
			$this->view = 'add';

			$this->load->view($this->folder . '/view_header');
			$this->load->view($this->folder . '/' . $this->pageName . '/view_manage', array(
				'heading' => lang($this->pageName . '_add_heading'),
				'images' => array(),
				'row' => '',
				'id' => '',
				'identifier' => ''
			));
			$this->load->view($this->folder . '/view_footer');
		}
	}

	public function edit($id) {
		if ($this->quickManage) {
			redir($this->pageName . '/#edit/' . $id);
		} else {
			$this->breadcrumbs = array(
				array(
					'title' => lang($this->pageName . '_heading'),
					'url' => url($this->pageName)
				),
				array(
					'title' => lang($this->pageName . '_edit_heading'),
					'url' => redir($this->pageName . '/edit/' . $id, TRUE)
				)
			);
			$this->view = 'edit';

			$row = $this->{$this->model}->get_by_id($id);
			$identifier = $row->{$this->identifierField};

			$this->load->view($this->folder . '/view_header');
			$this->load->view($this->folder . '/' . $this->pageName . '/view_manage', array(
				'heading' => lang($this->pageName . '_edit_heading'),
				'row' => $row,
				'id' => $id,
				'identifier' => $identifier
			));
			$this->load->view($this->folder . '/view_footer');
		}
	}

	public function set_singular_plural() {
		$this->plural = ! empty($this->plural) ? $this->plural : $this->page;

		//Attempt to create a singular from plural
		if (empty($this->singular)) {
			$this->singular = preg_replace('/s$|es$/', '', $this->plural);
		}
	}

	public function set_site_structure() {
		if (is_file(APPPATH . 'views/' . $this->folder . '/site_map.php')) {
			include_once(APPPATH . 'views/' . $this->folder . '/site_map.php');
		}
	}

	public function ajax_pager_fetch_batch_grid($batch = 1, $section = "") {
		$this->ajax_pager_fetch_batch($batch, $section, "grid");
	}

	public function ajax_pager_fetch_batch($batch = 1, $section = "", $template = "listing") {
		$this->page = $batch;
		
		try {
			$query = $this->{$this->model}->get_all($section);
		}
		catch(Exception $e) {
			echo $e->getMessage();

			if (ENVIRONMENT != 'production') {
				dd($this->db->last_query()) ;
			}
			else {
				exit();
			}
		}

		$amount = $this->{$this->model}->get_count();
		$view = '';
		//Can be used to inject anything extra that needs to happen after the get_all function is called
		$query = $this->pager_fetch_batch_extra($query, $batch, $section);

		$html = $this->load->view('cms/crud/ajax_' . $template . '_rows', array(
			'query' => $query,
			'batch' => $batch,
			'amount' => $amount
		), TRUE);

		$this->output->set_output($html);
	}

	//Placeholder function, if needed overide in the controller
	public function pager_fetch_batch_extra($query, $batch, $section) {
		return $query;
	}

	public function ajax_set_sorting($field, $dir, $extend = FALSE) {
		//Get sorting array for the current module
		if ($extend && ! empty($this->session->{$this->pageName . 'Sorting'})) {
			$sorting = $this->session->{$this->pageName . 'Sorting'};
		} else {
			$sorting = array();
		}

		//Set the sorting direction
		if ($dir == 'reset') {
			if (isset($sorting[$field])) {
				unset($sorting[$field]);
			}
		} else {
			$sorting[$field] = $dir;
		}

		//Set the sorting session
		$this->session->set_userdata($this->pageName . 'Sorting', $sorting);

		$message = $dir == 'reset' ? 'Ordering for field has been reset.' : $field . ' is now sorted ' . $dir;
		$this->output->set_output($message);
	}

	public function ajax_get_list_values() {
		$table = $this->input->post('table');
		$field = $this->input->post('field');
		$value = $this->input->post('value');
		$selected = $this->input->post('selected');
		$query = $this->{$this->model}->get_simple_list_data($table, $field, $value);

		$html = '';
		if ($query->num_rows() > 0) {
			$html = select_options($query, $selected);
		}

		ajax_return(array(
			'html' => $html
		));
	}

	public function ajax_apply_filters() {
		$section = $this->input->post('section') ?: '';
		$filters = ! empty($this->sections) && isset($this->filters[$section]) ? $this->filters[$section] : $this->filters;

		$filterData = array();
		foreach ($filters as $fname => $filter) {
			if ($filter['type'] == 'date_range' || $filter['type'] == 'range' || $filter['type'] == 'date_period') {
				if ($this->input->post($fname . '-from') != '' && $this->input->post($fname . '-to') != '') {
					$filterData[$fname] = $filter;
					$filterData[$fname]['value']['from'] = $this->input->post($fname . '-from');
					$filterData[$fname]['value']['to'] = $this->input->post($fname . '-to');
				}
			} else if ($this->input->post($fname) || $this->input->post($fname) === '0') {
				$filterData[$fname] = $filter;
				$filterData[$fname]['value'] = $this->input->post($fname);
			}
		}

		//Update filter
		set_ecms_filters($this->pageName, $filterData);

		ajax_return();
	}

	public function ajax_get_autocomplete() {
		$section = $this->input->post('section');
		$filters = ! empty($this->sections) && isset($this->filters[$section]) ? $this->filters[$section] : $this->filters;
		$name = $this->input->post('name');
		$value = $this->input->post('value');
		$html = '';
		$temp = array();
		if ( ! empty($filters[$name])) {
			$query = $this->{$this->model}->get_autocomplete_results($filters[$name]['field'], $value, $filters[$name]['select'], $filters[$name]['where']);
			foreach ($query->result_array() as $row) {
				foreach ($row as $key => $col) {
					if ($key == 'id') {
						continue;
					}

					if ( ! in_array($col, $temp)) {
						array_push($temp, $col);
					}
				}
			}

			foreach ($temp as $val) {
				if (stripos($val, $value) !== FALSE) {
					$html .= '<li class="autocomplete-results-li">' . $val . '</li>';
				}
			}
		}

		ajax_return(array(
			'html' => $html
		));
	}

	public function reset_filters() {
		$this->clear_filters();
		redir($this->pageName);
	}

	public function clear_filters() {
		unset_ecms_filters($this->pageName);
	}

	/**
	 * Ajax Fetch Assignment Data
	 *
	 * Gets the data and builds the html for the assignment grid
	 */
	public function ajax_fetch_assignment_data() {
		$group = $this->input->post('assignment_group');
		$parentID = $this->input->post('parent_id');
		$filters = $this->input->post('filters');

		$query = $this->{$this->model}->get_assignment_data($group, $parentID, $filters);

		$html = $this->load->view('cms/crud/ajax_assignment_grid', array(
			'query' => $query
		), TRUE);

		ajax_return(array(
			'html' => $html
		));
	}

	/**
	 * Ajax Save Assignment Data
	 *
	 * Associate the posted value to the parent table
	 */
	public function ajax_save_assignment_data() {
		$group = $this->input->post('assignment_group');
		$parentID = $this->input->post('parent_id');
		$childID = $this->input->post('child_id');
		$childValue = $this->input->post('child_value');

		$result = $this->{$this->model}->save_assignment_data($group, $parentID, $childID, $childValue);

		if ( ! $result) {
			$groupOptions = $this->assignmentGroup[$group];

			Anomalies::log('Failed to associate ' . $groupOptions['child'] . ' with ' . $groupOptions['parent'] . '.', $this->db->last_query());
		}

		ajax_return(array(
			'result' => ($result ? 1 : 0)
		));
	}

	/**
	 * Ajax is unique email
	 */
	public function ajax_is_unique_email() {
		$id = $this->input->post('id');
		$email = $this->input->post('email');

		//Get all login groups
		$loginGroups = $this->config->item('login_groups');

		//Build a query to check across all defined login groups
		$sql = '';
		$i = 0;
		foreach ($loginGroups as $g => $group) {
			$i++;
			$sql .= '
				SELECT id, "' . $g . '" AS login_group
				FROM ' . $group['table'] . '
				WHERE removed = 0
				AND email = "' . $this->db->escape_str($email) . '"
			';

			if ( ! empty($id)) {
				$sql .= ' AND id != "' . $id . '" ';
			}

			if ($i != count($loginGroups)) {
				$sql .= ' UNION ALL ';
			}
		}

		if ( ! empty($sql)) {
			$query = $this->db->query($sql);
			if ($query->num_rows() == 0) {
				$result = TRUE;
			} else {
				$result = FALSE;
			}
		} else {
			$result = FALSE;
		}
		ajax_return(array(
			'result' => ($result ? 1 : 0)
		));
	}

	/**
	 * Assignment Filter HTML
	 *
	 * Returns the html for the filter side menu
	 *
	 * @param string $group
	 *
	 * @return string
	 */
	public function assignment_filter_html($group) {
		$filters = ! empty($this->assignmentFilters[$group]) ? $this->assignmentFilters[$group] : '';

		$html = '';
		//Only show filters if there are any.
		if ( ! empty($filters)) {
			$html = $this->load->view('cms/crud/partial_assignment_filter', array(
				'filters' => $filters
			), TRUE);
		}

		return $html;
	}

	public function ajax_upload_file() {
		$field = $this->input->post('field');
		$uploadDir = 'resources/uploads/temp/' . date('ymd');
		dir_create($uploadDir);

		$options['uploadDir'] = $uploadDir . '/';
		$options['title'] = 'auto';

		//Set module
		if ( ! empty($this->fileUploader[$field]['module'])) {
			$module = $this->fileUploader[$field]['module'];
		} else {
			$module = $this->pageName;
		}

		//Add extra options
		if ( ! empty($this->fileUploader[$field]['options'])) {
			$options = array_merge($options, $this->fileUploader[$field]['options']);
		}

		$this->load->library('file_uploader', array(
			'name' => $field,
			'options' => $options
		));

		// call to upload the files
		$data = $this->file_uploader->upload();

		if ($data['isSuccess'] && isset($data['files'][0])) {
			$params = array(
				'file_name' => $data['files'][0]['name'],
				'file_path' => $uploadDir . '/',
				'original_filename' => safe_file_name($this->input->post('name')),
				'extension' => $this->input->post('extension'),
				'mime' => $this->input->post('type'),
				'file_size' => $this->input->post('size'),
				'file_format' => $this->input->post('format'),
				'module' => $module,
				'field' => $field,
				'login_token' => login_token()
			);

			$data['rowData'] = $this->{$this->model}->save_file($params);
		}

		ajax_return($data);
	}

	public function ajax_search_cities() {
		ini_set('memory_limit', '256M');

		$search = $this->input->post('search');
		$countryID = $this->input->post('country_id');
		$showRegions = $this->input->post('show_regions');

		if ($this->input->post('search_type')) {
			$searchType = $this->input->post('search_type');
		} else {
			$searchType = 'after';
		}

		$query = $this->{$this->model}->search_cities($search, $countryID, $searchType);

		$html = '';
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$html .= '
					<li data-city-id="' . $row->city_id . '" data-region-id="' . $row->region_id . '" data-city-name="' . $row->city . '">
						' . $row->city . ($showRegions ? ' - ' . $row->region : '') . '
					</li>';
			}
		} else {
			$html .= '<li class="field-city-no-rows">' . lang('field_city_no_cities') . '</li>';
		}

		ajax_return(array(
			'html' => $html
		));
	}

	/**
	 * User for mapping module with another modules
	 * @param type $parent_module_name
	 * @param type $parent_module_id
	 * @param type $modulesData
	 */
	public function process_mapping_modules($parent_module_name, $parent_module_id, $modulesData = array()) {
		$mapped_modules = array();
		if ( ! empty($modulesData)) {
			foreach ($modulesData as $module => $list) {
				if ( ! empty($list)) {
					foreach ($list as $r) {
						array_push($mapped_modules, array('module_name' => $module, 'module_id' => $r, $parent_module_name => $parent_module_id));
					}
				}
			}
		}
		$new = array();
		$existing = array();
		$removed = array();
		$mlists = array();
		if ( ! empty($mapped_modules)) {
			foreach ($mapped_modules as $list) {
				$module_name = $list['module_name'];
				$module_id = $list['module_id'];
				//Get existing files
				$query = $this->{$this->model}->get_existing_mapped_modules($module_name, $module_id, $parent_module_name, $parent_module_id);

				//Sort list into new, removed and exiting
				$mlists = array_flip($list);

				if ($query->num_rows() > 0) {
					foreach ($query->result() as $row) {
						$existing[] = $row->id;
					}
				} else {
					$new[] = $list;
				}
			}
		}

		//Removed mapped module other than existings
		$query = $this->{$this->model}->delete_mapped_modules($existing, $parent_module_name, $parent_module_id);

		//New mapped modules - Will be stored
		if ( ! empty($new)) {
			$query = $this->{$this->model}->insert_mapped_modules($new);
		}
	}

	public function action_validation($action) {
		if ($this->input->post('action') != $action) {
			$message = '
				Incorrect action posted to create function.
				Expected: "' . $action . '"
				Passed: "' . $this->input->post('action') . '"
			';
			Anomalies::log(trim($message));
			flash_notification(lang('system_error_description'), 'error');

			return FALSE;
		} else {
			return TRUE;
		}
	}

	public function setup_validation($page = 'add', $group = 'main') {
		$this->load->library('form_validation');

		//Set validation for main table fields
		if ( ! empty($this->formFields[$group])) {
			foreach ($this->formFields[$group] as $field => $rules) {
				//Check for custom label
				$label = ! empty($this->formLabels[$field]) ? $this->formLabels[$field] : 'label_' . $field;

				if (is_array($rules)) {
					if (!empty($rules['reliesOn'])) {
						$reliantField = explode('|', $rules['reliesOn']);
						if (!empty($reliantField[0]) && $this->input->post($reliantField[0])) {
							if (empty($reliantField[1])) {
								//Set rule if reliant field has a value
								$this->form_validation->set_rules($field, lang($label), $rules['validation']);
							}
							elseif ($this->input->post($reliantField[0]) == $reliantField[1]) {
								//Only set rule if reliant field matches a certain value
								$this->form_validation->set_rules($field, lang($label), $rules['validation']);
							}
						}
					}
					elseif (!empty($rules['page'])) {

					}
				}
				else {
					//Set rule
					$this->form_validation->set_rules($field, lang($label), $rules);
				}

			}
		}

		//Set validation for multi selects
		if ( ! empty($this->formFields['multi_selects'])) {
			foreach ($this->formFields['multi_selects'] as $field => $data) {
				//Check for custom label
				$label = ! empty($this->formLabels[$field]) ? $this->formLabels[$field] : 'label_' . $field;

				//Set rule
				$this->form_validation->set_rules($field . '[]', lang($label), $data['validation']);
			}
		}
	}

	public function build_params($extra = array(), $group = 'main') {
		$params = array();

		//Check fields for main table
		if ( ! empty($this->formFields[$group])) {
			foreach ($this->formFields[$group] as $field => $rules) {
				$xss = ( ! empty($this->noXSS) && in_array($field, $this->noXSS)) ? FALSE : TRUE;
				$params[$field] = $this->input->post($field, $xss);
			}
		}

		//Set extra data
		if ( ! empty($extra)) {
			foreach ($extra as $field => $data) {
				$params[$field] = $data;
			}
		}

		return $params;
	}

	public function process_dynamic_fields($parentID) {
		$dfData = array();

		if ( ! empty($this->formFields['dynamic_fields'])) {
			foreach ($this->formFields['dynamic_fields'] as $field => $data) {
				$dfData[$field] = $this->{$this->model}->save_dynamic_fields($field, $data['table'], $data['parent_field'], $parentID);

				//Process uploaders
				foreach ($dfData[$field]['uploaders'] as $rowID => $dzUploader) {
					foreach ($dzUploader as $uploader) {
						$uploaderPostData = explode('|', $uploader);
						$uploaderFieldName = $uploaderPostData[0];
						$dfFieldName = $uploaderPostData[1];

						//Get uploaderData
						if (!empty($this->uploaders[$uploaderFieldName])) {
							$uploaderData = $this->uploaders[$uploaderFieldName];

							if (in_array($uploaderData['type'], ['multi_image', 'multi_file'])) {
								$uploaderData['dbField'] = $uploaderFieldName;
								$this->process_multi_uploads($rowID, $dfFieldName, $uploaderData);
							}
							elseif (in_array($uploaderData['type'], ['single_image', 'single_file'])) {
								$uploaderData['dbField'] = $uploaderFieldName;
								$this->process_single_uploads($rowID, $dfFieldName, $uploaderData);
							}
						}
					}
				}
			}
		}
		
		return $dfData;
	}

	public function ajax_get_df_upload_data() {
		if ($this->input->post('action') != 'get_df_upload_data') {
			ajax_error('Invalid Action Performed');
			return;
		}

		$fieldName 	= $this->input->post('originalFieldName');
		$rowID		= $this->input->post('rowID');

		$uploaderData = method_exists($this, 'get_uploader_data') ? $this->{'get_uploader_data'}($fieldName, $rowID) : array();

		ajax_return([
			'data' => $uploaderData
		]);
	}

	public function process_multi_selects($parentID) {

		if ( ! empty($this->formFields['multi_selects'])) {
			foreach ($this->formFields['multi_selects'] as $field => $data) {
				$postedData = $this->input->post($field);

				//Clear previously selected values from the database
				$this->{$this->model}->hard_delete(array($data['main_field'] => $parentID), $data['pivot_table']);

				//Save values if there are any selected
				if (is_array($postedData)) {
					$bulkData = array();
					foreach ($postedData as $value) {
						$bulkData[] = array(
							$data['main_field'] => $parentID,
							$data['link_field'] => $value
						);
					}

					//Bulk insert data if there are any selected values
					! empty($bulkData) && $this->db->insert_batch($data['pivot_table'], $bulkData);
				}
			}
		}
	}

    public function process_multi_images($parentID) {

        if (!empty($this->formFields['multi_images'])) {
            foreach ($this->formFields['multi_images'] as $field => $data) {
                $postedData = $this->input->post($field);
                $insertData = array();
                $updateData = array();
                $deleteData = array();

                foreach ($postedData as $imageID => $data) {
                    if (preg_match("/new/i", $imageID)) {
                        //If new then move image file from temp location to permanent location
                    }
                    elseif (!empty($data['removed'])) {
                        //Set image as removed
                    }
                    else {
                        //Update field data for existing images
                    }
                }

                //Save values if there are any selected
                if (is_array($postedData)) {
                    $bulkData = array();
                    foreach ($postedData as $value) {
                        $bulkData[] = array(
                            $data['main_field'] => $parentID,
                            $data['link_field'] => $value
                        );
                    }

                    //Bulk insert data if there are any selected values
                    !empty($bulkData) && $this->db->insert_batch($data['pivot_table'], $bulkData);
                }
            }
        }
    }

	public function get_all_dynamic_field_data($rowID = 0) {

		$df = array();
		if ( ! empty($this->formFields['dynamic_fields']) && $this->input->post('df')) {
			$df = $this->input->post('df');
		} elseif ( ! empty($this->formFields['dynamic_fields']) && ! empty($rowID)) {
			foreach ($this->formFields['dynamic_fields'] as $field => $data) {
				$formatters = !empty($data['formatters']) ? $data['formatters'] : [];
				$df = $this->{$this->model}->get_dynamic_field_data($field, $data['table'], $data['parent_field'], $rowID, $data['fields'], $df, $formatters);
			}
		}

		return $df;
	}

	public function ajax_upload_image() {

		$field = $this->input->post('field');
		$uploadDir = 'resources/uploads/temp/' . date('ymd');
		dir_create($uploadDir);

		$params = array();
		$params['upload_path'] = $uploadDir . '/';

		//Set module
		if ( ! empty($this->fileUploader[$field]['module'])) {
			$module = $this->fileUploader[$field]['module'];
		} else {
			$module = $this->pageName;
		}

		//Add extra options
		if ( ! empty($this->fileUploader[$field]['options'])) {
			$params = array_merge($params, $this->fileUploader[$field]['options']);
		}

		//Initialize image size array
		if (empty($params['image_sizes'])) {
			$params['image_sizes'] = array();
		}

		//Get default sizes
		if ( ! empty($this->imageSizes)) {
			$params['image_sizes'] = array_merge($params['image_sizes'], $this->imageSizes);
		}

		$this->load->library('image_uploader', $params);

		$data = $this->image_uploader->upload();

		if ($data['success'] && ! empty($data['image_data'])) {
			foreach ($data['image_data'] as &$imageData) {
				if (empty($imageData['error'])) {
					$imageData['basename'] = safe_file_name($imageData['basename']);
					$params = array(
						'file_name' => $imageData['hashed_name'],
						'file_path' => $uploadDir . '/',
						'original_filename' => $imageData['basename'],
						'extension' => $imageData['file_ext'],
						'mime' => $imageData['type'],
						'file_size' => $imageData['size'],
						'file_format' => 'image',
						'module' => $module,
						'field' => $field,
						'login_token' => login_token()
					);

					$row = $this->{$this->model}->save_file($params);
					$imageData['row_id'] = $row['id'];
					$imageData['row_hash'] = $row['hash'];
				}
			}
		}

		ajax_return($data);
	}

	public function ajax_edit_image() {
		$imageID = $this->input->post('image_id');

		$row = $this->{$this->model}->get_file_data($imageID);
	}

	public function export() {
		require_once('application/third_party/xlsxwriter/xlsxwriter.class.php');

		try {
			$query = $this->{$this->model}->get_all_export();
		}
		catch(Exception $e) {
			echo $e->getMessage();

			if (ENVIRONMENT != 'production') {
				dd($this->db->last_query()) ;
			}
			else {
				exit();
			}
		}

		//Set headings
		$headings = array();
		foreach ($this->export['fields'] as $field => $options) {
			$headings[] = $options['label'];
		}

		//Set body
		$data = array();
		if ($query->num_rows() > 0) {
			$r = 0;
			foreach ($query->result() as $row) {
				$c = 0;
				foreach ($this->export['fields'] as $field => $options) {
					if (!empty($options['function']) && is_callable($options['function'])) {
						//Runs the function specified to format/change the value before showing it
						$data[$r][$c] = call_user_func($options['function'], $row->{$field}, $row);
					}
					else {
						$data[$r][$c] = $row->{$field};
					}
					$c++;
				}
				$r++;
			}
		}

		$filename = $this->export['filename'];

		//Make sure export directory exists
		$dir = 'resources/exports/' . $this->pageName . '/';
		dir_create($dir);

		$writer = new XLSXWriter();
		$writer->writeSheetRow('Sheet1', $headings, array('fill' => "#333333", 'color' => "#ffffff"));
		$writer->writeSheet($data);
		$writer->writeToFile($dir . $filename . '.xlsx');

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-length: " . filesize($dir . $filename . '.xlsx'));
		header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
		header('Cache-Control: max-age=0');
		echo file_get_contents($dir . $filename . '.xlsx');

		unlink($dir . $filename . '.xlsx');

		exit();
	}

	/**
	 * Is Unique
	 *
	 * Callback function to check whether or not the entry already exists in the database
	 *
	 * @param string $email
	 *
	 * @return bool
	 */
	public function is_unique($value) {

		$id = $this->input->post('id');
		$field = $this->identifierField;

		$this->form_validation->set_message('is_unique', lang('validation_entry_exists'));
		$result = $this->{$this->model}->is_unique($value, $id, $field);

		return $result;
	}

	public function process_dynamic_content($parentID) {
		if ($this->input->post('dc_fields')) {
			$dcFields = $this->input->post('dc_fields');
			$dc = $this->input->post('dc');

			$insertData = array();
			$updateData = array();
			$c = 0;
			foreach ($dcFields as $f) {
				$id = $this->input->post('dc_id_' . $f);
				$position = $this->input->post('dc_position_' . $f);
				$template = $this->input->post('dc_template_' . $f);

				$fields = $this->dynamicContent['templates'][$template];
				$parentField = $this->dynamicContent['parent_field'];

				if ($id) {
					//Prepare data to update
					foreach ($fields as $field) {
						$updateData[$id][$field] = $dc[$field][$f];
					}

					$updateData[$id]['dc_position'] = $position;
					$updateData[$id]['dc_template'] = $template;
					$updateData[$id][$parentField] = $parentID;
				} else {
					//Prepare data to insert
					foreach ($fields as $field) {
						$insertData[$c][$field] = $dc[$field][$f];
					}

					$insertData[$c]['dc_position'] = $position;
					$insertData[$c]['dc_template'] = $template;
					$insertData[$c][$parentField] = $parentID;
					$c++;
				}
			}

			//Update existing data
			if ( ! empty($updateData)) {
                bulk_update($this->dynamicContent['table'], $updateData);
			}

			//Insert new data
			if ( ! empty($insertData)) {
				$this->{$this->model}->multi_insert($insertData, $this->dynamicContent['table']);
			}
		}
	}

	public function get_dynamic_content($parentID = 0) {

		$data = array();
		$table = $this->dynamicContent['table'];
		$parentField = $this->dynamicContent['parent_field'];

		if ($parentID) {
			$query = $this->{$this->model}->get_dynamic_content($table, $parentField, $parentID);

			if ($query->num_rows() > 0) {
				foreach ($query->result_array() as $row) {
					$data[$row['dc_position']] = $row;

					//unset system fields
					unset($data[$row['dc_position']]['enabled']);
					unset($data[$row['dc_position']]['removed']);
					unset($data[$row['dc_position']]['created_at']);
					unset($data[$row['dc_position']]['updated_at']);
					unset($data[$row['dc_position']]['deleted_at']);
				}
			}
		}

		return $data;
	}

	public function ajax_change_position() {
		$id = $this->input->post('id');
		$position = $this->input->post('position');
		$num = $this->input->post('num');

		if ( ! empty($this->positioningFilters)) {
			$filters = $this->positioningFilters;
		} else {
			$filters = array();
		}

		$result = $this->{$this->model}->change_position($id, $position, $num, $filters);

		if ($result) {
			ajax_return();
		}
	}

	/**
	 * Unique Login Email
	 *
	 * Callback function to check whether or not the email already exists in the database
	 *
	 * @param string $email
	 *
	 * @return bool
	 */
	public function unique_login_email($email) {

		$id = $this->uri->segment(4, '');

		$loginID = $this->{$this->model}->get_login_id($id);

		$this->form_validation->set_message('unique_login_email', lang('email_exists'));
		$result = $this->{$this->model}->is_unique_login_email($email, $loginID);

		return $result;
	}

	public function remove($id) {
		$row = $this->{$this->model}->get_by_id($id);

		//Extra code to execute before removing an entry
		if (!$this->remove_extra_before($row)) {
			redir($this->pageName);
			return FALSE;
		}

		if ($row) {
			$messageParams = array('name' => $row->{$this->identifierField});
			$result = $this->{$this->model}->remove($id);
			if ($result) {
				Logger::log('Removed ' . $this->singular, array('id' => $id));
				flash_notification(langs($this->pageName . '_remove_success_description', $messageParams), 'success');

				$this->remove_extra_success($row);
			} else {
				Anomalies::log('Failed to remove ' . $this->singular, $this->db->last_query());
				flash_notification(langs($this->pageName . '_remove_failed_description', $messageParams), 'error');
			}
		} else {
			flash_notification(lang('access_denied_description'), 'warning');
		}

		redir($this->pageName);
	}

	public function remove_extra_before($row) {
		return TRUE;
	}

	public function remove_extra_success($row) {
		return TRUE;
	}

	public function enable($id) {
		$row = $this->{$this->model}->get_by_id($id);

		//Extra code to execute before enabling an entry
		if ( ! $this->enable_extra_before($row)) {
			return FALSE;
		}

		if ($row) {
			$result = $this->{$this->model}->enable($id);

			$messageParams = array('name' => $row->{$this->identifierField});
			if ($result) {
				Logger::log('Enabled ' . $this->singular, array('id' => $id));
				if ( ! is_ajax()) {
					flash_notification(langs($this->pageName . '_enable_success_description', $messageParams), 'success');
				}
				$this->enable_extra_success($row);
			} else {
				Anomalies::log('Failed to enable ' . $this->singular, $this->db->last_query());
				if ( ! is_ajax()) {
					flash_notification(langs($this->pageName . '_enable_failed_description', $messageParams), 'error');
				} else {
					http_response_code(422);
					echo json_encode(
						array('success' => 0,
							'header' => lang($this->pageName . '_enable_failed_heading'),
							'body' => str_replace('{name}', $row->title, lang($this->pageName . '_enable_failed_description'))
						)
					);
				}
			}
		} else {
			if ( ! is_ajax()) {
				flash_notification(lang('access_denied_description'), 'warning');
			} else {
				http_response_code(422);
				echo json_encode(
					array('success' => 0,
						'error' => array(
							'header' => lang('access_denied_heading'),
							'body' => lang('access_denied_description')
						)
					)
				);
			}
		}

		if (is_ajax()) {
			http_response_code(200);
			echo json_encode(array('success' => 1));
		} else {
			redir($this->pageName);
		}
	}

	public function enable_extra_before($row) {
		return TRUE;
	}

	public function enable_extra_success($row) {
		return TRUE;
	}

	public function disable($id) {
		$row = $this->{$this->model}->get_by_id($id);

		//Extra code to execute before disabling an entry
		if ( ! $this->disable_extra_before($row)) {
			return FALSE;
		}

		if ($row) {

			$messageParams = array('name' => $row->{$this->identifierField});

			$result = $this->{$this->model}->disable($id);
			if ($result) {
				Logger::log('Disabled ' . $this->singular, array('id' => $id));
				if ( ! is_ajax()) {
					flash_notification(langs($this->pageName . '_disable_success_description', $messageParams), 'success');
				}
				$this->disable_extra_success($row);
			} else {
				Anomalies::log('Failed to enable ' . $this->singular, $this->db->last_query());
				if ( ! is_ajax()) {
					flash_notification(langs($this->pageName . '_disable_failed_description', $messageParams), 'error');
				} else {
					http_response_code(422);
					echo json_encode(
						array('success' => 0,
							'header' => lang($this->pageName . '_disable_failed_heading'),
							'body' => str_replace('{name}', $row->title, lang($this->pageName . '_disable_failed_description'))
						)
					);
				}
			}
		} else {
			if ( ! is_ajax()) {
				flash_notification(lang('access_denied_description'), 'warning');
			} else {
				http_response_code(422);
				echo json_encode(
					array('success' => 0,
						'error' => array(
							'header' => lang('access_denied_heading'),
							'body' => lang('access_denied_description')
						)
					)
				);
			}
		}

		if (is_ajax()) {
			http_response_code(200);
			echo json_encode(array('success' => 1));
		} else {
			redir($this->pageName);
		}
	}

	public function disable_extra_before($row) {
		return TRUE;
	}

	public function disable_extra_success($row) {
		return TRUE;
	}

	public function create() {
        //ECMS no longer supports a direct form post
		if (!is_ajax()) {
            flash_notification(lang('not_ajax_error'), 'error');
            redir($this->pageName);
            return;
        }

        $this->setup_validation('add');
        $identifier = $this->input->post($this->identifierField);

        if ($this->form_validation->run()) {
            $this->load->helper('string');

            $messageParams = array('name' => $identifier);

            //If you want to add extra data to the post
            $extra = $this->create_extra_params();

            $params = $this->build_params($extra);
            $params['created_at'] = date('Y-m-d H:i:s');

            if($this->sluggify) {
                $params['slug'] = $this->sluggify($identifier);
            }

            //If you want to modify the existing post data
            $params = $this->create_modify_params($params);

            $this->db->trans_start();
            $id = $this->{$this->model}->create($params);
            $this->process_dynamic_fields($id);
            $this->process_multi_selects($id);
            $this->process_uploads($id);

            //Run extra create functionality
            $this->create_success_extra($id);

            $this->db->trans_complete();

            if ($this->db->trans_status() !== FALSE) {
                ajax_return(array(
                    'success' => TRUE,
                    'id' => $id,
                    'flasherbody' => langs($this->pageName . '_create_success_description', $messageParams),
                    'extra' => $this->create_return_extra($id)
                ));
            } else {
                Anomalies::log('Failed to edit ' . $this->singular, $this->db->last_query());
                ajax_return(array(
                    'success' => FALSE,
                    'error' => langs($this->pageName . '_add_failed_description', $messageParams)
                ));
            }
        }
        else{
            ajax_return(array(
                'success' => FALSE,
                'error' => langs('validation_errors_ajax', ['errors' => validation_errors()]),
                'fields' => $this->form_validation->get_errors()
            ));
        }

        return;
	}

	public function update($id) {
        //ECMS no longer supports a direct form post
		if (!is_ajax()) {
            flash_notification(lang('not_ajax_error'), 'error');
            redir($this->pageName);
            return;
        }

		$this->setup_validation('edit');
		$identifier = $this->input->post($this->identifierField);

			if ($this->form_validation->run()) {
				$this->load->helper('string');

				$messageParams = array('name' => $identifier);

				//If you want to add extra data to the post
				$extra = $this->update_extra_params($id);

			$params = $this->build_params($extra);

			//Check if we included the slug as an editable field
			if($this->sluggify && !empty($params['slug'])) {
                $params['slug'] = $this->sluggify($params['slug'], null, $id);
            }

				//If you want to modify the existing post data
				$params = $this->update_modify_params($params);

                $this->db->trans_start();
                $this->{$this->model}->update($params, $id);
				$this->process_dynamic_fields($id);
                $this->process_multi_selects($id);
                $this->process_uploads($id);

				//Run extra update functionality
				$this->update_success_extra($id);

				$this->db->trans_complete();

				if ($this->db->trans_status() !== FALSE) {
					ajax_return(array(
						'success' => TRUE,
						'flasherbody' => langs($this->pageName . '_update_success_description', $messageParams),
						'extra' => $this->update_return_extra($id)
					));
				} else {
					Anomalies::log('Failed to edit ' . $this->singular, $this->db->last_query());
					ajax_return(array(
						'success' => FALSE,
						'error' => langs($this->pageName . '_update_failed_description', $messageParams)
					));
				}
			} else {
				ajax_return(array(
					'success' => FALSE,
					'error' => langs('validation_errors_ajax', ['errors' => validation_errors()]),
					'fields' => $this->form_validation->get_errors()
				));
			}
			return;
	}

	public function system_url_history($url = '') {
        if (!isset($this->session->userdata['system_url_history'])) {
            $this->session->set_userdata('system_url_history', array());
        }
    
        $system_url_history = $this->session->userdata('system_url_history');
    
        if ($url !== '') {
            $topLevelUrl = end($system_url_history);
            $beforeTopLevelUrl = prev($system_url_history);

            if ($topLevelUrl !== $url) {
                if($beforeTopLevelUrl == $url){
                    $latestKey = array_keys($system_url_history, $beforeTopLevelUrl);
                    $latestKey = end($latestKey);
        
                    if ($latestKey !== false) {
                        $updatedsystem_url_history = array_slice($system_url_history, 0, $latestKey + 1);
                        $this->session->set_userdata('system_url_history', $updatedsystem_url_history);
                        $previousUrl = $latestKey > 0 ? $updatedsystem_url_history[$latestKey - 1] : null;
                        $this->session->set_userdata('previous_url', $previousUrl);
                    }
                } else {
                    $system_url_history[] = $url;
                    $this->session->set_userdata('system_url_history', $system_url_history);
                    $this->session->set_userdata('previous_url', $topLevelUrl ?: null);
                }
            } else {
                $latestKey = array_keys($system_url_history, $url);
                $latestKey = end($latestKey);
    
                if ($latestKey !== false) {
                    $updatedsystem_url_history = array_slice($system_url_history, 0, $latestKey + 1);
                    $this->session->set_userdata('system_url_history', $updatedsystem_url_history);
                    $previousUrl = $latestKey > 0 ? $updatedsystem_url_history[$latestKey - 1] : null;
                    $this->session->set_userdata('previous_url', $previousUrl);
                }
            }
        }
    }

	public function submodule($submodulePage, $id, $path='') {
		//Check if the items exists
		$row = $this->{$this->model}->get_by_id($id);
		if (!$row) {
			flash_notification(lang('access_denied_description'), 'warning');
			redir($this->pageName);
			return;
		}

		//Check if child listing options has been set
		if (empty($this->submodules[$submodulePage])) {
			flash_notification('Child page options has not been set up!', 'warning');
			redir($this->pageName);
			return;
		}

		$submodule = $this->submodules[$submodulePage];

		if (!empty($submodule['identifier'])) {
			$identifier = preg_replace_callback('/\{([a-z_]+)\}/', (function ($matches) use ($row) {
				$tag = $matches[1];
				if (isset($row->{$tag})) {
					return $row->{$tag};
				}
			}), $submodule['identifier']);
		}
		elseif (!empty($row->{$this->identifierField})) {
			$identifier = $row->{$this->identifierField};
		}
		else {
			$identifier = '-';
		}

		$options = array(
			'pageName'		=> !empty($submodule['pageName']) ? $submodule['pageName'] : $this->pageName,
			'identifier'	=> $identifier,
			'table'			=> !empty($submodule['table']) ? $submodule['table'] : '',
			'field'			=> !empty($submodule['field']) ? $submodule['field'] : '',
			'id'			=> $id,
			'hideFilters'	=> !empty($submodule['hideFilters']) ? (array) $submodule['hideFilters'] : [],
			'hideFields'	=> !empty($submodule['hideFields']) ? (array) $submodule['hideFields'] : []
		);

		$options['manageField'] = !empty($submodule['manageField']) ? $submodule['manageField'] : $options['field'];

		//Assign options to submodule sessions
		$session = $this->session->submodules;

		$session[$submodulePage] = (object) $options;
		$this->session->submodules = $session;

		//Clear filter for Submodule
		unset_ecms_filters($submodulePage);

		redir($submodulePage.$path);
	}

	public function submodule_add($submodulePage, $id) {
		$this->submodule($submodulePage, $id, '/add');
	}

	public function setup_breadcrumbs($extraBefore=[], $extraAfter=[], $overideMain=[]) {
		$this->breadcrumbs = array();

		//Add extra breadcrumbs before
		if (!empty($extraBefore)) {
			$this->breadcrumbs[] = $extraBefore;
		}

		//Possible Submodules
		$submodules = $this->session->submodules;
		if (!empty($submodules[$this->pageName])) {
			$submodule = $submodules[$this->pageName];

			//Item listing
			$this->breadcrumbs[] = array(
				'title' => lang($submodule->pageName . '_heading'),
				'url'   => url($submodule->pageName)
			);

			//Item edit
			$this->breadcrumbs[] = array(
				'title' => $submodule->identifier,
				'url'   => $submodule->id ? url($submodule->pageName.'/edit/'.$submodule->id) : url($submodule->pageName)
			);
		}

		//Main Listing
		if (!empty($overideMain)) {
			$this->breadcrumbs[] = $overideMain;
		}
		else {
			$this->breadcrumbs[] = array(
				'title' => lang($this->pageName . '_heading'),
				'url'   => url($this->pageName)
			);
		}

		//Add extra breadcrumbs after
		if (!empty($extraAfter)) {
			$this->breadcrumbs[] = $extraAfter;
		}
	}

	public function reset() {
		//Assign options to submodule sessions
		$submodules = $this->session->submodules;

		if (!empty($submodules[$this->pageName])) {
			unset($submodules[$this->pageName]);
		}

		$this->session->submodules = $submodules;
		redir($this->pageName);
	}

	public function ajax_get_filters() {
		$html = $this->load->view('cms/crud/view_list_filters', [], true);

		ajax_return([
			'html' => $html
		]);
	}

	//Initialise extra create functions
	public function create_extra_params() {
		return [];
	}
	public function create_modify_params($params) {
		return $params;
	}
	public function create_success_extra($id) {
		return;
	}
	public function create_return_extra($id) {
		return [];
	}

	//Initialise extra update functions
	public function update_extra_params($id) {
		return [];
	}
	public function update_modify_params($params) {
		return $params;
	}
	public function update_success_extra($id) {
		return;
	}
	public function update_return_extra($id) {
		return [];
	}

	
}