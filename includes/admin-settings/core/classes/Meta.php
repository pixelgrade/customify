<?php defined('ABSPATH') or die;

/**
 * @package    pixcustomify
 * @category   core
 * @author     Pixelgrade Team
 * @copyright  (c) 2013, Pixelgrade
 */
class PixCustomifyMetaImpl implements PixCustomifyMeta {

	/** @var array metadat */
	protected $metadata = array();

	/**
	 * @param  array metadata
	 * @return PixCustomifyMeta
	 */
	static function instance($metadata) {
		$i = new self;
		$i->metadata = $metadata;
		return $i;
	}

	/**
	 * @param string meta key
	 * @return boolean true if key exists, false otherwise
	 */
	function has($key) {
		return isset($this->metadata[$key]);
	}

	/**
	 * @param  string key
	 * @param  mixed  default
	 * @return mixed
	 */
	function get($key, $default = null) {
		return $this->has($key) ? $this->metadata[$key] : $default;
	}

	/**
	 * @param  string key
	 * @param  mixed  value
	 * @return static $this
	 */
	function set($key, $value) {
		$this->metadata[$key] = $value;
		return $this;
	}

	/**
	 * Set the key if it's not already set.
	 *
	 * @param string key
	 * @param string value
	 */
	function ensure($key, $value) {
		if ( ! $this->has($key)) {
			$this->set($key, $value);
		}

		return $this;
	}

	/**
	 * If the key is currently a non-array value it will be converted to an
	 * array maintaning the previous value (along with the new one).
	 *
	 * @param  string name
	 * @param  mixed  value
	 * @return static $this
	 */
	function add($name, $value) {

		// Cleanup
		// -------

		if ( ! isset($this->metadata[$name])) {
			$this->metadata[$name] = array();
		}
		else if ( ! is_array($this->metadata[$name]))
		{
			$this->metadata[$name] = array($this->metadata[$name]);
		}
		# else: array, no cleanup required

		// Register new value
		// ------------------

		$this->metadata[$name][] = $value;

		return $this;
	}

	/**
	 * @return array all metadata as array
	 */
	function metadata_array() {
		return $this->metadata;
	}

	/**
	 * Shorthand for a calling set on multiple keys.
	 *
	 * @return static $this
	 */
	function overwritemeta($overwrites) {
		foreach ($overwrites as $key => $value) {
			$this->set($key, $value);
		}

		return $this;
	}

} # class
