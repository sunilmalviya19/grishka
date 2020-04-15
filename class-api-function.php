<?php
  //  if ( ! defined( 'ABSPATH' ) ) {
	 // exit; // Exit if accessed directly
  //  }

use Automattic\WooCommerce\Client;
class Api_Function {


			public function __construct()

			{
                add_action( 'rest_api_init', array($this,'add_api_routes') );
                add_action( 'init', array($this,'on_activate_create_return_table') );
                add_filter( 'rest_pre_echo_response', array($this,'sm_change_api_response'), 10, 3 );
             
			  // add the filter 
			 //add_filter( 'wpcf7_ajax_json_echo', array($this,'sm_filter_wpcf7_ajax_json_echo'), 10, 2 ); 

             

           

            }


            public function on_activate_create_return_table() {
				     global $wpdb; 
				      $db_table_name = $wpdb->prefix . 'otp_expiry';  // table name
				     
				      $charset_collate = $wpdb->get_charset_collate();
				     

				     //Check to see if the table exists already, if not, then create it
				      if($wpdb->get_var( "show tables like '$db_table_name'" ) != $db_table_name ) 
				     {
				           
				           $sql = "CREATE TABLE $db_table_name (
				                    id bigint(11) NOT NULL auto_increment,
				                    user_id bigint(20) NULL,
				                    otp varchar(10) NOT NULL,
								    is_expired int(11) NOT NULL,
								    create_at datetime NOT NULL,
				                    

				                    PRIMARY KEY id (id)
				            ) $charset_collate;";


				       require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				       dbDelta( $sql );
				       add_option( 'test_db_version', $test_db_version );
				     }
            }

            
			public function add_api_routes() {
		                 /**
					   * Handle login User request.
					   */

					  register_rest_route(
					    'ck', 'users/login',
					    array(
					      'methods'  => 'POST',
					      'callback' => array($this,'wc_rest_user_endpoint_login_handler'),
					    )
					  );

					  /**
					   * Handle Register User request.
					   */
					  register_rest_route('ck', 'users/register', array(
					    'methods' => 'POST',
					    'callback' => array($this,'wc_rest_user_endpoint_register_handler'),
					  ));


					   /**
					   * Handle lost-password User request.
					   */
					  register_rest_route('ck', 'users/lost-password', array(
					    'methods' => 'POST',
					    'callback' => array($this,'wc_rest_user_endpoint_lost_password_handler'),
					  ));

					  /**
					   * Handle lost-password verify User request.
					   */
					  register_rest_route('ck', 'users/otp-verify', array(
					    'methods' => 'POST',
					    'callback' => array($this,'wc_rest_user_endpoint_verify_otp_handler'),
					  ));

					   /**
					   * Handle lost-password verify User request.
					   */
					  register_rest_route('ck', 'users/update-password', array(
					    'methods' => 'POST',
					    'callback' => array($this,'wc_rest_user_endpoint_update_password_handler'),
					  ));

		                /**
					   * Handle Register User request.
					   */
					  register_rest_route('ck', 'users/edit_profile', array(
					    'methods' => 'PUT',
					    'callback' => array($this,'wc_rest_user_endpoint_edit_profile_handler'),
					  ));


		               /**
					   * Handle pricing content request.
					   */
					  register_rest_route('ck', 'pricing', array(
					    'methods' => 'GET',
					    'callback' => array($this,'wc_rest_user_endpoint_pricing_handler'),
					  ));

					   /**
					   * Handle help content request.
					   */
					  register_rest_route('ck', 'help', array(
					    'methods' => 'GET',
					    'callback' => array($this,'wc_rest_user_endpoint_help_handler'),
					  ));

					   /**
					   * Handle faq (General) content request.
					   */
					  register_rest_route('ck', 'faq/general/(?P<id>\d+)', array(
					    'methods' => 'GET',
					    'callback' => array($this,'wc_rest_user_endpoint_faq_general_handler'),
					  ));

					  /**
					   * Handle FAQ By country content request.
					   */
					  register_rest_route('ck', 'faq/country/(?P<id>\d+)', array(
					    'methods' => 'GET',
					    'callback' => array($this,'wc_rest_user_endpoint_faq_country_handler'),
					  ));
		               

		               /**
					   * Handle privacy-policy content request.
					   */
					  register_rest_route('ck', '/privacy-policy', array(
					    'methods' => 'GET',
					    'callback' => array($this,'wc_rest_user_endpoint_privacy_policy_handler'),
					  ));


					   /**
					   * Handle privacy-policy content request.
					   */
					  register_rest_route('ck', 'users/logout', array(
					    'methods' => 'POST',
					    'callback' => array($this,'wc_rest_user_endpoint_logout_handler'),
					  ));

					   /**
					   * Handle statstics content request.
					   */
					  register_rest_route('ck', 'statstics', array(
					    'methods' => 'GET',
					    'callback' => array($this,'wc_rest_statstics_handler'),
					  ));
		                



					   /**
					   * Handle Add to Basket content request.
					   */
					  register_rest_route('ck', '/cart/add-item', array(
					    'methods' => 'POST',
					    'callback' => array($this,'wc_rest_add_to_cart_handler'),
					  ));


		               // Get Cart of a Customer - get-cart/1 (GET)
						register_rest_route( 'ck', '/cart/get-cart/(?P<id>[\d]+)', array(
							'methods'             => 'GET',
							'callback'            => array( $this, 'get_cart_customer' ),
							'args'                => array(
								'id' => array(
									'required'    => true,
									'description' => 'Unique identifier for the customer.',
									'type'        => 'integer',
								),
							),
						) );

		                /**
					   * Handle privacy-policy content request.
					   */
					  register_rest_route('ck', 'cart/remove', array(
					    'methods' => 'DELETE',
					    'callback' => array($this,'wc_rest_crt_item_remove_handler'),
					  ));



					   /**
					   * Handle pay request.
					   */
					  register_rest_route('ck', '/pay', array(
					    'methods' => 'POST',
					    'callback' => array($this,'wc_rest_order_pay_endpoint_handler'),
					  ));


					  // WooCommerce end points
					   /**
					   * Handle Countries request.
					   */
					  register_rest_route('ck', '/countries', array(
					    'methods' => 'GET',
					    'callback' => array($this,'wc_rest_countries_endpoint_handler'),
					  ));

					   /**
					   * Handle payment_gateways request.
					   */
					  register_rest_route('ck', '/payment_gateways', array(
					    'methods' => 'GET',
					    'callback' => array($this,'wc_rest_payment_gateways_endpoint_handler'),
					  ));


					   /**
					   * Handle orders request.
					   */
					  register_rest_route('ck', '/orders', array(
					    'methods' => 'GET',
					    'callback' => array($this,'wc_rest_order_history_endpoint_handler'),
					  ));


					  // Get Transaction History Detail
						register_rest_route( 'ck', '/transaction/(?P<id>[\d]+)', array(
							'methods'             => 'GET',
							'callback'            => array( $this, 'wc_rest_transaction_history_detail_endpoint_handler' ),
							'args'                => array(
								'id' => array(
									'required'    => true,
									'description' => 'Unique identifier for the order.',
									'type'        => 'integer',
								),
							),
						) );


			}

            public function get_user_info_arry($user_id){

            	$user = get_userdata($user_id);
                    $userInfo = array(
					'id' => $user->data->ID,
					'username' => $user->data->user_login,
					'nicename' => $user->data->user_nicename,
					'email' => $user->data->user_email,
					'url' => $user->data->user_url, 
					'registered' => $user->data->user_registered,
					'display_name' => $user->data->display_name,
					'nickname' => $user->data->display_name
			       );

            	return $userInfo; 

            }


			
			public function wc_rest_user_endpoint_login_handler($request){
			    $creds = array();
			    $response = array();
			    $response['status'] = null;
			    $response['status_code'] = null;
			    $response['message'] = null;
			    $response['api'] = 'ck_api';
			    $response['results'] = null;
			    $headers = getallheaders();
			    $creds['user_login'] = $request["username"];
			    $creds['user_password'] =  $request["password"];
			    $creds['remember'] = true;
			    
			    
			    $user = wp_signon( $creds, true );
			   // return $headers;
                  
              
			    if ( is_wp_error($user) ){
			    	$response['status'] = 'faliure';
			    	$response['status_code'] = 404;
			    	$response['message'] = $user->get_error_message();
			    	$response['results'] = $user;
			    	return $response;
			        //return $user->get_error_message();
			    }

			     // wp_set_current_user( $user->ID, $creds['user_login'] );
			      //wp_set_auth_cookie($user->ID);
			    $response['status'] = 'success';
			    $response['status_code'] = 200;
			    $response['message'] = 'login successfull';


			    $id = $user->ID;
			    $meta = get_user_meta($id);
			    $session_tokens = $meta['session_tokens'];
               
			   $sessions = get_user_meta( $id, 'session_tokens', true );
				foreach ($sessions as $key => $value) {
					 if(!next($sessions)) {
					   $token = $key;
			           $token_data = $value;
			         }
					
				}
                $userdata = $this->get_user_info_arry($id);
				
			    $data = array(

			    	        //'status' => 'ok',
			    	        'authtoken' => $token,
			    	        'token_data' => $token_data,
							'user' => $userdata,
				           
				        );

			    $response['results'] = $data;

			    return $response;
			}



			

			public function wc_rest_user_endpoint_register_handler($request = null) {
				      $parameters = $request->get_json_params();
					  $response = array();

					  $response['status'] = null;
					  $response['status_code'] = null;
					  $response['message'] = null;
					  $response['api'] = 'ck_api';
					  $response['results'] = null;
					  $first_name = sanitize_text_field($parameters['first_name']);
					  $last_name = sanitize_text_field($parameters['last_name']);
					  $username = sanitize_text_field($parameters['username']);
					  $email = sanitize_text_field($parameters['email']);
					  $password = sanitize_text_field($parameters['password']);
					  $phone = sanitize_text_field($parameters['phone']);
					  $billing_address = sanitize_text_field($parameters['address']);
					  $city = sanitize_text_field($parameters['city']);
					  $postal_code = sanitize_text_field($parameters['postal_code']);
					  $country = sanitize_text_field($parameters['country']);
					  $device_details = sanitize_text_field($parameters['device_details']);
					  $userdata = array(
				        'user_login' => $username,
				        'user_pass'  => $password,
				        'user_email' => $email,
				        'first_name' => $first_name,
				        'last_name' => $last_name
				        
				      );
					  // $role = sanitize_text_field($parameters['role']);
					  $error = new WP_Error();
					  if (empty($username)) {
					    $error->add(400, __("Username field 'username' is required.", 'wp-rest-user'), array('status' => 400));
					    $response['status'] = 'faliure';
			    	    $response['status_code'] = 400;
			    	    $response['message'] = "Username field 'username' is required.";
			    	    
			    	    return $response;
					    
					  }
					  if (empty($email)) {
					    $error->add(401, __("Email field 'email' is required.", 'wp-rest-user'), array('status' => 400));
					     $response['status'] = 'faliure';
			    	     $response['status_code'] = 400;
			    	     $response['message'] = "Email field 'email' is required.";
			    	    
			    	     return $response;
					  }
					  if (empty($password)) {
					     $error->add(404, __("Password field 'password' is required.", 'wp-rest-user'), array('status' => 400));
					     $response['status'] = 'faliure';
			    	     $response['status_code'] = 400;
			    	     $response['message'] = "Password field 'password' is required.";
			    	   
			    	     return $response;
					  }
					 
					  $user_id = username_exists($username);
					  if (!$user_id && email_exists($email) == false) {
					    $user_id = wp_insert_user($userdata);
					    if (!is_wp_error($user_id)) {
					      // Ger User Meta Data (Sensitive, Password included. DO NOT pass to front end.)
					      $user = get_user_by('id', $user_id);
					      $user->set_role('subscriber');
					      // WooCommerce specific code
					      if (class_exists('WooCommerce')) {
					        $user->set_role('customer');
					      }
					      $userdata = $this->get_user_info_arry($user_id);
					      	
					     update_user_meta( $user_id, 'billing_first_name', $first_name );
					     update_user_meta( $user_id, 'billing_last_name', $last_name );
					     update_user_meta( $user_id, 'billing_address_1', $billing_address );
					     update_user_meta( $user_id, 'billing_city', $city );
					     update_user_meta( $user_id, 'billing_country', $country );
					     update_user_meta( $user_id, 'billing_phone', $phone );
					     update_user_meta( $user_id, 'billing_postcode', $postal_code );
					     update_user_meta( $user_id, 'device_details', $device_details );
					      $creds = array();
						 // $creds['user_login'] = $username;
						 // $creds['user_password'] =  $password;
						 // $creds['remember'] = true;
						 // $user_login = wp_signon( $creds, true );


						   // if ( is_wp_error($user_login) ){
						   //    $response['status'] = 'faliure';
					    // 	  $response['status_code'] = 404;
					    // 	  $response['message'] = $user_login->get_error_message();
					    // 	  $response['results'] = $user_login;
					    // 	  return $response;
						   // }
               
						 //   $sessions = get_user_meta( $user_login->ID, 'session_tokens', true );
							// foreach ($sessions as $key => $value) {
							// 	 if(!next($sessions)) {
							// 	   $token = $key;
						 //           $token_data = $value;
						 //         }
								
							// }
				
						    $data = array(

						    	       // 'status' => 'ok',
						    	       // 'authtoken' => $token,
						    	       // 'token_data' => $token_data,
										'user' => $userdata,
							           
							        );
					      
						     $response['status'] = 'success';
			                 $response['status_code'] = 200;
			                 $response['message'] = 'Registration was Successful';

					       
					         $response['results'] = $data;
					    } else {
					      //return $user_id;
					          $response['status'] = 'faliure';
					    	  $response['status_code'] = 404;
					    	  $response['message'] = "Registration is not Successful";
					    	  $response['results'] = $user_id;
					    	  return $response;
					    }
					  } else {
					    $error->add(406, __("Email already exists, please try 'Reset Password'", 'wp-rest-user'), array('status' => 400));
					          $response['status'] = 'faliure';
					    	  $response['status_code'] = 406;
					    	  $response['message'] = "Email already exists, please try 'Reset Password'";
					    	  
					    	  return $response;
					  }
					  return new WP_REST_Response($response, 123);
            }


   

			public function wc_rest_user_endpoint_lost_password_handler($request = null){
				     global $wpdb;
					$response = array();
					$response['status'] = null;
					$response['status_code'] = null;
					$response['message'] = null;
					$response['api'] = 'ck_api';
					$response['results'] = null;
					$parameters = $request->get_json_params();
					$user_login = sanitize_text_field($parameters['user_login']);
					$error = new WP_Error();

					if (empty($user_login)) {
						//$error->add(400, __("The field 'user_login' is required.", 'wp-rest-user'), array('status' => 400));
						$response['status'] = 'faliure';
			    	    $response['status_code'] = 400;
			    	    $response['message'] = "The field 'user_login' is required.";

						return $response;
					} else {
						$user_id = username_exists($user_login);
						if ($user_id == false) {
							$user_id = email_exists($user_login);
							if ($user_id == false) {
								//$error->add(401, __("User '" . $user_login . "' not found.", 'wp-rest-user'), array('status' => 401));
								$response['status'] = 'faliure';
			    	            $response['status_code'] = 404;
			    	            $response['message'] = $user_login . " is not found.";
								return $response;
							}
						}
					}

					// run the action
					$user = null;
					$email = "";
					if (strpos($user_login, '@')) {
						$user = get_user_by('email', $user_login);
						$email = $user_login;
					} else {
						$user = get_user_by('login', $user_login);
						$email = $user->user_email;
					}
					$key = get_password_reset_key($user);
					$otp = rand(1000,9999);
					//$random_password = wp_generate_password(16);
					//wp_set_password( $random_password, $user_id );
					$insert_id = $wpdb->insert( 
                    $wpdb->prefix . 'otp_expiry', 
                    array( 
                        'user_id' => $user_id,
                        'otp' => $otp,
                        'is_expired' => 0, 
                        'create_at'  => date("Y-m-d H:i:s"),
                         
                    )
                      );
					

					function wpdocs_set_html_mail_content_type() {
						return 'text/html';
					}
					add_filter('wp_mail_content_type', 'wpdocs_set_html_mail_content_type');
					$email_successful = wp_mail($email, 'Reset password', 'your otp for reset your password:<br><br>' . $otp);
					// Reset content-type to avoid conflicts -- https://core.trac.wordpress.org/ticket/23578
					remove_filter('wp_mail_content_type', 'wpdocs_set_html_mail_content_type');
					// ==============================================================

					if ($email_successful) {
						  //$response['code'] = 200;
						
						 $response['status'] = 'success';
			             $response['status_code'] = 200;
			             $response['message'] = "Otp had been send to your email.";
			             $response['results'] = $email_successful;
					} else {
						//$error->add(402, __("Failed to send Reset Password email. Check your WordPress Hosting Email Settings.", 'wp-rest-user'), array('status' => 402));
						$response['status'] = 'faliure';
			    	    $response['status_code'] = 402;
			    	    $response['message'] = "Failed to send Reset Password email. Check Hosting Email Settings.";
						return $response;
					}

					return new WP_REST_Response($response, 200);

			}



			public function wc_rest_user_endpoint_verify_otp_handler($request = null){

				        global $wpdb;
				        $table_name = $wpdb->prefix . 'otp_expiry';
					    $response = array();
					    $response['status'] = null;
					    $response['status_code'] = null;
					    $response['message'] = null;
					    $response['api'] = 'ck_api';
					    $response['results'] = null;
					    $parameters = $request->get_json_params();
					    $otp = $parameters["otp"];
					    
						   if (empty($otp)) {
						   	$response['status'] = 'faliure';
			    	        $response['status_code'] = 402;
						    $response['message'] = "otp field 'otp' is required.";
						    return $response;
						  }
						  

					    if(!empty($parameters["otp"])) {
					    	 
                            $query  = $wpdb->prepare("SELECT * FROM $table_name WHERE otp = %d ORDER BY id DESC", $otp);
                            $result = $wpdb->get_results($query);
							
							if(!empty($result)) {
								
								     $dateTime = new DateTime($result[0]->create_at);
                                     $minutesToAdd = 30;
                                     $dateTime->modify("+{$minutesToAdd} minutes");
									 $expriydate = $dateTime;
								if ($result[0]->is_expired!=1 && $result[0]->is_expired!=2 && date("Y-m-d H:i:s") <= $expriydate) {
									
									 $wpdb->update($table_name, array('is_expired'=> '1'), array('otp' => $result[0]->otp));

									 $data['otpId'] = $result[0]->id;
                                     
                                     $response['status'] = 'success';
			                         $response['status_code'] = 200;
			                         $response['message'] = "OTP is verified.";
			                         $response['results'] = $data;
								}else{

                                    
                                     $response['status'] = 'faliure';
				    	             $response['status_code'] = 300;
							         $response['message'] = "OTP is expired.";
							         return $response;								}
								
								
							} else {
								
									
								$response['status'] = 'faliure';
				    	        $response['status_code'] = 404;
							    $response['message'] = "Invalid OTP!";
							    return $response;
							}	
						}

						return $response;

			}



			public function wc_rest_user_endpoint_update_password_handler($request = null){

				        global $wpdb;
				        $table_name = $wpdb->prefix . 'otp_expiry';
					    $response = array();
					    $response['status'] = null;
					    $response['status_code'] = null;
					    $response['message'] = null;
					    $response['api'] = 'ck_api';
					    $response['results'] = null;
					    $parameters = $request->get_json_params();
					    
					    $otpId = $parameters["otpId"];
					    $password = $parameters["password"];
					      if (empty($otpId)) {
					      	$response['status'] = 'faliure';
				    	    $response['status_code'] = 404;
						    $response['message'] = "otpId field 'otpId' is required.";
						    return $response;
						  }

						   
						  if (empty($password)) {
						  	$response['status'] = 'faliure';
				    	    $response['status_code'] = 404;
						    $response['message'] = "password field 'password' is required.";
						    return $response;
						  }

					    
					    	 
                            $query  = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $otpId);
                            $result = $wpdb->get_results($query);
							
							if(!empty($result)) {
								
								     $dateTime = new DateTime($result[0]->create_at);
                                     $minutesToAdd = 30;
                                     $dateTime->modify("+{$minutesToAdd} minutes");
									 $expriydate = $dateTime;
								if ($result[0]->is_expired==1 && $result[0]->is_expired!=2 && $result[0]->is_expired!=0 && date("Y-m-d H:i:s") <= $expriydate) {
									$user_id =	$result[0]->user_id;
									
									$data = wp_set_password( $password, $user_id );
									$wpdb->update($table_name, array('is_expired'=> '2'), array('id' => $otpId));


									 $response['status'] = 'success';
			                         $response['status_code'] = 200;
			                         $response['message'] = "Password change Successful";
			                         $response['results'] = $data;

                                     

								}else{
									$response['status'] = 'faliure';
				    	            $response['status_code'] = 300;
                                    $response['message'] = "OTP is expired try again.";
                                    return $response;
							
                                }
								
								
							} else {
								$response['status'] = 'faliure';
				    	        $response['status_code'] = 404;
								$response['message'] = "Invalid OTP!";
								return $response;
							}	
						

						return $response;

			}




			public function wc_rest_user_endpoint_pricing_handler(){

				        $response = array();
					    $response['status'] = null;
					    $response['status_code'] = null;
					    $response['message'] = null;
					    $response['api'] = 'ck_api';
					    $response['results'] = null;
				
						$page_id = 69;
						$pricing_data = array();
						if( have_rows('pricing_app', $page_id) ): 
						while( have_rows('pricing_app', $page_id) ): the_row(); 
							      $title = '';
							      $content = '';
						         $title = get_sub_field('pricing_title'); 
						         $content = get_sub_field('pricing_content'); 
						         $pricing_data[] = array('title' => $title, 'content' => $content);

						endwhile; 
						endif; 
		                if (!empty($pricing_data)) {
		                	 $response['status'] = 'success';
					         $response['status_code'] = 200;
					         $response['message'] = "get pricing data Successful";
					         $response['results'] = $pricing_data;
		                }else{
		                	 $response['status'] = 'faliure';
						     $response['status_code'] = 404;
							 $response['message'] = "Pricing data Not Found";
		                }
						return $response;
			}


			public function wc_rest_user_endpoint_help_handler(){


			        $response = array();
				    $response['status'] = null;
				    $response['status_code'] = null;
				    $response['message'] = null;
				     $response['api'] = 'ck_api';
				    $response['results'] = null;

				    $page_id = 71;
				    $help_data = array();
					if( have_rows('help_api_post', $page_id) ): 
					while( have_rows('help_api_post', $page_id) ): the_row(); 

						     $post_item = '';
						   
					         $post_item = get_sub_field('help_item'); 
					         $excerpt = get_the_excerpt($post_item->ID);
					         $help_data[] = array('id' => $post_item->ID, 'title' => $post_item->post_title, 'sort_content' => $excerpt, 'content' => $post_item->post_content);

					endwhile; 

					endif; 

					
					if (!empty($help_data)) {
		                	 $response['status'] = 'success';
					         $response['status_code'] = 200;
					         $response['message'] = "get help data Successful";
					         $response['results'] = $help_data;
		            }else{
		                	 $response['status'] = 'faliure';
						     $response['status_code'] = 404;
							 $response['message'] = "Help data Not Found";
		            }
					return $response;


			}



			public function wc_rest_user_endpoint_faq_general_handler($request){
				     $response = array();
				    $response['status'] = null;
				    $response['status_code'] = null;
				    $response['message'] = null;
				     $response['api'] = 'ck_api';
				    $response['results'] = null;
				    $page_id = $request['id'];
				    if (empty($page_id)) {
					      	$response['status'] = 'faliure';
				    	    $response['status_code'] = 404;
						    $response['message'] = "id parameter 'id' is required.";
						    return $response;
					}

					$faq_data = array();
					if( have_rows('faq_item', $page_id) ){
					while( have_rows('faq_item', $page_id) ): the_row(); 
						      $title = '';
						      $content = '';
					         $title = get_sub_field('faq_tilte'); 
					         $content = get_sub_field('faq_content'); 
					         $faq_data[] = array('title' => $title, 'content' => $content);

					endwhile; 
					 $response['status'] = 'success';
					 $response['status_code'] = 200;
					 $response['message'] = "Get FAQ data Successful";
					 $response['results'] = $faq_data;
					}else{
					 $response['status'] = 'faliure';
				     $response['status_code'] = 404;
                     $response['message'] = "FAQ not found ";
					}

                    return $response;

			}
			
			public function wc_rest_user_endpoint_faq_country_handler($request){
				    $response = array();
				    $response['status'] = null;
				    $response['status_code'] = null;
				    $response['message'] = null;
				     $response['api'] = 'ck_api';
				    $response['results'] = null;
				    $page_id = $request['id'];
				      if (empty($page_id)) {
					      	$response['status'] = 'faliure';
				    	    $response['status_code'] = 404;
						    $response['message'] = "id parameter 'id' is required.";
						    return $response;
					}

					$faq_data = array();
					if( have_rows('faq_item', $page_id) ){
					while( have_rows('faq_item', $page_id) ): the_row(); 
						      $title = '';
						      $content = '';
					         $title = get_sub_field('faq_tilte'); 
					         $content = get_sub_field('faq_content'); 
					         $faq_data[] = array('title' => $title, 'content' => $content);

					endwhile; 
					 $response['status'] = 'success';
					 $response['status_code'] = 200;
					 $response['message'] = "Get FAQ data Successful";
					 $response['results'] = $faq_data;
					}else{
                     $response['status'] = 'faliure';
				     $response['status_code'] = 404;
                     $response['message'] = "FAQ not found ";
					}

                    return $response;

			}

			public function wc_rest_user_endpoint_privacy_policy_handler(){
                    $page_id = 192;
                    $response = array();
				    $response['status'] = null;
				    $response['status_code'] = null;
				    $response['message'] = null;
				     $response['api'] = 'ck_api';
				    $response['results'] = null;
					$post = get_post($page_id);
					if (!empty($post)) {
					  $content = $post->post_content;
					 $response['status'] = 'success';
					 $response['status_code'] = 200;
					 $response['message'] = "Get privacy policy data Successful";
					 $response['results'] = $content;
						
					}else{
					   $response['status'] = 'faliure';
				       $response['status_code'] = 404;
                       $response['message'] = "Privacy policy data not found ";
					}
					

                    return $response;


			}

             public function wc_user_destroy_session($verifier, $user_id){

			    $sessions = get_user_meta( $user_id, 'session_tokens', true );


			    if(!isset($sessions[$verifier]))
			        return true;

			    unset($sessions[$verifier]);

			    if(!empty($sessions)){
			        update_user_meta( $user_id, 'session_tokens', $sessions );
			        return true;
			    }

			    delete_user_meta( $user_id, 'session_tokens');
			    return true;

			}

			public function wc_rest_user_endpoint_logout_handler($request = null){
				        $response = array();
				        $response['status'] = null;
				        $response['status_code'] = null;
				        $response['message'] = null;
				         $response['api'] = 'ck_api';
				        $response['results'] = null;
					    $parameters = $request->get_json_params();
					    $user_id = $parameters["user_id"];
					    $token = $parameters["token"];
					     if (empty($user_id)) {
					        $response['status'] = 'faliure';
				            $response['status_code'] = 404;
						    $response['message'] = "user_id field 'user_id' is required.";
						    return $response;
						  }

						   
						  if (empty($token)) {
						  	$response['status'] = 'faliure';
				            $response['status_code'] = 404;
						    $response['message'] = "token field 'token' is required.";
						    return $response;
						  }

                       
                        if ($this->wc_user_destroy_session($token, $user_id)) {
                        	wp_clear_auth_cookie();
                        	wp_destroy_current_session();
                        	$response['status'] = 'success';
					        $response['status_code'] = 200;
                        	$response['message'] = "User id ".$user_id." Logout Successful.";
                        }else{
                        	$response['status'] = 'faliure';
				            $response['status_code'] = 500;
                        	$response['message'] = "error";
                        }

                        return $response;


			}



			public function get_cart_customer( $data = array(), $cart_item_key = '' ) {

                        $response = array();
                        $response['status'] = null;
				        $response['status_code'] = null;
				        $response['message'] = null;
				         $response['api'] = 'ck_api';
				        $response['results'] = null;
					    $headers = getallheaders();
					   
					    $verifier = $headers['authtoken'];
					    $user_id = $headers['user_id'];
						  if ( empty( $data['id'] ) ) {
							return new WP_Error( 'ck_customer_missing', 'Customer ID is required!', array( 'status' => 500 ) );
						  }
					      if (empty($user_id)) {
					      	$response['status'] = 'faliure';
				            $response['status_code'] = 404;
						    $response['message'] = "user_id field 'user_id' is required.";
						    return $response;
						  }

						  if (empty($verifier)) {
						  	$response['status'] = 'faliure';
				            $response['status_code'] = 404;
						    $response['message'] = "token field 'token' is required.";
						    return $response;
						  }
                           if($this->chek_user_login($user_id, $verifier)){
                               $saved_cart = $this->get_saved_cart( $data );
                               $response['status'] = 'success';
					           $response['status_code'] = 200;
					            $response['message'] = "get cart data Successful";
                               $response['results'][] = $saved_cart;
                           }else{
                           	  $response['status'] = 'faliure';
				              $response['status_code'] = 100;
                              $response['message'] = "authentication error";

                           }

                           return $response;
					

					    
				} // END get_cart_customer()

            public function get_saved_cart( $data = array() ) {
				$saved_cart = array();

				$customer_id = ! empty( $data['id'] ) ? $data['id'] : 0;


				if ( $customer_id > 0 ) {
					$saved_cart_meta = get_user_meta( $customer_id, '_woocommerce_persistent_cart_' . get_current_blog_id(), true );

					if ( isset( $saved_cart_meta['cart'] ) ) {
						$saved_cart = array_filter( (array) $saved_cart_meta['cart'] );
					}
				}

				return $saved_cart;
			} // END get_saved_cart()




			public function chek_user_login($user_id, $verifier){
				          $vaild = false;

				          $sessions = get_user_meta( $user_id, 'session_tokens', true );
		                  if(empty($sessions)){
			                 $vaild = false;
			              }
                            
			               $token_data = $sessions[$verifier];

                           $expiration = date('Y-m-d H:i:s', $token_data['expiration']);
                           if($expiration >= date("Y-m-d H:i:s")){
                           $vaild = true;

                           }else{
                             $vaild = false;
                           }
                            return $vaild;


			}

			public function wc_rest_add_to_cart_handler($data= array()){
				     
				        $response = array();
				        $myParam = $data->get_param('cart_item_data');
				         $_FILES = $data->get_file_params();
				        //return $myParam;
				        $response['status'] = null;
				        $response['status_code'] = null;
				        $response['message'] = null;
				         $response['api'] = 'ck_api';
				        $response['results'] = null;
					    global $woocommerce,$wpdb;
					    $headers = getallheaders();

					    $verifier = $headers['authtoken'];
					    $user_id = $headers['user_id'];
					      if (empty($user_id)) {
					      	$response['status'] = 'faliure';
				            $response['status_code'] = 404;
						    $response['message'] = "user_id field 'user_id' is required.";
						    return $response;
						  }

						  if (empty($verifier)) {
						  	$response['status'] = 'faliure';
				            $response['status_code'] = 404;
						    $response['message'] = "token field 'token' is required.";
						    return $response;
						  }

						  if (empty($data['country_code'])) {
						  	$response['status'] = 'faliure';
				            $response['status_code'] = 404;
						    $response['message'] = "country_code field 'country_code' is required.";
						    return $response;
						  }
						  if (empty($data['cart_item_data'])) {
						  	$response['status'] = 'faliure';
				            $response['status_code'] = 404;
						    $response['message'] = "cart_item_data field 'cart_item_data' is required.";
						    return $response;
						  }

						   $country_code = $data['country_code'];
						   
                           $check_product = $wpdb->get_results("SELECT * FROM $wpdb->postmeta
                          WHERE meta_key = 'cart_transfer_country' AND  meta_value = '$country_code' LIMIT 1", ARRAY_A);
                          
                           if (!empty($check_product)) {
                           	  $product_id  = $check_product[0]['post_id'];
                           }else{
                           	 	$response['status'] = 'faliure';
				                $response['status_code'] = 404;
						        $response['message'] = "country not found.";
						        return $response;
                           }

						$quantity       = 1;
						$cart_item_data = $data['cart_item_data'];
							if( !empty($_FILES)) {

                              $maxsize    = 5242880;
						      $acceptable = array(
						          'application/pdf',
						          'image/jpeg',
						          'image/jpg',
						          'image/gif',
						          'image/png'
						      );

				        if(($_FILES['image']['size'] >= $maxsize) || ($_FILES["image"]["size"] == 0)) {
				                $response['status'] = 'faliure';
				                $response['status_code'] = 502;
						        $response['message'] = "File too large. File must be less than 5 megabytes.";
						        return $response;
				        }

				       if((!in_array($_FILES['image']['type'], $acceptable)) && (!empty($_FILES["image"]["type"]))) {
				                $response['status'] = 'faliure';
				                $response['status_code'] = 502;
						        $response['message'] = "Invalid file type. Only PDF, JPG, GIF and PNG types are accepted.";
						        return $response;
				        }

							      $upload = wp_upload_bits( $_FILES['image']['name'], null, file_get_contents( $_FILES['image']['tmp_name'] ) );

							      $filetype = wp_check_filetype( basename( $upload['file'] ), null );

							      $upload_dir = wp_upload_dir();

							      $upl_base_url = is_ssl() ? str_replace('http://', 'https://', $upload_dir['baseurl']) : $upload_dir['baseurl'];

							      $base_name = basename( $upload['file'] );

							      $cart_item_data['custom_file'] = array(
							          'guid'      => $upl_base_url .'/'. _wp_relative_upload_path( $upload['file'] ),
							          'file_type' => $filetype['type'],
							          'file_name' => $base_name,
							          'title'     => preg_replace('/\.[^.]+$/', '', $base_name ),
							          'side'      => '',
							          'key'       => md5( microtime().rand() ),
							      );
							}
					      
                         //return $cart_item_data;
		                $item_added = array();

		                 // Generate a ID based on product ID, variation ID, variation data, and other cart item data.
		                // $cart_id = WC()->cart->generate_cart_id( $product_id, '', '', $cart_item_data );
		                     
		                    
			                if($this->chek_user_login($user_id, $verifier)){ // only update if session is not expired 

                              // wp_set_auth_cookie($headers['user_id']);
			                	//return WC()->cart->get_cart();
			                	$item_key = WC()->cart->add_to_cart( $product_id, $quantity, '', '', $cart_item_data );
			                	

			                	
								 // Return response to added item to cart or return error.
								if ( $item_key ) {
									 $response['status'] = 'success';
					                 $response['status_code'] = 200;
									 $response['message'] = "Product added cart Successful";
									 $response['results'][] = WC()->cart->get_cart();
								} else {
									/* translators: %s: product name */
									//return new WP_Error( 'ck_cannot_add_to_cart',  'You cannot add  to your cart is already in your cart.', array( 'status' => 500 ) );
										$response['status'] = 'faliure';
				                        $response['status_code'] = 500;
						                $response['message'] = "You cannot add  to your cart";
						                 $response['results'] = new WP_Error( 'ck_cannot_add_to_cart',  'You cannot add  to your cart is already in your cart.', array( 'status' => 500 ) );
						                

								}
			              
							}else{
                                $response['status'] = 'faliure';
				                $response['status_code'] = 100;
						        $response['message'] = "authentication error";

							}

					      return$response;



			}



			public function wc_rest_crt_item_remove_handler($request = null){
				        $response = array();
				        $response['status'] = null;
				        $response['status_code'] = null;
				        $response['message'] = null;
				         $response['api'] = 'ck_api';
				        $response['results'] = null;
					   $parameters = $request->get_json_params();

					   $cart_item_key = $parameters['cart_item_key'];
					   $headers = getallheaders();

					   if (empty($cart_item_key)) {
					   	$response['status'] = 'faliure';
				            $response['status_code'] = 404;
						    $response['message'] = "cart_item_key field 'cart_item_key' is required.";
						    return $response;
						  }
                         $verifier = $headers['authtoken'];
					     $user_id = $headers['user_id'];
						
					      if (empty($user_id)) {
					      	$response['status'] = 'faliure';
				            $response['status_code'] = 404;
						    $response['message'] = "user_id field 'user_id' is required.";
						    return $response;
						  }

						  if (empty($verifier)) {
						  	$response['status'] = 'faliure';
				            $response['status_code'] = 404;
						    $response['message'] = "token field 'token' is required.";
						    return $response;
						  }

						   if($this->chek_user_login($user_id, $verifier)){
                              // Checks to see if the cart is empty before attempting to remove item.
							if ( WC()->cart->is_empty() ) {
								 $response['status'] = 'faliure';
				                 $response['status_code'] = 500;
						         $response['message'] = 'Item specified does not exist in cart.';
								 //return new WP_Error( 'ckcart_no_items', 'No items in cart.', array( 'status' => 500 ) );
								return $response;
							}

							if ( $cart_item_key != '0' ) {
								// Check item exists in cart before fetching the cart item data to update.
		                    	$current_data = WC()->cart->get_cart_item( $cart_item_key );
		                    	// If item does not exist in cart return response.
								if ( empty( $current_data ) ) {
									 $response['status'] = 'faliure';
				                     $response['status_code'] = 400;
						             $response['message'] = 'Item specified does not exist in cart.';
									//return new WP_Error( 'ckcart_item_not_in_cart', 'Item specified does not exist in cart.', array( 'status' => 404 ) );
									return $response;
								}

								if ( WC()->cart->remove_cart_item( $cart_item_key ) ) {
									 $response['status'] = 'success';
					                 $response['status_code'] = 200;
									 $response['message'] = 'Item has been removed from cart.';
									   $response['results'] = $cart_item_key;
										//return new WP_REST_Response(  'Item has been removed from cart.', 200 );
									} else {
										 $response['status'] = 'faliure';
				                         $response['status_code'] = 100;
						                 $response['message'] = 'Unable to remove item from cart.';
										//return new WP_Error( 'ckcart_can_not_remove_item', 'Unable to remove item from cart.', array( 'status' => 500 ) );
									}

							}  
                           }else{
                             
                                $response['status'] = 'faliure';
				                $response['status_code'] = 100;
						        $response['message'] = "authentication error";

							

                           }

                           return $response;




			}




			public function wc_rest_statstics_handler($request){
				     $response = array();
				     $response['status'] = null;
				     $response['status_code'] = null;
				     $response['message'] = null;
				      $response['api'] = 'ck_api';
				     $response['results'] = null;
				     $page_id = 1106;

				     $statstics_users = get_post_meta( $page_id, 'statstics_users', true );
				     $statstics_countries = get_post_meta( $page_id, 'statstics_countries', true );
				     $statstics_currencies = get_post_meta( $page_id, 'statstics_currencies', true );
				     $statstics_data = array();
				     $result = count_users();
				     $users_count = count( get_users( array( 'role' => 'customer' ) ) );

				     $statstics_data['statstics'] = array('users' => $users_count, 'countries' => $statstics_countries , 'currencies' => $statstics_currencies);

				     if ($users_count) {
				     	$response['status'] = 'success';
					    $response['status_code'] = 200;
                        $response['message'] = "Get statstics data Successful.";
                        $response['results'] = $statstics_data;
				     }else{
                        $response['status'] = 'faliure';
				        $response['status_code'] = 404;
                        $response['message'] = "Statstics data not found ";
				     }
                    return $response;

			}


			public function sm_filter_wpcf7_ajax_json_echo( $response, $result) { 
	
				if ($response['status']=='mail_sent') {
				   $response['status'] = 'success';
				   $response['status_code'] = 200;
				}else{
			      $response['status'] = 'faliure';
				  $response['status_code'] = 404;
				}
				// $response['results'] = null;
				// unset($response['into']); 
			     return $response; 
			}



        public function get_api_client_object(){
        	          $woocommerce = '';
                        $url = site_url();
				       $store_consumer_key = 'ck_8af4d05d41fbae50b0eea61f2e4a48c5b683e581';
				       $store_consumer_secret = 'cs_0d5e70969b0392291e2d3aa1d775a86b6e0d651c';
				       $options = array(
							'debug'           => true,
							'return_as_array' => true,
							'validate_url'    => false,
							'timeout'         => 30,
							'ssl_verify'      => false,
							// 'version' => 'wc/v3',
							
		                );
				   $woocommerce =  new Client( $url, $store_consumer_key, $store_consumer_secret, $options);
				       return $woocommerce;


        }
        public function wc_rest_order_pay_endpoint_handler($request = null){
				        $response = array();
				        $response['status'] = null;
				        $response['status_code'] = null;
				        $response['message'] = null;
				        $response['api'] = 'ck_api';
				        $response['results'] = null;
				        
                        $woocommerce =  $this->get_api_client_object();
					    $parameters = $request->get_json_params();
					    $headers = getallheaders();

					     $verifier = $headers['authtoken'];
					     $user_id = $headers['user_id'];
						
					      if (empty($user_id)) {
					      	$response['status'] = 'faliure';
				            $response['status_code'] = 404;
						    $response['message'] = "user_id field 'user_id' is required.";
						    return $response;
						  }

						  if (empty($verifier)) {
						  	$response['status'] = 'faliure';
				            $response['status_code'] = 404;
						    $response['message'] = "token field 'token' is required.";
						    return $response;
						  }

						  if ( WC()->cart->is_empty() ) {
								 $response['status'] = 'faliure';
				                 $response['status_code'] = 500;
						         $response['message'] = 'cart is empty.';
								 //return new WP_Error( 'ckcart_no_items', 'No items in cart.', array( 'status' => 500 ) );
								return $response;
							}
                           $line_items = array();
						 foreach (WC()->cart->get_cart() as $cart_item_key => $values) {
						 	    $meta_data = array();
						 	    $tfs_open_price = '';
						 	    $line_subtotal = '';
						 	    $product_id = '';
						 	    $line_total = '';
                                  $product_id = $values['product_id'];
						 	      $tfs_open_price = $values['tfs_open_price'];
						 	     $line_subtotal = $values['line_subtotal'];
						 	      $line_total = $values['line_total'];
						 	   $meta_data[] = array(
                                 "key" => "Delivery Method",
                                 "value" => $values['tfs_delivery_method']
						 	   );
						 	   $meta_data[] = array(
                                 "key" => "_FX Amount",
                                 "value" => $values['tfs_fx_amount']
						 	   );
						 	    $meta_data[] = array(
                                 "key" => "_Send Amount",
                                 "value" => $values['tfs_open_price']
						 	   );
						 	     $meta_data[] = array(
                                 "key" => "_FX Rate",
                                 "value" => number_format($values['tfs_fx_rate'], 2, '.', '')
						 	   );
						 	      $meta_data[] = array(
                                 "key" => "_Receive Currency",
                                 "value" => $values['tfs_fx_receive_currency']
						 	   );
						 	       $meta_data[] = array(
                                 "key" => "_Send Currency",
                                 "value" => $values['tfs_fx_send_currency']
						 	   );
						 	         $meta_data[] = array(
                                 "key" => "Exchange Summary",
                                 "value" => $values['tfs_summary']
						 	   );

						 	    if (isset($values['custom_file'])) {
						 	         	$filedata = '';

						 	           $filedata = array(

	                                 	'guid' => $values['custom_file']['guid'],
	                                 	'file_type' => $values['custom_file']['file_type'],
	                                 	'file_name' => $values['custom_file']['file_name'], 
	                                 	'title' => $values['custom_file']['title'],
	                                 	'side' => '',
	                                 	'key' => $values['custom_file']['key'],

	                                  );
							 	      $meta_data[] = array(
	                                 "key" => "_id_proof_file_data",
	                                 "value" => $filedata
							 	      );
							 	     $meta_data[] = array(
	                                 "key" => "ID Proof",
	                                 "value" => $values['custom_file']['title']
							 	      );

						 	    }
						 	         
						 	    
                               $line_items[] = array(
                               	"product_id" => $product_id,
                               	"quantity" => 1,
                               	"price" => "$tfs_open_price",
                               	"subtotal" => "$line_subtotal",
                               	"total" => "$line_total", 
                               	"meta_data" => $meta_data,

                               );
                         }

                        $parameters['line_items'] = $line_items;
                        $data = $woocommerce->post('orders', $parameters);
                        //update_user_meta( 159, 'ob_meta', $data );
                       // return $data;
                        if ($data->results->id) {
                        	WC()->cart->empty_cart();
		                    WC()->session->set('cart', array());
                        	   $response['status'] = 'success';
				 			   $response['status_code'] = 200;
							   $response['message'] = "Data Successful.";
							   $response['results'] = $data->results;
                        }else{
                        	$response['status'] = 'faliure';
				            $response['status_code'] = 500;
						    $response['message'] = "order not created";
						    
                        }
                         return $response;
                        
        }



        public function sm_change_api_response( $response, $object, $request ) {
					          // return $response;

				                $nresponse = array();

					       
							  if ($response['code'] || $response['status'] == 'validation_failed') {
								    $nresponse['status'] = 'faliure';
								    $nresponse['status_code'] = $response['data']['status'];
								    $nresponse['message'] = $response['message'];
								    $nresponse['results'] = $response;
				                    return $nresponse;

							  }else if ($response['api'] != 'ck_api' || $response['status'] == 'mail_sent'){
				                         $nresponse['status'] = 'success';
				 				         $nresponse['status_code'] = 200;
							             $nresponse['message'] = "Data Successful.";
								         $nresponse['results'] = $response;
								         return $nresponse;
							  }

					         return $response;

				}


				public function wc_rest_countries_endpoint_handler(){

					    $response = array();
				        $response['status'] = null;
				        $response['status_code'] = null;
				        $response['message'] = null;
				        $response['api'] = 'ck_api';
				        $response['results'] = null;

					    $countries_obj   = new WC_Countries();
                        $countries   = $countries_obj->__get('countries');

                        if (!empty($countries)) {
                        	   $response['status'] = 'success';
				 			   $response['status_code'] = 200;
							   $response['message'] = "Countries Data Successful.";
							   $response['results'] = $countries;
                        }else{
                        	$response['status'] = 'faliure';
				            $response['status_code'] = 500;
						    $response['message'] = "countries not found";
						    
                        }
					    return $response;
				}

 
		public function wc_rest_payment_gateways_endpoint_handler(){
			            $response = array();
				        $response['status'] = null;
				        $response['status_code'] = null;
				        $response['message'] = null;
				        $response['api'] = 'ck_api';
				        $response['results'] = null;

			   $woocommerce =  $this->get_api_client_object();

			 $gateways = $woocommerce->get('payment_gateways');
			       
 
                    if (!empty($gateways->results)) {
                        	   $response['status'] = 'success';
				 			   $response['status_code'] = 200;
							   $response['message'] = "payment gateways Data Successful.";
							   $response['results'] = $gateways->results;
                        }else{
                        	$response['status'] = 'faliure';
				            $response['status_code'] = 500;
						    $response['message'] = "payment gateways not found";
						    
                        }
					    return $response;

		}

        public function wc_rest_order_history_endpoint_handler($request_data){
				        $response = array();
				        $response['status'] = null;
				        $response['status_code'] = null;
				        $response['message'] = null;
				        $response['api'] = 'ck_api';
				        $response['results'] = null;
				        
                        $woocommerce =  $this->get_api_client_object();
					     $parameters = $request_data->get_params();
					    $headers = getallheaders();

					     $verifier = $headers['authtoken'];
					     $user_id = $headers['user_id'];
						
					      if (empty($user_id)) {
					      	$response['status'] = 'faliure';
				            $response['status_code'] = 404;
						    $response['message'] = "user_id field 'user_id' is required.";
						    return $response;
						  }

						  if (empty($verifier)) {
						  	$response['status'] = 'faliure';
				            $response['status_code'] = 404;
						    $response['message'] = "token field 'token' is required.";
						    return $response;
						  }
                          if($this->chek_user_login($user_id, $verifier)){
                          $orders = $woocommerce->get('orders', $parameters);
                          
								if (!empty($orders->results)) {
		                        	   $response['status'] = 'success';
						 			   $response['status_code'] = 200;
									   $response['message'] = "orders Data Successful.";
									   $response['results'] = $orders->results;
		                        }else{
		                        	$response['status'] = 'faliure';
						            $response['status_code'] = 500;
								    $response['message'] = "orders not found";
								    
		                        }
						  }else{
						  	    $response['status'] = 'faliure';
				                $response['status_code'] = 100;
						        $response['message'] = "authentication error";
						        
						  }
						  return $response;
		}
         public function wc_rest_transaction_history_detail_endpoint_handler($request_data){
				        $response = array();
				        $response['status'] = null;
				        $response['status_code'] = null;
				        $response['message'] = null;
				        $response['api'] = 'ck_api';
				        $response['results'] = null;
				        
                        $woocommerce =  $this->get_api_client_object();
					     $parameters = $request_data->get_params();
					    $headers = getallheaders();

					     $verifier = $headers['authtoken'];
					     $user_id = $headers['user_id'];

						
					      if (empty($user_id)) {
					      	$response['status'] = 'faliure';
				            $response['status_code'] = 404;
						    $response['message'] = "user_id field 'user_id' is required.";
						    return $response;
						  }

						  if (empty($verifier)) {
						  	$response['status'] = 'faliure';
				            $response['status_code'] = 404;
						    $response['message'] = "token field 'token' is required.";
						    return $response;
						  }

						    if ( empty( $parameters['id'] ) ) {
								$response['status'] = 'faliure';
				                $response['status_code'] = 404;
						        $response['message'] = "id is required.";
						        return $response;
						  }
                          if($this->chek_user_login($user_id, $verifier)){
                              	$order_id = $parameters['id'];
                                 $order = $woocommerce->get('orders/'.$order_id);
                          
								if ($order->results) {
		                        	   $response['status'] = 'success';
						 			   $response['status_code'] = 200;
									   $response['message'] = "transaction Data Successful.";
									   $response['results'] = $order->results;
		                        }else{
		                        	$response['status'] = 'faliure';
						            $response['status_code'] = 500;
								    $response['message'] = "transaction not found";
								    
		                        }
						  }else{
						  	    $response['status'] = 'faliure';
				                $response['status_code'] = 100;
						        $response['message'] = "authentication error";
						        
						  }
						  return  $response;
		}
		

		public function wc_rest_user_endpoint_edit_profile_handler($request = null) {
				      $parameters = $request->get_json_params();
					  $response = array();
					  $response['status'] = null;
					  $response['status_code'] = null;
					  $response['message'] = null;
					  $response['api'] = 'ck_api';
					  $response['results'] = null;
					  $first_name = sanitize_text_field($parameters['first_name']);
					  $last_name = sanitize_text_field($parameters['last_name']);
					  $company = sanitize_text_field($parameters['company']);
					  $email = sanitize_text_field($parameters['email']);
					  $phone = sanitize_text_field($parameters['phone']);
					  $billing_address = sanitize_text_field($parameters['address']);

					  $city = sanitize_text_field($parameters['city']);
					  $postal_code = sanitize_text_field($parameters['postal_code']);
					  $country = sanitize_text_field($parameters['country']);
					  $state = sanitize_text_field($parameters['state']);

					    $headers = getallheaders();

					     $verifier = $headers['authtoken'];
					     $user_id = $headers['user_id'];

						
					      if (empty($user_id)) {
					      	$response['status'] = 'faliure';
				            $response['status_code'] = 404;
						    $response['message'] = "user_id field 'user_id' is required.";
						    return $response;
						  }

						  if (empty($verifier)) {
						  	$response['status'] = 'faliure';
				            $response['status_code'] = 404;
						    $response['message'] = "token field 'token' is required.";
						    return $response;
						  }
					  
					  if($this->chek_user_login($user_id, $verifier)){
                         if ($first_name) {
                         	update_user_meta( $user_id, 'first_name', $first_name );
                         	update_user_meta( $user_id, 'billing_first_name', $first_name );
                         }
                         if ($first_name) {
                         	update_user_meta( $user_id, 'last_name', $first_name );
                         	update_user_meta( $user_id, 'billing_last_name', $last_name );
                         }
                          if ($email) {
                         	update_user_meta( $user_id, 'billing_email', $email );
                         	
                         }
					  	 
					  	 if ($billing_address) {
                         	 update_user_meta( $user_id, 'billing_address_1', $billing_address );
                         }
                         if ($city) {
                         	 update_user_meta( $user_id, 'billing_city', $city );
                         }
					     if ($country) {
                         	update_user_meta( $user_id, 'billing_country', $country );
                         }
                         if ($phone) {
                         	update_user_meta( $user_id, 'billing_phone', $phone );
                         }
                         if ($state) {
                         	update_user_meta( $user_id, 'billing_state', $state );
                         }
                         if ($postal_code) {
                         	update_user_meta( $user_id, 'billing_postcode', $postal_code );
                         }

                                  $userdata = $this->get_user_info_arry($user_id);
					                $response['status'] = 'success';
						 		    $response['status_code'] = 200;
									$response['message'] = "Data update Successful.";
									$response['results'] = $userdata;
                              
					   }else{
						  	    $response['status'] = 'faliure';
				                $response['status_code'] = 100;
						        $response['message'] = "authentication error";
						        
						}
						  return  $response;
					      	
					    
					   
            }

	
}

new Api_Function();

?>
