<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * My Form Validation Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Validation
 * @author  	Benos (benomas@gmail.com) 2014
 */
class MY_Form_validation extends CI_Form_validation
{
	var $CI;
	public function __construct()
	{
		parent::__construct();
		$this->CI =& get_instance();
	}

	/**
		 * Set Rules
		 *
		 * This function takes an array of field names and validation
		 * rules as input, validates the info, and stores it
		 *
		 * @access	public
		 * @param	mixed
		 * @param	string
		 * @return	void
		 */
		public function set_rules($field, $label = '', $rules = '', $errors = array())
		{
			// No reason to set rules if we have no POST data
			//Solo cuando es GET se Ignora
			if ($_SERVER['REQUEST_METHOD']==='GET' && count($_POST)===0)
			{
				return $this;
			}


			// If an array was passed via the first parameter instead of indidual string
			// values we cycle through it and recursively call this function.
			if (is_array($field))
			{
				foreach ($field as $row)
				{
					// Houston, we have a problem...
					if ( ! isset($row['field']) OR ! isset($row['rules']))
					{
						continue;
					}

					// If the field label wasn't passed we use the field name
					$label = ( ! isset($row['label'])) ? $row['field'] : $row['label'];

					// Here we go!
					$this->set_rules($row['field'], $label, $row['rules']);
				}
				return $this;
			}

			// No fields? Nothing to do...
			if ( ! is_string($field) OR  ! is_string($rules) OR $field == '')
			{
				return $this;
			}

			// If the field label wasn't passed we use the field name
			$label = ($label == '') ? $field : $label;

			// Is the field name an array?  We test for the existence of a bracket "[" in
			// the field name to determine this.  If it is an array, we break it apart
			// into its components so that we can fetch the corresponding POST data later
			if (strpos($field, '[') !== FALSE AND preg_match_all('/\[(.*?)\]/', $field, $matches))
			{
				// Note: Due to a bug in current() that affects some versions
				// of PHP we can not pass function call directly into it
				$x = explode('[', $field);
				$indexes[] = current($x);

				for ($i = 0; $i < count($matches['0']); $i++)
				{
					if ($matches['1'][$i] != '')
					{
						$indexes[] = $matches['1'][$i];
					}
				}

				$is_array = TRUE;
			}
			else
			{
				$indexes	= array();
				$is_array	= FALSE;
			}

			// Build our master array
			$this->_field_data[$field] = array(
				'field'				=> $field,
				'label'				=> $label,
				'rules'				=> $rules,
				'is_array'			=> $is_array,
				'keys'				=> $indexes,
				'postdata'			=> NULL,
				'error'				=> ''
			);

			return $this;
		}

		/**
		 * Run the Validator
		 *
		 * This function does all the work.
		 *
		 * @access	public
		 * @return	bool
		 */
		public function run($group = '')
		{
			// Do we even have any data to process?  Mm?
			//Solo cuando es GET se Ignora
			if ($_SERVER['REQUEST_METHOD']==='GET' && count($_POST)===0)
			{
				return $this;
			}

			// Does the _field_data array containing the validation rules exist?
			// If not, we look to see if they were assigned via a config file
			if (count($this->_field_data) == 0)
			{
				// No validation rules?  We're done...
				if (count($this->_config_rules) == 0)
				{
					return FALSE;
				}

				// Is there a validation rule for the particular URI being accessed?
				$uri = ($group == '') ? trim($this->CI->uri->ruri_string(), '/') : $group;

				if ($uri != '' AND isset($this->_config_rules[$uri]))
				{
					$this->set_rules($this->_config_rules[$uri]);
				}
				else
				{
					$this->set_rules($this->_config_rules);
				}

				// We're we able to set the rules correctly?
				if (count($this->_field_data) == 0)
				{
					log_message('debug', "Unable to find validation rules");
					return FALSE;
				}
			}

			// Load the language file containing error messages
			$this->CI->lang->load('form_validation');

			// Cycle through the rules for each field, match the
			// corresponding $_POST item and test for errors
			foreach ($this->_field_data as $field => $row)
			{
				// Fetch the data from the corresponding $_POST array and cache it in the _field_data array.
				// Depending on whether the field name is an array or a string will determine where we get it from.

				if ($row['is_array'] == TRUE)
				{
					$this->_field_data[$field]['postdata'] = $this->_reduce_array($_POST, $row['keys']);
				}
				else
				{
					if (isset($_POST[$field]) AND $_POST[$field] != "")
					{
						$this->_field_data[$field]['postdata'] = $_POST[$field];
					}
				}

				$this->_execute($row, explode('|', $row['rules']), $this->_field_data[$field]['postdata']);
			}

			// Did we end up with any errors?
			$total_errors = count($this->_error_array);

			if ($total_errors > 0)
			{
				$this->_safe_form_data = TRUE;
			}

			// Now we need to re-set the POST data with the new, processed data
			$this->_reset_post_array();

			// No errors, validation passes!
			if ($total_errors == 0)
			{
				return TRUE;
			}

			// Validation fails
			return FALSE;
		}

	// --------------------------------------------------------------------

	/**
	 * Executes the Validation routines
	 *
	 * @access	private
	 * @param	array
	 * @param	array
	 * @param	mixed
	 * @param	integer
	 * @return	mixed
	 */

	 /**
	 *Mod by Benomas, now parse third params with $row, for access to field
	 *
	 */
	protected function _execute($row, $rules, $postdata = NULL, $cycles = 0)
	{
		// If the $_POST data is an array we will run a recursive call
		if (is_array($postdata))
		{
			foreach ($postdata as $key => $val)
			{
				$this->_execute($row, $rules, $val, $cycles);
				$cycles++;
			}

			return;
		}
		// --------------------------------------------------------------------

		// If the field is blank, but NOT required, no further tests are necessary
		$callback = FALSE;
		if ( !$this->checkForAlwaysFail($rules) AND !in_array('required', $rules) AND is_null($postdata))
		{
			// Before we bail out, does the rule contain a callback?
			if (preg_match("/(callback_\w+(\[.*?\])?)/", implode(' ', $rules), $match))
			{
				$callback = TRUE;
				$rules = (array('1' => $match[1]));
			}
			else
			{
				return;
			}
		}

		// --------------------------------------------------------------------

		// Isset Test. Typically this rule will only apply to checkboxes.
		if (is_null($postdata) AND $callback == FALSE)
		{
			if (in_array('isset', $rules, TRUE) OR in_array('required', $rules))
			{
				// Set the message type
				$type = (in_array('required', $rules)) ? 'required' : 'isset';

				if ( ! isset($this->_error_messages[$type]))
				{
					if (FALSE === ($line = $this->CI->lang->line($type)))
					{
						$line = 'The field was not set';
					}
				}
				else
				{
					$line = $this->_error_messages[$type];
				}

				// Build the error message
				$message = sprintf($line, $this->_translate_fieldname($row['label']));

				// Save the error message
				$this->_field_data[$row['field']]['error'] = $message;

				if ( ! isset($this->_error_array[$row['field']]))
				{
					$this->_error_array[$row['field']] = $message;
				}
			}

			if(!$this->checkForAlwaysFail($rules))
				return;
		}


		// Cycle through each rule and run it
		foreach ($rules As $rule)
		{
			$_in_array = FALSE;

			// We set the $postdata variable with the current data in our master array so that
			// each cycle of the loop is dealing with the processed data from the last cycle
			if ($row['is_array'] == TRUE AND is_array($this->_field_data[$row['field']]['postdata']))
			{
				// We shouldn't need this safety, but just in case there isn't an array index
				// associated with this cycle we'll bail out
				if ( ! isset($this->_field_data[$row['field']]['postdata'][$cycles]))
				{
					continue;
				}

				$postdata = $this->_field_data[$row['field']]['postdata'][$cycles];
				$_in_array = TRUE;
			}
			else
			{
				$postdata = $this->_field_data[$row['field']]['postdata'];
			}

			// --------------------------------------------------------------------

			// Is the rule a callback?
			$callback = FALSE;
			if (substr($rule, 0, 9) == 'callback_')
			{
				$rule = substr($rule, 9);
				$callback = TRUE;
			}

			// Strip the parameter (if exists) from the rule
			// Rules can contain a parameter: max_length[5]
			$param = FALSE;
			if (preg_match("/(.*?)\[(.*)\]/", $rule, $match))
			{
				$rule	= $match[1];
				$param	= $match[2];
			}

			// Call the function that corresponds to the rule
			if ($callback === TRUE)
			{
				if ( ! method_exists($this->CI, $rule))
				{
					continue;
				}

				// Run the function and grab the result
				$result = $this->CI->$rule($postdata, $param);

				// Re-assign the result to the master data array
				if ($_in_array == TRUE)
				{
					$this->_field_data[$row['field']]['postdata'][$cycles] = (is_bool($result)) ? $postdata : $result;
				}
				else
				{
					$this->_field_data[$row['field']]['postdata'] = (is_bool($result)) ? $postdata : $result;
				}

				// If the field isn't required and we just processed a callback we'll move on...
				if ( ! in_array('required', $rules, TRUE) AND $result !== FALSE)
				{
					continue;
				}
			}
			else
			{
				if ( ! method_exists($this, $rule))
				{
					// If our own wrapper function doesn't exist we see if a native PHP function does.
					// Users can use any native PHP function call that has one param.
					if (function_exists($rule))
					{
						$result = $rule($postdata);

						if ($_in_array == TRUE)
						{
							$this->_field_data[$row['field']]['postdata'][$cycles] = (is_bool($result)) ? $postdata : $result;
						}
						else
						{
							$this->_field_data[$row['field']]['postdata'] = (is_bool($result)) ? $postdata : $result;
						}
					}
					else
					{
						log_message('debug', "Unable to find validation rule: ".$rule);
					}

					continue;
				}

				$result = $this->$rule($postdata, $param,$row);

				if ($_in_array == TRUE)
				{
					$this->_field_data[$row['field']]['postdata'][$cycles] = (is_bool($result)) ? $postdata : $result;
				}
				else
				{
					$this->_field_data[$row['field']]['postdata'] = (is_bool($result)) ? $postdata : $result;
				}
			}

			// Did the rule test negatively?  If so, grab the error.
			if ($result === FALSE)
			{
				if ( ! isset($this->_error_messages[$rule]))
				{
					if (FALSE === ($line = $this->CI->lang->line($rule)))
					{
						$line = 'Unable to access an error message corresponding to your field name.';
					}
				}
				else
				{
					$line = $this->_error_messages[$rule];
				}

				// Is the parameter we are inserting into the error message the name
				// of another field?  If so we need to grab its "field label"
				if (isset($this->_field_data[$param]) AND isset($this->_field_data[$param]['label']))
				{
					$param = $this->_translate_fieldname($this->_field_data[$param]['label']);
				}

				// Build the error message
				$message = sprintf($line, $this->_translate_fieldname($row['label']), $param);

				// Save the error message
				$this->_field_data[$row['field']]['error'] = $message;

				if ( ! isset($this->_error_array[$row['field']]))
				{
					$this->_error_array[$row['field']] = $message;
				}

				return;
			}
		}
	}


	// --------------------------------------------------------------------

	/**
	 * modified Get Error Message
	 *
	 * Gets the error message associated with a particular field
	 *
	 * @access	public
	 * @param	string	the field name
	 * @return	void
	 */
	public function error($field = '', $prefix = '', $suffix = '')
	{
		if(	$field==='')
		{
			$errors = array();
			foreach($this->_field_data AS $field)
			{
				if($field["error"] !== '')
				{
					$errors[$field['field']] = $field['error'];
				}
			}
			return $errors;
		}

		if ( ! isset($this->_field_data[$field]['error']) OR $this->_field_data[$field]['error'] == '')
		{
			return '';
		}

		if ($prefix == '')
		{
			$prefix = $this->_error_prefix;
		}

		if ($suffix == '')
		{
			$suffix = $this->_error_suffix;
		}

		return $prefix.$this->_field_data[$field]['error'].$suffix;
	}

	// --------------------------------------------------------------------

	/**
	 * Run validation and get Error Message
	 *
	 * Gets the error message associated with a particular field
	 *
	 * @access	public
	 * @param	string	the field name
	 * @return	void
	 */
	public function run_error($field = '', $prefix = '', $suffix = '')
	{
		$this->run();
		if(	$field==='')
		{
			$errors = array();
			foreach($this->_field_data AS $field)
			{
				if($field["error"] !== '')
				{
					$errors[$field['field']] = $field['error'];
				}
			}
			return $errors;
		}

		if ( ! isset($this->_field_data[$field]['error']) OR $this->_field_data[$field]['error'] == '')
		{
			return '';
		}

		if ($prefix == '')
		{
			$prefix = $this->_error_prefix;
		}

		if ($suffix == '')
		{
			$suffix = $this->_error_suffix;
		}

		return $prefix.$this->_field_data[$field]['error'].$suffix;
	}


	/**
	 * Alpha latin
	 * This validation check if string has a permited chars
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function alpha_latin($str)
	{
		return ( ! preg_match("/^([a-zñáéíóú])+$/i", $str)) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Alpha-numeric latin
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function alpha_numeric_latin($str)
	{
		return ( ! preg_match("/^([a-z0-9ñáéíóú])+$/i", mb_strtolower($str)) ) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Alpha-numeric with underscores and dashes
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function alpha_dash_latin($str)
	{
		return ( ! preg_match("/^([-a-z0-9_-zñáéíóú])+$/i", mb_strtolower($str) )) ? FALSE : TRUE;
	}

	/**
	 * Alpha-numeric with underscores, dashes and space
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function alpha_space_latin($str)
	{
		return ( ! preg_match("/^([-a-z0-9_-zñáéíóú ])+$/i", mb_strtolower($str)) ) ? FALSE : TRUE;
	}

	/**
	 * force alwaysfail in case field is not set
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	private function checkForAlwaysFail($rules)
	{
		foreach($rules AS $rule)
		{
			if(strstr($rule,'alwaysFail'))
				return true;
		}
		return false;
	}

	/**
	 * Return false, this function force a error	 *
 	 * @author  Benomas  (benomas@gmail.com) 2015
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function alwaysFail($str,$message)
	{
		$this->set_message('alwaysFail', $message);
		return false;
	}

	/**
	 * Return int >0
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function positive($str)
	{
		return $str > 0;
	}

	/**
	 * Return int >=0
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function noNegative($str)
	{
		return $str >= 0;
	}

}
/* End of file MY_form_validation.php */
/* Location: ./application/libraries/MY_form_validation.php */