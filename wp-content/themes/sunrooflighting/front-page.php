<?php
/**
 * Front page template.
 *
 * @package Sunrooflighting
 */

get_header();
?>

<main id="main-content" class="jcs-home">
	<?php
	jcs_get_template_part( 'home/hero' );
	jcs_get_template_part( 'home/packages' );
	jcs_get_template_part( 'home/categories' );
	jcs_get_template_part( 'home/promos' );
	jcs_get_template_part( 'home/brands' );
	jcs_get_template_part( 'home/services' );
	jcs_get_template_part( 'home/seo-content' );
	?>
</main>

<?php
get_footer();
