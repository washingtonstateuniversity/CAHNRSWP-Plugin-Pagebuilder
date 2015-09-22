<?php
class Query_PB {
	
	protected $args;	
	
	public function __construct( $args ){
		
		$this->args = $args;
		
	} // end __construct
	
	public function get_query_items( $supports = array() , $image_size = 'thumbnail' ){
		
		$is_local = true;
		
		$query_args = $this->get_query_args();
		
		if ( ! empty( $query_args['source'] ) ){
			
			$query_items = $this->get_remote_items( $query_args , $supports , $image_size );
			
		} else {
			
			$query_items = $this->get_items( $query_args , $supports , $image_size );
			
		}// end if
		
		return $query_items;
		
	} // end do_query
	
	public function get_remote_items( $query_args , $supports = array() , $image_size = 'thumbnail' ){
		
		$items = array();
		
		$query_url = $this->build_remote_query_url( $query_args );
		
		var_dump( $query_args );
		
		$response = wp_remote_get( $query_url );
		
		if ( is_array( $response ) ){
			
			$body = wp_remote_retrieve_body( $response );
			
			$json = json_decode( $body , true );
			
			if ( is_array( $json ) && $json ){
				
				foreach( $json as $post ){
					
					$item = array();
					
					// Set the title
					if ( empty( $supports ) || in_array( 'title' , $supports ) ) {
						
						$item['title'] = $post['title'];
						
					} // end if
			
					// Set the content
					if ( empty( $supports ) || in_array( 'content' , $supports ) ) {
		
						$item['content'] = $post['content'];
						
					} // end if
			
					// Set the excerpt
					if ( empty( $supports ) || in_array( 'excerpt' , $supports ) ) {
		
						$item['excerpt'] = $post['excerpt'];
						
						if ( isset( $this->args['excerpt_length'] ) ){
							
							$item['excerpt'] = wp_trim_words( $item['excerpt'] , $this->args['excerpt_length'] , '' );
							
						} // end if
						
					} // end if
			
					// Set the excerpt
					if ( empty( $supports ) || in_array( 'link' , $supports ) ) {
		
						$item['link'] = $post['link'];
						
					} // end if
			
					// Set the image
					if ( empty( $supports ) || in_array( 'image' , $supports ) ) {
						
						if ( ! empty( $post['featured_image']['attachment_meta']['sizes'] ) ) {
							
							$image_meta = $post['featured_image']["attachment_meta"]['sizes'];
							
							if ( array_key_exists( $image_size , $image_meta ) ){
								
								$image = $image_meta[ $image_size ];
								
							} else {
								
								$image = $image_meta[ 'thumbnail' ];
								
							} // end if
		
							$item['img'] = $image['url'];
							
						} // end if
						
					} // end if
			
					$items[ 'post' . '-' . $post['ID'] ] = $item;
					
					
				} // end foreach
				
			} // end if
			
		} else {
		}
		
		return $items;
		
	} // end if
	
	
	
	public function get_items( $query_args , $supports = array() , $image_size = 'thumbnail' ){
		
		$items = array();
		
		$the_query = new WP_Query( $query_args );
		
		while ( $the_query->have_posts() ) {
			
			$the_query->the_post();
			
			$item = array();
			
			// Set the title
			if ( empty( $supports ) || in_array( 'title' , $supports ) ) {
				
				$item['title'] = get_the_title();
				
			} // end if
			
			// Set the content
			if ( empty( $supports ) || in_array( 'content' , $supports ) ) {

				$item['content'] = get_the_content();
				
			} // end if
			
			// Set the excerpt
			if ( empty( $supports ) || in_array( 'excerpt' , $supports ) ) {

				$item['excerpt'] = get_the_excerpt();
				
				if ( isset( $this->args['excerpt_length'] ) ){
					
					$item['excerpt'] = wp_trim_words( $item['excerpt'] , $this->args['excerpt_length'] , '' );
					
				} // end if
				
			} // end if
			
			// Set the excerpt
			if ( empty( $supports ) || in_array( 'link' , $supports ) ) {

				$item['link'] = get_permalink();
				
			} // end if
			
			// Set the image
			if ( empty( $supports ) || in_array( 'image' , $supports ) ) {
				
				$image = wp_get_attachment_image_src( get_post_thumbnail_id( $the_query->post->ID ), $image_size );
				
				if ( $image ) {

					$item['img'] = $image[0];
					
				} // end if
				
			} // end if
			
			$items[ 'post' . '-' . $the_query->post->ID ] = $item;
			
		} // end while
		
		return $items;
		
	} // end  get_query
	
	public function build_remote_query_url( $query_args ){
		
		$params = array();
		
		if ( ! empty( $query_args['post_type'] ) ){
			
			$params[] = 'type=' . $query_args['post_type'];
			
		} // end if
		
		if ( ! empty( $query_args['posts_per_page'] ) ){
			
			$params[] = 'filter[posts_per_page]=' . $query_args['posts_per_page'];
			
		} // end if
		
		$url = $query_args['source'] . '/wp-json/posts?' . implode( '&' , $params );
		
		return $url;
		
	}
	
	
	public function get_query_args(){
		
		$query = array();
		
		if ( ! empty( $this->args['ext_source'] ) ){
			
			$query['source'] = $this->args['ext_source'];
			
		} // end if
		
		// Get count args
		$query['posts_per_page'] = $this->get_post_per_page();
		
		// Get post type args
		$query['post_type'] = $this->get_post_type();
		
		// Get taxonomy args
		if ( ! empty( $this->args['taxonomy'] ) && ! empty( $this->args['terms'] ) ) {
			
			$query['tax_query'] = $this->get_tax_query();
			
		} // end if
		
		return $query;
		
	} // get_query_args
	
	
	public function get_post_per_page() {
		
		if ( ! empty( $this->args['posts_per_page'] ) ){
			
			$n = $this->args['posts_per_page'];
			
		} else {
			
			$n = get_option( 'posts_per_page' );
			
		} // end if
		
		return $n;
		
	} // end get_post_per_page
	
	
	public function get_post_type(){
		
		$type = ( ! empty( $this->args['post_type'] ) ) ? $type = $this->args['post_type'] : 'post';
			
		return $type;
		
	} // end get_post_type
	
	
	public function get_tax_query(){
		
		$tax_query = array();
			
		$tax_query['taxonomy'] = $this->args['taxonomy'];
			
		$tax_query['field'] = 'name';
		
		$this->args['terms'] = explode( ',' , $this->args['terms'] );	
		
		return array( $tax_query );
		
	} // end get_tax_query
	
	
	
	
	/*public static function get_local_query_args( $settings ){
		
		$query = array();
		
		// Post Pagation
		
		if ( ! empty( $settings['posts_per_page'] ) ) $query['posts_per_page'] = $settings['posts_per_page'];
		
		// Post Type ---------------------------------------
		
		$query['post_type'] = ( ! empty( $settings['post_type'] ) )? $settings['post_type'] : 'page' ;
		
		// Taxonomy ---------------------------------------
		
		if ( ! empty( $settings['taxonomy'] ) && ! empty( $settings['terms'] ) ){
			
			$tax_query = array();
			
			$tax_query['taxonomy'] = $settings['taxonomy'];
			
			$tax_query['field'] = 'name';
			
			if ( ! empty( $settings['terms'] ) ) $tax_query['terms'] = explode( ',' , $settings['terms'] );	
			
			$query['tax_query'] = array( $tax_query );
			
		} // end if
		
		return $query;
		
		
	} // end get_local_query_args
	
	public static function get_local_feed_objs( $args , $settings = array( 'img_size' => 'thumbnail' ) ) {
		
		global $wp_filter;
		
		
		
		$feed = array();
		
		$the_query = new WP_Query( $args );
		
		if ( $the_query->have_posts() ){
			
			$i = 0;
			
			while ( $the_query->have_posts() ) {
				
				$the_query->the_post();
				
				ob_start(); 
				
				the_excerpt();
				
				$feed[$i]['excerpt'] = ob_get_clean();
				
				 
				
				//var_dump( wp_trim_excerpt() );

				
				/*if ( empty( $feed[$i]['excerpt'] ) ){
					
					$feed[$i]['excerpt'] = wp_trim_words( wp_strip_all_tags( strip_shortcodes( $the_query->post->post_content ) ) , 25 );
					
				} // end if
				
				the_excerpt();*/
					
				/*$feed[$i]['title'] = $the_query->post->post_title; 
					
				$feed[$i]['link'] = get_post_permalink();
					 
				$img_size = ( ! empty( $settings['img_size'] ) ) ? $settings['img_size'] : 'thumbnail';
					
				$img = wp_get_attachment_image_src( get_post_thumbnail_id( $the_query->post->ID ) , $img_size );
					
				$feed[$i]['img'] = ( isset( $img[0] ) && $img[0] ) ? $img[0] : false;
				
				$i++;
				
			} // end while
			
		} // end if
		
		return $feed;
		
	} // end get_local_object*/ 
	
}