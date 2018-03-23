<?php namespace CAHNRSWP\Plugin\Pagebuilder;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
} // End if

/*
* @desc Encapsulate query stuff
* @since 3.0.0
*/
class Query {

	protected $fields = array( 'title', 'content', 'img', 'link', 'excerpt' );

	public function get_fields() {

		return $this->fields;

	}

	public function get_local_items( $settings, $prefix = '', $fields = false ) {

		$items = array();

		if ( ! $fields ) {

			$fields = $this->get_fields();

		} // End if

		$query_args = $this->get_query_args( $settings, $prefix );

		$the_query = new \WP_Query( $query_args );

		if ( $the_query->have_posts() ) {

			while ( $the_query->have_posts() ) {

				$the_query->the_post();

				$item = array();

				if ( in_array( 'title', $fields, true ) ) {

					$item['title'] = get_the_title();

				}

				if ( in_array( 'content', $fields, true ) ) {

					$item['content'] = get_the_content();

				}

				if ( in_array( 'excerpt', $fields, true ) ) {

					$item['excerpt'] = $this->get_local_excerpt( $the_query->post->ID, $settings );

				}

				if ( in_array( 'img', $fields, true ) ) {

					$image = $this->get_local_image_array( $the_query->post->ID, $settings );

					$item['img'] = $image['src'];

					$item['img_alt'] = $image['alt'];

				} // End if

				if ( in_array( 'link', $fields, true ) ) {

					$item['link'] = \get_post_permalink();

				}

				$items[ $the_query->post->ID ] = $item;

			} // end while
		} // end if

		return $items;

	}

	public function get_query_args( $settings, $prefix = '', $defaults = array() ) {

		$args = array();

		$args['post_type'] = ( ! empty( $settings[ $prefix . 'post_type' ] ) ) ? $settings[ $prefix . 'post_type' ] : 'post';

		$args['posts_per_page'] = ( ! empty( $settings[ $prefix . 'count' ] ) ) ? $settings[ $prefix . 'count' ] : 5;

		if ( ! empty( $settings[ $prefix . 'offset' ] ) ) {

			$args['offset'] = $settings[ $prefix . 'offset' ];

		} // end if

		if ( ! empty( $settings[ $prefix . 'order_by' ] ) ) {

			$args['orderby'] = $settings[ $prefix . 'order_by' ];

		} // end if

		if ( ! empty( $settings[ $prefix . 'order' ] ) ) {

			$args['order'] = $settings[ $prefix . 'order' ];

		} // end if

		// Handle Taxonomy Query
		if ( ! empty( $settings['taxonomy'] ) && ! empty( $settings['terms'] ) ) {

			$tax_query = array();

			$tax_query['taxonomy'] = $settings['taxonomy'];

			$tax_query['field'] = 'id';

			$terms = explode( ',', $settings['terms'] );

			foreach ( $terms as $term ) {

				$wp_term = \get_term_by( 'name', trim( $term ), $settings['taxonomy'] );

				//$tax_query['terms'][] = trim( $term );

				$tax_query['terms'][] = $wp_term->term_id;

			} // end foreach

			if ( ! empty( $settings['term_operator'] ) ) {

				$tax_query['operator'] = $settings['term_operator'];

			} // end if

			$args['tax_query'] = array( $tax_query );

		} // end if

		return $args;

	}

	protected function get_local_img( $post_id, $settings ) {

		$img_src = '';

		$img_id = \get_post_thumbnail_id( $post_id );

		if ( $img_id ) {

			$image = \wp_get_attachment_image_src( $img_id, 'single-post-thumbnail' );

			$img_src = $image[0];

		} // end if

		return $img_src;

	} // end get_local_img


	protected function get_local_image_array( $post_id, $settings ) {

		$image_array = array(
			'alt' => '',
			'src' => '',
		);

		$img_id = \get_post_thumbnail_id( $post_id );

		if ( $img_id ) {

			$image = \wp_get_attachment_image_src( $img_id, 'single-post-thumbnail' );

			$image_array['alt'] = \get_post_meta( $img_id, '_wp_attachment_image_alt', true );

			$image_array['src'] = $image[0];

		} // end if

		return $image_array;

	} // End get_local_image_array

	protected function get_local_excerpt( $post_id, $settings ) {

		// TO DO: Rewrite excerpt call here
		$excerpt = $this->get_excerpt_from_post_id( $post_id );

		return $excerpt;

	} // end get_local_excerpt


	/*
	* @desc  Get the excerpt from the post
	* @since 0.0.3
	*
	* @param WP_Post $post WP Post object
	*
	* @return string Post excerpt
	*/
	protected function get_excerpt_from_post_id( $post_id ) {

		$post = get_post( $post_id );

		// If this has an excerpt let's just use that
		if ( isset( $post->post_excerpt ) && ! empty( $post->post_excerpt ) ) {

			// bam done
			return $post->post_excerpt;

		} else { // OK so someone didn't set an excerpt, let's make one

			// We'll start with the post content
			$excerpt = $post->post_content;

			// Remove shortcodes but keep text inbetween ]...[/
			$excerpt = \preg_replace( '~(?:\[/?)[^/\]]+/?\]~s', '', $excerpt );

			// Remove HTML tags and script/style
			$excerpt = \wp_strip_all_tags( $excerpt );

			// Shorten to 35 words and convert special characters
			$excerpt = \htmlspecialchars( \wp_trim_words( $excerpt, 35 ) );

			return $excerpt;

		}// End if

	} // End get_excerpt_from_post


	public function get_remote_items( $settings, $prefix = '', $fields = false ) {

		if ( ! $fields ) {

			$fields = $this->get_fields();

		}

		$items = array();

		if ( is_array( $settings[ $prefix . 'remote_items' ] ) && ! empty( $settings[ $prefix . 'remote_items' ] ) ) {

			foreach ( $settings[ $prefix . 'remote_items' ] as $request_item ) {

				$url = $request_item['site'] . '/wp-json/posts/' . $request_item['id'];

				//var_dump( url );

				$response = \wp_remote_get( $url );

				if ( ! is_wp_error( $response ) ) {

					$body = \wp_remote_retrieve_body( $response );

					$json = \json_decode( $body, true );

					if ( $json ) {

						$item = array();

						if ( in_array( 'title', $fields, true ) ) {

							$item['title'] = $json['title'];

						}

						if ( in_array( 'content', $fields, true ) ) {

							$item['content'] = $json['content'];

						}

						if ( in_array( 'excerpt', $fields, true ) ) {

							$item['excerpt'] = $json['excerpt'];

						}

						if ( in_array( 'img', $fields, true ) ) {

							$item['img'] = $this->get_remote_img( $json, $settings );

						} // end if

						if ( in_array( 'link', $fields, true ) ) {

							$item['link'] = $json['link'];

						}

						$items[ $request_item['id'] ] = $item;

						//var_dump( $json['featured_image']['attachment_meta']['sizes'] );

					} // end if
				} // end if
			} // end foreach
		} // end if

		return $items;

	} // end get_remote_items

	public function get_remote_items_feed( $settings, $prefix = '', $fields = false ) {

		if ( ! $fields ) {

			$fields = $this->get_fields();

		}

		$items = array();

		if ( ! empty( $settings[ $prefix . 'site_url' ] ) ) {

			$query = $this->get_query_args_remote( $settings, '' );

			if ( $query ) {

				$url = $settings[ $prefix . 'site_url' ] . '/wp-json/posts' . $query;

			} else {

				$url = $settings[ $prefix . 'site_url' ];

			} // End if

			$response = \wp_remote_get( $url );

			if ( ! is_wp_error( $response ) ) {

				$body = \wp_remote_retrieve_body( $response );

				$json = \json_decode( $body, true );

				if ( $json ) {

					foreach ( $json as $json_item ) {

						$item = array();

						if ( in_array( 'title', $fields, true ) ) {

							$item['title'] = $json_item['title']['rendered'];

						}

						if ( in_array( 'content', $fields, true ) ) {

							$item['content'] = $json_item['content']['rendered'];

						}

						if ( in_array( 'excerpt', $fields, true ) ) {

							$item['excerpt'] = $json_item['excerpt']['rendered'];

						}

						if ( ! empty( $json_item['post_images'] ) ) {

							$item['img'] = $json_item['post_images']['full'];

							$item['images'] = $json_item['post_images'];

						} // End if

						if ( in_array( 'link', $fields, true ) ) {

							$item['link'] = $json_item['link'];

						}

						$items[ $json_item['id'] ] = $item;

					} // end foreach
				} // end if
			} // end if
		} // end if

		return $items;

	} // end get_remote_items

	protected function get_remote_img( $item, $settings, $prefix = '' ) {

		$size = ( ! empty( $settings[ $prefix . 'img_size' ] ) ) ? $settings[ $prefix . 'img_size' ] : 'medium';

		$url = '';

		if ( $item['featured_image'] ) {

			$sizes = $item['featured_image']['attachment_meta']['sizes'];

			if ( array_key_exists( $size, $sizes ) ) {

				$url = $sizes[ $size ]['url'];

			} // end if
		} // end if

		return $url;

	} // eng get_remote_img

	protected function get_query_args_remote( $settings, $prefix = '' ) {

		$get = '';

		$query = array();

		if ( ! empty( $settings[ $prefix . 'post_type' ] ) ) {

			$query[] = 'type=' . $settings[ $prefix . 'post_type' ];

		}

		if ( ! empty( $settings[ $prefix . 'taxonomy' ] ) ) {

			$query[] = 'filter[taxonomy]=' . $settings[ $prefix . 'taxonomy' ];

		}

		if ( ! empty( $settings[ $prefix . 'terms' ] ) ) {

			$query[] = 'filter[term]=' . $settings[ $prefix . 'terms' ];

		}

		if ( ! empty( $settings[ $prefix . 'count' ] ) ) {

			$query[] = 'filter[posts_per_page]=' . $settings[ $prefix . 'count' ];

		}

		if ( $query ) {

			$get = '?' . implode( '&', $query );

		} // end if

		return $get;

	} // end get_query_args_remote

}
