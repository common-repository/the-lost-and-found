=== The Lost and Found ===
Contributors: tickerator
Donate link: http://www.tickerator.org
Tags: issue tracking, bug tracking, project tracking, submit bugs, issues
Requires at least: 3.4.1
Tested up to: 5.2.3
Stable tag: 0.11
License: GPLv2 
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Lost and Found tracking software. Allows people to view items in your lost and found and submit 
their information to claim it.

== Description ==

This is an easy to use lost and found system for your community theater, church group, 
community group, etc.

You submit a picture and a description of your items then people can enter their information 
to claim the items.


== Installation ==


1. Upload "the-lost-and-found.zip" to the "/wp-content/plugins/" directory and unzip - or use the internal plugin install
1. Activate the plugin through the "Plugins" menu in WordPress
1. Enter the short code "[lost_found]" on a blank page.

From the wordpress dashboard there will be a menu Settings - lost-and-found with configuration options.


== Frequently Asked Questions ==

= Who is this for? =

This is for any group with a wordpress site that one way or another gets items in their lost and found.

= Who can add items? =

By default the wordpress admins can add items.  Within the configuration page you can add other individuals or add that ability to additional users.  Note: in order for a user to be able to add items they must have the "upload_files" capability in wordpress.  By default these are authors, editors, and admins.


== Screenshots ==


== Changelog ==

= 0.10 =

Moved in-line javascript to seperate file. Should help bad javascript from wrecking plugin.

= 0.9 =

Fixed bug related to clearing claims.

= 0.8 =

Removed the rest of the $post->guid references so it works better.

= 0.7 =

Fixed install creating output during the install (made it static)

= 0.6 =

Added ability to add all author type users to add items.
Fixed GUID issues

= 0.5 =

Added ability to sort newest to oldest or vice versa (in the settings on the dashboard)
Added ability to change how the date is displayed.
Changed the css handling so it will never override your custom css

= 0.4 =

Major CSS changes - now more theme friendly
Fixed div by zero
Fixed lack of www on links in some WP installs

= 0.3 = 

Added choice for items per page
Cosmetic fixes
Ability to edit items after they are submitted

= 0.2 =

Completed email feature.
Fixed nav template

= 0.1.1 =

Fixed the admin settings

= 0.1 = 

Initial release

== Upgrade Notice ==

= 0.10 =

Fixed javascript.

= 0.9 =

Fixed bug related to clearing claims.

= 0.8 =

Found some lingering post->guid references. Some things were broken. You should upgrade.

= 0.7 =

Minor bugfix with the install.

= 0.6 =

Fixed dependency on guid which is bad.  Added an "all users" checkbox for people who can add items.  User must have "upload_files" permission to add items.

= 0.5 =

Ability to change the date format and how it sorts. May as well upgrade just for kicks. Shouldn't break anything.

= 0.4 =

Major CSS overhaul, couple of bug fixes.  

= 0.3 = 

A couple of pages looked funny in certain themes. Also you can now edit existing
 items.

= 0.2 =

Now you can receive an email when a item is claimed. Also fixed display bug for users that aren't admins. Suggest everyone upgrade.

= 0.1.1 =

The dashboard options page (settings->Lost-and-found) was not working in 0.1. Fixed here

= 0.1 = 

Initial release
