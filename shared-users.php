<?php
/*
Plugin Name: Shared Users
Plugin URI: http://www.kruse-net.dk/wordpress-plugins/shared-users/
Description: Uses the user table from another Wordpress install.
Version: 1.1
Author: Jakob Kruse
Author URI: http://www.kruse-net.dk/
*/
?>
<?php
/*  Copyright 2007  Jakob Kruse  (email : kruse@kruse-net.dk)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?php
// Activation hook
register_activation_hook(__FILE__, 'shared_users_activate');
function shared_users_activate() {
  global $table_prefix;
  add_option('shared_users_prefix', $table_prefix, '', 'no');
}

// Function for setting up options page
add_action('admin_menu', 'shared_users_options');
function shared_users_options() {
  add_options_page('Shared Users Options', 'Shared Users', 10, __FILE__, 'shared_users_options_page');
}

// Function for displaying contents of options page
function shared_users_options_page() {
  global $wpdb, $table_prefix;
  
  // The current prefix
  $current_prefix = get_option('shared_users_prefix');
  
  // The local prefix
  $local_prefix = $table_prefix;
  
  // All possible prefixes
  $user_tables = $wpdb->get_col("SHOW TABLES LIKE '%users'");
  foreach ($user_tables as $table) {
    $prefixes[] = str_replace("users", "", $table);
  }
  
  // Page contents
  echo "<div class=\"wrap\">";
  echo "<h2>Shared Users Options</h2>";
  echo "<form method=\"post\" action=\"options.php\">";
  wp_nonce_field('update-options');
  
  echo "<table class=\"form-table\">";
  echo "<tr valign=\"top\">";
  echo "<th scope=\"row\">Share users from this blog</th>";
  echo "<td><select name=\"shared_users_prefix\">";
  foreach ($prefixes as $p) {
    $options = $p . "options";
    $blogname = $wpdb->get_var("SELECT option_value FROM $options WHERE option_name = 'blogname'");
    echo "<option value=\"$p\"", ($p == $current_prefix) ? " selected=\"selected\"" : "", ">$blogname", ($p == $local_prefix) ? " (turns off user sharing)" : "", "</option>";
  }
  echo "</select></td>";
  echo "</tr>";
  echo "</table>";
  
  echo "<input type=\"hidden\" name=\"action\" value=\"update\" />";
  echo "<input type=\"hidden\" name=\"page_options\" value=\"shared_users_prefix\" />";
  echo "<p class=\"submit\"><input type=\"submit\" name=\"Submit\" value=\"", __('Save Changes'), "\" class=\"button\" /></p>";
  echo "</form>";
  echo "</div>";
}

// Function called when prefix is changed
add_action('update_option_shared_users_prefix', 'shared_users_change_prefix', 10, 2);
function shared_users_change_prefix($old, $new) {
  global $wpdb;
  
  // Foreign usermeta table name
  $usermeta = $wpdb->escape($new . "usermeta");
  // Foreign user_level meta_key name
  $f_user_level = $wpdb->escape($new . "user_level");
  // Local user_level meta_key name
  $l_user_level = $wpdb->escape($wpdb->prefix . "user_level");
  // Foreign capabilities meta_key name
  $f_capabilities = $wpdb->escape($new . "capabilities");
  // Local capabilities meta_key name
  $l_capabilities = $wpdb->escape($wpdb->prefix . "capabilities");
  
  // Get a list of level 10 users in the foreign database
  $foreign10s = $wpdb->get_col("SELECT user_id FROM $usermeta WHERE meta_key = '$f_user_level' AND meta_value = 10");
  foreach ($foreign10s as $userid) {
    // Check if 'user_level' and 'capabilities' has already been copied
    $level = $wpdb->get_var("SELECT meta_value FROM $usermeta WHERE user_id = $userid AND meta_key = '$l_user_level'");
    $capabilities = $wpdb->get_var("SELECT meta_value FROM $usermeta WHERE user_id = $userid AND meta_key = '$f_capabilities'");
    if ($level === null) {
      // Not copied, let's insert
      $wpdb->query("INSERT INTO $usermeta SET user_id = $userid, meta_key = '$l_user_level', meta_value = 10");
      $wpdb->query("INSERT INTO $usermeta SET user_id = $userid, meta_key = '$l_capabilities', meta_value = '$capabilities'");
    } else {
      // Already copied, let's update
      $wpdb->query("UPDATE $usermeta SET meta_value = 10 WHERE user_id = $userid AND meta_key = '$l_user_level'");
      $wpdb->query("UPDATE $usermeta SET meta_value = '$capabilities' WHERE user_id = $userid AND meta_key = '$l_capabilities'");
    }
  }
  
  // By now, every level 10 user in the foreign installation should also be a level 10 user for the local installation
}

// On every startup, switch users and usermeta tables to other prefix
add_action('plugins_loaded', 'shared_users_patch_wpdb');
function shared_users_patch_wpdb() {
  global $wpdb;
  $prefix = get_option('shared_users_prefix');
  if ($prefix != "") {
    $wpdb->users = $prefix . "users";
    $wpdb->usermeta = $prefix . "usermeta";
  }
}

?>