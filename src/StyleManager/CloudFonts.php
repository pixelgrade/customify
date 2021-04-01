<?php
/**
 * This is the class that handles the overall logic for cloud fonts.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\StyleManager;

use Pixelgrade\Customify\Vendor\Cedaro\WP\Plugin\AbstractHookProvider;
use Pixelgrade\Customify\Vendor\Psr\Log\LoggerInterface;
use function Pixelgrade\Customify\is_sm_supported;

/**
 * Provides the cloud fonts logic.
 *
 * @since 3.0.0
 */
class CloudFonts extends AbstractHookProvider {

	/**
	 * Design assets.
	 *
	 * @var DesignAssets
	 */
	protected DesignAssets $design_assets;

	/**
	 * Logger.
	 *
	 * @var LoggerInterface
	 */
	protected LoggerInterface $logger;

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param DesignAssets    $design_assets Design assets.
	 * @param LoggerInterface $logger        Logger.
	 */
	public function __construct(
		DesignAssets $design_assets,
		LoggerInterface $logger
	) {
		$this->design_assets = $design_assets;
		$this->logger        = $logger;
	}

	/**
	 * Register hooks.
	 *
	 * @since 3.0.0
	 */
	public function register_hooks() {
		/*
		 * Handle the cloud fonts preprocessing.
		 */
		add_filter( 'customify_get_cloud_fonts', [ $this, 'preprocess_config' ], 5, 1 );

		/*
		 * Add the cloud fonts to the Font Selector
		 */
		$this->add_filter( 'customify_cloud_fonts', 'add_cloud_fonts_to_font_selector', 10, 1 );

		/*
		 * Handle the cloud fonts preprocessing.
		 */
		add_filter( 'customify_get_cloud_system_fonts', [ $this, 'preprocess_config' ], 5, 1 );

		/*
		 * Add the cloud system fonts to the Font Selector
		 */
		$this->add_filter( 'customify_system_fonts', 'add_cloud_system_fonts_to_font_selector', 10, 1 );

		/*
		 * Add the cloud font categories to the list.
		 */
		$this->add_filter( 'customify_font_categories', 'add_cloud_font_categories', 10, 1 );
	}

	/**
	 * Determine if Cloud Fonts are supported.
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	public function is_supported(): bool {
		// For now we will only use the fact that Style Manager is supported.
		return apply_filters( 'style_manager_cloud_fonts_are_supported', is_sm_supported() );
	}

	/**
	 * Get the cloud fonts configuration.
	 *
	 * @since 2.7.0
	 *
	 * @param bool $skip_cache Optional. Whether to use the cached config or fetch a new one.
	 *
	 * @return array
	 */
	public function get_cloud_fonts( $skip_cache = false ): array {
		$config = $this->design_assets->get_entry( 'cloud_fonts', $skip_cache );
		if ( is_null( $config ) ) {
			$config = $this->get_default_cloud_fonts();
		}

		return apply_filters( 'customify_get_cloud_fonts', $config );
	}

	/**
	 * Get the cloud standard fonts configuration.
	 *
	 * @since 2.8.0
	 *
	 * @param bool $skip_cache Optional. Whether to use the cached config or fetch a new one.
	 *
	 * @return array
	 */
	public function get_system_fonts( $skip_cache = false ): array {
		$config = $this->design_assets->get_entry( 'system_fonts', $skip_cache );
		if ( is_null( $config ) ) {
			$config = $this->get_default_system_fonts();
		}

		return apply_filters( 'customify_get_cloud_system_fonts', $config );
	}

	/**
	 * Get the font categories configuration.
	 *
	 * @since 3.0.0
	 *
	 * @param bool $skip_cache Optional. Whether to use the cached config or fetch a new one.
	 *
	 * @return array
	 */
	public function get_categories( $skip_cache = false ): array {
		$categories = $this->design_assets->get_entry( 'font_categories', $skip_cache );
		if ( is_null( $categories ) ) {
			$categories = $this->get_default_categories();
		}

		return apply_filters( 'customify_get_cloud_font_categories', $categories );
	}

	/**
	 * Get the default (hard-coded) standard fonts configuration.
	 *
	 * This is only a fallback config in case we can't communicate with the cloud, the first time.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	protected function get_default_system_fonts(): array {
		$default_system_fonts = [
			"Arial, Helvetica, sans-serif"                         => "Arial, Helvetica, sans-serif",
			"'Arial Black', Gadget, sans-serif"                    => "'Arial Black', Gadget, sans-serif",
			"'Bookman Old Style', serif"                           => "'Bookman Old Style', serif",
			"'Comic Sans MS', cursive"                             => "'Comic Sans MS', cursive",
			"Courier, monospace"                                   => "Courier, monospace",
			"Garamond, serif"                                      => "Garamond, serif",
			"Georgia, serif"                                       => "Georgia, serif",
			"Impact, Charcoal, sans-serif"                         => "Impact, Charcoal, sans-serif",
			"'Lucida Console', Monaco, monospace"                  => "'Lucida Console', Monaco, monospace",
			"'Lucida Sans Unicode', 'Lucida Grande', sans-serif"   => "'Lucida Sans Unicode', 'Lucida Grande', sans-serif",
			"'MS Sans Serif', Geneva, sans-serif"                  => "'MS Sans Serif', Geneva, sans-serif",
			"'MS Serif', 'New York', sans-serif"                   => "'MS Serif', 'New York', sans-serif",
			"'Palatino Linotype', 'Book Antiqua', Palatino, serif" => "'Palatino Linotype', 'Book Antiqua', Palatino, serif",
			"Tahoma, Geneva, sans-serif"                           => "Tahoma, Geneva, sans-serif",
			"'Times New Roman', Times,serif"                       => "'Times New Roman', Times, serif",
			"'Trebuchet MS', Helvetica, sans-serif"                => "'Trebuchet MS', Helvetica, sans-serif",
			"Verdana, Geneva, sans-serif"                          => "Verdana, Geneva, sans-serif",
		];

		return apply_filters( 'customify_style_manager_default_system_fonts', $default_system_fonts );
	}

	/**
	 * Get the default (hard-coded) cloud fonts configuration.
	 *
	 * This is only a fallback config in case we can't communicate with the cloud, the first time.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	protected function get_default_cloud_fonts(): array {
		$default_config = [];

		return apply_filters( 'customify_style_manager_default_cloud_fonts', $default_config );
	}

	/**
	 * Get the default (hard-coded) font categories configuration.
	 *
	 * This is only a fallback config in case we can't communicate with the cloud, the first time.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	protected function get_default_categories(): array {
		$default_categories = [];

		return apply_filters( 'customify_style_manager_default_cloud_font_categories', $default_categories );
	}

	protected function add_cloud_fonts_to_font_selector( array $fonts ): array {
		if ( empty( $fonts ) ) {
			$fonts = [];
		}

		if ( ! $this->is_supported() ) {
			return $fonts;
		}

		$fonts = array_merge( $fonts, $this->get_cloud_fonts() );

		return $fonts;
	}

	protected function add_cloud_system_fonts_to_font_selector( array $fonts ): array {
		if ( empty( $fonts ) ) {
			$fonts = [];
		}

		if ( ! $this->is_supported() ) {
			return array_merge( $fonts, $this->get_default_system_fonts() );
		}

		return array_merge( $fonts, $this->get_system_fonts() );
	}

	protected function add_cloud_font_categories( $categories ) {
		if ( empty( $categories ) ) {
			$categories = [];
		}

		if ( ! $this->is_supported() ) {
			return $categories;
		}

		return array_merge( $categories, $this->get_categories() );
	}

	/**
	 * Preprocess the cloud fonts configuration.
	 *
	 * Convert the cloud font list to a format suitable for Customify.
	 *
	 * @since 3.0.0
	 *
	 * @param array $config
	 *
	 * @return array
	 */
	public function preprocess_config( array $config ): array {
		if ( empty( $config ) ) {
			return $config;
		}

		$new_config = [];
		foreach ( $config as $font_id => $font_config ) {
			if ( empty( $font_config['font_family'] ) ) {
				continue;
			}


			$new_config[ $font_config['font_family'] ] = $this->preprocess_font_config( $font_config );
		}

		return $new_config;
	}

	/**
	 * Preprocess a cloud font config before using it.
	 *
	 * @since 3.0.0
	 *
	 * @param array $font_config
	 *
	 * @return array
	 */
	protected function preprocess_font_config( array $font_config ): array {
		if ( empty( $font_config ) ) {
			return $font_config;
		}

		// We need to convert the received data structure to the one expected by Customify.
		return [
			'family'         => $font_config['font_family'],
			'family_display' => empty( $font_config['font_family_display'] ) ? '' : $font_config['font_family_display'],
			'src'            => empty( $font_config['stylesheet'] ) ? false : $font_config['stylesheet'],
			'variants'       => empty( $font_config['variants'] ) ? [] : $font_config['variants'],
			'category'       => empty( $font_config['category'] ) ? '' : $font_config['category'],
			'fallback_stack' => empty( $font_config['fallback_stack'] ) ? '' : $font_config['fallback_stack'],
		];
	}
}
