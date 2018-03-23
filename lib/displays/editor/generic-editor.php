<?php namespace CAHNRSWP\Plugin\Pagebuilder;

?><div class="cpb-generic-editor <?php echo esc_html( $class ); ?>"  data-id="<?php echo esc_html( $id ); ?>" >
	<header><a class="cpb-move-item-action cpb-item-title" href="#">Item: <?php echo esc_html( $name ); ?></a><?php echo wp_kses_post( cpb_get_editor_remove_button() ); ?></header>
	<div class="cpb-child-set">
		<?php
		// @codingStandardsIgnoreStart Already escaped: Has Iframe in it
		echo $editor_content;
		// @codingStandardsIgnoreEnd Already escaped: Has Iframe in it
		?>
		<?php echo wp_kses_post( cpb_get_editor_edit_button() ); ?>
	</div>
	<footer></footer>
</div>
