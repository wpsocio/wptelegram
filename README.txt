=== WP Telegram (Auto Post and Notifications) ===
Contributors: manzoorwanijk
Donate link: https://paypal.me/manzoorwanijk
Tags: telegram, notifications, posts, channel, group
Requires at least: 4.0
Tested up to: 5.2.2
Requires PHP: 5.2.4
Stable tag: 2.1.6
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
* ⏳ Supports Conditional logic inside Message Template
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

https://www.youtube.com/watch?v=MFTQo3ObWmc

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
7. P2TG Settings (Cont...)
8. Private Notification Settings
9. User profile page
10. Proxy Settings
11. Proxy Settings (Cont...)
12. P2TG Post Edit Page (Classic Editor)
13. P2TG Post Edit Page (Block Editor)

== Changelog ==

= 2.1.6 =
* Fix the fatal error caused on post edit page.

= 2.1.5 =
* Fixed the issue of unintended posts being scheduled for delay

= 2.1.4 =
* Fixed the long integer chat ID issue when using Google Script
* Fixed the Notification Chat IDs sanitization issue
* Minor fixes

= 2.1.4 =
* Fixed the long integer chat ID issue when using Google Script
* Fixed the Notification Chat IDs sanitization issue
* Minor fixes

= 2.1.3 =
* Fixed the new lines removed by classic editor

= 2.1.2 =
* Improved the proxy hooking
* Fixed the fatal error with helper function
* Fixed the issue with delayed posts

= 2.1.1 =
* Fixed the step in delay on post edit page

= 2.1.0 =
* Improved the template conditional logic
* Made delay to be more granular - can be set in half a minute steps
* Added option to allow newlines in `{post_excerpt`
* Added option to send `{categories}` as hashtags
* Fixed the issue with scheduled posts not being sent to Telegram

== Upgrade Notice ==

= 1.3.0 =
* Goto WP Telegram Settings and just save the settings (Recommended)
