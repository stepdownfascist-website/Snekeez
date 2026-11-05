<?php
/**
 * Info Card Block CSS Generator.
 *
 * Generates CSS for the Info Card block using CSS_Helpers.
 *
 * @package BlockFlow
 */

namespace ThemeRuby\BlockFlow\CSS_Generators;

use ThemeRuby\BlockFlow\CSS_Builder;
use ThemeRuby\BlockFlow\CSS_Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Info Card CSS Generator class.
 */
class Info_Card_CSS {

	/**
	 * Generate CSS for Info Card block.
	 *
	 * @param array                    $attrs Block attributes.
	 * @param CSS_Builder $css   CSS generator instance.
	 */
	public static function generate( $attrs, $css ) {
		$block_id = $attrs['blockId'] ?? '';

		if ( empty( $block_id ) ) {
			return;
		}

		// Selectors.
		$selector         = '.blockflow-info-card.blockflow-info-card-' . $block_id;
		$inner_selector   = $selector . ' .blockflow-info-card__inner';
		$media_selector   = $selector . ' .blockflow-info-card__media';
		$content_selector = $selector . ' .blockflow-info-card__content';
		$title_selector   = $selector . ' .blockflow-info-card__title';
		$desc_selector    = $selector . ' .blockflow-info-card__description';
		$btn_selector     = $selector . ' .blockflow-info-card__btn';

		// Extract attributes.
		$media_type          = $attrs['mediaType'] ?? 'icon';
		$media_view          = $attrs['mediaView'] ?? 'default';
		$media_shape         = $attrs['mediaShape'] ?? 'square';
		$layout              = $attrs['layout'] ?? [];
		$media_gap           = $attrs['mediaGap'] ?? [];
		$content_alignment   = $attrs['contentAlignment'] ?? [];
		$icon_size           = $attrs['iconSize'] ?? [];
		$image_width         = $attrs['imageWidth'] ?? [];
		$icon_color          = $attrs['iconColor'] ?? [];
		$icon_color_hover    = $attrs['iconColorHover'] ?? [];
		$icon_bg_color       = $attrs['iconBgColor'] ?? [];
		$icon_bg_color_hover = $attrs['iconBgColorHover'] ?? [];
		$icon_padding        = $attrs['iconPadding'] ?? [];
		$icon_border_radius  = $attrs['iconBorderRadius'] ?? [];
		$icon_border_width   = $attrs['iconBorderWidth'] ?? [];
		$icon_border_color   = $attrs['iconBorderColor'] ?? [];
		$icon_border_hover   = $attrs['iconBorderColorHover'] ?? [];
		$title_color         = $attrs['titleColor'] ?? [];
		$title_color_hover   = $attrs['titleColorHover'] ?? [];
		$title_typography    = $attrs['titleTypography'] ?? [];
		$title_margin        = $attrs['titleMargin'] ?? [];
		$desc_color          = $attrs['descColor'] ?? [];
		$desc_color_hover    = $attrs['descColorHover'] ?? [];
		$desc_typography     = $attrs['descTypography'] ?? [];
		$desc_margin         = $attrs['descMargin'] ?? [];
		$cta_text_color      = $attrs['ctaTextColor'] ?? [];
		$cta_text_hover      = $attrs['ctaTextColorHover'] ?? [];
		$cta_bg_color        = $attrs['ctaBgColor'] ?? [];
		$cta_bg_hover        = $attrs['ctaBgColorHover'] ?? [];
		$cta_padding         = $attrs['ctaPadding'] ?? [];
		$cta_border          = $attrs['ctaBorder'] ?? [];
		$cta_border_hover    = $attrs['ctaBorderHover'] ?? [];
		$cta_typography      = $attrs['ctaTypography'] ?? [];
		$image_overlay       = $attrs['imageOverlay'] ?? [];
		$media_shadow        = $attrs['mediaShadow'] ?? [];

		$is_styled_view = in_array( $media_view, [ 'stacked', 'framed', 'custom' ], true );

		// =====================================================================
		// DESKTOP STYLES (base)
		// =====================================================================

		// Root selector: layout, colors (variables), advanced tab.
		$css->set_selector( $selector );
		self::add_layout( $css, $layout, 'desktop' );
		self::add_gap( $css, $media_gap, 'desktop' );

		// Icon colors (variables on root).
		if ( 'icon' === $media_type ) {
			self::add_icon_colors( $css, $icon_color, $icon_color_hover, $icon_bg_color, $icon_bg_color_hover );
			self::add_icon_border_color( $css, $icon_border_color, $icon_border_hover );
			self::add_icon_size( $css, $icon_size, 'desktop' );
		} elseif ( 'image' === $media_type ) {
			self::add_image_width( $css, $image_width, 'desktop' );
			self::add_image_overlay( $css, $image_overlay );
		}

		// Title colors (variables on root).
		self::add_title_colors( $css, $title_color, $title_color_hover );

		// Description colors (variables on root).
		self::add_desc_colors( $css, $desc_color, $desc_color_hover );

		// Button colors (variables on root).
		self::add_button_colors( $css, $cta_text_color, $cta_text_hover, $cta_bg_color, $cta_bg_hover );
		self::add_button_border_color( $css, $cta_border, $cta_border_hover );

		// Content alignment (variable on root).
		self::add_content_alignment( $css, $content_alignment, 'desktop' );

		// Advanced tab CSS.
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'desktop', true );

		// Media selector: padding, border-width, border-radius, shadow (direct properties).
		$css->set_selector( $media_selector );
		CSS_Helpers::add_shadow( $css, $media_shadow );

		if ( 'icon' === $media_type && $is_styled_view ) {
			CSS_Helpers::add_padding_var( $css, $icon_padding['desktop'] ?? [], 'infocard-icon' );
			CSS_Helpers::add_border_width( $css, $icon_border_width['desktop'] ?? [] );
			if ( 'circle' !== $media_shape ) {
				CSS_Helpers::add_border_radius( $css, $icon_border_radius['desktop'] ?? [] );
			}
		}

		// Title selector: typography, margin (direct properties).
		$css->set_selector( $title_selector );
		CSS_Helpers::add_typography_css( $css, $title_typography, 'desktop' );
		self::add_margin_prop( $css, $title_margin, 'desktop' );

		// Description selector: typography, margin (direct properties).
		$css->set_selector( $desc_selector );
		CSS_Helpers::add_typography_css( $css, $desc_typography, 'desktop' );
		self::add_margin_prop( $css, $desc_margin, 'desktop' );

		// Button selector: typography, padding, border (direct properties).
		$css->set_selector( $btn_selector );
		CSS_Helpers::add_typography_css( $css, $cta_typography, 'desktop' );
		CSS_Helpers::add_padding_var( $css, $cta_padding['desktop'] ?? [], 'infocard-btn' );
		self::add_button_border_props( $css, $cta_border, 'desktop' );

		// =====================================================================
		// TABLET STYLES
		// =====================================================================
		$css->start_media_query( 'tablet' );

		// Root selector.
		$css->set_selector( $selector );
		self::add_layout( $css, $layout, 'tablet' );
		self::add_gap( $css, $media_gap, 'tablet' );
		self::add_content_alignment( $css, $content_alignment, 'tablet' );

		if ( 'icon' === $media_type ) {
			self::add_icon_size( $css, $icon_size, 'tablet' );
		} elseif ( 'image' === $media_type ) {
			self::add_image_width( $css, $image_width, 'tablet' );
		}

		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'tablet', false );

		// Media selector.
		if ( 'icon' === $media_type && $is_styled_view ) {
			$css->set_selector( $media_selector );
			CSS_Helpers::add_padding_var( $css, $icon_padding['tablet'] ?? [], 'infocard-icon' );
			CSS_Helpers::add_border_width( $css, $icon_border_width['tablet'] ?? [] );
			if ( 'circle' !== $media_shape ) {
				CSS_Helpers::add_border_radius( $css, $icon_border_radius['tablet'] ?? [] );
			}
		}

		// Title selector.
		$css->set_selector( $title_selector );
		CSS_Helpers::add_typography_css( $css, $title_typography, 'tablet' );
		self::add_margin_prop( $css, $title_margin, 'tablet' );

		// Description selector.
		$css->set_selector( $desc_selector );
		CSS_Helpers::add_typography_css( $css, $desc_typography, 'tablet' );
		self::add_margin_prop( $css, $desc_margin, 'tablet' );

		// Button selector.
		$css->set_selector( $btn_selector );
		CSS_Helpers::add_typography_css( $css, $cta_typography, 'tablet' );
		CSS_Helpers::add_padding_var( $css, $cta_padding['tablet'] ?? [], 'infocard-btn' );
		self::add_button_border_props( $css, $cta_border, 'tablet' );

		$css->end_media_query();

		// =====================================================================
		// MOBILE STYLES
		// =====================================================================
		$css->start_media_query( 'mobile' );

		// Root selector.
		$css->set_selector( $selector );
		self::add_layout( $css, $layout, 'mobile' );
		self::add_gap( $css, $media_gap, 'mobile' );
		self::add_content_alignment( $css, $content_alignment, 'mobile' );

		if ( 'icon' === $media_type ) {
			self::add_icon_size( $css, $icon_size, 'mobile' );
		} elseif ( 'image' === $media_type ) {
			self::add_image_width( $css, $image_width, 'mobile' );
		}

		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'mobile', false );

		// Media selector.
		if ( 'icon' === $media_type && $is_styled_view ) {
			$css->set_selector( $media_selector );
			CSS_Helpers::add_padding_var( $css, $icon_padding['mobile'] ?? [], 'infocard-icon' );
			CSS_Helpers::add_border_width( $css, $icon_border_width['mobile'] ?? [] );
			if ( 'circle' !== $media_shape ) {
				CSS_Helpers::add_border_radius( $css, $icon_border_radius['mobile'] ?? [] );
			}
		}

		// Title selector.
		$css->set_selector( $title_selector );
		CSS_Helpers::add_typography_css( $css, $title_typography, 'mobile' );
		self::add_margin_prop( $css, $title_margin, 'mobile' );

		// Description selector.
		$css->set_selector( $desc_selector );
		CSS_Helpers::add_typography_css( $css, $desc_typography, 'mobile' );
		self::add_margin_prop( $css, $desc_margin, 'mobile' );

		// Button selector.
		$css->set_selector( $btn_selector );
		CSS_Helpers::add_typography_css( $css, $cta_typography, 'mobile' );
		CSS_Helpers::add_padding_var( $css, $cta_padding['mobile'] ?? [], 'infocard-btn' );
		self::add_button_border_props( $css, $cta_border, 'mobile' );

		$css->end_media_query();

		// =====================================================================
		// DARK MODE STYLES
		// =====================================================================
		self::add_dark_mode(
			$css,
			$selector,
			$media_selector,
			$media_type,
			$icon_color,
			$icon_color_hover,
			$icon_bg_color,
			$icon_bg_color_hover,
			$icon_border_color,
			$icon_border_hover,
			$title_color,
			$title_color_hover,
			$desc_color,
			$desc_color_hover,
			$cta_text_color,
			$cta_text_hover,
			$cta_bg_color,
			$cta_bg_hover,
			$cta_border,
			$cta_border_hover,
			$image_overlay,
			$media_shadow
		);

		CSS_Helpers::add_advanced_tab_dark_mode( $css, $selector, $attrs );

		// =====================================================================
		// HOVER STATES
		// =====================================================================
		CSS_Helpers::add_advanced_tab_hover_css( $css, $selector, $attrs );
	}

	/**
	 * Add layout CSS variables.
	 *
	 * @param CSS_Builder $css    CSS generator.
	 * @param array                    $layout Layout attribute.
	 * @param string                   $device Device key.
	 */
	private static function add_layout( $css, $layout, $device ) {
		$device_layout = $layout[ $device ] ?? [];

		if ( ! empty( $device_layout['direction'] ) ) {
			$css->add_variable( 'direction', $device_layout['direction'] );
		}

		if ( ! empty( $device_layout['alignItems'] ) ) {
			$css->add_variable( 'align', $device_layout['alignItems'] );
		}
	}

	/**
	 * Add gap CSS variable.
	 *
	 * @param CSS_Builder $css    CSS generator.
	 * @param array                    $gap    Gap attribute.
	 * @param string                   $device Device key.
	 */
	private static function add_gap( $css, $gap, $device ) {
		$device_gap = $gap[ $device ] ?? [];
		$value      = $device_gap['value'] ?? '';

		if ( '' === $value ) {
			return;
		}

		$unit = $device_gap['unit'] ?? $gap['desktop']['unit'] ?? 'px';
		$css->add_variable( 'gap', $value . $unit );
	}

	/**
	 * Add content alignment CSS variable.
	 *
	 * @param CSS_Builder $css       CSS generator.
	 * @param array                    $alignment Alignment attribute.
	 * @param string                   $device    Device key.
	 */
	private static function add_content_alignment( $css, $alignment, $device ) {
		$value = $alignment[ $device ] ?? '';

		if ( '' !== $value ) {
			$css->add_variable( 'content-align', $value );
		}
	}

	/**
	 * Add media size CSS variables for icons.
	 *
	 * @param CSS_Builder $css    CSS generator.
	 * @param array                    $size   Size attribute.
	 * @param string                   $device Device key.
	 */
	private static function add_icon_size( $css, $size, $device ) {
		$device_size = $size[ $device ] ?? [];
		$width       = $device_size['width'] ?? '';
		$height      = $device_size['height'] ?? '';
		$unit        = $device_size['unit'] ?? $size['desktop']['unit'] ?? 'px';

		// Output width if value is set.
		if ( '' !== $width ) {
			$css->add_variable( 'icon-w', $width . $unit );
		}
		// Output height if value is set.
		if ( '' !== $height ) {
			$css->add_variable( 'icon-h', $height . $unit );
		}
	}

	/**
	 * Add image width CSS variable.
	 *
	 * @param CSS_Builder $css    CSS generator.
	 * @param array                    $width  Width attribute.
	 * @param string                   $device Device key.
	 */
	private static function add_image_width( $css, $width, $device ) {
		$device_width = $width[ $device ] ?? [];
		$value        = $device_width['value'] ?? '';
		$unit         = $device_width['unit'] ?? $width['desktop']['unit'] ?? '%';

		// Output image width if value is set.
		if ( '' !== $value ) {
			$css->add_variable( 'img-w', $value . $unit );
		}
	}

	/**
	 * Add image overlay CSS variables.
	 *
	 * @param CSS_Builder $css     CSS generator.
	 * @param array                    $overlay Overlay attribute.
	 */
	private static function add_image_overlay( $css, $overlay ) {
		$gradient = $overlay['gradient']['light'] ?? '';
		$opacity  = $overlay['opacity'] ?? '';

		if ( ! empty( $gradient ) ) {
			$css->add_variable( 'img-overlay', $gradient );
		}
		if ( '' !== $opacity ) {
			$css->add_variable( 'img-overlay-opacity', $opacity );
		}
	}

	/**
	 * Add icon colors CSS variables.
	 *
	 * @param CSS_Builder $css            CSS generator.
	 * @param array                    $color          Color.
	 * @param array                    $color_hover    Color hover.
	 * @param array                    $bg_color       Background color.
	 * @param array                    $bg_color_hover Background color hover.
	 */
	private static function add_icon_colors( $css, $color, $color_hover, $bg_color, $bg_color_hover ) {
		if ( ! empty( $color['light'] ) ) {
			$css->add_variable( 'icon-color', $color['light'] );
		}
		if ( ! empty( $color_hover['light'] ) ) {
			$css->add_variable( 'icon-color-hover', $color_hover['light'] );
		}
		if ( ! empty( $bg_color['light'] ) ) {
			$css->add_variable( 'icon-bg', $bg_color['light'] );
		}
		if ( ! empty( $bg_color_hover['light'] ) ) {
			$css->add_variable( 'icon-bg-hover', $bg_color_hover['light'] );
		}
	}

	/**
	 * Add icon border color CSS variables.
	 *
	 * @param CSS_Builder $css         CSS generator.
	 * @param array                    $color       Color.
	 * @param array                    $color_hover Color hover.
	 */
	private static function add_icon_border_color( $css, $color, $color_hover ) {
		if ( ! empty( $color['light'] ) ) {
			$css->add_variable( 'icon-border-c', $color['light'] );
		}
		if ( ! empty( $color_hover['light'] ) ) {
			$css->add_variable( 'icon-border-c-hover', $color_hover['light'] );
		}
	}

	/**
	 * Add title colors CSS variables.
	 *
	 * @param CSS_Builder $css         CSS generator.
	 * @param array                    $color       Color.
	 * @param array                    $color_hover Color hover.
	 */
	private static function add_title_colors( $css, $color, $color_hover ) {
		if ( ! empty( $color['light'] ) ) {
			$css->add_variable( 'title-color', $color['light'] );
		}
		if ( ! empty( $color_hover['light'] ) ) {
			$css->add_variable( 'title-color-hover', $color_hover['light'] );
		}
	}

	/**
	 * Add description colors CSS variables.
	 *
	 * @param CSS_Builder $css         CSS generator.
	 * @param array                    $color       Color.
	 * @param array                    $color_hover Color hover.
	 */
	private static function add_desc_colors( $css, $color, $color_hover ) {
		if ( ! empty( $color['light'] ) ) {
			$css->add_variable( 'desc-color', $color['light'] );
		}
		if ( ! empty( $color_hover['light'] ) ) {
			$css->add_variable( 'desc-color-hover', $color_hover['light'] );
		}
	}



	/**
	 * Add margin-bottom as direct property.
	 *
	 * @param CSS_Builder $css    CSS generator.
	 * @param array                    $margin Margin attribute.
	 * @param string                   $device Device key.
	 */
	private static function add_margin_prop( $css, $margin, $device ) {
		$device_margin = $margin[ $device ] ?? [];
		$value         = $device_margin['value'] ?? '';

		if ( '' === $value ) {
			return;
		}

		$unit = $device_margin['unit'] ?? $margin['desktop']['unit'] ?? 'px';
		$css->add_property( 'margin-bottom', $value . $unit );
	}

	/**
	 * Add button colors CSS variables.
	 *
	 * @param CSS_Builder $css         CSS generator.
	 * @param array                    $color       Text color.
	 * @param array                    $color_hover Text color hover.
	 * @param array                    $bg          Background color.
	 * @param array                    $bg_hover    Background color hover.
	 */
	private static function add_button_colors( $css, $color, $color_hover, $bg, $bg_hover ) {
		if ( ! empty( $color['light'] ) ) {
			$css->add_variable( 'btn-color', $color['light'] );
		}
		if ( ! empty( $color_hover['light'] ) ) {
			$css->add_variable( 'btn-color-hover', $color_hover['light'] );
		}
		if ( ! empty( $bg['light'] ) ) {
			$css->add_variable( 'btn-bg', $bg['light'] );
		}
		if ( ! empty( $bg_hover['light'] ) ) {
			$css->add_variable( 'btn-bg-hover', $bg_hover['light'] );
		}
	}

	/**
	 * Add button border color CSS variables.
	 *
	 * @param CSS_Builder $css          CSS generator.
	 * @param array                    $border       Border attribute.
	 * @param array                    $border_hover Border hover attribute.
	 */
	private static function add_button_border_color( $css, $border, $border_hover ) {
		$desktop = $border['desktop'] ?? [];

		if ( ! empty( $desktop['color']['light'] ) ) {
			$css->add_variable( 'btn-border-color', $desktop['color']['light'] );
		}

		$desktop_hover = $border_hover['desktop'] ?? [];

		if ( ! empty( $desktop_hover['color']['light'] ) ) {
			$css->add_variable( 'btn-border-color-hover', $desktop_hover['color']['light'] );
		}
	}

	/**
	 * Add button border as direct properties (style, width, radius).
	 * Color is handled separately as CSS variable.
	 *
	 * @param CSS_Builder $css    CSS generator.
	 * @param array                    $border Border attribute.
	 * @param string                   $device Device key.
	 */
	private static function add_button_border_props( $css, $border, $device ) {
		$device_border = $border[ $device ] ?? [];

		// Border style.
		if ( ! empty( $device_border['style'] ) ) {
			$css->add_property( 'border-style', $device_border['style'] );
		}

		// Border width.
		if ( ! empty( $device_border['width'] ) ) {
			$width = CSS_Helpers::build_spacing_shorthand( $device_border['width'] );
			if ( $width ) {
				$css->add_property( 'border-width', $width );
			}
		}

		// Border radius.
		if ( ! empty( $device_border['radius'] ) ) {
			$radius = CSS_Helpers::build_radius_shorthand( $device_border['radius'] );
			if ( $radius ) {
				$css->add_property( 'border-radius', $radius );
			}
		}
	}

	/**
	 * Add dark mode CSS.
	 *
	 * @param CSS_Builder $css               CSS generator.
	 * @param string                   $selector          Block selector.
	 * @param string                   $media_selector    Media element selector.
	 * @param string                   $media_type        Media type.
	 * @param array                    $icon_color        Icon color.
	 * @param array                    $icon_color_hover  Icon color hover.
	 * @param array                    $icon_bg_color     Icon bg color.
	 * @param array                    $icon_bg_hover     Icon bg hover.
	 * @param array                    $icon_border_color Icon border color.
	 * @param array                    $icon_border_hover Icon border hover.
	 * @param array                    $title_color       Title color.
	 * @param array                    $title_color_hover Title color hover.
	 * @param array                    $desc_color        Desc color.
	 * @param array                    $desc_color_hover  Desc color hover.
	 * @param array                    $cta_text_color    CTA text color.
	 * @param array                    $cta_text_hover    CTA text hover.
	 * @param array                    $cta_bg_color      CTA bg color.
	 * @param array                    $cta_bg_hover      CTA bg hover.
	 * @param array                    $cta_border        CTA border.
	 * @param array                    $cta_border_hover  CTA border hover.
	 * @param array                    $image_overlay     Image overlay.
	 * @param array                    $media_shadow      Media shadow.
	 */
	private static function add_dark_mode( $css, $selector, $media_selector, $media_type, $icon_color, $icon_color_hover, $icon_bg_color, $icon_bg_hover, $icon_border_color, $icon_border_hover, $title_color, $title_color_hover, $desc_color, $desc_color_hover, $cta_text_color, $cta_text_hover, $cta_bg_color, $cta_bg_hover, $cta_border, $cta_border_hover, $image_overlay = [], $media_shadow = [] ) {
		// Collect dark mode values.
		$dark_icon_color        = 'icon' === $media_type ? ( $icon_color['dark'] ?? '' ) : '';
		$dark_icon_color_hover  = 'icon' === $media_type ? ( $icon_color_hover['dark'] ?? '' ) : '';
		$dark_icon_bg           = 'icon' === $media_type ? ( $icon_bg_color['dark'] ?? '' ) : '';
		$dark_icon_bg_hover     = 'icon' === $media_type ? ( $icon_bg_hover['dark'] ?? '' ) : '';
		$dark_icon_border       = 'icon' === $media_type ? ( $icon_border_color['dark'] ?? '' ) : '';
		$dark_icon_border_hover = 'icon' === $media_type ? ( $icon_border_hover['dark'] ?? '' ) : '';
		$dark_title             = $title_color['dark'] ?? '';
		$dark_title_hover       = $title_color_hover['dark'] ?? '';
		$dark_desc              = $desc_color['dark'] ?? '';
		$dark_desc_hover        = $desc_color_hover['dark'] ?? '';
		$dark_cta_text          = $cta_text_color['dark'] ?? '';
		$dark_cta_text_hover    = $cta_text_hover['dark'] ?? '';
		$dark_cta_bg            = $cta_bg_color['dark'] ?? '';
		$dark_cta_bg_hover      = $cta_bg_hover['dark'] ?? '';
		$dark_cta_border        = $cta_border['desktop']['color']['dark'] ?? '';
		$dark_cta_border_hover  = $cta_border_hover['desktop']['color']['dark'] ?? '';
		$dark_img_overlay       = 'image' === $media_type ? ( $image_overlay['gradient']['dark'] ?? '' ) : '';
		$dark_media_shadow      = CSS_Helpers::get_dark_color( $media_shadow['color'] ?? '' );

		$has_dark_styles = $dark_icon_color || $dark_icon_color_hover || $dark_icon_bg || $dark_icon_bg_hover ||
			$dark_icon_border || $dark_icon_border_hover ||
			$dark_title || $dark_title_hover || $dark_desc || $dark_desc_hover ||
			$dark_cta_text || $dark_cta_text_hover || $dark_cta_bg || $dark_cta_bg_hover ||
			$dark_cta_border || $dark_cta_border_hover || $dark_img_overlay || $dark_media_shadow;

		if ( ! $has_dark_styles ) {
			return;
		}

		CSS_Helpers::add_dark_mode_css(
			$css,
			$selector,
			function ( $css ) use (
				$dark_icon_color,
				$dark_icon_color_hover,
				$dark_icon_bg,
				$dark_icon_bg_hover,
				$dark_icon_border,
				$dark_icon_border_hover,
				$dark_title,
				$dark_title_hover,
				$dark_desc,
				$dark_desc_hover,
				$dark_cta_text,
				$dark_cta_text_hover,
				$dark_cta_bg,
				$dark_cta_bg_hover,
				$dark_cta_border,
				$dark_cta_border_hover,
				$dark_img_overlay,
				$dark_media_shadow,
				$media_shadow,
				$media_selector
			) {
				// Icon dark mode.
				if ( $dark_icon_color ) {
					$css->add_variable( 'icon-color', $dark_icon_color );
				}
				if ( $dark_icon_color_hover ) {
					$css->add_variable( 'icon-color-hover', $dark_icon_color_hover );
				}
				if ( $dark_icon_bg ) {
					$css->add_variable( 'icon-bg', $dark_icon_bg );
				}
				if ( $dark_icon_bg_hover ) {
					$css->add_variable( 'icon-bg-hover', $dark_icon_bg_hover );
				}
				if ( $dark_icon_border ) {
					$css->add_variable( 'icon-border-c', $dark_icon_border );
				}
				if ( $dark_icon_border_hover ) {
					$css->add_variable( 'icon-border-c-hover', $dark_icon_border_hover );
				}

				// Title dark mode.
				if ( $dark_title ) {
					$css->add_variable( 'title-color', $dark_title );
				}
				if ( $dark_title_hover ) {
					$css->add_variable( 'title-color-hover', $dark_title_hover );
				}

				// Description dark mode.
				if ( $dark_desc ) {
					$css->add_variable( 'desc-color', $dark_desc );
				}
				if ( $dark_desc_hover ) {
					$css->add_variable( 'desc-color-hover', $dark_desc_hover );
				}

				// Button dark mode.
				if ( $dark_cta_text ) {
					$css->add_variable( 'btn-color', $dark_cta_text );
				}
				if ( $dark_cta_text_hover ) {
					$css->add_variable( 'btn-color-hover', $dark_cta_text_hover );
				}
				if ( $dark_cta_bg ) {
					$css->add_variable( 'btn-bg', $dark_cta_bg );
				}
				if ( $dark_cta_bg_hover ) {
					$css->add_variable( 'btn-bg-hover', $dark_cta_bg_hover );
				}
				if ( $dark_cta_border ) {
					$css->add_variable( 'btn-border-color', $dark_cta_border );
				}
				if ( $dark_cta_border_hover ) {
					$css->add_variable( 'btn-border-color-hover', $dark_cta_border_hover );
				}

				// Image overlay dark mode.
				if ( $dark_img_overlay ) {
					$css->add_variable( 'img-overlay', $dark_img_overlay );
				}

				// Media shadow dark mode.
				if ( $dark_media_shadow && ! empty( $media_shadow['enabled'] ) ) {
					$css->set_selector( $media_selector );
					$dark_shadow          = $media_shadow;
					$dark_shadow['color'] = $dark_media_shadow;
					$shadow_value         = CSS_Helpers::build_box_shadow( $dark_shadow );
					if ( $shadow_value ) {
						$css->add_property( 'box-shadow', $shadow_value );
					}
				}
			}
		);
	}
}
