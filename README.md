# covid-notificationbar
WordPress Notification Bar for COVID-19

Use in a template file:
if(has_action('covid_notification_action')) {
   // action exists so execute it
    do_action('covid_notification_action');
} else {
    // action has not been registered
}

Use as a shortcode
[covidnotifybar]
or
[covidnotifybar]Notice Text[/covidnotifybar]


Support at http://git.pkwy.digital/
