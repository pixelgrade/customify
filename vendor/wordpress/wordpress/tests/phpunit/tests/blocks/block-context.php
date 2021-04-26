<?php
/**
 * WP_Block_Context Tests
 *
 * @package WordPress
 * @subpackage Blocks
 * @since 5.5.0
 */

/**
 * Tests for WP_Block_Context
 *
 * @since 5.5.0
 *
 * @group blocks
 */
class WP_Block_Context_Test extends WP_UnitTestCase {

	/**
	 * Registered block names.
	 *
	 * @var string[]
	 */
	private $registered_block_names = array();

	/**
	 * Sets up each test method.
	 */
	public function setUp() {
		global $post;

		parent::setUp();

		$args = array(
			'post_content' => 'example',
			'post_excerpt' => '',
		);

		$post = $this->factory()->post->create_and_get( $args );
		setup_postdata( $post );
	}

	/**
	 * Tear down each test method.
	 */
	public function tearDown() {
		parent::tearDown();

		while ( ! empty( $this->registered_block_names ) ) {
			$block_name = array_pop( $this->registered_block_names );
			unregister_block_type( $block_name );
		}
	}

	/**
	 * Registers a block type.
	 *
	 * @param string|WP_Block_Type $name Block type name including namespace, or alternatively a
	 *                                   complete WP_Block_Type instance. In case a WP_Block_Type
	 *                                   is provided, the $args parameter will be ignored.
	 * @param array                $args {
	 *     Optional. Array of block type arguments. Any arguments may be defined, however the
	 *     ones described below are supported by default. Default empty array.
	 *
	 *     @type callable $render_callback Callback used to render blocks of this block type.
	 * }
	 */
	protected function register_block_type( $name, $args ) {
		register_block_type( $name, $args );

		$this->registered_block_names[] = $name;
	}

	/**
	 * Tests that a block which provides context makes that context available to
	 * its inner blocks.
	 *
	 * @ticket 49927
	 */
	function test_provides_block_context() {
		$provided_context = array();

		$this->register_block_type(
			'gutenberg/test-context-provider',
			array(
				'attributes'       => array(
					'contextWithAssigned'   => array(
						'type' => 'number',
					),
					'contextWithDefault'    => array(
						'type'    => 'number',
						'default' => 0,
					),
					'contextWithoutDefault' => array(
						'type' => 'number',
					),
					'contextNotRequested'   => array(
						'type' => 'number',
					),
				),
				'provides_context' => array(
					'gutenberg/contextWithAssigned'   => 'contextWithAssigned',
					'gutenberg/contextWithDefault'    => 'contextWithDefault',
					'gutenberg/contextWithoutDefault' => 'contextWithoutDefault',
					'gutenberg/contextNotRequested'   => 'contextNotRequested',
				),
			)
		);

		$this->register_block_type(
			'gutenberg/test-context-consumer',
			array(
				'uses_context'    => array(
					'gutenberg/contextWithDefault',
					'gutenberg/contextWithAssigned',
					'gutenberg/contextWithoutDefault',
				),
				'render_callback' => function( $attributes, $content, $block ) use ( &$provided_context ) {
					$provided_context[] = $block->context;

					return '';
				},
			)
		);

		$parsed_blocks = parse_blocks(
			'<!-- wp:gutenberg/test-context-provider {"contextWithAssigned":10} -->' .
			'<!-- wp:gutenberg/test-context-consumer /-->' .
			'<!-- /wp:gutenberg/test-context-provider -->'
		);

		render_block( $parsed_blocks[0] );

		$this->assertSame(
			array(
				'gutenberg/contextWithDefault'  => 0,
				'gutenberg/contextWithAssigned' => 10,
			),
			$provided_context[0]
		);
	}

	/**
	 * Tests that a block can receive default-provided context through
	 * render_block.
	 *
	 * @ticket 49927
	 */
	function test_provides_default_context() {
		global $post;

		$provided_context = array();

		$this->register_block_type(
			'gutenberg/test-context-consumer',
			array(
				'uses_context'    => array( 'postId', 'postType' ),
				'render_callback' => function( $attributes, $content, $block ) use ( &$provided_context ) {
					$provided_context[] = $block->context;

					return '';
				},
			)
		);

		$parsed_blocks = parse_blocks( '<!-- wp:gutenberg/test-context-consumer /-->' );

		render_block( $parsed_blocks[0] );

		$this->assertSame(
			array(
				'postId'   => $post->ID,
				'postType' => $post->post_type,
			),
			$provided_context[0]
		);
	}

	/**
	 * Tests that default block context can be filtered.
	 *
	 * @ticket 49927
	 */
	function test_default_context_is_filterable() {
		$provided_context = array();

		$this->register_block_type(
			'gutenberg/test-context-consumer',
			array(
				'uses_context'    => array( 'example' ),
				'render_callback' => function( $attributes, $content, $block ) use ( &$provided_context ) {
					$provided_context[] = $block->context;

					return '';
				},
			)
		);

		$filter_block_context = function( $context ) {
			$context['example'] = 'ok';
			return $context;
		};

		$parsed_blocks = parse_blocks( '<!-- wp:gutenberg/test-context-consumer /-->' );

		add_filter( 'render_block_context', $filter_block_context );

		render_block( $parsed_blocks[0] );

		remove_filter( 'render_block_context', $filter_block_context );

		$this->assertSame( array( 'example' => 'ok' ), $provided_context[0] );
	}

}
