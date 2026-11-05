<?php
/**
 * Team Member Block CSS Generator.
 *
 * Generates CSS for the Team Member block using CSS_Helpers.
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
 * Team Member CSS Generator class.
 */
class Team_Member_CSS {

	/**
	 * Generate CSS for Team Member block.
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
		$selector         = '.blockflow-team-member.blockflow-team-member-' . $block_id;
		$image_selector   = $selector . ' .blockflow-team-member__image';
		$content_selector = $selector . ' .blockflow-team-member__content';
		$social_selector  = $selector . ' .blockflow-team-member__social';
		$name_selector    = $selector . ' .blockflow-team-member__name';
		$desc_selector    = $selector . ' .blockflow-team-member__desc';
		$meta_selector    = $selector . ' .blockflow-team-member__meta';

		// Extract attributes.
		$content_alignment      = $attrs['contentAlignment'] ?? [];
		$elements_gap           = $attrs['elementsGap'] ?? [];
		$image_gap              = $attrs['imageGap'] ?? [];
		$image_width            = $attrs['imageWidth'] ?? [];
		$image_border           = $attrs['imageBorder'] ?? [];
		$image_border_hover     = $attrs['imageBorderHover'] ?? [];
		$image_box_shadow       = $attrs['imageBoxShadow'] ?? [];
		$image_box_shadow_hover = $attrs['imageBoxShadowHover'] ?? [];
		$name_color             = $attrs['nameColor'] ?? [];
		$name_typography        = $attrs['nameTypography'] ?? [];
		$desc_color             = $attrs['descColor'] ?? [];
		$desc_typography        = $attrs['descTypography'] ?? [];
		$meta_color             = $attrs['metaColor'] ?? [];
		$meta_typography        = $attrs['metaTypography'] ?? [];
		$social_size            = $attrs['socialSize'] ?? [];
		$social_gap             = $attrs['socialGap'] ?? [];
		$social_top_spacing     = $attrs['socialTopSpacing'] ?? [];
		$social_primary_color   = $attrs['socialPrimaryColor'] ?? [];
		$social_secondary_color = $attrs['socialSecondaryColor'] ?? [];
		$social_primary_hover   = $attrs['socialPrimaryColorHover'] ?? [];
		$social_secondary_hover = $attrs['socialSecondaryColorHover'] ?? [];

		// Inner content attributes (avatar/overlay mode).
		$inner_background    = $attrs['innerBackground'] ?? [];
		$inner_padding       = $attrs['innerPadding'] ?? [];
		$inner_border_radius = $attrs['innerBorderRadius'] ?? [];
		$inner_box_shadow    = $attrs['innerBoxShadow'] ?? [];
		$avatar_overlap      = $attrs['avatarOverlap'] ?? [];

		// =====================================================================
		// DESKTOP STYLES (base)
		// =====================================================================
		$css->set_selector( $selector );

		// Elements gap (content gap).
		self::add_gap( $css, $elements_gap, 'desktop' );

		// Image gap.
		self::add_image_gap( $css, $image_gap, 'desktop' );

		// Inner content variables.
		self::add_inner_background( $css, $inner_background );
		CSS_Helpers::add_padding_var( $css, $inner_padding['desktop'] ?? [], 'tm-inner' );
		self::add_inner_radius( $css, $inner_border_radius, 'desktop' );
		self::add_avatar_overlap( $css, $avatar_overlap, 'desktop' );
		self::add_inner_box_shadow( $css, $inner_box_shadow );

		// Text colors.
		if ( ! empty( $name_color['light'] ) ) {
			$css->add_variable( 'tm-name-color', $name_color['light'] );
		}
		if ( ! empty( $desc_color['light'] ) ) {
			$css->add_variable( 'tm-desc-color', $desc_color['light'] );
		}
		if ( ! empty( $meta_color['light'] ) ) {
			$css->add_variable( 'tm-meta-color', $meta_color['light'] );
		}

		// Social icons.
		self::add_social_size( $css, $social_size, 'desktop' );
		self::add_social_gap( $css, $social_gap, 'desktop' );
		self::add_social_top_spacing( $css, $social_top_spacing, 'desktop' );
		self::add_social_colors( $css, $social_primary_color, $social_secondary_color, $social_primary_hover, $social_secondary_hover );

		// Advanced tab CSS.
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'desktop', true );

		// Image selector: direct properties for child element.
		$css->set_selector( $image_selector );
		self::add_image_width( $css, $image_width, 'desktop' );
		self::add_image_border( $css, $image_border, 'desktop' );
		CSS_Helpers::add_shadow( $css, $image_box_shadow );

		// Name selector: typography.
		$css->set_selector( $name_selector );
		CSS_Helpers::add_typography_css( $css, $name_typography, 'desktop' );

		// Description selector: typography.
		$css->set_selector( $desc_selector );
		CSS_Helpers::add_typography_css( $css, $desc_typography, 'desktop' );

		// Meta selector: typography.
		$css->set_selector( $meta_selector );
		CSS_Helpers::add_typography_css( $css, $meta_typography, 'desktop' );

		// =====================================================================
		// TABLET STYLES
		// =====================================================================
		$css->start_media_query( 'tablet' );

		$css->set_selector( $selector );
		self::add_gap( $css, $elements_gap, 'tablet' );
		self::add_image_gap( $css, $image_gap, 'tablet' );
		CSS_Helpers::add_padding_var( $css, $inner_padding['tablet'] ?? [], 'tm-inner' );
		self::add_inner_radius( $css, $inner_border_radius, 'tablet' );
		self::add_avatar_overlap( $css, $avatar_overlap, 'tablet' );
		self::add_social_size( $css, $social_size, 'tablet' );
		self::add_social_gap( $css, $social_gap, 'tablet' );
		self::add_social_top_spacing( $css, $social_top_spacing, 'tablet' );
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'tablet', false );

		// Responsive alignment.
		self::add_alignment( $css, $content_alignment, 'tablet', $selector, $content_selector, $social_selector );

		$css->set_selector( $image_selector );
		self::add_image_width( $css, $image_width, 'tablet' );
		self::add_image_border( $css, $image_border, 'tablet' );

		$css->set_selector( $name_selector );
		CSS_Helpers::add_typography_css( $css, $name_typography, 'tablet' );

		$css->set_selector( $desc_selector );
		CSS_Helpers::add_typography_css( $css, $desc_typography, 'tablet' );

		$css->set_selector( $meta_selector );
		CSS_Helpers::add_typography_css( $css, $meta_typography, 'tablet' );

		$css->end_media_query();

		// =====================================================================
		// MOBILE STYLES
		// =====================================================================
		$css->start_media_query( 'mobile' );

		$css->set_selector( $selector );
		self::add_gap( $css, $elements_gap, 'mobile' );
		self::add_image_gap( $css, $image_gap, 'mobile' );
		CSS_Helpers::add_padding_var( $css, $inner_padding['mobile'] ?? [], 'tm-inner' );
		self::add_inner_radius( $css, $inner_border_radius, 'mobile' );
		self::add_avatar_overlap( $css, $avatar_overlap, 'mobile' );
		self::add_social_size( $css, $social_size, 'mobile' );
		self::add_social_gap( $css, $social_gap, 'mobile' );
		self::add_social_top_spacing( $css, $social_top_spacing, 'mobile' );
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'mobile', false );

		// Responsive alignment.
		self::add_alignment( $css, $content_alignment, 'mobile', $selector, $content_selector, $social_selector );

		$css->set_selector( $image_selector );
		self::add_image_width( $css, $image_width, 'mobile' );
		self::add_image_border( $css, $image_border, 'mobile' );

		$css->set_selector( $name_selector );
		CSS_Helpers::add_typography_css( $css, $name_typography, 'mobile' );

		$css->set_selector( $desc_selector );
		CSS_Helpers::add_typography_css( $css, $desc_typography, 'mobile' );

		$css->set_selector( $meta_selector );
		CSS_Helpers::add_typography_css( $css, $meta_typography, 'mobile' );

		$css->end_media_query();

		// =====================================================================
		// DARK MODE STYLES
		// =====================================================================
		self::add_dark_mode(
			$css,
			$selector,
			$image_selector,
			$name_color,
			$desc_color,
			$meta_color,
			$social_primary_color,
			$social_secondary_color,
			$social_primary_hover,
			$social_secondary_hover,
			$image_border,
			$image_box_shadow,
			$inner_background,
			$inner_box_shadow
		);

		CSS_Helpers::add_advanced_tab_dark_mode( $css, $selector, $attrs );

		// =====================================================================
		// HOVER STATES
		// =====================================================================
		self::add_image_hover( $css, $image_selector, $image_border_hover, $image_box_shadow_hover );
		CSS_Helpers::add_advanced_tab_hover_css( $css, $selector, $attrs );
	}

	/**
	 * Add gap CSS variable (content gap).
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

		$unit = $device_gap['unit'] ?? 'px';
		$css->add_variable( 'tm-gap', $value . $unit );
	}

	/**
	 * Add image gap CSS variable.
	 *
	 * @param CSS_Builder $css    CSS generator.
	 * @param array                    $gap    Gap attribute.
	 * @param string                   $device Device key.
	 */
	private static function add_image_gap( $css, $gap, $device ) {
		$device_gap = $gap[ $device ] ?? [];
		$value      = $device_gap['value'] ?? '';

		if ( '' === $value ) {
			return;
		}

		$unit = $device_gap['unit'] ?? 'px';
		$css->add_variable( 'tm-image-gap', $value . $unit );
	}

	/**
	 * Add image width as direct property (child element).
	 *
	 * @param CSS_Builder $css    CSS generator.
	 * @param array                    $width  Width attribute.
	 * @param string                   $device Device key.
	 */
	private static function add_image_width( $css, $width, $device ) {
		$device_width = $width[ $device ] ?? [];
		$value        = $device_width['value'] ?? '';

		if ( '' === $value ) {
			return;
		}

		$unit = $device_width['unit'] ?? '%';
		$css->add_property( 'width', $value . $unit );
	}

	/**
	 * Add image border as direct properties (child element).
	 *
	 * @param CSS_Builder $css    CSS generator.
	 * @param array                    $border Border attribute (responsive).
	 * @param string                   $device Device key.
	 */
	private static function add_image_border( $css, $border, $device ) {
		$device_border = $border[ $device ] ?? [];

		if ( empty( $device_border ) ) {
			return;
		}

		$is_desktop = 'desktop' === $device;

		// Border width.
		CSS_Helpers::add_border_width( $css, $device_border['width'] ?? [] );

		// Border style and color (desktop only - non-responsive).
		if ( $is_desktop ) {
			$style = $device_border['style'] ?? '';
			if ( $style ) {
				$css->add_property( 'border-style', $style );
			}

			$color = $device_border['color']['light'] ?? '';
			if ( $color ) {
				$css->add_property( 'border-color', $color );
			}
		}

		// Border radius.
		CSS_Helpers::add_border_radius( $css, $device_border['radius'] ?? [] );
	}

	/**
	 * Add inner background CSS variable.
	 *
	 * @param CSS_Builder $css        CSS generator.
	 * @param array                    $background Background color { light, dark }.
	 */
	private static function add_inner_background( $css, $background ) {
		$color = $background['light'] ?? '';
		if ( $color ) {
			$css->add_variable( 'tm-inner-bg', $color );
		}
	}

	/**
	 * Add inner border radius CSS variable.
	 *
	 * @param CSS_Builder $css    CSS generator.
	 * @param array                    $radius Radius attribute.
	 * @param string                   $device Device key.
	 */
	private static function add_inner_radius( $css, $radius, $device ) {
		$device_radius = $radius[ $device ] ?? [];
		$value         = $device_radius['value'] ?? '';

		if ( '' === $value ) {
			return;
		}

		$unit = $device_radius['unit'] ?? 'px';
		$css->add_variable( 'tm-inner-radius', $value . $unit );
	}

	/**
	 * Add avatar overlap CSS variable.
	 *
	 * @param CSS_Builder $css     CSS generator.
	 * @param array                    $overlap Overlap attribute.
	 * @param string                   $device  Device key.
	 */
	private static function add_avatar_overlap( $css, $overlap, $device ) {
		$device_overlap = $overlap[ $device ] ?? [];
		$value          = $device_overlap['value'] ?? '';

		if ( '' === $value ) {
			return;
		}

		$unit = $device_overlap['unit'] ?? 'px';
		$css->add_variable( 'tm-overlap', $value . $unit );
	}

	/**
	 * Add inner box shadow CSS variable.
	 *
	 * @param CSS_Builder $css    CSS generator.
	 * @param array                    $shadow Shadow attribute.
	 */
	private static function add_inner_box_shadow( $css, $shadow ) {
		if ( empty( $shadow['enabled'] ) ) {
			return;
		}

		$shadow_value = CSS_Helpers::build_box_shadow( $shadow );
		if ( $shadow_value ) {
			$css->add_variable( 'tm-inner-shadow', $shadow_value );
		}
	}

	/**
	 * Add social size CSS variable.
	 *
	 * @param CSS_Builder $css    CSS generator.
	 * @param array                    $size   Size attribute.
	 * @param string                   $device Device key.
	 */
	private static function add_social_size( $css, $size, $device ) {
		$device_size = $size[ $device ] ?? [];
		$value       = $device_size['value'] ?? '';

		if ( '' === $value ) {
			return;
		}

		$unit = $device_size['unit'] ?? 'px';
		$css->add_variable( 'tm-social-size', $value . $unit );
	}

	/**
	 * Add social gap CSS variable.
	 *
	 * @param CSS_Builder $css    CSS generator.
	 * @param array                    $gap    Gap attribute.
	 * @param string                   $device Device key.
	 */
	private static function add_social_gap( $css, $gap, $device ) {
		$device_gap = $gap[ $device ] ?? [];
		$value      = $device_gap['value'] ?? '';

		if ( '' === $value ) {
			return;
		}

		$unit = $device_gap['unit'] ?? 'px';
		$css->add_variable( 'tm-social-gap', $value . $unit );
	}

	/**
	 * Add social top spacing CSS variable.
	 *
	 * @param CSS_Builder $css     CSS generator.
	 * @param array                    $spacing Spacing attribute.
	 * @param string                   $device  Device key.
	 */
	private static function add_social_top_spacing( $css, $spacing, $device ) {
		$device_spacing = $spacing[ $device ] ?? [];
		$value          = $device_spacing['value'] ?? '';

		if ( '' === $value ) {
			return;
		}

		$unit = $device_spacing['unit'] ?? 'px';
		$css->add_variable( 'tm-social-top', $value . $unit );
	}

	/**
	 * Add social colors CSS variables.
	 *
	 * @param CSS_Builder $css             CSS generator.
	 * @param array                    $primary_color   Primary color { light, dark }.
	 * @param array                    $secondary_color Secondary color { light, dark }.
	 * @param array                    $primary_hover   Primary hover color { light, dark }.
	 * @param array                    $secondary_hover Secondary hover color { light, dark }.
	 */
	private static function add_social_colors( $css, $primary_color, $secondary_color, $primary_hover, $secondary_hover ) {
		if ( ! empty( $primary_color['light'] ) ) {
			$css->add_variable( 'tm-social-color', $primary_color['light'] );
		}
		if ( ! empty( $secondary_color['light'] ) ) {
			$css->add_variable( 'tm-social-bg', $secondary_color['light'] );
		}
		if ( ! empty( $primary_hover['light'] ) ) {
			$css->add_variable( 'tm-social-color-hover', $primary_hover['light'] );
		}
		if ( ! empty( $secondary_hover['light'] ) ) {
			$css->add_variable( 'tm-social-bg-hover', $secondary_hover['light'] );
		}
	}

	/**
	 * Add dark mode CSS.
	 *
	 * @param CSS_Builder $css              CSS generator.
	 * @param string                   $selector         Block selector.
	 * @param string                   $image_selector   Image selector.
	 * @param array                    $name_color       Name color { light, dark }.
	 * @param array                    $desc_color       Description color { light, dark }.
	 * @param array                    $meta_color       Meta color { light, dark }.
	 * @param array                    $social_primary   Social primary color { light, dark }.
	 * @param array                    $social_secondary Social secondary color { light, dark }.
	 * @param array                    $social_pri_hover Social primary hover { light, dark }.
	 * @param array                    $social_sec_hover Social secondary hover { light, dark }.
	 * @param array                    $image_border     Image border (responsive).
	 * @param array                    $image_shadow     Image shadow.
	 * @param array                    $inner_background Inner background color { light, dark }.
	 * @param array                    $inner_shadow     Inner box shadow.
	 */
	private static function add_dark_mode( $css, $selector, $image_selector, $name_color, $desc_color, $meta_color, $social_primary, $social_secondary, $social_pri_hover, $social_sec_hover, $image_border, $image_shadow, $inner_background, $inner_shadow ) {
		// Collect dark mode values.
		$dark_name         = $name_color['dark'] ?? '';
		$dark_desc         = $desc_color['dark'] ?? '';
		$dark_meta         = $meta_color['dark'] ?? '';
		$dark_social_pri   = $social_primary['dark'] ?? '';
		$dark_social_sec   = $social_secondary['dark'] ?? '';
		$dark_social_pri_h = $social_pri_hover['dark'] ?? '';
		$dark_social_sec_h = $social_sec_hover['dark'] ?? '';
		$dark_img_border   = $image_border['desktop']['color']['dark'] ?? '';
		$dark_img_shadow   = $image_shadow['color']['dark'] ?? '';
		$dark_inner_bg     = $inner_background['dark'] ?? '';
		$dark_inner_shadow = $inner_shadow['color']['dark'] ?? '';

		$has_dark = $dark_name || $dark_desc || $dark_meta || $dark_social_pri || $dark_social_sec ||
					$dark_social_pri_h || $dark_social_sec_h || $dark_img_border || $dark_img_shadow ||
					$dark_inner_bg || $dark_inner_shadow;

		if ( ! $has_dark ) {
			return;
		}

		CSS_Helpers::add_dark_mode_css(
			$css,
			$selector,
			function ( $css ) use ( $dark_name, $dark_desc, $dark_meta, $dark_social_pri, $dark_social_sec, $dark_social_pri_h, $dark_social_sec_h, $dark_img_border, $dark_img_shadow, $image_shadow, $image_selector, $dark_inner_bg, $dark_inner_shadow, $inner_shadow ) {
				// Text colors.
				if ( $dark_name ) {
					$css->add_variable( 'tm-name-color', $dark_name );
				}
				if ( $dark_desc ) {
					$css->add_variable( 'tm-desc-color', $dark_desc );
				}
				if ( $dark_meta ) {
					$css->add_variable( 'tm-meta-color', $dark_meta );
				}

				// Social colors.
				if ( $dark_social_pri ) {
					$css->add_variable( 'tm-social-color', $dark_social_pri );
				}
				if ( $dark_social_sec ) {
					$css->add_variable( 'tm-social-bg', $dark_social_sec );
				}
				if ( $dark_social_pri_h ) {
					$css->add_variable( 'tm-social-color-hover', $dark_social_pri_h );
				}
				if ( $dark_social_sec_h ) {
					$css->add_variable( 'tm-social-bg-hover', $dark_social_sec_h );
				}

				// Inner background.
				if ( $dark_inner_bg ) {
					$css->add_variable( 'tm-inner-bg', $dark_inner_bg );
				}

				// Inner box shadow dark mode.
				if ( $dark_inner_shadow && ! empty( $inner_shadow['enabled'] ) ) {
					$dark_shadow_obj          = $inner_shadow;
					$dark_shadow_obj['color'] = $dark_inner_shadow;
					$shadow_value             = CSS_Helpers::build_box_shadow( $dark_shadow_obj );
					if ( $shadow_value ) {
						$css->add_variable( 'tm-inner-shadow', $shadow_value );
					}
				}

				// Image border dark mode (direct property on child).
				if ( $dark_img_border ) {
					$css->set_selector( $image_selector );
					$css->add_property( 'border-color', $dark_img_border );
				}

				// Image shadow dark mode.
				if ( $dark_img_shadow && ! empty( $image_shadow['enabled'] ) ) {
					$css->set_selector( $image_selector );
					$dark_shadow          = $image_shadow;
					$dark_shadow['color'] = $dark_img_shadow;
					$shadow_value         = CSS_Helpers::build_box_shadow( $dark_shadow );
					if ( $shadow_value ) {
						$css->add_property( 'box-shadow', $shadow_value );
					}
				}
			}
		);
	}

	/**
	 * Add image hover CSS (border + shadow).
	 *
	 * @param CSS_Builder $css           CSS generator.
	 * @param string                   $selector      Image selector.
	 * @param array                    $border_hover  Border hover attribute.
	 * @param array                    $shadow_hover  Box shadow hover attribute.
	 */
	private static function add_image_hover( $css, $selector, $border_hover, $shadow_hover ) {
		$has_border_hover = ! empty( $border_hover['desktop'] );
		$has_shadow_hover = ! empty( $shadow_hover['enabled'] );

		if ( ! $has_border_hover && ! $has_shadow_hover ) {
			return;
		}

		$css->set_selector( $selector . ':hover' );

		// Border hover.
		if ( $has_border_hover ) {
			$desktop_border = $border_hover['desktop'] ?? [];

			// Border width.
			CSS_Helpers::add_border_width( $css, $desktop_border['width'] ?? [] );

			// Border style.
			$style = $desktop_border['style'] ?? '';
			if ( $style ) {
				$css->add_property( 'border-style', $style );
			}

			// Border color.
			$color = $desktop_border['color']['light'] ?? '';
			if ( $color ) {
				$css->add_property( 'border-color', $color );
			}

			// Border radius.
			CSS_Helpers::add_border_radius( $css, $desktop_border['radius'] ?? [] );
		}

		// Shadow hover.
		if ( $has_shadow_hover ) {
			$shadow_value = CSS_Helpers::build_box_shadow( $shadow_hover );
			if ( $shadow_value ) {
				$css->add_property( 'box-shadow', $shadow_value );
			}
		}
	}

	/**
	 * Add responsive content alignment CSS.
	 *
	 * Desktop alignment is handled by CSS classes (is-align-*).
	 * This method outputs CSS for tablet/mobile to override the class-based alignment.
	 *
	 * @param CSS_Builder $css              CSS generator.
	 * @param array                    $alignment        Alignment attribute { desktop, tablet, mobile }.
	 * @param string                   $device           Device key (tablet or mobile).
	 * @param string                   $selector         Block selector.
	 * @param string                   $content_selector Content selector.
	 * @param string                   $social_selector  Social selector.
	 */
	private static function add_alignment( $css, $alignment, $device, $selector, $content_selector, $social_selector ) {
		$device_align = $alignment[ $device ] ?? '';

		if ( '' === $device_align ) {
			return;
		}

		// Map alignment value to CSS properties.
		$align_items_map = [
			'left'   => 'flex-start',
			'center' => 'center',
			'right'  => 'flex-end',
		];

		$justify_content_map = [
			'left'   => 'flex-start',
			'center' => 'center',
			'right'  => 'flex-end',
		];

		$text_align   = $device_align;
		$align_items  = $align_items_map[ $device_align ] ?? 'center';
		$justify_cont = $justify_content_map[ $device_align ] ?? 'center';

		// Root selector: text-align + align-items.
		$css->set_selector( $selector );
		$css->add_property( 'text-align', $text_align );
		$css->add_property( 'align-items', $align_items );

		// Content selector: align-items.
		$css->set_selector( $content_selector );
		$css->add_property( 'align-items', $align_items );

		// Social selector: justify-content.
		$css->set_selector( $social_selector );
		$css->add_property( 'justify-content', $justify_cont );
	}
}
