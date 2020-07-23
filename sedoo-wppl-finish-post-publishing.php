<?php
/**
 * Plugin Name: Sedoo - Finish Publishing
 * Description: Blocs d'édition : Permet d'ajouter une date de fin de publication aux articles.
 * Version: 0.0.1
 * Author: Nicolas Gruwe  - SEDOO DATA CENTER
 * Author URI:      https://www.sedoo.fr 
 * GitHub Plugin URI: sedoo/sedoo-wppl-finish-post-publishing
 * GitHub Branch:     master
 */

if ( ! function_exists('get_field') ) {
        
	add_action( 'admin_init', 'sb_plugin_deactivate');
	add_action( 'admin_notices', 'sb_plugin_admin_notice');

	//Désactiver le plugin
	function sb_plugin_deactivate () {
		deactivate_plugins( plugin_basename( __FILE__ ) );
	}
	
	// Alerter pour expliquer pourquoi il ne s'est pas activé
	function sb_plugin_admin_notice () {
		
		echo '<div class="error">Le plugin requiert ACF Pro pour fonctionner <br><strong>Activez ACF Pro ci-dessous</strong> ou <a href=https://wordpress.org/plugins/advanced-custom-fields/> Téléchargez ACF Pro &raquo;</a><br></div>';

		if ( isset( $_GET['activate'] ) ) 
			unset( $_GET['activate'] );	
	}
} else {

	include 'inc/sedoo-wppl-finish-post-publishing-acf-field.php';
	/////////////
	// configuring the CRONtask
	/////////////

	if (!wp_next_scheduled('sedoo_finish_publishing')) {
		wp_schedule_event( time(), 'daily', 'sedoo_finish_publishing' );
	}
	add_action ( 'sedoo_finish_publishing', 'sedoo_finish_publishing_func' );
	
	function sedoo_finish_publishing_func() {
		$posts = get_posts(
			array(
			 'numberposts' => -1,
			 'post_status' => 'publish',
			 'post_type' => 'post',
			)
		   );
		    foreach($posts as $post) {
			   $temporaire = get_field( "ajouter_une_date_de_fin_pour_cet_article", $post->ID );
				if($temporaire == true) { // if post is temp and has finish date

					$date_de_fin = get_field('date_de_fin_de_publication', $post->ID);
					$today = date('d/m/Y');

					if($today == $date_de_fin) { // if finish date is today
						
						$action = get_field('action_a_la_depublication', $post->ID);
						if($action == 'depublier') {
							$my_post = array(
								'ID'           => $post->ID,
								'post_status'   => 'draft'
							);
							wp_update_post( $my_post );
						}
						else {
							wp_delete_post($post->ID);
						}
					}
				}
			}
	}
}
