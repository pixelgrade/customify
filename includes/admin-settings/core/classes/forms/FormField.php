<?php defined('ABSPATH') or die;

/**
 * @package    pixcustomify
 * @category   core
 * @author     Pixelgrade Team
 * @copyright  (c) 2013, Pixelgrade
 */
class PixCustomifyFormFieldImpl extends PixCustomifyHTMLElementImpl implements PixCustomifyFormField {

	/**
	 * @param array config
	 */
	static function instance($config = null) {
		$i = new self;
		$i->configure($config);
		return $i;
	}

	// Error Handling Helpers
	// ------------------------------------------------------------------------

	/**
	 * @return boolean true if field has errors
	 */
	function has_errors() {
		$form = $this->getmeta('form', null);
		$errors = $form->errors_for($this->getmeta('name'));
		return ! empty($errors);
	}

	/**
	 * @return string first error message
	 */
	function one_error() {
		$form = $this->getmeta('form', null);
		$errors = $form->errors_for($this->getmeta('name'));
		return array_shift($errors);
	}

	// Rendering
	// ------------------------------------------------------------------------

	/**
	 * Render field emulates wordpress template behaviour. First searches for
	 * name, then searches field type and so on.
	 *
	 * @return string
	 */
	function render() {
		$form = $this->getmeta('form');

		// we reverse the order so that last added is first checked
		$template_paths = array_reverse($form->getmeta('template-paths', array()));

		if (empty($template_paths)) {
			throw new Exception('Missing template paths.');
		}

		// the following are the file patterns we look for
		$patterns = array
			(
				'fields/'.$this->getmeta('name'),
				'fields/'.$this->getmeta('type'),
				$this->getmeta('name'),
				$this->getmeta('type')
			);

		foreach ($patterns as $pattern) {
			foreach ($template_paths as $path) {
				$dirpath = rtrim($path, '\\/').DIRECTORY_SEPARATOR;
				if (file_exists($dirpath.$pattern.EXT)) {
					return $this->render_template_file($dirpath.$pattern.EXT);
				}
			}
		}

		throw new Exception('Failed to match any pattern for field ['.$this->getmeta('name').'] of type '.$this->getmeta('type', '[unknown]'));
	}

	/**
	 * @param  string template path
	 * @return string rendered field
	 */
	protected function render_template_file($_template_filepath) {
		// variables which we wish to expose to template
		$field = $this; # $this will also work
		$form = $this->getmeta('form');
		$name = $this->getmeta('name', null);
		$label = $this->getmeta('label', null);
		$default = $this->getmeta('default', null);
		$desc = $this->getmeta('desc', '');
		$rendering = $this->getmeta('rendering', 'standard');

		// cleaned name (names may be "something[]")
		$idname = preg_replace('/[^a-zA-Z0-9_-]/', '', $name);

		ob_start();
		include $_template_filepath;
		return ob_get_clean();
	}

	/**
	 * @return string
	 */
	function __toString() {
		return $this->render();
	}

} # class
