<?php


namespace Pixelgrade\Customify\Provider;

use Carbon_Fields\Field\Field;
use Carbon_Fields\Datastore\Datastore;

/**
 * Stores all container values in a single DB entry in wp_options.
 */
class PluginSettingsCFDatastore extends Datastore {

	/**
	 * Initialization tasks for concrete datastores.
	 **/
	public function init() {

	}

	protected function get_key_for_field( Field $field ) {
		// No prefixing. Just use the field base name.
		return $field->get_base_name();
	}

	/**
	 * Save a single key-value pair to the database with autoload.
	 *
	 * @param string $key
	 * @param string $value
	 * @param bool   $autoload We will ignore this since we save in a single entry that is always autoloaded.
	 */
	protected function save_key_value_pair_with_autoload( $key, $value, $autoload ) {
		// We will save the value in a simpler format, not the full one.
		$value = $this->format_value( $value );

		// First, get the entire serialized array.
		$all_values = $this->get_all_values();

		if ( empty( $all_values ) ) {
			$all_values = [
				$key => $value,
			];
		} else {
			$all_values[ $key ] = $value;
		}

		$this->update_all_values( $all_values );
	}

	protected function format_value( $value ) {
		$formatted_value = $value;

		if ( is_array( $value ) ) {
			$formatted_value = array_map( function( $entry) {
				if ( isset( $entry['value'] ) ) {
					return $entry['value'];
				}

				return $entry;
			}, $value );

			if ( count( $formatted_value ) === 1 ) {
				return reset( $formatted_value );
			}
		}

		return $formatted_value;
	}

	/**
	 * Load the field value(s).
	 *
	 * @param Field $field The field to load value(s) in.
	 *
	 * @return array
	 */
	public function load( Field $field ) {
		$key   = $this->get_key_for_field( $field );

		// First, get the entire serialized array.
		$all_values = $this->get_all_values();

		if ( ! empty( $all_values ) && isset( $all_values[ $key ] ) ) {
			return $all_values[ $key ];
		}

		return null;
	}

	public function get_all_values() {
		return get_option( PluginSettings::OPTION_NAME, null );
	}

	public function update_all_values( $values ) {
		return update_option( PluginSettings::OPTION_NAME, $values, true );
	}

	/**
	 * Save the field value(s)
	 *
	 * @param Field $field The field to save.
	 */
	public function save( Field $field ) {
		if ( ! empty( $field->get_hierarchy() ) ) {
			return; // only applicable to root fields
		}
		$key   = $this->get_key_for_field( $field );
		$value = $field->get_full_value();
		if ( is_a( $field, '\\Carbon_Fields\\Field\\Complex_Field' ) ) {
			$value = $field->get_value_tree();
		}
		$this->save_key_value_pair_with_autoload( $key, $value, $field->get_autoload() );
	}

	/**
	 * Delete the field value(s)
	 *
	 * @param Field $field The field to delete.
	 */
	public function delete( Field $field ) {
		if ( ! empty( $field->get_hierarchy() ) ) {
			return; // only applicable to root fields
		}
		$key = $this->get_key_for_field( $field );

		$all_values = $this->get_all_values();

		if ( ! empty( $all_values ) && isset( $all_values[ $key ] ) ) {
			unset( $all_values[ $key ] );
			$this->update_all_values( $all_values );
		}
	}
}
