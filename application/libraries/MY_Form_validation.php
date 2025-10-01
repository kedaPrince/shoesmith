<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Form Validation Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Validation
 * @author		EllisLab Dev Team
 * @link		https://codeigniter.com/user_guide/libraries/form_validation.html
 */
class MY_Form_validation extends CI_Form_validation
{
	/**
	 * Required
	 *
	 * @param	string
	 * @return	bool
	 */
	public function valid_phone($str)
	{
		//If it does not contain the characters:
		//Numbers: 0-9
		//Symbols: -+)(
		//Spaces
		return preg_match('/[^\d\-\(\)\+ ]+/', $str) ? false : true;
	}

	/**
	 * Required
	 *
	 * @param	string
	 * @return	bool
	 */
	public function valid_date($str)
	{
		//If it does not contain the characters:
		//Numbers: 0-9
		//Symbols: -+)(
		//Spaces
		return preg_match('/\d{4}\-\d{2}\-\d{2}/', $str) ? true : false;
	}

	public function format_date($str)
	{
		return date('Y-m-d', strtotime($str));
	}

	public function present_future_date($str)
	{
		$today = strtotime(date('j M Y'));
		$posted_date = strtotime(date('j M Y', strtotime($str)));

		return $posted_date < $today ? false : true;
	}

    /**
     * Alpha with underscores and dashes w/ spaces
     *
     * @param	string
     * @return	bool
     */
    public function alpha_dash_spaces($str)
    {
        return (bool) preg_match('/^[a-z_\-\s]+$/i', $str);
    }


    /**
     * Prep Clean Method
     *
     * @param	string
     * @return	string
     */
    public function prep_clean_method($str = '')
	{
		if ($str === '') {
			return '';
		}

		$pattern = '/^[a-zA-ZÀ-ÿ_\-\s]+$/u';

		if (!preg_match($pattern, $str)) {
			return false;
		}

		if (strlen($str) >= 30) {
			return false;
		}

		return ucwords(strtolower($str));
	}

	public function get_errors() {
		return $this->_error_array;
	}

	public function valid_url($str)
	{
		if (empty($str)) {
			return true;
		}

		return (bool)preg_match('/^https?:\/\/.+\..+/', $str);
	}
}
