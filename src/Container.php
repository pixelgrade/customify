<?php
/**
 * Container.
 *
 * @package Pixelgrade Customify
 * @license GPL-2.0-or-later
 * @since 0.1.0
 */

declare ( strict_types = 1 );

namespace Pixelgrade\Customify;

use Pixelgrade\Customify\Vendor\Psr\Container\ContainerInterface;
use Pixelgrade\Customify\Vendor\Pimple\Container as PimpleContainer;

/**
 * Container class.
 *
 * Extends PimpleContainer to satisfy the ContainerInterface.
 *
 * @since 0.1.0
 */
class Container extends PimpleContainer implements ContainerInterface {
	/**
	 * Finds an entry of the container by its identifier and returns it.
	 *
	 * @since 0.1.0
	 *
	 * @param string $id Identifier of the entry to look for.
	 * @return mixed Entry.
	 */
	public function get( $id ) {
		return $this->offsetGet( $id );
	}

	/**
	 * Whether the container has an entry for the given identifier.
	 *
	 * @since 0.1.0
	 *
	 * @param string $id Identifier of the entry to look for.
	 * @return bool
	 */
	public function has( $id ): bool {
		return $this->offsetExists( $id );
	}
}
