=== WP Telegram (Auto Post and Notifications) ===
Contributors: manzoorwanijk
Donate link: https://paypal.me/manzoorwanijk
Tags: telegram, notifications, posts, channel, group
Requires at least: 3.8
Tested up to: 4.9.8
Requires PHP: 5.2.4
Stable tag: 2.0.12
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integrate your WordPress site perfectly with Telegram with full control.

== Description ==
Integrate your WordPress site perfectly with Telegram with full control.

== Excellent LIVE Support on Telegram ==

**Join the Chat**

We have a public group on Telegram to provide help setting up the plugin, discuss issues, features, translations etc. Join [@WPTelegramChat](https://t.me/WPTelegramChat)
For rules, see the pinned message. No spam please.

== Modules ==

**1. Post to Telegram**

* 📝 Send posts automatically to Telegram when published or updated
* 📢 You can send to a Telegram Channel, Group, Supergroup or private chat
* 👥 Supports multiple Channels/chats
* 🙂 Has Message Template composer with Emojis
* 🖼 Supports sending featured image along with the text
* 🏞 You can choose to send only the Featured Image
* ⏱ Supports scheduled (future) posts
* 🕰 Messages can be delayed by a specific interval
* ⬜️ You can add an Inline button for the post URL
* 🛒 Supports WooCommerce products and other Custom Post Types
* ✒️ Direct Support for sending Custom Fields
* 🗃 You can send Custom Taxonomy Terms
* 📋 You can select the post types to be sent
* ⏲ You can choose when to send (New and/or existing posts)
* 🎛 Make use of Custom Rules to filter posts by authors, categories, tags, post formats or custom taxonomy terms
* 🎚 You can override the default settings on post edit page

**2. Private Notifications**

* 📧 Get your email notifications on Telegram
* 🔔 Supports WooCommerce order notifications, Contact Form 7 and other plugin notifications
* 🔕 Allow users to receive their email notifications on Telegram
* 🔐 Integrated with [WP Telegram Login](https://wordpress.org/plugins/wptelegram-login) to let users connect their Telegram.
* 🖊 Users can also enter their Telegram Chat ID manually on page


**3. Proxy**

* 🚫 If your host blocks Telegram, you can use this module
* ✅ Bypass the ban on Telegram by making use of proxy
* 😍 Option to use custom **Google Script as proxy**
* ❇️ Supports all proxies supported by PHP
* 🔛 You can select Proxy type - HTTP, SOCKS4, SOCKS4A, SOCKS5, SOCKS5_HOSTNAME

== Features ==

* **Excellent LIVE Support on Telegram**
* Easy to install and set up for the admin
* Fully customizable with actions and filters
* Can be extended with custom code
* Translation ready

**Get in touch**

*	Website [wptelegram.com](https://wptelegram.com)
*	Telegram [@WPTelegram](https://t.me/WPTelegram)
*	Facebook [@WPTelegram](https://fb.com/WPTelegram)
*	Twitter [@WPTelegram](https://twitter.com/WPTelegram)

**Contribution**
Development occurs on [Github](https://github.com/manzoorwanijk/wptelegram), and all contributions welcome.

**Translations**

Many thanks to the translators for the great job!

* [mohammadhero](https://profiles.wordpress.org/mohammadhero/) and [Aydin Mirzaie](http://mirzaie-aydin.com) (Persian)
* [Mirko Genovese](http://www.mirkogenovese.it) (Italian)
* [Mohamad Bush](https://profiles.wordpress.org/Mohamadbush) and Mohammad Taher (Arabic)
* [robertskiba](https://profiles.wordpress.org/robertskiba/) (German)
* [HellFive Osborn](https://t.me/HellFiveOsborn) (Portuguese Brazilian)
* [Oxford](http://radiowolf.ru) and [Artem Rez](https://profiles.wordpress.org/zzart) (Russian)
* [jdellund](https://profiles.wordpress.org/jdellund) (Catalan)
* [Jack Kuo](https://profiles.wordpress.org/ggsnipe) (Chinese (Taiwan))

Note: You can also contribute in translating this plugin into your local language. Join the Chat (above)


== Installation ==

1. Upload the `wptelegram` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the Plugins menu in WordPress. After activation, you should see the menu of this plugin the the admin
3. Configure the plugin.

**Enjoy!**

== Frequently Asked Questions ==

= How to create a Telegram Bot =

[How do I create a bot?](https://core.telegram.org/bots/faq#how-do-i-create-a-bot).


== Screenshots ==

1. Basic Settings
2. Advanced Settings
3. Post To Telegram (P2TG) Settings 
4. P2TG Settings (Cont...)
5. P2TG Settings (Cont...)
6. P2TG Settings (Cont...)
7. Private Notification Settings
8. User profile page
9. Proxy Settings
10. Proxy Settings (Cont...)
11. P2TG Post Edit Page

== Changelog ==

= 2.0.13 =
* Removed the override metabox from the post types not chosen to be sent

= 2.0.12 =
* Fixed the Notification issue caused by some faulty plugins
* Fixed the issue with Post to Telegram caused by Cron Control
* JS fixes

= 2.0.11 =
* Fixed the bug when scheduling the posts

= 2.0.10 =
* Delayed loading of modules to fix the translation issues
* Fixed the HTML entity issue for Markdown
* re-enabled sending password protected posts
* Added support for saving override options for Pending posts
* Minor fixes

= 2.0.9 =
* Fixed Send to Telegram button for Drafts
* Added support for saving override options for drafts and future posts
* Removed the ugly newline character at the beginning of the message when using Single Message with Image after the text
* Added Disable Notifications in override options
* Fixed the issue with saving of "Send files by URL" option
* Minor fixes

= 2.0.8 =
* Added the logging feature for debugging
* Added the option to upload the files
* Improved the proxy handling
* Changed the way Bot API creates logs
* Minor fixes

= 2.0.7 =
* Fixed the 404 CSS error for public.min.css
* Added the delAy options for posts
* Restored the old user profile field for Chat ID
* Added Bot Username field 

= 2.0.6 =
* Fixed the 404 JS error for public.min.js

= 2.0.5 =
* Fixed the override metabox issue

= 2.0.4 =
* Fixed the override metabox issue caused by other JS errors

= 2.0.3 =
* Fixed the issue with image being sent as caption

= 2.0.2 =
* Fixed the issue caused by is_success()

= 2.0.0 =
* Major Release with full revamp
* Added modular functionality
* Removed PHP 5.3 requirement to avoid double posting

== Upgrade Notice ==

= 1.3.0 =
* Goto WP Telegram Settings and just save the settings (Recommended)
