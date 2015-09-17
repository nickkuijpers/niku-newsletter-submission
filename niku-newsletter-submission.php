<?php
/*
 * Plugin Name: NIKU Newsletter Submission
 * Plugin URI: https://niku-solutions.nl
 * Description: This plugin will create a [niku-newsletter] shortcode which you can enter in your website. This shortcode will create a form which can receive e-mail adressen and save it in the WordPress database so you can export it later.
 * Author: Nick Kuijpers
 * Version: 1.0.0
 * Stable Tag: 1.0.0
 * Author URI: https://niku-solutions.nl
 * License GPL2
 */

/*
|--------------------------------------------------------------------------
| Overriding the form
|--------------------------------------------------------------------------
|
| You can override the HTML of the form by creating using a filter in
| the theme functions.php. Make sure you use the name emailaddress
| in the form field.
|
| The filtername is niku_newsletter_submission
|
*/

/**
 * NIKU Contacts
 */
class niku_contacts
{


	public $send = false;
	public $success = false;


	/**
	 * Constructor
	 */
	function __construct() {

	 	// register and initialize the custom post type for the e-mail adresses
	    add_action( 'init', array( $this, 'register_custom_post_type' ) );	     

	    // Check if the e-mail already exists
	    $this->check_if_new_contact();

	    // Add Shortcode
		add_shortcode( 'niku-newsletter', array( $this, 'custom_shortcode' ) );

	}

	 

	/**
	 * Add the e-mail metabox
	 */
	function register_meta_boxes() {

    	add_meta_box( 'contact-details', 'Contact Details', array( $this, 'output_meta_box' ), 'contacts', 'normal', 'high' );   

	}



	/**
	 * Register Custom Post Type
	 */
	function register_custom_post_type(){


		register_post_type( 'niku_contacts',
		    array(
		      'labels' => array(
		        'name' => __( 'Contacten' ),
		        'singular_name' => __( 'Contact' ),
		        'add_new' => __( 'Nieuwe contact' ),
		        'add_new_item' => __( 'Voeg nieuw contact toe' ),
		        'edit_item' => __( 'Contact wijzigen' ),
		        'new_item' => __( 'Nieuw contact' ),
		        'view_item' => __( 'Bekijk dit contact' ),
		        'search_items' => __( 'Doorzoek contacten' ),
		        'not_found' => __( 'Geen contacten gevonden' ),
		        'menu_name' => __( 'Contacten' ),
		      ),		      
		      'description' => __('Contacten'),
		      'public' => false,
		      'show_in_admin_bar' => false,
		      'exclude_from_search' => true,
		      'publicly_queryable' => false,
		      'show_in_nav_menus' => false,
		      'show_ui' => true,
		      'has_archive' => false,
		      'supports' => 'title',
		      'rewrite' => array( 'slug' => '', 'with_front' => false ),
		    )
		);

	}


	/**
	 * Add the form to the shortcode 
	 */	
	public function custom_shortcode( ) {

		if( $this->success == true ){

			echo '
			<div class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" style="z-index:99999;" id="mySmallModalLabel" aria-labelledby="mySmallModalLabel">
			  <div class="modal-dialog modal-sm" style="z-index:999999;">
			     <div class="modal-content">
			      <div class="modal-header">
			        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			        <h4 class="modal-title">Nieuwsbrief</h4>
			      </div>
			      <div class="modal-body">
			        <p style="color:#000;">Bedankt voor uw inschrijving!</p>
			      </div>
			      <div class="modal-footer">
			        <button type="button" class="btn btn-default" data-dismiss="modal">Sluiten</button>        
			      </div>
			    </div><!-- /.modal-content -->
			  </div>
			</div>
			<script type="text/javascript">
			    $(window).load(function(){
			        $("#mySmallModalLabel").modal("show");
			    });
			</script>
			';	

		}

		$return = '';
		$return .= 	'<form class="nieuwsbrief" method="post" action=""><a name="nieuwsbrief"></a>';
		$return .=	'	<div class="input-group">';		
		$return .=	'		<input type="email" name="emailaddress" class="form-control form-input" id="inputPassword2" placeholder="Geef je emailadres op">';		
		$return .= 	'		<div class="input-group-btn">';
		$return .=	'			<button class="btn btn-green" type="submit"><i class="fa fa-check"></i></button>';
		$return .=  '		</div>';
		$return .=  '	</div>';
		$return .=	'</form>';

		$return = apply_filters( 'niku_newsletter_submission', $return );

		$form = '<form class="nieuwsbrief" method="post" action=""><a name="nieuwsbrief"></a>';
		$form .= $return;
		$form .= '</form>';

		return $form;

	}
	
	

	/**
	* Saves the meta box field data
	*
	* @param int $post_id Post ID
	*/
	function save_meta_boxes( $post_id ) {
	 
	    // Check if our nonce is set.
	    if ( ! isset( $_POST['contacts_nonce'] ) ) {
	        return $post_id;    
	    }
	 
	    // Verify that the nonce is valid.
	    if ( ! wp_verify_nonce( $_POST['contacts_nonce'], 'save_contact' ) ) {
	        return $post_id;
	    }
	 
	    // Check this is the Contact Custom Post Type
	    if ( 'niku_contacts' != $_POST['post_type'] ) {
	        return $post_id;
	    }
	 
	    // Check the logged in user has permission to edit this post
	    if ( ! current_user_can( 'edit_post', $post_id ) ) {
	        return $post_id;
	    }
	 
	    // OK to save meta data
	    $email = sanitize_text_field( $_POST['contact_email'] );

	    update_post_meta( $post_id, '_contact_email', $email );	     

	}

	/**
	* Output a Contact Details meta box
	*
	* @param WP_Post $post WordPress Post object
	*/
	function output_meta_box($post) {
	 
	    $email = get_post_meta( $post->ID, '_contact_email', true );
	     
	    // Output label and field
	    echo ( '<label for="contact_email">' . __( 'Email Address', 'tuts-crm' ) . '</label>' );
	    echo ( '<input type="text" name="contact_email" id="contact_email" value="' . esc_attr( $email ) . '" />' );
	     
	}


	/**
	 * Check for new contacts
	 *
	 */
	function check_if_new_contact()
	{
		if( isset( $_POST ) ){

			if( isset( $_POST['emailaddress'] ) ){

				$emailaddress = sanitize_email( $_POST['emailaddress'] );
				$this->send = true;

				if( !empty( $emailaddress ) ){

					if ( is_email( $emailaddress ) ) {

						$this->success = true;												

						$my_post = array(
						  	'post_title'    => $emailaddress,
						  	'post_type'  => 'niku_contacts',
						   	'post_status'   => 'publish'
						);

						// Insert the post into the database
						wp_insert_post( $my_post );

					} else {

						$this->send = false;						

					}

				}

			}

		}

	}

}


/**
 * Initializing contacts
 */
function activate_niku_contacts(){

	new niku_contacts();

}

add_action('after_setup_theme', 'activate_niku_contacts');

?>