<?php
/*
Plugin Name: CCS-HTTPS
Plugin URI: http://www.creativecloudsolutions.com/products/wordpress/plugins/ccs-https/
Description: CCS-HTTPS is a simple plugin that allows you to identify which pages should have HTTPS enforced. All other pages will have HTTP enforced. This plug-in will *not* interfere with or override any of the built-in WordPress options related to forcing HTTPS for the login and admin pages.
Version: 1.0.0
Author: David Gregg, Creative Cloud Solutions.
Author URI: http://www.creativecloudsolutions.com
*/

/*
	Copyright (C) 2012 David Gregg, Creative Cloud Solutions

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

function ccs_https_fixup() {
	$ccs_https_hit=0;

	if ( (!is_admin()) && (!preg_match('/wp-login/', $_SERVER['REQUEST_URI']) === true) ) {
		$ccs_https_url = $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		$ccs_https_slug = basename($_SERVER['REQUEST_URI']);
		$ccs_https_options = get_option('ccs_https_options');

		if ($_SERVER['HTTPS'] == "on") {
			if (strripos($ccs_https_options['secureslugs'], $ccs_https_slug) === FALSE) {
				$ccs_https_protocol = "http://";
				$ccs_https_hit = 1;
			} else {
				$ccs_https_hit = 0;
			}
		} else {
			if (strripos($ccs_https_options['secureslugs'], $ccs_https_slug) === FALSE) {
				$ccs_https_hit = 0;
			} else {
				$ccs_https_protocol = "https://";
				$ccs_https_hit = 1;
			}
		}
		
		if ($ccs_https_hit == 1) {
			header("Location: $ccs_https_protocol$ccs_https_url");
			exit;
		}

	}
}

function ccs_https_options_init(){
	register_setting( 'ccs_https_options_group', 'ccs_https_options', 'ccs_https_options_validate' );
}

function ccs_https_options_add_page() {
	add_options_page('CCS-HTTPS Options Page', 'CCS-HTTPS', 'manage_options', 'ccs_https_options', 'ccs_https_options_do_page');
}

function ccs_https_options_do_page() {
	?>
	<div class="wrap">
		<h2>CCS-HTTPS Options</h2>
		<form method="post" action="options.php">
			<?php settings_fields('ccs_https_options_group'); ?>
			<?php $ccs_https_options = get_option('ccs_https_options'); ?>
			<table class="form-table">
				<!-- Text Area Control -->
				<tr>
					<th scope="row">Comma separated list of pages that require HTTPS (SSL)</th>
					<td>
						<textarea name="ccs_https_options[secureslugs]" rows="2" cols="100" type='textarea'><?php echo $ccs_https_options['secureslugs']; ?></textarea>
						<br /><span style="color:#666666;margin-left:2px;">Please use the "slug" names only.  The human readable (i.e. friendly) page names will not be recognized.</span>
					</td>
				</tr>
			</table>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
		</form>
	</div>
	<?php	
}

function ccs_https_options_validate($input) {
	$input['secureslugs'] =  wp_filter_nohtml_kses($input['secureslugs'].",");
	$input['secureslugs'] = str_replace(" ", "", $input['secureslugs']);
	$input['secureslugs'] = str_replace(",,,", ",", $input['secureslugs']);
	$input['secureslugs'] = str_replace(",,", ",", $input['secureslugs']);
	
	return $input;
}

add_action( 'init', 'ccs_https_fixup', 0 );
add_action('admin_init', 'ccs_https_options_init' );
add_action('admin_menu', 'ccs_https_options_add_page');

?>
