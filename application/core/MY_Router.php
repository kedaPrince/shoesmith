<?php

class MY_Router extends CI_Router {
	/**
	 * Override Set default controller
	 *
	 * @return  void
	 * @author : amit
	 *
	 */
	protected function _set_default_controller() {
		if (empty($this->default_controller)) {
			show_error('Unable to determine what should be displayed. A default route has not been specified in the routing file.');
		}

		// Is the method being specified?
		$x = explode('/', $this->default_controller);
		$dir = APPPATH . 'controllers'; // set the controllers directrory path
		$dir_arr = array();
		foreach ($x as $key => $val) {
			if ( ! is_dir($dir . '/' . $val)) {
				// find out class i.e. controller
				if (file_exists($dir . '/' . ucfirst($val) . '.php')) {
					$class = $val;
					if (array_key_exists(($key + 1), $x)) {
						$method = $x[$key + 1]; // find out method i.e. action
					} else {
						$method = 'index'; // default method i.e. action
					}
				} else {
					// show error message if the specified controller not found
					show_error('Not found specified default controller : ' . $this->default_controller);
				}
				break;
			}
			$dir_arr[] = $val;
			$dir = $dir . '/' . $val;
		}
		//set directory
		$this->set_directory(implode('/', $dir_arr));

		$this->set_class($class);
		$this->set_method($method);

		// Assign routed segments, index starting from 1
		$this->uri->rsegments = array(
			1 => $class,
			2 => $method
		);

		log_message('debug', 'No URI present. Default controller set.');
	}
}

?>
