<?php
	/*
	Plugin Name: WPFlexSlider
	Plugin URI: http://dominicmcphee.com/wpflexslider
	Description: WPFlexSlider is a WordPress plugin to display galleries using WooThemes popular FlexSlider plugin.
	Version: 0.0.1
	Author: Dominic McPhee
	Author URI: Dominic McPhee
	License: GPL2
	*/
	
	$wp_responsive_slides_height = 500;
	$wp_responsive_slides_width = 1200;
	
	class WPFlexSlider {
		/* Main function to display FlexSlider */
		public static function display_gallery($id) {
			$gallery_post = get_post($id);
			
			if ($gallery_post) {
				$post_content = $gallery_post->post_content;
				
				preg_match('/\[gallery.*ids=.(.*).\]/', $post_content, $ids);
				$attachment_ids = explode(",", $ids[1]);
				
				echo '<div class="flexslider">';
				
				echo '<ul class="slides">';
				
				foreach ($attachment_ids as $attachment_id) {
					$attachment = wp_get_attachment_image_src($attachment_id, 'wp-resp-slide');
					echo '<li><img src="' . $attachment[0] . '" /></li>';
				}
				
				echo '</ul>';
				
				echo '</div>';
			}
		}
		
		public static function get_gallery($id){
			$gallery = '';
			
			$gallery_post = get_post($id);
			
			if ($gallery_post) {
				$post_content = $gallery_post->post_content;
				
				preg_match('/\[gallery.*ids=.(.*).\]/', $post_content, $ids);
				$attachment_ids = explode(",", $ids[1]);
				
				$gallery .= '<div class="flexslider">';
				
				$gallery .= '<ul class="slides">';
				
				foreach ($attachment_ids as $attachment_id) {
					$attachment = wp_get_attachment_image_src($attachment_id, 'wp-resp-slide');
					$attachment_info = get_post($attachment_id);
					$gallery .= '<li>';
					$gallery .= '<img src="' . $attachment[0] . '" />';
					if ($attachment_info->post_excerpt) {
    					$gallery .= '<p class="flex-caption">' . $attachment_info->post_excerpt . '</p>';
					}
					$gallery .= '</li>';
				}
				
				$gallery .= '</ul>';
				
				$gallery .= '</div>';
			}
			
			return $gallery;
		}
		
		/* Shortcode to create FlexSlider */
		public static function flexslider_shortcode($atts, $content = null) {  
		    extract(shortcode_atts(array(
		    	'id' => null,
		        'width' => '1200',
		        'height' => '500'
		    ), $atts));

		    return WPFlexSlider::get_gallery($id);  
		}  
		
		/* Adds a box to the main column on the Post and Page edit screens */
		function myplugin_add_custom_box() {
		    add_meta_box( 
		        'myplugin_sectionid',
		        __( 'My Post Section Title', 'myplugin_textdomain' ),
		        'myplugin_inner_custom_box',
		        'wpflexslider' 
		    );
		    add_meta_box(
		        'myplugin_sectionid',
		        __( 'My Post Section Title', 'myplugin_textdomain' ), 
		        'myplugin_inner_custom_box',
		        'page'
		    );
		    add_meta_box('wpt_events_location', 'Event Location', 'wpt_events_location', 'events', 'side', 'default');
		}
		
		/* Prints the box content */
		function myplugin_inner_custom_box( $post ) {
		
		  // Use nonce for verification
		  wp_nonce_field( plugin_basename( __FILE__ ), 'myplugin_noncename' );
		
		  // The actual fields for data entry
		  // Use get_post_meta to retrieve an existing value from the database and use the value for the form
		  $value = get_post_meta( $_POST['post_ID'], $key = '_my_meta_value_key', $single = true );
		  echo '<label for="myplugin_new_field">';
		       _e("Description for this field", 'myplugin_textdomain' );
		  echo '</label> ';
		  echo '<input type="text" id="myplugin_new_field" name="myplugin_new_field" value="'.$value.'" size="25" />';
		}
		
		public static function create_post_type(){
			register_post_type( 'flexslide',
				array(
					'labels' => array(
						'name' => __( 'FlexSliders' ),
						'singular_name' => __( 'FlexSlide' )
					),
					'public' => true,
					'has_archive' => true,
				)
			);
		}
		
		/**
		 * Enqueue plugin style-file
		 */
		public static function add_wpflexslider_stylesheet() {
		    // Respects SSL, Style.css is relative to the current file
		    wp_register_style( 'prefix-style', plugins_url('css/flexslider.css', __FILE__) );
		    wp_enqueue_style( 'prefix-style' );
		}
		
		
		/**
		 * Enqueue flexslider js file
		 */
		public static function add_wpflexslider_script() {
			wp_enqueue_script(
				'wpflexslider-plugin',
				plugins_url('/js/jquery.flexslider-min.js', __FILE__),
				array( 'jquery' )
			);
		}
		
		/**
		 * Enqueue main js file
		 */
		public static function add_main_script() {
			wp_enqueue_script(
				'wpflexslider-main',
				plugins_url('/js/main.js', __FILE__),
				array( 'jquery' )
			);
		}
		
		
		public static function flexslider_admin() {
		    add_meta_box( 'flexslider_meta_box',
		        'FlexSlider ID',
		        array('WPFlexSlider', 'display_flexslider_meta_box'),
		        'flexslide', 'normal', 'high'
		    );
		}

		public static function display_flexslider_meta_box( $movie_review ) {
			echo '<h3>' . get_the_ID() . '</h3>';	
		}	
	}
	
	// Add image size for slides
	add_image_size( 'wp-resp-slide', $wp_responsive_slides_width, $wp_responsive_slides_height, true );
	
	// Register custom post type
	add_action( 'init', array('WPFlexSlider', 'create_post_type') );
	
	// Register stylesheet
	add_action( 'wp_enqueue_scripts', array('WPFlexSlider', 'add_wpflexslider_stylesheet'));
	
	// Register flexslider javascript
	add_action('wp_enqueue_scripts', array('WPFlexSlider', 'add_wpflexslider_script'));
	
	// Register main javascript
	add_action('wp_enqueue_scripts', array('WPFlexSlider', 'add_main_script'));
	
	// Register shortcode
	add_shortcode('flexslider',  array('WPFlexSlider', 'flexslider_shortcode'));  
	
	// Register widget to display ID when editing
	add_action( 'admin_init', array('WPFlexSlider','flexslider_admin') );
?>