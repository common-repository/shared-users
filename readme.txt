=== Shared Users ===
Contributors: kruse
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=kruse%40kruse%2dnet%2edk&item_name=Donation%20for%20Shared%20Users%20plugin&no_shipping=0&no_note=1&tax=0&currency_code=DKK&lc=DK&bn=PP%2dDonationsBF&charset=UTF%2d8
Tags: admin, authentication, login, users
Requires at least: 2.3
Tested up to: 2.6.2
Stable tag: 1.1

Have your blog run off the user table of another blog, in the same database.

== Description ==

If you have multiple WordPress installations using the same database, this plugin makes it possible for one
installation to run off the user table of another installation. Main user table selection is completely configurable
through the options panel, and permissions of the users can be different from blog to blog.

When you switch the user table from one blog to another, all level 10 users (administrators) of the blog you are
switching **to** are also made level 10 users on the blog in which you installed the plugin. Only a level 10 user can
make the switch.

After you switch, you should visit the Users section of the Admin panel to setup permissions for all other users.
They will be listed as "No role for this blog" in WordPress 2.3, or with an empty Role column under "All Users" in WordPress 2.5/2.6.

Note that ideally the blog you are switching should not have more than the default admin user. Content created by all other users
will automatically be assigned to more or less random users from the main blog when you switch (based on `user_id`). Future versions
of the plugin may support migrating existing users and content.

== Installation ==

1. Upload `shared-users` folder to the `/wp-content/plugins/` directory
1. Login as an administrator
1. Activate Shared Users through the 'Plugins' menu in WordPress
1. Go to Options > Shared Users and select the blog whose users you would like to share
1. Save options, and the switch is done!

If you get logged out, login as an administrator on the blog you just selected in the options panel.
In case of problems, delete or rename the `shared_users` directory.

== Changelog ==

Version 1.1:

* Fixed a bug that made it impossible to change options since WordPress 2.5.