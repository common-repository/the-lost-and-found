<?php

/*
  Plugin Name: The Lost and Found
  Plugin URI: http://www.tickerator.org/lostfound
  Description: A simple way to display lost items and allow people to claim them.
  Version: 0.11
  Author: David Whipple
  Author URI: http://www.tickerator.org
  License: GPL2
 */

/*  Copyright 2012  David Whipple (email : david@tickerator.org)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */


/* * ************************************************************
 * Globals and other fun set up stuff to be run every time
 */
// Define our table globally so I won't have redundant code
global $wpdb;
global $lost_found_table;
// This table will have all of the lost and found issues
$lost_found_table = $wpdb->prefix . "lost_and_found";

global $lf_no_require_admin;
$lf_no_require_admin = false;


// version info
global $lost_found_version;
$lost_found_version = "0.10";

// add menu option - this is in the admin menu
add_action('admin_menu', array('LostFoundMenu', 'show_menu'));


// Testing mode.  If true then a deactivate call will uninstall the tables
global $testing_mode;
$testing_mode = false;

// make file path available for inclusions.  These are php include files
global $lfpath;
$lfpath = (plugin_dir_path(__FILE__));

// set up upload directory
global $lf_upload_dir;
$dir = wp_upload_dir();
$lf_upload_dir = $dir['basedir'] . "/lost-found";

function add_js_variable() {
    ?>
<script>
var adminUrl = "<?PHP echo admin_url() ?>";
</script>
    <?PHP
}
add_action("wp_head","add_js_variable");


/* * ******************************************
 * Hooks and ways this gets run.
 * ****************************************** */
// Install Me please
register_activation_hook(__FILE__, array('LostFoundInstall', 'install'));
// Deactivate plugin
register_deactivation_hook(__FILE__, array('LostFoundInstall', 'deactivate'));
// Uninstall plugin - delete tables and all data
register_uninstall_hook(__FILE__, array('LostFoundInstall', 'uninstall'));

// Check version for possible upgrade
add_action('plugins_loaded', array('LostFoundInstall', 'update_check'));

// start the process of loading the css / javascript the nice way.
add_action('wp_enqueue_scripts', 'lost_found_enqueue');

// Shortcode to run main program
add_shortcode('lost_found', 'lost_found_function');

// scripts to enqueue since it is required to do it this way to play nice with others.
function lost_found_enqueue() {
    // get the style going
    wp_register_style('lost_found', plugin_dir_url(__FILE__) . "lost-found.css");
}

/* * ***********************************************
 * Ajax section
 * ********************************************** */
add_action('wp_ajax_lost_found_add_user', array('LFAjax', 'add_user'));
add_action('wp_ajax_lost_found_remove_user', array('LFAjax', 'remove_user'));
add_action('wp_ajax_lost_found_email_checkbox', array('LFAjax', 'email_checkbox'));
add_action('wp_ajax_lost_found_sort_order', array('LFAjax', 'sort_order'));
add_action('wp_ajax_lost_found_date_format', array('LFAjax', 'date_format'));
add_action('wp_ajax_lost_found_add_all_users', array('LFAjax', 'add_all_users'));

/**
 * This is called by the shortcode and directs all functions drawn from the shortcode
 *
 * All of the shortcode traffic goes through here.  It formats the main page and uses a
 * return from the functions to draw the rest of the page.
 *
 * @global string $lost_found_table
 * @global type $wpdb
 * @global type $lf_date_format
 * @global type $user
 * @return string
 */
function lost_found_function() {
    // give us jquery
    wp_enqueue_script(array('jquery'));
    wp_enqueue_script('lost-found', plugins_url('lost-found.js', __FILE__));

    date_default_timezone_set('America/Denver');

    global $lost_found_table;
    global $wpdb;

    $main = new LostFoundMain;

    // Go through the options settings and assign them.
    global $lf_date_format;
    global $lf_sort_order;
    global $lf_no_require_admin;

    $options = LostFoundMain::get_options();
    $lf_date_format = $options['date_format'];
    $lf_sort_order = $options['sort_order'];
    $lf_no_require_admin = $options['no_require_admin'];


    global $user;
    $user = wp_get_current_user();

    if (isset($_GET['action'])) {
        $action = $_GET['action'];
    } else {
        $action = "";
    }

    // change this to an action or something
    switch ($action) {
        case "new":
            $output = $main->new_item();
            break;
        case "submit":
            $output = $main->process_item();
            break;
        case "claim":
            $output = $main->process_claim();
            break;
        case "show":
            $output = $main->show_items();
            break;
        case "admin":
            $output = $main->admin_claims();
            break;
        default:
            $output = $main->show_items();
    }



    $output = "<div class=lost_found_page id=lost_found_page >\r\n" 

            //$menu .
            . "\r\n<div class=lost_found_content id=lost_found_content >\r\n" .
            $output . "\r\n</div><!-- lost_found_content -->\r\n</div><!-- lost_found_page -->";
    return $output;
}

/* LFAjax class
 * group all of these together.
 */

/**
 * Class that handles all ajax calls
 *
 * All ajax calls are run through this class.  It is called via add_action from
 * Wordpress
 * @version Release: 1.0
 * @since   1.0
 */
class LFAjax {

    static function add_user() {
        check_ajax_referer('lost_found_options', 'security');
        $user = intval($_POST['selection']);
        $type = $_POST['type'];
        $display_names = LostFoundMenu::get_display_names();

        if ("admins" == $type) {
            if (0 <> $user) {
                $admins = explode(",", get_option('lost_found_admins'));
                if ("" == $admins[0]) {
                    $admins[0] = $user;
                    update_option('lost_found_admins', implode(",", $admins));
                }
                if (false === array_search($user, $admins)) {
                    $admins[] = $user;
                    update_option('lost_found_admins', implode(",", $admins));
                }
            }

            // Yes we kind of already have this but this formats the list
            echo LostFoundMenu::get_admin_names();
        } elseif ("email_people" == $type) {
            if (0 <> $user) {
                $email_people = explode(",", get_option('lost_found_email_list'));
                if ("" == $email_people[0]) {
                    $email_people[0] = $user;
                    update_option('lost_found_email_list', implode(",", $email_people));
                }
                if (false === array_search($user, $email_people)) {
                    $email_people[] = $user;
                    update_option('lost_found_email_list', implode(",", $email_people));
                }
            }
            echo LostFoundMenu::get_email_people();
        }
        die();
    }

    static function add_all_users() {
        check_ajax_referer('lost_found_options', 'security');
        $boxchecked = intval($_POST['checked']);
        $options = LostFoundMain::get_options();
        $option_keys = array_keys($options);
        $option_values = array_values($options);
        $option_output = "";
        // iterate through and rebuild the option string

        for ($x = 0; $x < count($option_keys); $x++) {
            if ("no_require_admin" == $option_keys[$x])
                $option_values[$x] = $boxchecked;
            if ($option_output <> "")
                $option_output .= ",";
            $option_output .= $option_keys[$x] . "|" . $option_values[$x];
        }
        echo "Updated";
        update_option("lost_found_options", $option_output);
        die();
    }

    static function remove_user() {
        check_ajax_referer('lost_found_options', 'security');
        $key = intval($_POST['selection']);
        $type = $_POST['type'];
        $display_names = LostFoundMenu::get_display_names();

        if ("admins" == $type) {
            $admins = explode(",", get_option('lost_found_admins'));
            for ($x = 0; $x < count($admins); $x++) {
                if ($x <> $key)
                    $newadmins[] = $admins[$x];
            }
            update_option('lost_found_admins', implode(",", $newadmins));

            // Yes we kind of already have this but this formats the list
            echo LostFoundMenu::get_admin_names();
        } elseif ("email_people" == $type) {
            $email_people = explode(",", get_option('lost_found_email_list'));

            $newemail = array();
            for ($x = 0; $x < count($email_people); $x++) {
                if ($x <> $key)
                    $newemail[] = $email_people[$x];
            }
            update_option('lost_found_email_list', implode(",", $newemail));
            echo LostFoundMenu::get_email_people();
        }
        die();
    }

    static function email_checkbox() {
        check_ajax_referer('checkbox', 'security');
        $user = $_POST['user'];
        $checkbox = $_POST['checkbox'];
        $email_users = explode(",", get_option('lost_found_email_list'));

        // Change to empty array
        if ("" == $email_users[0])
            $email_users = array();

        // decide if we are adding or removing a user from the list.
        if ("true" == $checkbox) {
            // make sure they are not already there
            if (false == in_array($user, $email_users)) {
                $email_users[] = $user;
            }
            echo "You have been added to the email list.";
        } else {
            // see if they are there first
            if (true == in_array($user, $email_users)) {
                $key = array_search($user, $email_users);
                $email_users = array_merge(array_slice($email_users, 0, array_search($user, $email_users))
                        , array_slice($email_users, array_search($user, $email_users) + 1));
            }
            echo "You have been removed from the email list.";
        }
        $email_users = implode(",", $email_users);
        update_option('lost_found_email_list', $email_users);

        die();
    }

    // change the default sort order
    static function sort_order() {
        check_ajax_referer('lost_found_options', 'security');
        $sort_option = $_POST['selection'];
        $options = LostFoundMain::get_options();
        $option_keys = array_keys($options);
        $option_values = array_values($options);
        $option_output = "";

        // make sure the selection is valud
        if ("positive" <> $sort_option and
                "negative" <> $sort_option) {
            echo $sort_option;
            if ("positive" == $options['sort_order'])
                echo "Current: Oldest first";
            else
                echo "Current: Newest first";
            die();
        }


        // iterate through and rebuild the option string
        for ($x = 0; $x < count($option_keys); $x++) {
            if ("sort_order" == $option_keys[$x])
                $option_values[$x] = $sort_option;
            if ($option_output <> "")
                $option_output .= ",";
            $option_output .= $option_keys[$x] . "|" . $option_values[$x];
        }
        update_option("lost_found_options", $option_output);
        if ("positive" == $sort_option)
            echo "Current: Oldest first";
        else
            echo "Current: Newest first";
        die();
    }

    // date_format|m/d/Y,sort_order|positive
    // change the date format
    static function date_format() {
        check_ajax_referer('lost_found_options', 'security');
        $new_date_format = $_POST['selection'];
        $options = LostFoundMain::get_options();
        $option_keys = array_keys($options);
        $option_values = array_values($options);
        $option_output = "";
        // iterate through and rebuild the option string
        for ($x = 0; $x < count($option_keys); $x++) {
            if ("date_format" == $option_keys[$x])
                $option_values[$x] = $new_date_format;
            if ($option_output <> "")
                $option_output .= ",";
            $option_output .= $option_keys[$x] . "|" . $option_values[$x];
        }
        update_option("lost_found_options", $option_output);
        echo "Example: " . date($new_date_format, time());


        die();
    }

}

// end Ajax class

/** * ****************************
 * Main Class
 * Displays the different pages visible to the user
 * Queries the database and that kind of stuff
 */
class LostFoundMain {

    function new_item() {
        global $lfpath;
        global $post;
        global $lf_no_require_admin;
        $oktoshow = 0;
        if ($lf_no_require_admin) {
            $ouruser = wp_get_current_user();
            if ($ouruser->has_cap('upload_files'))
                $oktoshow = 1;
        }
        $page_guid = $this->get_post_guid();


        // include things for the media upload
        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');
        wp_enqueue_style('thickbox');
        wp_enqueue_style('lost_found');

        $navigator = false;

        // header navigation
        $navtab['show'] = "";
        $navtab['new'] = "nav-tab-active";
        $navtab['admin'] = "";

        $admin = $this->is_admin();

        // include the stuff
        ob_start();
        include ("$lfpath/lost-found-header-tpl.php");
        include ("$lfpath/lost-found-add-tpl.php");
        $output = ob_get_clean();
        return $output;
    }

    function process_item() {
        global $lost_found_table;
        global $wpdb;

        // if there is nothing submitted go away
        if (!isset($_POST['item_name']) or !isset($_POST['upload_image']))
            return $this->show_items();

        if ("" == $_POST['item_name'] or "" == $_POST['upload_image'])
            return $this->show_items("Item name and picture are required.");

        if (!wp_verify_nonce($_POST['security'], 'submit'))
            return $this->show_items();


        // check for duplicates
        $sql = "SELECT count(id) FROM $lost_found_table WHERE name = '%s' AND description = '%s'";
        $duplicate_check = $wpdb->get_var($wpdb->prepare($sql, $_POST['item_name'], $_POST['description']));
        if ($duplicate_check > 0)
            return $this->show_items("Duplicate Name/Description. Please try again.");


        $dir = wp_upload_dir();
        // get the name of the file less the upload directory
        $filename = str_replace($dir['baseurl'], "", $_POST['upload_image']);
        $user = wp_get_current_user();
        $submitted = date("Y-m-d H:i:s", time());
        $sql = "INSERT INTO $lost_found_table (name, description, " .
                "picname, status, submitted, updated, submitter, " .
                "hidden) VALUES (\"%s\", \"%s\", " .
                "\"$filename\", \"1\", \"$submitted\", \"$submitted\", " .
                "\"$user->ID\", \"0\")";
        $wpdb->query($wpdb->prepare($sql, $_POST['item_name'], $_POST['description']));
        return $this->show_items("Item successfully added.");
    }

    function process_claim($message = "") {
        global $lfpath;
        global $post;
        global $lost_found_table;
        global $wpdb;
        global $lf_date_format;
        global $lf_no_require_admin;
        $oktoshow = 0;
        if ($lf_no_require_admin) {
            $ouruser = wp_get_current_user();
            if ($ouruser->has_cap('upload_files'))
                $oktoshow = 1;
        }
        $page_guid = $this->get_post_guid();

        if (isset($_POST['claim_name']) and wp_verify_nonce($_POST['security'], 'claim')) {

            $name = $_POST['claim_name'];
            $phone = $_POST['claim_phone'];
            $email = $_POST['claim_email'];
            $item = intval($_GET['item']);
            if (0 == $item)
                return $this->show_items("Invalid item.");
            if ("" == $name)
                $message = "You must enter your name.";
            elseif ("" == $phone and "" == $email)
                $message = "You must enter a phone number or email address.";
            // validate a phone number and email
            elseif (!preg_match("/^([1]-)?[0-9]{3}-[0-9]{3}-[0-9]{4}$/i", $phone) and
                    !preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i", $email)) {
                $message = "Email or phone number invalid.";
            } else {
                // things are valid
                // will only update if it is valid
                $date = date("Y-m-d H:i:s", time());
                $sql = "UPDATE $lost_found_table SET status = '2', hidden = '1', " .
                        "claim_name = '%s', claim_email = '%s', claim_phone = " .
                        "'%s', updated = '%s' WHERE id = '%d' AND status = " .
                        "'1' AND hidden = '0'";
                $rows_changed = $wpdb->query($wpdb->prepare($sql, $name, $email, $phone, $date, $item));
                if ($rows_changed < 1)
                    return $this->show_items("Item already claimed.");
                // now need to email everybody on the list
                $email_list = explode(",", get_option('lost_found_email_list'));
                $subject = "Lost and found item claimed.";
                $site = get_bloginfo('wpurl');
                $permalink = get_permalink();
                $email_message = "You are subscribed to view emails when somebody claims and item " .
                        "in the lost and found from $site.\r\n\r\n" .
                        "To process this claim you can follow this link: " .
                        "$permalink&action=admin&item_choice=$item. \r\nHere is the information proved:\r\n\r\n" .
                        "\tName: $name\r\n\tEmail address: $email\r\n\tPhone number:$phone\r\n\r\n" .
                        "If you no longer want to receive these emails go to the link above and uncheck the " .
                        "\"I want to be emailed\" box.\r\n\r\nThank you.";
                // is anyone on the list?
                if ("" <> $email_list['0']) {
                    foreach ($email_list as $mail) {
                        $data = get_userdata($mail);
                        $name = $data->data->user_nicename;
                        if (strstr($data->data->user_email, "<") === false)
                            $name .= "<" . $data->data->user_email . ">";
                        else
                            $name .= $data->data->user_email;
                        wp_mail($name, $subject, $email_message);
                    }
                }

                return $this->show_items("Item claimed. You will be contacted soon.");
            }
        }

        wp_enqueue_style('lost_found');
        $lf_upload_dir = wp_upload_dir();
        $id = intval($_GET['item']);
        if (0 == $id)
            return $this->show_items();
        $sql = "SELECT * FROM $lost_found_table WHERE id = %d";
        $item = $wpdb->get_row($wpdb->prepare($sql, $id));

        $navigator = false;
        $admin = $this->is_admin();
        ob_start();
        include ("$lfpath/lost-found-header-tpl.php");
        include("$lfpath/lost-found-claim-tpl.php");
        $result = ob_get_clean();
        return $result;
    }

    function show_items($message = "") {
        global $lost_found_table;
        global $wpdb;
        global $lf_date_format;
        global $post;
        global $lfpath;
        global $lf_no_require_admin;
        $oktoshow = 0;
        if ($lf_no_require_admin) {
            $ouruser = wp_get_current_user();
            if ($ouruser->has_cap('upload_files'))
                $oktoshow = 1;
        }
        global $lf_sort_order;
        $page_guid = $this->get_post_guid();
        $nav_style = "";
        $total_pages = 1;
        $navigator = false;
        wp_enqueue_style('lost_found');

        // decide the sort order for the output.  Start with nothing.
        $order = "";
        // see if it should be positive / negative
        if ("positive" == $lf_sort_order)
            $order = "ORDER BY submitted ASC";
        elseif ("negative" == $lf_sort_order)
            $order = "ORDER BY submitted DESC";

        // where are the pictures?
        $lf_upload_dir = wp_upload_dir(); // user 'base_url'
        $sql = "SELECT * FROM $lost_found_table WHERE hidden <> 1 $order";
        $results = $wpdb->get_results($sql);

        // decide what page we are on.
        $item_count = count($results);

        // get page number and page size submitted if any
        if (isset($_GET['page_number'])) {
            $page_number = $_GET['page_number'];
        } else {
            $page_number = 1;
        }
        if (isset($_GET['page_size'])) {
            $page_size = intval($_GET['page_size']);
            // I doubt anybody will have a billion items.
            if ("all" == $page_size)
                $page_size = 999999999;
        } else {
            $page_size = 5;
        }
        if ($page_size < 5)
            $page_size = 5;

        $link_page_size = $page_size;

        if ($page_size > $item_count) {
            $page_size = $item_count;
            $link_page_size = "ALL";
        }


        $pages = "";
        // put the page navigation in when necessary
        if ($item_count > $page_size or $page_size > 5) {
            $navigator = true;

            // Decide what page we are at, etc.
            $total_pages = ceil($item_count / $page_size);


            // last page should be complete and not allow too many pages
            if ($page_size * $page_number > $item_count) {
                $page_number = $total_pages;
            }

            if ($total_pages < 1)
                $total_pages = 1;
            if ("first" == $page_number) {
                $page_number = 1;
            } elseif ("last" == $page_number) {
                $page_number = $total_pages;
            } else {
                $page_number = intval($page_number);
            }
            if ($page_number < 1)
                $page_number = 1;

            // make sure the final page is full
            $beginning_item = (($page_number - 1) * $page_size);
            $ending_item = $beginning_item + $page_size - 1;

            while ($ending_item > ($item_count - 1)) {
                $beginning_item--;
                $ending_item--;
            }
            if ($beginning_item < 0)
                $beginning_item = 0;


            // pull out only the items in our range
            for ($x = $beginning_item; $x <= $ending_item; $x++) {
                $new_results[] = $results[$x];
            }
            // pass on the new list
            $results = $new_results;

            $pages = "Page $page_number of $total_pages";

            // figure out how to center it
            $characters = strlen($pages);
            // calculating the shift depending on the length
            switch ($characters) {
                case 11:
                    $shift = 75;
                    break;
                case 12:
                    $shift = 73;
                    break;
                case 13:
                    $shift = 70;
                    break;
                case 14:
                    $shift = 65;
                    break;
                default:
                    $shift = 61;
                    break;
            }

            $nav_style = "left: " . $shift . "px;";
        }
        $less_page = $page_number - 1;
        if ($less_page < 1)
            $less_page = 1;
        $more_page = $page_number + 1;
        if ($more_page > $total_pages)
            $more_page = $total_pages;

        // header navigation
        $navtab['show'] = "nav-tab-active";
        $navtab['new'] = "";
        $navtab['admin'] = "";

        $admin = $this->is_admin();
        ob_start();
        include ("$lfpath/lost-found-header-tpl.php");
        include ("$lfpath/lost-found-show-tpl.php");
        $output = ob_get_clean();
        return $output;
    }

    /**
     * Returns the options
     *
     * returns an array of the user selectible options
     * @return type
     */
    static function get_options() {
        $options_array = explode(",", get_option('lost_found_options'));
        for ($x = 0; $x < count($options_array); $x++) {
            $this_option = explode("|", $options_array[$x]);
            $options[$this_option[0]] = $this_option[1];
        }
        return $options;
    }

    /**
     * This seems to hate the network install so I'm forcing it to bend to my will
     *  get_permalink seems to be what I really really want.
     */
    function get_post_guid() {
        global $post;
        $page_id = get_permalink();

        // force a www into the $page_id.  Doesn't seem to do this on the network install for some reason.
        // in any case this isn't hurting anything.
        if (!strstr($page_id, "www") and !strstr($page_id, "localhost")) {
            $page_id = str_replace("//", "//www.", $page_id);
        }
        if(!strstr($page_id,"?")) {
            $page_id .= "?";
        }
        return $page_id;
    }

    function admin_claims() {
        global $lfpath;
        global $post;
        global $lf_date_format;
        global $wpdb;
        global $lost_found_table;
        global $user;
        $page_guid = $this->get_post_guid();
        $oktoshow = 0;
        $page_guid = $this->get_post_guid();
        $nonce = wp_create_nonce("admin_edit");
        $message = "";
        if (false == $this->is_admin())
            return $this->show_items("You are not an admin.");

        // edit the thingie
        if (isset($_POST['admin_nonce']) and true ==
                wp_verify_nonce($_POST['admin_nonce'], "admin_edit")) {
            $name = $_POST['lfname'];
            $description = $_POST['lfdescription'];
            $id = intval($_POST['lfid']);
            if ($id > 0) {
                $updated = date("Y-m-d H:i:s", time());
                $sql = "UPDATE $lost_found_table SET name = '%s', description = '%s', " .
                        "updated = '$updated' WHERE id = '%d'";
                $wpdb->query($wpdb->prepare($sql, $name, $description, $id));
            }
        }

        wp_enqueue_style('lost_found');
        $on_email_list = "";
        $email_list = explode(",", get_option('lost_found_email_list'));
        // set up the get email checkbox (ajax function)
        if (in_array($user->ID, $email_list))
            $on_email_list = " checked ";
        $statuses = array("", "Unclaimed", "Pending", "Claimed");

        $dir = wp_upload_dir();
        $lf_upload_dir = $dir['baseurl'];

        // either limit it to hidden items or not
        $hidden = "WHERE status = '2' OR status = '1'";
        if (isset($_GET['show_hidden']))
            $hidden = "";

        $admin = true;
        $navigator = false;

        // check if we have chosen an item
        if (isset($_GET['item_choice'])) {
            $adminshow = "item_choice";
            $item_choice = intval($_GET['item_choice']);
            $sql = "SELECT * FROM $lost_found_table WHERE id = '%d'";
            $item = $wpdb->get_row($wpdb->prepare($sql, $item_choice));
        } elseif (isset($_GET['process_item'])) {
            $adminshow = "process_item";
        } elseif (isset($_GET['item_delete']) or isset($_GET['item_undelete'])) {
            $adminshow = "item_choice";
            if (isset($_GET['item_delete'])) {
                $item_choice = intval($_GET['item_delete']);
                $db_hidden = 1;
                $status = 3;
                $deleted = ", claim_name = '', claim_email = '', claim_phone = ''";
                $message = "Item has been deleted.";
            } else {
                $item_choice = intval($_GET['item_undelete']);
                $db_hidden = 0;
                $status = 1;
                $deleted = "";
                $message = "Item is no longer deleted.";
            }
            $updated = date("Y-m-d H:i:s", time());
            $sql = "UPDATE $lost_found_table SET hidden = '$db_hidden', status = '$status', " .
                    "updated='$updated' $deleted WHERE id = '$item_choice'";
            $wpdb->query($sql);

            $sql = "SELECT * FROM $lost_found_table WHERE id = '%d'";
            $item = $wpdb->get_row($wpdb->prepare($sql, $item_choice));
        } elseif (isset($_GET['item_claim']) or isset($_GET['item_unclaim']) or isset($_GET['item_unclaim']) or isset($_GET['item_clearinfo'])) {
            $adminshow = "item_choice";
            if (isset($_GET['item_claim'])) {
                $item_choice = intval($_GET['item_claim']);
                $message = "The item has been claimed";
                $sql = "UPDATE $lost_found_table SET hidden = '1', status = '3' WHERE " .
                        "id = '$item_choice'";
                $wpdb->query($sql);
            } elseif (isset($_GET['item_unclaim'])) {
                $item_choice = intval($_GET['item_unclaim']);
                $message = "The item has been un-claimed";
                $sql = "UPDATE $lost_found_table SET hidden = '0', status = '2' WHERE " .
                        "id = '$item_choice'";
                $wpdb->query($sql);
            } elseif (isset($_GET['item_clearinfo'])) {
                $item_choice = intval($_GET['item_clearinfo']);
                $message = "The information has been cleared";
                $sql = "UPDATE $lost_found_table SET hidden = '0', status = '1', " .
                        "claim_name = '', claim_email = '', claim_phone = '' WHERE id = '$item_choice'";
                $wpdb->query($sql);
            } else {
                $adminshow = "item_list";
            }

            $sql = "SELECT * FROM $lost_found_table WHERE id = '%d'";
            $item = $wpdb->get_row($wpdb->prepare($sql, $item_choice));
        } else { // Show the items & names
            $adminshow = "item_list";
            $sql = "SELECT * FROM $lost_found_table $hidden ORDER BY claim_name DESC";
            $result = $wpdb->get_results($sql);
        }


        ob_start();
        require_once("$lfpath/lost-found-header-tpl.php");
        require_once("$lfpath/lost-found-admin-tpl.php");
        $output = ob_get_clean();
        return $output;
    }

    function is_admin() {
        global $user;
        global $wpdb;
        if (0 == $user->ID)
            return false;
        $admins = explode(",", get_option('lost_found_admins'));

        $admin = true;
        if (false == array_search($user->ID, $admins) and
                (false == current_user_can('manage_options')))
            $admin = false;

        return $admin;
    }

}

/* * **************************
 * Menu Class
 * Does the options menu and all related whatevers
 * This is inside the admin section of WP
 */

class LostFoundMenu {

    /**
     * This is called by the add_action lost and found menu. Just generates a menu
     */
    static function show_menu() {
        add_options_page('Lost and Found options', 'Lost-and-found', 'manage_options', 'lost_found_menu', array('LostFoundMenu', 'options_page'));
    }

    /**
     * Shows the options page on the admin screen.
     */
    static function options_page() {
        global $path;
        $options = LostFoundMain::get_options();
        $lf_date_format = $options['date_format'];
        $lf_sort_order = $options['sort_order'];
        $lf_no_require_admin = $options['no_require_admin'];
        //must check that the user has the required capability
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        $current_sort = "";
        $sort_options = "<option";
        if ("positive" == $lf_sort_order) {
            $sort_options .= " SELECTED ";
            $current_sort = "Current: Oldest first";
        }
        $sort_options .= " value=\"positive\" >Oldest first</option>\r\n<option";
        if ("negative" == $lf_sort_order) {
            $sort_options .= " SELECTED ";
            $current_sort = "Current: Newest first";
        }
        $sort_options .= " value=\"negative\" >Newest first</option>";

        $user = wp_get_current_user();
        $user_id = $user->ID;
        $admins = get_option('lost_found_admins');
        if ("" == $admins)
            update_option('lost_found_admins', $user_id);
        elseif (false === strstr($admins, ",") and $admins <> $user_id)
            update_option('lost_found_admins', "$admins,$user_id");
        else {
            $admins = explode(",", $admins);
            if (false === in_array($user_id, $admins)) {
                $admins[] = $user_id;
                $admins = implode(",", $admins);
                update_option('lost_found_admins', $admins);
            }
        }

        $nonce = wp_create_nonce('lost_found_options');


        $admin_names = LostFoundMenu::get_admin_names();
        $email_names = LostFoundMenu::get_email_people();
        $display_names = LostFoundMenu::get_display_names();

        echo "<script type='text/javascript' >
    var please_wait_image = '<IMG src=\'" . admin_url() . "images/loading.gif\' >';
    var nonce = '$nonce';

</script>
<script type='text/javascript' src='" . plugin_dir_url(__FILE__) . "lost-found-admin.js' ></script>
<div class='lostfoundoptions' >
    <h2>Lost and found</h2>
    <h3>Options page</h3>

    <p>All configurable options are on this page.</p>

    <table class=' lostfound'>
	<tr class='widefat lostfound'>
	    <th class=' lostfound' style='width: 50%'>
		Current lost and found admins:
	    </th>
	    <th class=' lostfound' style='width: 50%'>
		Lost and found email recipients:
	    </th>
	</tr>
	<tr class='widefat lostfound'>
	    <td class=' lostfound'>
		<span id='lfadmins' >
		    $admin_names
		</span>
	    </td>
	    <td class=' lostfound'>
		<span id='lfemail_people'>
		    $email_names
		</span>
	    </td>
	</tr>
	<tr class='widefat lostfound' >
	    <td class=' lostfound'>
		Add user as admin:
		<select id='lfselectadmins' name='lfadmins' onChange=\"add_user('admins')\"'  style=\"min-width: 150px;\" >
		    <option value='0' > </option>";

        foreach ($display_names as $key => $name):
            echo "\t\t\t\t<option value='$key'>$name</option>\r\n";
        endforeach;

        echo "</select>
	    </td>
	    <td class=' lostfound'>
		Add user to email list:
		<select id='lfselectemail_people' name='lfemail_people' onChange=\"add_user('email_people')\"  style=\"min-width: 150px;\">
		    <option value='0' > </option>";
        foreach ($display_names as $key => $name):
            echo "\t\t\t\t<option value='$key'>$name</option>\r\n";
        endforeach;
        echo "</select>
	    </td>
	</tr>
   <tr class='widefat lostfound'><td class='lostfound' colspan=2>
      <input type=checkbox id='lfallowallusers' name='lfallowallusers' onChange=\"add_all_user()\" ";
        if ($lf_no_require_admin)
            echo "checked";
        echo " > Check to allow all users to add items. Note: users must have permission to upload pictures from wordpress (authors, editors, etc)
      <span id='lfallusercheck' ></span>
      </td></tr>
	<!-- blank line -->
	<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
	<tr class='widefat lostfound'>
	    <th class=' lostfound' style='width: 50%'>
		Date format: <a href='http://us2.php.net/manual/en/function.date.php' >(instructions here)</a>
	    </th>
	    <th class=' lostfound' style='width: 50%'>
		Default sort order:
	    </th>
	</tr>
	<tr class='widefat lostfound' >
	    <td class=' lostfound'>
	         <input type=text id='lf_date' value='$lf_date_format' onChange=\"submit_date()\" />
		 <a onclick=\"reset_date()\" >default format</a>
	    </td>
	    <td class=' lostfound'>
		<select id='lfsort_options' name='lfsort_option' onChange=\"sort_option()\" style=\"min-width: 150px;\" >
		" . $sort_options . "
		</select>
	</td>
        </tr>
	<tr class='widefat lostfound' >
	    <td class=' lostfound'>
		<input type=button value=\"Submit date format\" onClick=\"submit_date()\" />
		<span id='lf_date_example' >Example: " . date($lf_date_format, time()) . "</span>
	    </td>
	    <td class=' lostfound' style='vertical-align: middle;' >
		<span id='lf_cur_sort' >$current_sort</span>
	    </td>
        </tr>
    </table>
</div><!-- lostfoundoptions -->";
    }

    static function get_admin_names() {
        $admins = explode(",", get_option('lost_found_admins'));
        $admin_names = "";
        $display_names = LostFoundMenu::get_display_names();
        $user = wp_get_current_user();
        $user_id = $user->ID;
        if ($admins[0] <> "") {
            for ($x = 0; $x < count($admins); $x++) {
                $admin_names .= $display_names[$admins[$x]];
                if ($admins[$x] <> $user_id)
                    $admin_names .= " <span id='rm_admin_$x' >
		    <a onClick='remove_user(\"admins\",\"$x\")'>Click to remove admin.</a></span> ";
                $admin_names .= " <br/>\r\n";
            }
            return $admin_names;
        }
    }

    static function get_email_people() {
        $email_people = explode(",", get_option('lost_found_email_list'));
        $email_names = "";
        $display_names = LostFoundMenu::get_display_names();
        if ($email_people[0] <> "") {
            for ($x = 0; $x < count($email_people); $x++) {
                $email_names .= $display_names[$email_people[$x]] .
                        " <span id='rm_email_$x' ><a onClick='remove_user(\"email_people\",\"$x\")'>
			Click to remove email recipient.</a></span><br/>\r\n";
            }
        } else {
            $email_names = "There is nobody receiving email updates.\r\n";
        }
        return $email_names;
    }

    static function get_display_names() {
        global $wpdb;
        // get and format all users on the system.
        $sql = "SELECT ID,display_name from " . $wpdb->users;
        $name_array = $wpdb->get_results($sql);
        foreach ($name_array as $name) {
            $display_names[$name->ID] = $name->display_name;
        }
        return $display_names;
    }

}

/* * *******************************
 * Install Class
 * constains the install, update, deactivate, uninstall functions
 */

class LostFoundInstall {

    /**
     * Installs the database and the options
     */
    static function install() {
        // instructions here http://codex.wordpress.org/Creating_Tables_with_Plugins
        global $lost_found_version;
        // installing user is automatically an admin
        $user = wp_get_current_user();
        $id = $user->ID;
        $path = (plugin_dir_path(__FILE__));

        // create table. See function below for definition
        $sql = self::get_tables_sql();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        foreach ($sql as $sql) {
            dbDelta($sql);
        }

        // copy the css file if it doesn't exist
        if (!is_file("$path/lost-found.css"))
            copy("$path/lost-found-default.css", "$path/lost-found.css");

        // Add version info to options
        add_option("lost_found_version", $lost_found_version, '', 'no');

        // List of lost and found admins (wp user id)
        add_option("lost_found_admins", $id, '', 'no');

        // List of lost and found email recipients (wp user id)
        add_option("lost_found_email_list", '', '', 'no');

        // List of the options
        add_option("lost_found_options", 'date_format|m/d/Y,sort_order|positive', '', 'no');
    }

    /**
     * Checks the version in our file and runs db/option updates if necessary
     */
    static function update_check() {
        $path = (plugin_dir_path(__FILE__));
        global $lost_found_version;
        global $lf_upload_dir;
        if (get_option('lost_found_version') != $lost_found_version) {
            // dbDelta will change / upgrade the table as necessary.
            $sql = self::get_tables_sql();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            foreach ($sql as $sql) {
                dbDelta($sql);
            }
            $options = get_option("lost_found_options");

            // update 0.5 to 0.6
            // update to 0.4 to 0.5
            if (false == strpos($options, "sort_order")) {
                update_option("lost_found_options", "$options,sort_order|positive");
            }
            if (false == strpos($options, "no_require_admin")) {
                update_option("lost_found_options", "$options,no_require_admin|0");
            }

            // update css if the old version is 0.3 or older
            $old_version = get_option('lost_found_version');
            if ("0.1" == $old_version or "0.2" == $old_version or
                    "0.3" == $old_version) {
                copy("$path/lost-found-default.css", "$path/lost-found.css");
            }

            // copy the css file if it doesn't exist
            if (!is_file("$path/lost-found.css"))
                copy("$path/lost-found-default.css", "$path/lost-found.css");

            update_option('lost_found_version', $lost_found_version);
        }
    }

    /**
     * Deactivates which in our case does nothing.  If testing mode is on this kills the install
     *
     * @global boolean $testing_mode
     * @return boolean
     */
    static function deactivate() {
        // honestly I can't think of anything to do here
        // It would be bad to uninstall anything and I don't really
        // want to kill the preferences so we'll just go with:
        // testing mode check:
        global $testing_mode;
        if (true == $testing_mode)
            self::uninstall();
        return true;
    }

    /**
     * Uninstalls everything
     *
     * This kills all options set by the file, kills the database tables, deletes all attached
     * files, and deletes the file attachment directory.  No turning back here.
     *
     * @global type $wpdb
     * @global string $lost_found_table
     */
    static function uninstall() {
        // delete everything and disappear
        global $wpdb;
        global $lost_found_table;

        // goodbye sweet database tables
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $sql = "DROP TABLE " . $lost_found_table . ";";
        $wpdb->query($sql);


        // Goodbye options, I barely knew thee
        delete_option("lost_found_version");
        delete_option("lost_found_admins");
        delete_option("lost_found_email_list");
        delete_option("lost_found_options");
    }

    /**
     * Returns an array of the table schema.  Is accessed by the update and the install.
     *
     * @global string $lost_found_table
     * @return type
     */
    static function get_tables_sql() {
        global $lost_found_table;

        $sql[0] = "CREATE TABLE $lost_found_table (
                            id INT NOT NULL AUTO_INCREMENT,
                            name VARCHAR(100),
                            description TEXT,
                            picname VARCHAR(100),
                            status INT,
                            submitted DATETIME,
                            updated DATETIME,
                            submitter BIGINT(20),
                            hidden TINYINT,
			    claim_name VARCHAR(100),
			    claim_email VARCHAR(100),
			    claim_phone VARCHAR(100),
                            UNIQUE KEY id (id)
                        );
                        ";
        return $sql;
    }

}

?>