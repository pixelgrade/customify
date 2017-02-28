<?php defined('ABSPATH') or die;

/**
 * @package    pixcustomify
 * @category   core
 * @author     Pixelgrade Team
 * @copyright  (c) 2013, Pixelgrade
 */
class PixCustomifyProcessorImpl implements PixCustomifyProcessor {

	/** @var PixCustomifyMeta plugin configuration */
	protected $meta = null;

	/** @var PixCustomifyMeta field information */
	protected $fields = null;

	/**
	 * @param array config
	 */
	static function instance($config = null) {
		$i = new self;
		$i->configure($config);
		return $i;
	}

	/**
	 * Apply configuration.
	 */
	protected function configure($config = null) {
		$this->meta = pixcustomify::instance('PixCustomifyMeta', $config);

		// extract fields from configuration
		$fields = $this->extract($this->meta->get('fields', array()));
		$this->fields = pixcustomify::instance('PixCustomifyMeta', $fields);
	}

	/**
	 * Extracts fields from raw fields configuration returning an array with
	 * the fields in a flat plain.
	 *
	 * Fields are extracted as follows: if an array has the key "type" it's a
	 * field configuration and the key above it is considered the field name
	 * unless a "name" key is also available.
	 *
	 * @param array raw fields
	 * @return array flat field list
	 */
	protected function extract($rawfields) {
		$fields = array();

		foreach ($rawfields as $key => $value) {
			if (is_array($value)) {
				if (isset($value['type'])) {
					if (is_string($key)) {
						$fields[$key] = $value;
					}
					else if (isset($value['name'])) {
						$fields[$value['name']] = $value;
					}
					# else: assume rendering sugar or other
				}

				// search deeper for embeded fields
				$embeded_fields = $this->extract($value);
				$fields = array_merge($fields, $embeded_fields);
			}
		}

		return $fields;
	}

	/** @var array status */
	protected $status = null;

	/** @var PixCustomifyMeta current data; including submitted data */
	protected $data = null;

	/**
	 * @return static $this
	 */
	function run() {
		// if the status has been generated we skip execution
		if ($this->status !== null) {
			return $this;
		}

		$this->status = array
			(
				'state' => 'nominal',
				'errors' => array(),
				'dataupdate' => false,
			);

		try {
			$option_key = $this->meta->get('settings-key', null);

			if ($option_key === null) {
				throw new Exception('Missing option_key in plugin configuration.');
			}

			if ($this->form_was_submitted()) {
				$input = $this->cleanup_input($_POST);
				$errors = $this->validate_input($input);

				if (empty($errors)) {
					$this->preupdate($input);
					$this->status['dataupdate'] = true;
					$current_values = get_option($option_key);
					$new_option = array_merge($current_values, $input);
					update_option($option_key, $new_option);
					$this->data = pixcustomify::instance('PixCustomifyMeta', $input);
					$this->postupdate($input);
				}
				else { // got errors
					$this->status['errors'] = $errors;
					$this->load_data_from_database($option_key);
					$this->data->overwritemeta($input);
				}
			}
			else { // GET request
				$this->load_data_from_database($option_key);
			}
		}
		catch (Exception $e) {
			if ($this->meta->get('debug', false)) {
				throw $e;
			}

			$this->status['state'] = 'error';
			$this->status['message'] = $e->getMessage();
		}

		return $this;
	}

	/**
	 * @return static $this
	 */
	protected function load_data_from_database($option_key) {
		$dbconfig = get_option($option_key);

		if ($dbconfig === false) {
			throw new Exception('Unable to retrieve options.');
		}

		$this->data = pixcustomify::instance('PixCustomifyMeta', $dbconfig);
	}

	/**
	 * @param array input
	 * @return array cleaned up input
	 */
	protected function cleanup_input($input) {
		$defaults = pixcustomify::defaults();
		$plugin_cleanup = $this->meta->get('cleanup', array());

		foreach ($this->fields->metadata_array() as $key => $field) {

			// ensure a value is present
			if ( ! isset($input[$key])) {
				$input[$key] = null;
			}

			// Calculate cleanup rules
			// -----------------------

			$cleanup = array();
			// check pixcustomify defaults
			if (isset($defaults['cleanup'][$field['type']])) {
				$cleanup = $defaults['cleanup'][$field['type']];
			}
			// check plugin defaults
			if (isset($plugin_cleanup[$field['type']])) {
				$cleanup = array_merge($cleanup, $plugin_cleanup[$field['type']]);
			}
			// check field presets
			if (isset($field['cleanup'])) {
				$cleanup = array_merge($cleanup, $field['cleanup']);
			}

			// Perform Cleanup
			// ---------------

			foreach ($cleanup as $rule) {
				$callback = pixcustomify::callback($rule, $this->meta);
				$input[$key] = call_user_func($callback, $input[$key], $field, $this);
			}
		}

		return $input;
	}

	/**
	 * @param array input
	 * @return array
	 */
	protected function validate_input($input) {
		$validator = pixcustomify::instance('PixCustomifyValidator', $this->meta, $this->fields);
		return $validator->validate($input);
	}

	/**
	 * @return boolean
	 */
	protected function form_was_submitted() {
		return $_SERVER['REQUEST_METHOD'] === 'POST';
	}

	/**
	 * @return array
	 */
	function status() {
		if ($this->status === null) {
			$this->run();
		}

		return $this->status;
	}

	/**
	 * @return PixCustomifyMeta current data (influenced by user submitted data)
	 */
	function data() {
		if ($this->status === null) {
			$this->run();
		}

		return $this->data;
	}

	/**
	 * Shorthand.
	 *
	 * @return array
	 */
	function errors() {
		if ($this->status === null) {
			$this->run();
		}

		return $this->status['errors'];
	}

	/**
	 * Shorthand.
	 *
	 * @return boolean
	 */
	function performed_update() {
		if ($this->status === null) {
			$this->run();
		}

		return $this->status['dataupdate'];
	}

	/**
	 * @return boolean true if state is nominal
	 */
	function ok() {
		if ($this->status === null) {
			$this->run();
		}

		return $this->status['state'] == 'nominal';
	}

	// ------------------------------------------------------------------------
	// Hooks

	/**
	 * Execute preupdate hooks on input.
	 */
	protected function preupdate($input)
	{
		$defaults = pixcustomify::defaults();
		$plugin_hooks = $this->meta->get('processor', array('preupdate' => array(), 'postupdate' => array()));

		// Calculate hooks
		// ---------------

		$hooks = array();
		// check pixcustomify defaults
		if (isset($defaults['processor']['preupdate'])) {
			$hooks = $defaults['processor']['preupdate'];
		}
		// check plugin defaults
		if (isset($plugin_hooks['preupdate'])) {
			$hooks = array_merge($hooks, $plugin_hooks['preupdate']);
		}

		// Execute hooks
		// -------------

		foreach ($hooks as $rule) {
			$callback = pixcustomify::callback($rule, $this->meta);
			call_user_func($callback, $input, $this);
		}
	}

	/**
	 * Execute postupdate hooks on input.
	 */
	protected function postupdate($input)
	{
		$defaults = pixcustomify::defaults();
		$plugin_hooks = $this->meta->get('processor', array('preupdate' => array(), 'postupdate' => array()));

		// Calculate hooks
		// ---------------

		$hooks = array();
		// check pixcustomify defaults
		if (isset($defaults['processor']['postupdate'])) {
			$hooks = $defaults['processor']['postupdate'];
		}
		// check plugin defaults
		if (isset($plugin_hooks['postupdate'])) {
			$hooks = array_merge($hooks, $plugin_hooks['postupdate']);
		}

		// Execute hooks
		// -------------

		foreach ($hooks as $rule) {
			$callback = pixcustomify::callback($rule, $this->meta);
			call_user_func($callback, $input, $this);
		}
	}

} # class
