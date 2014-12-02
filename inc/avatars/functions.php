<?php
/**
 * Allow users to upload their own avatar instead of relying on Gravatar
 */

// Constants
define('LARGO_AVATAR_META_NAME', 'largo_avatar');
define('LARGO_AVATAR_ACTION_NAME', 'largo_avatar_upload');
define('LARGO_AVATAR_INPUT_NAME', 'largo_user_avatar');

if (is_admin())
	include dirname(__FILE__) . '/admin.php';

function largo_get_avatar_filter($avatar, $id_or_email, $size, $default, $alt ) {
	global $_wp_additional_image_sizes;

	$copy = $_wp_additional_image_sizes;
	usort($copy, function($a, $b) {
		return $a['width'] - $b['width'];
	});

	$square_image_sizes = array_filter($copy, function($arg) {
		return ($arg['width'] / $arg['height']) == 1;
	});

	$avatar_id = largo_get_user_avatar_id($id_or_email);
	$requested_size = array($size, $size);

	foreach ($square_image_sizes as $key => $val) {
		if (round((float) ($val['width'] / $val['height']), 1, PHP_ROUND_HALF_DOWN) ==
			round((float) ($requested_size[0] / $requested_size[1]), 1, PHP_ROUND_HALF_DOWN))
		{
			// Try to find an image size equal to or just slightly larger than what was requested
			if ($val['width'] >= $requested_size[0]) {
				$requested_size = array($val['width'], $val['height']);
				break;
			}

			// If we can't find an image size, set the requested size to the largest of the
			// square sizes available
			if (end($square_image_sizes) == $square_image_sizes[$key])
				$requested_size = array($val['width'], $val['height']);
		}
	}
	$image_src = wp_get_attachment_image_src($avatar_id, $requested_size);

	return '<img src="' . $image_src[0] . '" alt="'. $alt . '" width="' . $size . '" height="' . $size . '" class="avatar avatar-' . $size . ' photo" />';
}
add_action('get_avatar', 'largo_get_avatar_filter', 1, 5);

function largo_get_user_avatar_id($user_id) {
	return get_user_meta($user_id, LARGO_AVATAR_META_NAME, true);
}
