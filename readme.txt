=== COVID Notification Bar ===
Contributors: pkwydigital
Tags: covid-19,covid,notification,bar,coronavirus
Requires at least: 5.0
Tested up to: 5.0.0
Requires PHP: 7.1
License: GPLv2

COVID Notification Bar

Support at http://git.pkwy.digital/

== Description ==
Add a Notification Bar for COVID-19 Coronavirus to a WordPress site.

== Installation ==
Install Zip. Configure settings in WordPress Dashboard > COVID Notification Bar.

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


== Screenshots ==
1. Admin Screen
2. Example Use

== Upgrade Notice ==
Stay up-to-date with the most recent features and security fixes. 