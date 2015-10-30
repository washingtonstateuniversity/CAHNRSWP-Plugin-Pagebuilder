<?php
class Item_Row_PB extends Item_PB {

	public $slug = 'row';

	public $name = 'Page Row';

	public $allowed_children = 'column';

	public $default_child = 'column';

	public function item( $settings, $content ) {

		global $cpb_column_i;

		$cpb_column_i = 1;

		if ( ! empty( $settings['bgcolor'] ) ) {
			$class .= ' ' . $settings['bgcolor'] . '-back';
		}

		if ( ! empty( $settings['textcolor'] ) ) {
			$class .= ' ' . $settings['textcolor'] . '-text';
		}

		if ( ! empty( $settings['padding'] ) ) {
			$class .= ' ' . $settings['padding'];
		}

		if ( ! empty( $settings['gutter'] ) ) {
			$class .= ' ' . $settings['gutter'];
		}

		if ( ! empty( $settings['csshook'] ) ) {
			$class .= ' ' . $settings['csshook'];
		}

		$html = '<div class="row ' . $settings['layout'] . $class . '">' . $content . '</div>';

		return $html;

	} // end item

	public function editor( $settings, $editor_content ) {

		$title = ( ! empty( $settings['title'] ) ) ? $settings['title'] : $this->name;

		$html .= '<header class="cpb-item-' . $this->slug . '-header">';
			
			$html .= '<nav>';

				$html .= '<div class="title">' . $title . '</div>';

				$html .= '<a href="#" class="cpb-edit-item" data-id="' . $this->id . '"></a>';

				$html .= '<a href="#" class="remove-item-action"></a>';
			
			$html .= '</nav>';

		$html .= '</header>';

		$html .= '<div class="cpb-item-set cpb-' . $settings['layout'] . '  cpb-item-' . $this->slug . '-set">';

			$html .= $editor_content;

		$html .= '</div>';

		$html .= Forms_PB::hidden_field( $this->get_name_field( 'children', false  ), implode( ',', $this->get_child_ids() ), 'cpb-input-items-set' );

		$html .= '<footer>';

		$html .= '</footer>';

		return $html;

	} // end editor

	public function form( $settings ) {

		$html = Forms_PB::hidden_field( $this->get_name_field( 'layout' ), $settings['layout'] );

		$html .= Forms_PB::select_field( $this->get_name_field('bgcolor'), $settings['bgcolor'], Forms_PB::get_wsu_colors(), 'Background Color' );

		$html .= Forms_PB::select_field( $this->get_name_field('textcolor'), $settings['textcolor'], Forms_PB::get_wsu_colors(), 'Text Color' );

		$html .= Forms_PB::select_field( $this->get_name_field('padding'), $settings['padding'], Forms_PB::get_padding(), 'Padding' );

		$html .= Forms_PB::select_field( $this->get_name_field('gutter'), $settings['gutter'], Forms_PB::get_gutters(), 'Gutter' );

		$html .= Forms_PB::text_field( $this->get_name_field('csshook'), $settings['csshook'], 'CSS Hook' );

		return $html;

	} // end form

	public function clean( $s ) {

		$clean = array();

		$clean['layout'] = ( ! empty( $s['layout'] ) ) ? sanitize_text_field( $s['layout'] ) : 'single';

		$clean['bgcolor'] = ( ! empty( $s['bgcolor'] ) ) ? sanitize_text_field( $s['bgcolor'] ) : '';

		$clean['textcolor'] = ( ! empty( $s['textcolor'] ) ) ? sanitize_text_field( $s['textcolor'] ) : '';

		$clean['padding'] = ( ! isset( $s['padding'] ) ) ? sanitize_text_field( $s['padding'] ) : 'pad-top';

		$clean['gutter'] = ( ! empty( $s['gutter'] ) ) ? sanitize_text_field( $s['gutter'] ) : 'gutter';

		$clean['csshook'] = ( ! empty( $s['csshook'] ) ) ? sanitize_text_field( $s['csshook'] ) : '';

		return $clean;

	} // end clean_settings

}