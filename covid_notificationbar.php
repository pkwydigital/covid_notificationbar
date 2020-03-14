<?php
/**
 * Plugin Name: COVID Notification Bar
 * Plugin URI: https://www.pkwydigital.com
 * Description: Notification Bar for COVID-19 Coronavirus
 * Version: 1.0.3
 * Author: Parkway Digital
 * Author URI: http://www.pkwydigital.com
 * License: GPLv2 or later
 * Text Domain: covid
 */

add_action( 'admin_menu', 'covidnotify_add_admin_menu' );
add_action( 'admin_init', 'covidnotify_settings_init' );

/**
	 * ---------------------
	 *
	 * Updater 
	 */

add_action( 'init', 'github_plugin_updater_test_init' );
function github_plugin_updater_test_init() {

	include_once 'updater.php';

	define( 'WP_GITHUB_FORCE_UPDATE', true );

	if ( is_admin() ) { // note the use of is_admin() to double check that this is happening in the admin

		$config = array(
			'slug' => plugin_basename( __FILE__ ),
			'proper_folder_name' => 'github-updater',
			'api_url' => 'https://api.github.com/repos/pkwydigtial/WordPress-GitHub-Plugin-Updater',
			'raw_url' => 'https://raw.github.com/pkwydigtial/WordPress-GitHub-Plugin-Updater/master',
			'github_url' => 'https://github.com/pkwydigtial/WordPress-GitHub-Plugin-Updater',
			'zip_url' => 'https://github.com/pkwydigtial/WordPress-GitHub-Plugin-Updater/archive/master.zip',
			'sslverify' => true,
			'requires' => '3.0',
			'tested' => '3.3',
			'readme' => 'README.md',
			'access_token' => '',
		);

		new WP_GitHub_Updater( $config );

	}

}




/**
 * Configuration assistant for updating from private repositories.
 * Do not include this in your plugin once you get your access token.
 *
 * @see /wp-admin/plugins.php?page=github-updater
 */
class WPGitHubUpdaterSetup {

	/**
	 * Full file system path to the main plugin file
	 *
	 * @var string
	 */
	var $plugin_file;

	/**
	 * Path to the main plugin file relative to WP_CONTENT_DIR/plugins
	 *
	 * @var string
	 */
	var $plugin_basename;

	/**
	 * Name of options page hook
	 *
	 * @var string
	 */
	var $options_page_hookname;

	function __construct() {

		// Full path and plugin basename of the main plugin file
		$this->plugin_file = __FILE__;
		$this->plugin_basename = plugin_basename( $this->plugin_file );

		add_action( 'admin_init', array( $this, 'settings_fields' ) );
		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'network_admin_menu', array( $this, 'add_page' ) );

		add_action( 'wp_ajax_set_github_oauth_key', array( $this, 'ajax_set_github_oauth_key' ) );
	}

	/**
	 * Add the options page
	 *
	 * @return none
	 */
	function add_page() {
		if ( current_user_can ( 'manage_options' ) ) {
			$this->options_page_hookname = add_plugins_page ( __( 'GitHub Updates', 'github_plugin_updater' ), __( 'GitHub Updates', 'github_plugin_updater' ), 'manage_options', 'github-updater', array( $this, 'admin_page' ) );
			add_filter( "network_admin_plugin_action_links_{$this->plugin_basename}", array( $this, 'filter_plugin_actions' ) );
			add_filter( "plugin_action_links_{$this->plugin_basename}", array( $this, 'filter_plugin_actions' ) );
		}
	}

	/**
	 * Add fields and groups to the settings page
	 *
	 * @return none
	 */
	public function settings_fields() {
		register_setting( 'ghupdate', 'ghupdate', array( $this, 'settings_validate' ) );

		// Sections: ID, Label, Description callback, Page ID
		add_settings_section( 'ghupdate_private', 'Private Repositories', array( $this, 'private_description' ), 'github-updater' );

		// Private Repo Fields: ID, Label, Display callback, Menu page slug, Form section, callback arguements
		add_settings_field(
			'client_id', 'Client ID', array( $this, 'input_field' ), 'github-updater', 'ghupdate_private',
			array(
				'id' => 'client_id',
				'type' => 'text',
				'description' => '',
			)
		);
		add_settings_field(
			'client_secret', 'Client Secret', array( $this, 'input_field' ), 'github-updater', 'ghupdate_private',
			array(
				'id' => 'client_secret',
				'type' => 'text',
				'description' => '',
			)
		);
		add_settings_field(
			'access_token', 'Access Token', array( $this, 'token_field' ), 'github-updater', 'ghupdate_private',
			array(
				'id' => 'access_token',
			)
		);
		add_settings_field(
			'gh_authorize', '<p class="submit"><input class="button-primary" type="submit" value="'.__( 'Authorize with GitHub', 'github_plugin_updater' ).'" /></p>', null, 'github-updater', 'ghupdate_private', null
		);

	}

	public function private_description() {
?>
		<p>Updating from private repositories requires a one-time application setup and authorization. These steps will not need to be repeated for other sites once you receive your access token.</p>
		<p>Follow these steps:</p>
		<ol>
			<li><a href="https://github.com/settings/applications/new" target="_blank">Create an application</a> with the <strong>Main URL</strong> and <strong>Callback URL</strong> both set to <code><?php echo bloginfo( 'url' ) ?></code></li>
			<li>Copy the <strong>Client ID</strong> and <strong>Client Secret</strong> from your <a href="https://github.com/settings/applications" target="_blank">application details</a> into the fields below.</li>
			<li><a href="javascript:document.forms['ghupdate'].submit();">Authorize with GitHub</a>.</li>
		</ol>
		<?php
	}

	public function input_field( $args ) {
		extract( $args );
		$gh = get_option( 'ghupdate' );
		$value = $gh[$id];
?>
		<input value="<?php esc_attr_e( $value )?>" name="<?php esc_attr_e( $id ) ?>" id="<?php esc_attr_e( $id ) ?>" type="text" class="regular-text" />
		<?php echo $description ?>
		<?php
	}

	public function token_field( $args ) {
		extract( $args );
		$gh = get_option( 'ghupdate' );
		$value = $gh[$id];

		if ( empty( $value ) ) {
?>
			<p>Input Client ID and Client Secret, then <a href="javascript:document.forms['ghupdate'].submit();">Authorize with GitHub</a>.</p>
			<input value="<?php esc_attr_e( $value )?>" name="<?php esc_attr_e( $id ) ?>" id="<?php esc_attr_e( $id ) ?>" type="hidden" />
			<?php
		}else {
?>
			<input value="<?php esc_attr_e( $value )?>" name="<?php esc_attr_e( $id ) ?>" id="<?php esc_attr_e( $id ) ?>" type="text" class="regular-text" />
			<p>Add to the <strong>$config</strong> array: <code>'access_token' => '<?php echo $value ?>',</code>
			<?php
		}
?>
		<?php
	}

	public function settings_validate( $input ) {
		if ( empty( $input ) ) {
			$input = $_POST;
		}
		if ( !is_array( $input ) ) {
			return false;
		}
		$gh = get_option( 'ghupdate' );
		$valid = array();

		$valid['client_id']     = strip_tags( $input['client_id'] );
		$valid['client_secret'] = strip_tags( $input['client_secret'] );
		$valid['access_token']  = strip_tags( $input['access_token'] );

		if ( empty( $valid['client_id'] ) ) {
			add_settings_error( 'client_id', 'no-client-id', __( 'Please input a Client ID before authorizing.', 'github_plugin_updater' ), 'error' );
		}
		if ( empty( $valid['client_secret'] ) ) {
			add_settings_error( 'client_secret', 'no-client-secret', __( 'Please input a Client Secret before authorizing.', 'github_plugin_updater' ), 'error' );
		}

		return $valid;
	}

	/**
	 * Add a settings link to the plugin actions
	 *
	 * @param array   $links Array of the plugin action links
	 * @return array
	 */
	function filter_plugin_actions( $links ) {
		$settings_link = '<a href="plugins.php?page=github-updater">' . __( 'Setup', 'github_plugin_updater' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Output the setup page
	 *
	 * @return none
	 */
	function admin_page() {
		$this->maybe_authorize();
?>
		<div class="wrap ghupdate-admin">

			<div class="head-wrap">
				<?php screen_icon( 'plugins' ); ?>
				<h2><?php _e( 'Setup GitHub Updates' , 'github_plugin_updater' ); ?></h2>
			</div>

			<div class="postbox-container primary">
				<form method="post" id="ghupdate" action="options.php">
					<?php
		settings_errors();
		settings_fields( 'ghupdate' ); // includes nonce
		do_settings_sections( 'github-updater' );
?>
				</form>
			</div>

		</div>
		<?php
	}

	public function maybe_authorize() {
		$gh = get_option( 'ghupdate' );
		if ( 'false' == $_GET['authorize'] || 'true' != $_GET['settings-updated'] || empty( $gh['client_id'] ) || empty( $gh['client_secret'] ) ) {
			return;
		}

		$redirect_uri = urlencode( admin_url( 'admin-ajax.php?action=set_github_oauth_key' ) );

		// Send user to GitHub for account authorization

		$query = 'https://github.com/login/oauth/authorize';
		$query_args = array(
			'scope' => 'repo',
			'client_id' => $gh['client_id'],
			'redirect_uri' => $redirect_uri,
		);
		$query = add_query_arg( $query_args, $query );
		wp_redirect( $query );

		exit;

	}

	public function ajax_set_github_oauth_key() {
		$gh = get_option( 'ghupdate' );

		$query = admin_url( 'plugins.php' );
		$query = add_query_arg( array( 'page' => 'github-updater' ), $query );

		if ( isset( $_GET['code'] ) ) {
			// Receive authorized token
			$query = 'https://github.com/login/oauth/access_token';
			$query_args = array(
				'client_id' => $gh['client_id'],
				'client_secret' => $gh['client_secret'],
				'code' => $_GET['code'],
			);
			$query = add_query_arg( $query_args, $query );
			$response = wp_remote_get( $query, array( 'sslverify' => false ) );
			parse_str( $response['body'] ); // populates $access_token, $token_type

			if ( !empty( $access_token ) ) {
				$gh['access_token'] = $access_token;
				update_option( 'ghupdate', $gh );
			}

			wp_redirect( admin_url( 'plugins.php?page=github-updater' ) );
			exit;

		}else {
			$query = add_query_arg( array( 'authorize'=>'false' ), $query );
			wp_redirect( $query );
			exit;
		}
	}
}
add_action( 'init', create_function( '', 'global $WPGitHubUpdaterSetup; $WPGitHubUpdaterSetup = new WPGitHubUpdaterSetup();' ) );

/**
	 * ---------------------
	 *
	 * Menu Bar
	 */

/**
 * Define the action and give functionality to the action.
 */
 function covid_notification_action() {
   do_action( 'covid_notification_action' );
 }

 /**
  * Register the action with WordPress.
  */
add_action( 'covid_notification_action', 'covid_action_function' );
function covid_action_function() {
    //echo 'This is a custom action hook.';
    $bar_options = get_option( 'covidnotify_settings' );
    if ( $bar_options) {
        if( $bar_options["covidnotify_checkbox_field_0"]) {
            ob_start();
                echo '<style>
                       body.using-mobile-browser .covid-custom-notify {
                       } 
                        .row#header_notice {
                            background-color: ' . $bar_options["covidnotify_input_field_3"] . '; 
                            padding-bottom: 0px;
                            font-family: Open Sans;
                            letter-spacing: 0px;
                            font-size: 15px;
                            line-height: 26px;
                            font-weight: 400;
                            top: 0px;
                            animation: flip-scale-down-diag-2 0.5s linear both;
                            color: ' . $bar_options["covidnotify_input_field_4"] . ' !important;
                            text-align: center;
                        }

                        .row#header_notice a, .row#header_notice a:link, .row#header_notice:vistied {
                            color: ' . $bar_options["covidnotify_input_field_4"] . ' !important;
                        }
                        .row#header_notice a:hover {
                            text-decoration: underline;
                        }
                        @media only screen and (max-width: 999px) and (min-width: 690px) {
                            .row#header_notice {
                                top: -13px;
                            }
                        }
                        @media only screen and (max-width: 690px) and (min-width: 480px) {
                            .row#header_notice {
                                top: -13px;
                            }
                        }
                </style>';
             
                
                echo '<div class="row" id="header_notice">';
                echo '<div class="container">';
                echo '<div class="col span_12">';
                echo '<span><i class="fa fa-exclamation-circle"></i>&nbsp;<strong style="font-weight: 900;">' . $bar_options["covidnotify_input_field_1"] . ':</strong> <a href="' . $bar_options["covidnotify_input_field_2"] . '" style="color: ' . $bar_options["covidnotify_input_field_4"] . ' !important;">' . $bar_options["covidnotify_textarea_field_1"] . '</a></span>';
                echo '</div><!--/span_5-->';

                echo '</div><!--/container-->';
	
                echo '</div><!--/row-->';
            
            //echo var_dump( $bar_options);
        } // end bar 1
    }
    print ob_get_clean();
} // end covid_action_function

function covidnotify_add_admin_menu(  ) { 

	add_menu_page( 'COVID Notification Bar', 'COVID Notification Bar', 'manage_options', 'covid_notification_bar', 'covidnotify_options_page' );

}


function covidnotify_settings_init(  ) { 

	register_setting( 'notificationsbarPage', 'covidnotify_settings' );

	add_settings_section(
		'covidnotify_notificationsbarPage_section', 
		__( 'Notification Bar', 'covidnotify' ), 
		'covidnotify_settings_section_callback', 
		'notificationsbarPage'
	);

	add_settings_field( 
		'covidnotify_checkbox_field_0', 
		__( 'Enable Notification Bar', 'covidnotify' ), 
		'covidnotify_checkbox_field_0_render', 
		'notificationsbarPage', 
		'covidnotify_notificationsbarPage_section' 
	);
	

    add_settings_field( 
		'covidnotify_input_field_1', 
		__( 'Alert Text', 'covidnotify' ), 
		'covidnotify_input_field_1_render', 
		'notificationsbarPage', 
		'covidnotify_notificationsbarPage_section' 
	);
    
    add_settings_field( 
		'covidnotify_input_field_2', 
		__( 'URL Target', 'covidnotify' ), 
		'covidnotify_input_field_2_render', 
		'notificationsbarPage', 
		'covidnotify_notificationsbarPage_section' 
	);

    add_settings_field( 
		'covidnotify_textarea_field_1', 
		__( 'Notification Text', 'covidnotify' ), 
		'covidnotify_textarea_field_1_render', 
		'notificationsbarPage', 
		'covidnotify_notificationsbarPage_section' 
	);
    
    add_settings_field( 
		'covidnotify_input_field_3', 
		__( 'Bar HEX Color', 'covidnotify' ), 
		'covidnotify_input_field_3_render', 
		'notificationsbarPage', 
		'covidnotify_notificationsbarPage_section' 
	);
    
     add_settings_field( 
		'covidnotify_input_field_4', 
		__( 'Link HEX Color', 'covidnotify' ), 
		'covidnotify_input_field_4_render', 
		'notificationsbarPage', 
		'covidnotify_notificationsbarPage_section' 
	);
    
    

} // end covidnotify_settings_init()


function covidnotify_checkbox_field_0_render(  ) { 

	$options = get_option( 'covidnotify_settings' );
	?>
	<input type='checkbox' name='covidnotify_settings[covidnotify_checkbox_field_0]' <?php checked( $options['covidnotify_checkbox_field_0'], 1 ); ?> value='1'>
	<?php

}


function covidnotify_textarea_field_1_render(  ) { 

	$options = get_option( 'covidnotify_settings' );
	?>
	<textarea cols='40' rows='5' name='covidnotify_settings[covidnotify_textarea_field_1]'><?php echo $options['covidnotify_textarea_field_1']; ?></textarea>
	<?php

}

function covidnotify_input_field_1_render(  ) { 

	$options = get_option( 'covidnotify_settings' );
	?>
	<input type='text' name='covidnotify_settings[covidnotify_input_field_1]' value='<?php echo $options['covidnotify_input_field_1']; ?>' /> 
	<?php
}

function covidnotify_input_field_2_render(  ) { 

	$options = get_option( 'covidnotify_settings' );
	?>
	<input type='text' name='covidnotify_settings[covidnotify_input_field_2]' value='<?php echo $options['covidnotify_input_field_2']; ?>'/> 
	<?php
}

function covidnotify_input_field_3_render(  ) { 

	$options = get_option( 'covidnotify_settings' );
	?>
	<input type='text' name='covidnotify_settings[covidnotify_input_field_3]'  value='<?php echo $options['covidnotify_input_field_3']; ?>' /> 
	<?php
}

function covidnotify_input_field_4_render(  ) { 

	$options = get_option( 'covidnotify_settings' );
	?>
	<input type='text' name='covidnotify_settings[covidnotify_input_field_4]'  value='<?php echo $options['covidnotify_input_field_4']; ?>' /> 
	<?php
}

function covidnotify_settings_section_callback(  ) { 

	echo __( 'Enable to display the COVID Notification Bar.', 'covidnotify' );

}


function covidnotify_options_page(  ) { 

	?>
	<form action='options.php' method='post'>

		<h2>COVID Notification Bar Settings</h2>
        <hr>
		<?php
		settings_fields( 'notificationsbarPage' );
		do_settings_sections( 'notificationsbarPage' );
        echo '<p style="font-weight: 900;">Remeber to Flush Cache <a href="/wp-admin/admin.php?page=wpengine-common" target="_blank">Here</a>.</p>';
		submit_button();
		?>
        
	</form>

	<?php

} // admin covidnotify_options_page 

function covidnotify_shortcode( $atts, $content = null)	{
 
	extract( shortcode_atts( array(
				'message' => ''
			), $atts 
		) 
	);
	// this will display our message before the content of the shortcode
	return $message . ' ' . $content;
 
}
add_shortcode('covidnotifybar', 'covidnotify_shortcode');

?>