<?php
/*
 * Plugin Name: Theme Manager
 * Plugin URI: https://wordpress.org/plugins/theme-manager/
 * Description: Theme Manager allows you to remove your themes straight from your dashboard.
 * Version: 2.0.1
 * Author: Mitch
 * Author URI: https://profiles.wordpress.org/lowest
 * Text Domain: thma
 * Domain Path:
 * Network:
 * License: GPL-2.0+
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! defined( 'TMANAGER_FILE' ) ) { define( 'TMANAGER_FILE', __FILE__ ); }

if ( ! defined( 'TMANAGER_V' ) ) { define( 'TMANAGER_V', '2.0.1' ); }

function thememanager_menu() {
	add_submenu_page( 'themes.php', 'Theme Manager', 'Theme Manager', 'edit_themes', 'manager', 'theme_manager' );
}
add_action( 'admin_menu', 'thememanager_menu' );

function theme_manager() {
	if ( ! current_user_can( 'delete_themes' ) ) {
		wp_die( __('Sorry, you are not allowed to delete themes for this site.') );
	}

	$themes = wp_get_themes();
	$current = wp_get_theme();
	add_thickbox();
	?>
	<div class="wrap">
		<h1>Theme Manager</h1>
		<p><?php
		printf( __('You have %1$s themes installed. %2$s is currently activated running version %3$s.', 'thememanager'), count($themes), $current->Name, $current->Version ); ?></p>
		<table class="widefat importers striped">
			<tbody>
			<?php
			foreach($themes as $theme) {
			?>
				<tr class="importer-item" data-item="<?php echo $theme->Template; ?>">
					<td class="import-system">
						<span class="importer-title"><?php echo $theme->Name; ?></span>
						<span class="importer-action"><a href="#TB_inline?width=300&height=350&inlineId=<?php echo $theme->Template; ?>" class="thickbox" title="<?php echo $theme->Name; ?>"><?php _e('Details'); ?></a> | <a href="#TB_inline?width=100&height=100&inlineId=delete-<?php echo $theme->Template; ?>" title="<?php echo $theme->Name; ?>" class="thickbox" data-slug="<?php echo $theme->Template; ?>"><?php _e('Delete'); ?></a></span>
					</td>
					<td class="desc">
						<span class="importer-desc"><?php echo $theme->Description; ?></span>
					</td>
				</tr>
			<?php
			}
			?>
			</tbody>
		</table>
		<?php
		foreach($themes as $theme) {
			$tags = $theme->Tags;
			
			echo '
			<div id="' . $theme->Template . '" style="display:none;">
			<table class="details-table">
				<tr>
					<th>' . __('Theme name') . ':</th>
					<td>' . $theme->Name . '</td>
				</tr>
				<tr>
					<th>' . __('Description') . ':</th>
					<td>' . $theme->Description . '</td>
				</tr>
				<tr>
					<th>' . __('Author') . ':</th>
					<td>' . $theme->Author . '</td>
				</tr>
				<tr>
					<th>' . __('Version') . ':</th>
					<td>' . $theme->Version . '</td>
				</tr>
				<tr>
					<th>' . __('Template') . ':</th>
					<td>' . $theme->Template . '</td>
				</tr>
				<tr>
					<th>' . __('Status') . ':</th>
					<td>' . $theme->Status . '</td>
				</tr>
				<tr>
					<th>' . __('Tags') . ':</th>
					<td>';
					if(!empty($tags)) {
						foreach($tags as $tag) {
							echo $tag . ', ';
						}
					} else {
						echo '<i>' . __('No tags to be displayed') . '</i>';
					}
					echo '</td>
				</tr>
			</table>
			</div>
			<div id="delete-' . $theme->Template . '" style="display:none;">
				<p>' . __('Are you sure you want to delete ' . $theme->Name . ' and its data?') . '</p>
				<div class="action-buttons"><a href="javascript:;" class="delete button button-primary" data-slug="' . $theme->Template . '">' . __('Yes, I am sure') . '</a></a>
			</div>
			';
		}
		?>
	</div>
	<?php
}

function thememanager_process() {
  	if ( isset( $_POST["theme"] ) ) {
		$response = $_POST["theme"];

		if(!delete_theme($response)) {
			$class = 'notice notice-error is-dismissible';
			$message = __( 'Something went wrong while deleting the theme.', 'thememanager' );
		}
		
		printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
		
		die();
	}
}
add_action('wp_ajax_thememanager_processor', 'thememanager_process');

function thememanager_scripts() {
	if(isset($_GET['page']) && $_GET['page'] == 'manager') {
		wp_register_style( 'thememanager-css', plugins_url( 'assets/css/app.css', TMANAGER_FILE ), false, '1.0.0' );
		wp_enqueue_style( 'thememanager-css' );
		wp_enqueue_script( "ajax-thememanager", plugins_url( 'assets/js/ajax.js', TMANAGER_FILE ), array( 'jquery' ) );
		wp_localize_script( 'ajax-thememanager', 'thememanager', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );	
	}
}
add_action( 'admin_enqueue_scripts', 'thememanager_scripts' );

function thememanager_footer() {
	if(isset($_GET['page']) && $_GET['page'] == 'manager') {
		return 'Theme Manager ' . TMANAGER_V;
	}
}
add_action( 'admin_footer_text', 'thememanager_footer' );

function thememanager_paypal( $link ) {
	return array_merge( $link, array('<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=2VYPRGME8QELC" target="_blank" rel="noopener noreferrer">Donate</a>') );
}
add_filter( 'plugin_action_links_' . plugin_basename( TMANAGER_FILE ), 'thememanager_paypal');