<?php
/*
Plugin Name: Top Slider
Description: Meilleur plugin wordpress pour créer, modifier et afficher des sliders pouvant contenir des lien url
Version: 1.0
Author: Léo Jehane Saoussene et Nicolas
*/


add_action('init', 'initParams');	// Initialisation de Wordpress


/**
 * Initialise les paramêtres du plugin
 **/
function initParams()
{

	$labels = array(                              // params du plugin sur wordpress
		'menu_name' => 'TopSlider',
		'name' => 'TopSlider',
		'singular_name' => 'TopSlider',
		'add_new' => 'Ajouter un Slider',
		'add_new_item' => 'Ajouter un nouveau Slider',
		'view_item' => 'Voir l\'Slide',
		'edit_item' => 'Editer un Slider',
		'new_item' => 'Nouveau Slider',
		'search_items' => 'Rechercher un Slider',
		'not_found' =>  'Aucun Slider',
		'not_found_in_trash' => 'Aucun Slider dans la corbeille',
		'parent_item_colon' => '',

	);

	register_post_type('slide', array(                      // fonction pour parametrer wordpress avec pour id 'Slide'
		'public' => true,            // parametre pour pouvoir administrer le plugin
		'publicly_queryable' => false, // pour que le contenue ne soit pas accessible au public mais seulement à l'admin
		'labels' => $labels,   // on renseigne nos params definis plus tôt
		'menu_position' => 10,  // on indique une position pour notre plugin
		'capability_type' => 'post',   // permission comme les articles ( une personne pouvant editer un article pourras editer les slide)
		'supports' => array('title', 'thumbnail'),  // indique que le plugin  ne supporte que les titres et les images à la une
	));

	add_image_size('slider', 800, 200, true); // Enregistre une nouvelle taille d'image

}

/** 
 * Affichage du slider
 **/

function showSlider($limit = 8)    // limite de 8 images
{

	// On importe le javascript (proprement)
	wp_deregister_script('jquery');
	wp_enqueue_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js', null, '1.7.2', true);
	wp_enqueue_script('caroufredsel', plugins_url() . '/TopSlider/js/jquery.carouFredSel-5.6.4-packed.js', array('jquery'), '5.6.4', true);
	add_action('wp_footer', 'showJS', 30);

	// On écrit le code HTML
	$slides = new WP_query("post_type=slide&posts_per_page=$limit"); // classe wordpress pour récuperer les données du slides  
	echo '<div id="TopSlider">';
	while ($slides->have_posts()) {     // loop dans les post (wordpress)
		$slides->the_post(); // start le loop
		global $post;  // récupère les infos du slider
		echo '<a style="display:block; float:left; height:400px;" href="' . esc_attr(get_post_meta($post->ID, '_link', true)) . '">';
		the_post_thumbnail('slider', array('style' => 'width:800px!important;'));  // récupère l'image 
		echo '</a>';
	}
	echo '</div>';
}

/**
 * Affiche le code Javascript pour lancer caroufredsel
 **/
function showJS()
{
?>
	<script type="text/javascript">
		(function($) {
			$('#TopSlider').caroufredsel();
		})(jQuery);
	</script>
<?php
}