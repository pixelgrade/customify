<?php

/**
 * Test the do_all_hook method of WP_Hook
 *
 * @group hooks
 * @covers WP_Hook::do_all_hook
 */
class Tests_Hooks_DoAllHook extends WP_UnitTestCase {

	public function test_do_all_hook_with_multiple_calls() {
		$a             = new MockAction();
		$callback      = array( $a, 'action' );
		$hook          = new WP_Hook();
		$tag           = 'all';
		$priority      = rand( 1, 100 );
		$accepted_args = rand( 1, 100 );
		$arg           = 'all_arg';

		$hook->add_filter( $tag, $callback, $priority, $accepted_args );
		$args = array( $arg );
		$hook->do_all_hook( $args );
		$hook->do_all_hook( $args );

		$this->assertSame( 2, $a->get_call_count() );
	}
}
