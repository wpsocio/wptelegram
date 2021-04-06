=== WP Telegram (Auto Post and Notifications) ===
Contributors: manzoorwanijk
Donate link: https://paypal.me/manzoorwanijk
Tags: telegram, notifications, posts, channel, group
Requires at least: 5.3
Tested up to: 5.7
Requires PHP: 7.0
Stable tag: 3.0.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integrate your WordPress site perfectly with Telegram with full control.

== Description ==
Integrate your WordPress site perfectly with Telegram with full control.

== Excellent LIVE Support on Telegram ==

**Join the Chat**

We have a public group on Telegram to provide help setting up the plugin, discuss issues, features, translations etc. Join [@WPTelegramChat](https://t.me/WPTelegramChat)
For rules, see the pinned message. No spam please.

**Upgrade to Pro**

WP Telegram Pro comes with more powerful features to give you more control. [Upgrade NOW](https://wptelegram.pro)

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

[WP Telegram Pro](https://wptelegram.pro) supports multiple channels based upon category/tag/author/post type etc. and also supports unlimited Reaction buttons.

**2. Private Notifications**

* 📧 Get your email notifications on Telegram
* 🔔 Supports **WooCommerce** order notifications, **Contact Form 7** and other plugin notifications
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

**Getting Started | Post to Telegram**

https://www.youtube.com/watch?v=m48V-gWz9-o

**WooCommerce, CF7 etc. Notifications**

https://www.youtube.com/watch?v=gVJCtwkorMA

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
2. Post To Telegram Settings 
3. Post To Telegram Settings (Cont...)
4. Post To Telegram Settings (Cont...)
5. Post To Telegram Settings (Cont...)
6. Private Notifications Settings
7. Private Notifications Settings (Cont...)
8. Proxy Settings
9. Proxy Settings (Cont...)
10. Advanced Settings
11. Post Edit Page (Classic Editor)
12. Post Edit Page (Block Editor)
13. Post Edit Page (Block Editor)

== Changelog ==

= 3.0.6 =
- Fixed "Send to Telegram" flag not saved for block editor drafts.

= 3.0.5 =
- Fixed the issue of posts being sent from block editor regardless of the rules.
- Fixed the issue of disabled Test Token button.
- Fixed the issue of delayed posts not sent.
- Fixed the empty rules being saved, preventing posts from being sent to Telegram.

= 3.0.4 =
- Fixed wrong template when using CMB2 override settings.

= 3.0.3 =
- Improved logging for better diagnosis
- Fixed the wrong post data when importing posts
- Fixed the issue caused by upgrade for fresh installations
- Fixed the settings not saved issue
- Fixed the Update failed issue in block editor for drafts
- Fixed the fatal error for old block editor posts

= 3.0.2 =
- Fixed the issue of posts not sent when using WP CLI

= 3.0.1 =
- Fixed the last messed up update

= 3.0.0 =
- Switched to PHP namespaces
- Removed CMB2 dependency
- Improved and changed admin UI to single page
- Added option for inline button URL source
- Added option to change the inline button icon
- Better support for block editor override settings
- Fixed the YouTube links being stripped out from the content.
- Minor fixes.
