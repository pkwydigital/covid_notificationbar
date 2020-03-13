<?php
/**
 * Plugin Name: COVID-19 Notification Bar
 * Plugin URI: https://www.pkwydigital.com
 * Description: Notification Bar for COVID-19
 * Version: 1.0.0 
 * Author: Parkway Digital
 * Author URI: http://www.pkwydigital.com
 */

add_action( 'admin_menu', 'covidnotify_add_admin_menu' );
add_action( 'admin_init', 'covidnotify_settings_init' );

if( ! class_exists( 'Parkway_Updater' ) ){
	include_once( plugin_dir_path( __FILE__ ) . 'updater.php' );
}

$updater = new Parkway_Updater( __FILE__ );
$updater->set_username( 'pkwydigital' );
$updater->set_repository( 'covid-notificationbar' );
/*
	$updater->authorize( 'abcdefghijk1234567890' ); // Your auth code goes here for private repos
*/
$updater->initialize();


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