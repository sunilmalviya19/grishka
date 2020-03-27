<?php

   if ( ! defined( 'ABSPATH' ) ) {
	 exit; // Exit if accessed directly
   }

class Api_Function {



			public function __construct()

			{
              add_action( 'rest_api_init', array($this,'add_api_routes') );
              add_action( 'init', array($this,'on_activate_create_return_table') );
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
			    $creds['user_login'] = $request["username"];
			    $creds['user_password'] =  $request["password"];
			    $creds['remember'] = true;
			    $user = wp_signon( $creds, true );


			    if ( is_wp_error($user) )
			      return $user->get_error_message();

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

			    	        'status' => 'ok',
			    	        'authtoken' => $token,
			    	        'token_data' => $token_data,
							'user' => $userdata,
				           
				        );

			    return $data;
			}



			

			public function wc_rest_user_endpoint_register_handler($request = null) {
				      $parameters = $request->get_json_params();
					  $response = array();
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
					    return $error;
					  }
					  if (empty($email)) {
					    $error->add(401, __("Email field 'email' is required.", 'wp-rest-user'), array('status' => 400));
					    return $error;
					  }
					  if (empty($password)) {
					    $error->add(404, __("Password field 'password' is required.", 'wp-rest-user'), array('status' => 400));
					    return $error;
					  }
					 
					  $user_id = username_exists($username);
					  if (!$user_id && email_exists($email) == false) {
					    $user_id = wp_insert_user($userdata);
					    if (!is_wp_error($user_id)) {
					      // Ger User Meta Data (Sensitive, Password included. DO NOT pass to front end.)
					      $user = get_user_by('id', $user_id);
					      $user->set_role('subscriber');
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
						  $creds['user_login'] = $username;
						  $creds['user_password'] =  $password;
						  $creds['remember'] = true;
						  $user_login = wp_signon( $creds, true );


						   if ( is_wp_error($user_login) )
						      return $user_login->get_error_message();
               
						   $sessions = get_user_meta( $user_login->ID, 'session_tokens', true );
							foreach ($sessions as $key => $value) {
								 if(!next($sessions)) {
								   $token = $key;
						           $token_data = $value;
						         }
								
							}
				
						    $data = array(

						    	        'status' => 'ok',
						    	        'authtoken' => $token,
						    	        'token_data' => $token_data,
										'user' => $userdata,
							           
							        );
					      
					      //$response['code'] = 200;
					      //$response['message'] = __("User '" . $username . "' Registration was Successful", "wp-rest-user");
					       $response = $data;
					    } else {
					      return $user_id;
					    }
					  } else {
					    $error->add(406, __("Email already exists, please try 'Reset Password'", 'wp-rest-user'), array('status' => 400));
					    return $error;
					  }
					  return new WP_REST_Response($response, 123);
            }


   

			public function wc_rest_user_endpoint_lost_password_handler($request = null){
				          global $wpdb;
							$response = array();
					$parameters = $request->get_json_params();
					$user_login = sanitize_text_field($parameters['user_login']);
					$error = new WP_Error();

					if (empty($user_login)) {
						$error->add(400, __("The field 'user_login' is required.", 'wp-rest-user'), array('status' => 400));
						return $error;
					} else {
						$user_id = username_exists($user_login);
						if ($user_id == false) {
							$user_id = email_exists($user_login);
							if ($user_id == false) {
								$error->add(401, __("User '" . $user_login . "' not found.", 'wp-rest-user'), array('status' => 401));
								return $error;
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
					//return $otp;
					//$rp_link = '<a href="' . site_url() . "/wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login) . '">' . site_url() . "/wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login) . '';


					//$passdata = $random_password;

					function wpdocs_set_html_mail_content_type() {
						return 'text/html';
					}
					add_filter('wp_mail_content_type', 'wpdocs_set_html_mail_content_type');
					$email_successful = wp_mail($email, 'Reset password', 'your otp for reset your password:<br><br>' . $otp);
					// Reset content-type to avoid conflicts -- https://core.trac.wordpress.org/ticket/23578
					remove_filter('wp_mail_content_type', 'wpdocs_set_html_mail_content_type');
					// ==============================================================

					if ($email_successful) {
						$response['code'] = 200;
						$response['message'] = __("Otp had been send to your email.", "wp-rest-user");
					} else {
						$error->add(402, __("Failed to send Reset Password email. Check your WordPress Hosting Email Settings.", 'wp-rest-user'), array('status' => 402));
						return $error;
					}

					return new WP_REST_Response($response, 200);

			}



			public function wc_rest_user_endpoint_verify_otp_handler($request = null){

				        global $wpdb;
				        $table_name = $wpdb->prefix . 'otp_expiry';
					    $response = array();
					    $parameters = $request->get_json_params();
					    $otp = $parameters["otp"];
					    
						   if (empty($otp)) {
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

									 $response['otpId'] = $result[0]->id;
                                     $response['message'] = "OTP is verified.";
								}else{

                                     $response['message'] = "OTP is expired.";								}
								
								
							} else {
								
								$response['message'] = "Invalid OTP!";
							}	
						}

						return $response;

			}



			public function wc_rest_user_endpoint_update_password_handler($request = null){

				        global $wpdb;
				        $table_name = $wpdb->prefix . 'otp_expiry';
					    $response = array();
					    $parameters = $request->get_json_params();
					    
					    $otpId = $parameters["otpId"];
					    $password = $parameters["password"];
					      if (empty($otpId)) {
						    $response['message'] = "otpId field 'otpId' is required.";
						    return $response;
						  }

						   
						  if (empty($password)) {
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
									
									wp_set_password( $password, $user_id );
									$wpdb->update($table_name, array('is_expired'=> '2'), array('id' => $otpId));

                                     $response['message'] = "Password change Successful";

								}else{

                                    $response['message'] = "OTP is expired try again.";								
                                }
								
								
							} else {
								
								$response['message'] = "Invalid OTP!";
							}	
						

						return $response;

			}




			public function wc_rest_user_endpoint_pricing_handler(){
				
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

				return $pricing_data;
			}


			public function wc_rest_user_endpoint_help_handler(){

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

					return $help_data;


			}



			public function wc_rest_user_endpoint_faq_general_handler($request){
				     $response = array();
				     $page_id = $request['id'];

					$faq_data = array();
					if( have_rows('faq_item', $page_id) ){
					while( have_rows('faq_item', $page_id) ): the_row(); 
						      $title = '';
						      $content = '';
					         $title = get_sub_field('faq_tilte'); 
					         $content = get_sub_field('faq_content'); 
					         $faq_data[] = array('title' => $title, 'content' => $content);

					endwhile; 
					 $response['data'] = $faq_data;
					}else{
                     $response['message'] = "FAQ not found ";
					}

                    return $response;

			}
			
			public function wc_rest_user_endpoint_faq_country_handler($request){
				     $response = array();
				     $page_id = $request['id'];

					$faq_data = array();
					if( have_rows('faq_item', $page_id) ){
					while( have_rows('faq_item', $page_id) ): the_row(); 
						      $title = '';
						      $content = '';
					         $title = get_sub_field('faq_tilte'); 
					         $content = get_sub_field('faq_content'); 
					         $faq_data[] = array('title' => $title, 'content' => $content);

					endwhile; 
					 $response['data'] = $faq_data;
					}else{
                     $response['message'] = "FAQ not found ";
					}

                    return $response;

			}

			public function wc_rest_user_endpoint_privacy_policy_handler(){
                    $page_id = 192;
                    $response = array();
					$post = get_post($page_id);
					if (!empty($post)) {
					  $content = $post->post_content;
					  $response['data'] = $content;
						
					}else{
                       $response['message'] = "Not found ";
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
					    $parameters = $request->get_json_params();
					    $user_id = $parameters["user_id"];
					    $token = $parameters["token"];
					     if (empty($user_id)) {
						    $response['message'] = "user_id field 'user_id' is required.";
						    return $response;
						  }

						   
						  if (empty($token)) {
						    $response['message'] = "token field 'token' is required.";
						    return $response;
						  }

                       
                        if ($this->wc_user_destroy_session($token, $user_id)) {
                        	$response['message'] = "User id ".$user_id." Logout Successful.";
                        }else{
                        	$response['message'] = "error";
                        }

                        return $response;


			}


			public function wc_rest_user_endpoint_edit_profile_handler($request = null){



			}



}

new Api_Function();

?>
