<?php
/**
 * Test the `WP_Privacy_Requests_Table` class.
 *
 * @package WordPress\UnitTests
 *
 * @since 5.1.0
 */

/**
 * Tests_Admin_WpPrivacyRequestsTable class.
 *
 * @group admin
 * @group privacy
 *
 * @since 5.1.0
 */
class Tests_Admin_WpPrivacyRequestsTable extends WP_UnitTestCase {
	/**
	 * Get instance for mocked class.
	 *
	 * @since 5.1.0
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject|WP_Privacy_Requests_Table Mocked class instance.
	 */
	public function get_mocked_class_instance() {
		$args = array(
			'plural'   => 'privacy_requests',
			'singular' => 'privacy_request',
			'screen'   => 'export_personal_data',
		);

		$instance = $this
			->getMockBuilder( 'WP_Privacy_Requests_Table' )
			->setConstructorArgs( array( $args ) )
			->getMockForAbstractClass();

		$reflection = new ReflectionClass( $instance );

		// Set the request type as 'export_personal_data'.
		$reflection_property = $reflection->getProperty( 'request_type' );
		$reflection_property->setAccessible( true );
		$reflection_property->setValue( $instance, 'export_personal_data' );

		// Set the post type as 'user_request'.
		$reflection_property = $reflection->getProperty( 'post_type' );
		$reflection_property->setAccessible( true );
		$reflection_property->setValue( $instance, 'user_request' );

		return $instance;
	}

	/**
	 * Test columns should be sortable.
	 *
	 * @since 5.1.0
	 *
	 * @param string|null $order    Order.
	 * @param string|null $orderby  Order by.
	 * @param string|null $search   Search term.
	 * @param string      $expected Expected in SQL query.

	 * @dataProvider data_test_columns_should_be_sortable
	 * @covers WP_Privacy_Requests_Table::prepare_items
	 * @ticket 43960
	 */
	public function test_columns_should_be_sortable( $order, $orderby, $search, $expected ) {
		global $wpdb;

		$table     = $this->get_mocked_class_instance();
		$this->sql = '';

		$_REQUEST['order']   = $order;
		$_REQUEST['orderby'] = $orderby;
		$_REQUEST['s']       = $search;

		add_filter( 'posts_request', array( $this, 'filter_posts_request' ) );
		$table->prepare_items();
		remove_filter( 'posts_request', array( $this, 'filter_posts_request' ) );

		unset( $_REQUEST['order'] );
		unset( $_REQUEST['orderby'] );
		unset( $_REQUEST['s'] );

		$this->assertContains( "ORDER BY {$wpdb->posts}.{$expected}", $this->sql );
	}

	/**
	 * Filter to grab the complete SQL query.
	 *
	 * @since 5.1.0
	 *
	 * @param string $request The complete SQL query.
	 * @return string The complete SQL query.
	 */
	public function filter_posts_request( $request ) {
		$this->sql = $request;
		return $request;
	}

	/**
	 * Data provider for `test_columns_should_be_sortable()`.
	 *
	 * @since 5.1.0
	 *
	 * @return array {
	 *     @type array {
	 *         @type string|null Order.
	 *         @type string|null Order by.
	 *         @type string|null Search term.
	 *         @type string      Expected in SQL query.
	 *     }
	 * }
	 */
	public function data_test_columns_should_be_sortable() {
		return array(
			// Default order (ID) DESC.
			array(
				'order'    => null,
				'orderby'  => null,
				's'        => null,
				'expected' => 'post_date DESC',
			),
			// Default order (ID) DESC.
			array(
				'order'    => '',
				'orderby'  => '',
				's'        => '',
				'expected' => 'post_date DESC',
			),
			// Order by requester (post_title) ASC.
			array(
				'order'    => 'ASC',
				'orderby'  => 'requester',
				's'        => '',
				'expected' => 'post_title ASC',
			),
			// Order by requester (post_title) DESC.
			array(
				'order'    => 'DESC',
				'orderby'  => 'requester',
				's'        => null,
				'expected' => 'post_title DESC',
			),
			// Order by requested (post_date) ASC.
			array(
				'order'    => 'ASC',
				'orderby'  => 'requested',
				's'        => null,
				'expected' => 'post_date ASC',
			),
			// Order by requested (post_date) DESC.
			array(
				'order'    => 'DESC',
				'orderby'  => 'requested',
				's'        => null,
				'expected' => 'post_date DESC',
			),
			// Search and order by relevance.
			array(
				'order'    => null,
				'orderby'  => null,
				's'        => 'foo',
				'expected' => 'post_title LIKE',
			),
			// Search and order by requester (post_title) ASC.
			array(
				'order'    => 'ASC',
				'orderby'  => 'requester',
				's'        => 'foo',
				'expected' => 'post_title ASC',
			),
			// Search and order by requested (post_date) ASC.
			array(
				'order'    => 'ASC',
				'orderby'  => 'requested',
				's'        => 'foo',
				'expected' => 'post_date ASC',
			),
		);
	}
}
