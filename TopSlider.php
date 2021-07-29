<?php
/*
Plugin Name: Top Slider
Description: Meilleur plugin wordpress pour créer, modifier et afficher des sliders pouvant contenir des lien url
Version: 1.0
Author: Léo Jehane Saoussene et Nicolas
*/


add_action('init', 'initParams');	// Initialisation de Wordpress
add_action('add_meta_boxes', 'topSlider_metaboxes');
add_action('save_post', 'topSlider_save',10, 2);					// pour la sauvegarde
add_action('manage_edit-slide_columns', 'topSlider_filterColumn');		// Capture la liste des colonnes pour les slides
add_action('manage_posts_custom_column', 'topSlider_column');			// Permet d'afficher du contenu en plus pour chaque column

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
 *  Gere les metabox
 **/
function topSlider_metaboxes()
{

	add_meta_box('topSlider', 'Lien Url Image', 'topSlider_metabox', 'slide', 'normal', 'high');
}

/**
 * Metabox Lien 
 **/
function topSlider_metabox($object)
{
	// On génère un token (SECURITE)
	wp_nonce_field('topslider', 'topSlider_token');  // methode wordpress de génération de token
?>
	<div class="meta-box-item-title">
		<h4>Lien url du slide</h4>
	</div>
	<div class="meta-box-item-content">
		<input type="text" name="topSlider_link" style="width:100%;" value="<?= esc_attr(get_post_meta($object->ID, '_link', true)); ?>">
	</div>
<?php
}

/**
 * Gestion de la sauvegarde d'un slider (pour la metabox)
 * @param int $post_id Id du contenu édité
 * @param object $post contenu édité
 **/

function topSlider_save($post_id, $post)
{
	// verifie que le champ lien est rempli et que le token est bon
	if (!isset($_POST['topSlider_link']) || !wp_verify_nonce($_POST['topSlider_token'], 'topslider')) {
		return $post_id;
	}


	$type = get_post_type_object($post->post_type);
	// L'utilisateur a le droit ?
	if (!current_user_can($type->cap->edit_post)) {
		return $post_id;
	}

	// On met à jour la meta !
	update_post_meta($post_id, '_link', $_POST['topSlider_link']);
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
		the_post_thumbnail('slider', array('style' => 'width:800px!important;'));  // résoud le bug d'affichage avec le theme twentyeleven
		echo '</a>';
	}
	echo '</div>';
}


/**
* Gestion des colonnes pour les slides
* @param array $columns tableau associatif contenant les column $id => $name
**/
function topSlider_filterColumn($columns){
	$thumb = array('thumbnail' => 'Image');
	$columns = array_slice($columns, 0, 1) + $thumb + array_slice($columns,1,null);
	return $columns;
}

/**
* Gestion du contenu d'une colonne
* @param String $column Id de la colonne traitée
**/
function topSlider_column($column){
	global $post;
	if($column == 'thumbnail'){
		echo edit_post_link(get_the_post_thumbnail($post->ID),null,null,$post->ID);
	}
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
