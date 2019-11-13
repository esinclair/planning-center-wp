# Planning Center Events 
Contributors: endocreative, Eliot Sinclair
Tags: planning center, church, events, groups, fullcalendar.io
Requires at least: 5.2.3
Tested up to: 5.2.3
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

## Description

Many churches utilize Planning Center Online to manage their services, group, events, and much more. With the Planning Center Online API you can access that data and display it as your wish on your website. 

This plugin is designed to display a calendar in List and Month views populated from all events and groups that the Planning Center app-id/secret have access to.  Please use a user that has read access to all events and groups wanting to be displayed.

The current APIs that are used include:

*   PCO Group Events
*   PCO Groups

The calendar functionality is from fullcalendar.io and loaded via the CDNJS.org project.

Note that a Planning Center account with a valid app id and secret is required to use the plugin.

## Installation

1. Upload the plugin files to the `/wp-content/plugins/planning-center-wp` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Planning Center Events settings screen to configure the plugin
1. Enter your Planning Center app id and secret
1. Add a shortcode to the post, page, or widget where you want to display your data 

## Changelog

= 1.0 =
* First version

## Shortcodes
[pcwp_events]

## Docker
Please update the passwords and users to values of your choice in docker-compose.yml
#### Launch a local Wordpress site with:
docker-compose up
#### Load the latest plugin by either zipping the entire project and installing via the UI or by using this command from the source root directory:
docker cp  ../planning-center-wp planning-center-wp_wordpress_1:/var/www/html/wp-content/plugins/

