<?php
/**
 * Video Helpers for BlockFlow.
 *
 * Provides utility methods for the Video block render.
 *
 * @package BlockFlow
 */

namespace ThemeRuby\BlockFlow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Video Helpers class.
 */
class Video_Helpers {

	/**
	 * Build embed URL for external video providers.
	 *
	 * @param string $source   Video source (youtube, vimeo, dailymotion).
	 * @param string $video_id Video ID.
	 * @param array  $options  Embed options.
	 * @return string Embed URL.
	 */
	public static function build_embed_url( string $source, string $video_id, array $options = [] ): string {
		// Sanitize video ID to prevent injection.
		$video_id = sanitize_text_field( $video_id );

		if ( empty( $video_id ) ) {
			return '';
		}

		$autoplay         = $options['autoplay'] ?? false;
		$mute             = $options['mute'] ?? false;
		$loop             = $options['loop'] ?? false;
		$controls         = $options['controls'] ?? true;
		$privacy_mode     = $options['privacy_mode'] ?? false;
		$captions         = $options['captions'] ?? false;
		$suggested_videos = $options['suggested_videos'] ?? 'channel';
		$start_time       = $options['start_time'] ?? 0;
		$end_time         = $options['end_time'] ?? 0;

		$params = [];

		switch ( $source ) {
			case 'youtube':
				$base_url = $privacy_mode
					? 'https://www.youtube-nocookie.com/embed/'
					: 'https://www.youtube.com/embed/';

				$params['autoplay']       = $autoplay ? '1' : '0';
				$params['mute']           = $mute ? '1' : '0';
				$params['loop']           = $loop ? '1' : '0';
				$params['controls']       = $controls ? '1' : '0';
				$params['modestbranding'] = '1';

				if ( $captions ) {
					$params['cc_load_policy'] = '1';
				}

				if ( 'channel' === $suggested_videos ) {
					$params['rel'] = '0';
				}

				if ( $start_time > 0 ) {
					$params['start'] = (string) absint( $start_time );
				}

				if ( $end_time > 0 ) {
					$params['end'] = (string) absint( $end_time );
				}

				if ( $loop ) {
					$params['playlist'] = $video_id;
				}

				return $base_url . $video_id . '?' . http_build_query( $params );

			case 'vimeo':
				$base_url = 'https://player.vimeo.com/video/';

				$params['autoplay'] = $autoplay ? '1' : '0';
				$params['muted']    = $mute ? '1' : '0';
				$params['loop']     = $loop ? '1' : '0';

				if ( ! $controls ) {
					$params['controls'] = '0';
				}

				if ( $privacy_mode ) {
					$params['dnt'] = '1';
				}

				if ( $start_time > 0 ) {
					$params['t'] = absint( $start_time ) . 's';
				}

				return $base_url . $video_id . '?' . http_build_query( $params );

			case 'dailymotion':
				$base_url = 'https://www.dailymotion.com/embed/video/';

				$params['autoplay'] = $autoplay ? '1' : '0';
				$params['mute']     = $mute ? '1' : '0';
				$params['loop']     = $loop ? '1' : '0';
				$params['controls'] = $controls ? '1' : '0';

				if ( $start_time > 0 ) {
					$params['start'] = (string) absint( $start_time );
				}

				return $base_url . $video_id . '?' . http_build_query( $params );

			default:
				return '';
		}
	}

	/**
	 * Get aspect ratio CSS value.
	 *
	 * @param string $ratio  Aspect ratio preset.
	 * @param array  $custom Custom aspect ratio values.
	 * @return string CSS aspect-ratio value.
	 */
	public static function get_aspect_ratio( string $ratio, array $custom = [] ): string {
		$presets = [
			'16-9' => '16 / 9',
			'4-3'  => '4 / 3',
			'21-9' => '21 / 9',
			'1-1'  => '1 / 1',
			'9-16' => '9 / 16',
		];

		if ( 'custom' === $ratio && ! empty( $custom ) ) {
			$width  = $custom['width'] ?? '16';
			$height = $custom['height'] ?? '9';
			return $width . ' / ' . $height;
		}

		return $presets[ $ratio ] ?? $presets['16-9'];
	}
}

/**
 * Backward compatibility wrapper for build_embed_url.
 *
 * @deprecated Use Video_Helpers::build_embed_url() instead.
 *
 * @param string $source   Video source.
 * @param string $video_id Video ID.
 * @param array  $options  Embed options.
 * @return string Embed URL.
 */
function blockflow_video_build_embed_url( $source, $video_id, $options = [] ) {
	return Video_Helpers::build_embed_url( $source, $video_id, $options );
}

/**
 * Backward compatibility wrapper for get_aspect_ratio.
 *
 * @deprecated Use Video_Helpers::get_aspect_ratio() instead.
 *
 * @param string $ratio  Aspect ratio preset.
 * @param array  $custom Custom aspect ratio values.
 * @return string CSS aspect-ratio value.
 */
function blockflow_video_get_aspect_ratio( $ratio, $custom = [] ) {
	return Video_Helpers::get_aspect_ratio( $ratio, $custom );
}
