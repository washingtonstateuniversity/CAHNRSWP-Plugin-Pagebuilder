<?php 
class Forms_PB {
	
	public static function text_field( $name , $value , $label = false , $class = '' ){
		
		$html = '<input type="text" name="' . $name . '" value="' . $value . '" />';
		
		if ( $label ) $html = '<label>' . $label . '</label>' . $html;
		
		return Forms_PB::wrap_field( $html , $class );
		
	} // end input_field
	
	public static function hidden_field( $name , $value , $class = '' ){
		
		$html = '<input type="hidden" name="' . $name . '" value="' . $value . '" class="' . $class . '" />';
		
		return $html;
		
	} // end input_field
	
	
	public static function radio_field( $name , $value , $current_value = false , $label = false , $class = '' ){
		
		$active = ( $value == $current_value ) ? ' active' : '';
		
		$id = str_replace( array( '[',']' ) , '_' , $name ) . '_' . rand( 0 , 1000000 );
		
		$html = '<input type="radio" id="' . $id . '" name="' . $name . '" value="' . $value . '" ' . checked( $value , $current_value , false )  . ' />';
		
		if ( $label ) $html = '<label for="' . $id . '" class="' . $active . '">' . $label . '</label>' . $html;
		
		return Forms_PB::wrap_field( $html , $class );
		
	} // end input_field
	
	
	public static function select_field( $name , $value , $options , $label = false , $class = '' ){
		
		$html = '<select name="' . $name . '" >';
		
		foreach( $options as $op_value => $op_label ){
			
			$html .= '<option value="' . $op_value . '" ' . selected( $op_value , $value , false ) . ' >' . $op_label . '</option>';
			
		} // end foreach
		
		$html .= '</select>';
		
		if ( $label ) $html = '<label for="' . $id . '">' . $label . '</label>' . $html;
		
		return Forms_PB::wrap_field( $html , $class );
		
	} // end select
	
	
	public static function insert_media( $base_name , $settings , $class = '' ){
		
		$html = '<div class="cwp-add-media-wrap">';
		
			$img = ( ! empty( $settings['img_src'] ) ) ? '<img src="' . $settings['img_src'] . '" />' : '<div class="cpb-image-item-empty">No Image Set</div>'; 
		
			$html .= '<div class="cpb-add-media-img">' . $img . '</div>';
		
			$html .= Forms_PB::button( 'Add Image' , 'add-media-action' );
			
			$html .= Forms_PB::hidden_field( $base_name . '[img_src]' , $settings['img_src'] , 'cpb-add-media-src' );
			
			$html .= Forms_PB::hidden_field( $base_name . '[img_id]' , $settings['img_id'] , 'cpb-add-media-id' );
		
		$html .= '</div>';
		
		return $html;
		
	} // end insert_media
	
	
	public function button( $title , $action, $class = 'cwp-standard-button' ){
		
		return '<a href="#" class="' . $action . ' ' . $class . '">' . $title . '</a>';
		
	} // end button
	
	
	public static function wp_editor_field( $id , $content , $label = false , $class = '' ){
		
		ob_start();
		
		wp_editor( $content , '_content_' . $id );
		
		$html = ob_get_clean();
		
		if ( $label ) $html = '<label>' . $label . '</label>' . $html;
		
		return Forms_PB::wrap_field( $html , $class );
		
	} // end wp_editor_field
	
	
	public static function wrap_field( $field , $class = false ){
		
		$class = ( $class ) ? $class : '';
		
		$html = '<div class="cpb-form-field ' . $class . '">' . $field . '</div>';
		
		return $html;
		
	} // end wrap_field
	
	public static function get_item_form( $forms , $action = 'close-form-action' , $action_label = 'Done' ){
		
		$tabs = '';
			
		$sections = '';
		
		$active = 'active';
	
		foreach( $forms as $label => $form ){
			
			$tabs .= '<a href="#" class="' . $active . '">' . $label . '</a>';
			
			$sections .= '<div class="cpb-form-section ' . $active . '"><div>' . $form . '</div></div>';
			
			$active = '';
			
		} // end foreach
		
		$html = '<nav>' . $tabs . '</nav>';
		
		$html .= $sections;
		
		$html .= '<footer>';
		
			$html .= '<a href="#" class="cpb-button ' . $action . '" >' . $action_label . '</a>';
		
		$html .= '</footer>'; 
		
		return $html;
		
	} // end get_item_form
	
	public static function get_sub_form( $form_array , $name = '' , $value = 'na' ){
		
		$options = '';
		
		$sections = '';
		
		foreach( $form_array as $title => $form ){
			
			$checked = '';
			
			$active = '';
			
			if ( $form['value'] == $value ){
				
				$checked = checked( $form['value'] , $value , false );
				
				$active = 'active ';
				
			} // end if
			
			$id = $name . '_' . rand( 0 , 100000 );
			
			$options .= '<label for="' . $id . '" class="' . $active . '">' . $title . '</label>';
			
			$options .= '<input type="radio" name="' . $name . '" value="' . $form['value'] . '" id="' . $id . '" ' . $checked . '/>';
			
			$sections .= '<div class="' . $active . 'cpb-subform-section"><div class="cpb-subform-inner">' . $form['form'] . '</div></div>';
			
		} // end foreach
		
		$html = '<div class="cpb-subform-nav">' . $options . '</div>';
		
		$html .= '<div class="cpb-subform-sections">' . $sections . '</div>';
		
		return $html;
		
	}
	
	public static function wrap_item_form( $id , $form , $width = 'small' , $class = '' ) {
		
		$html = '<fieldset class="cpb-form cpb-form-' . $width . ' ' . $class . '" id="form_' . $id . '">';
		
			$html .= '<div class="cpb-form-frame">';
			
				$html .= '<a href="#" class="close-form-action">Close X</a>';
		
				$html .= $form;
			
			$html .= '</div>';
		
		$html .= '</fieldset>';
		
		return $html;
		
	}
	
}