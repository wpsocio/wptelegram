# Changelog

All notable changes to this project are documented in this file.

## Unreleased

## [3.1.9 - 2022-06-12](https://github.com/wpsocio/wptelegram/releases/tag/v3.1.9)

### Enhancements

- Improved logging options to prevent users from mistakes

## [3.1.8 - 2022-03-26](https://github.com/wpsocio/wptelegram/releases/tag/v3.1.8)

### Bug fixes

- Fixed PHP error on plugin update
- Fixed Post to Telegram rule search bug

## [3.1.7 - 2021-12-31](https://github.com/wpsocio/wptelegram/releases/tag/v3.1.7)

### Enhancements

- Added "Protect content" option to Post to Telegram.

## [3.1.6 - 2021-12-29](https://github.com/wpsocio/wptelegram/releases/tag/v3.1.6)

### Bug fixes

- Misc bug fixes
- Fixed some typos

## [3.1.5 - 2021-10-23](https://github.com/wpsocio/wptelegram/releases/tag/v3.1.5)

### Enhancements

- Added override option for Send Featured Image.

## [3.1.4 - 2021-10-17](https://github.com/wpsocio/wptelegram/releases/tag/v3.1.4)

### Bug fixes

- Fixed posts not sent when Formatting is None

## [3.1.3 - 2021-09-15](https://github.com/wpsocio/wptelegram/releases/tag/v3.1.3)

### Enhancements

- Added `{post_slug}` macro

## [3.1.2 - 2021-07-5](https://github.com/wpsocio/wptelegram/releases/tag/v3.1.2)

### Bug fixes

- Fixed admin page not shown just after upgrade
- Fixed multiple empty lines in post content and excerpt

## [3.1.1 - 2021-06-10](https://github.com/wpsocio/wptelegram/releases/tag/v3.1.1)

### Bug fixes

- Fixed file upload for CloudFlare proxy

## [3.1.0 - 2021-05-30](https://github.com/wpsocio/wptelegram/releases/tag/v3.1.0)

### Enhancements

- Added CloudFlare Proxy support 🚀
- Added `{post_type}` and `{post_type_label}` macros

## [3.0.11 - 2021-05-7](https://github.com/wpsocio/wptelegram/releases/tag/v3.0.11)

### Bug fixes

- Fixed WooCommerce REST API products not sent to Telegram

## [3.0.10 - 2021-04-28](https://github.com/wpsocio/wptelegram/releases/tag/v3.0.10)

### Enhancements

- Added explicit filter for post edit switch
- Further improved logging for better troubleshooting
- Updated FAQ section

### Bug fixes

- Fixed the issue of settings not saved due to trailing slash redirects

## [3.0.9 - 2021-04-18](https://github.com/wpsocio/wptelegram/releases/tag/v3.0.9)

### Bug fixes

- Fixed the upgrade for Post to Telegram rules
- Fixed invalid argument error for failed post updates
- Fixed duplicate posts when using bulk import
- Fixed i18n for block editor override settings
- Fixed the Disable Notification settings not being reflected on post edit page

## [3.0.8 - 2021-04-16](https://github.com/wpsocio/wptelegram/releases/tag/v3.0.8)

### Bug fixes

- Fixed saving empty channels crashing the settings page
- Fixed "Changes could not be saved" error for old upgrades

## [3.0.7 - 2021-04-9](https://github.com/wpsocio/wptelegram/releases/tag/v3.0.7)

### Bug fixes

- Fixed the issue of scheduled posts being sent regardless of the overrides.
- Removed "Post edit switch" from post edit page when disabled.
- Minor admin UI fixes.

## [3.0.6 - 2021-04-7](https://github.com/wpsocio/wptelegram/releases/tag/v3.0.6)

### Bug fixes

- Fixed "Send to Telegram" flag not saved for block editor drafts.

## [3.0.5 - 2021-04-1](https://github.com/wpsocio/wptelegram/releases/tag/v3.0.5)

### Bug fixes

- Fixed the issue of posts being sent from block editor regardless of the rules.
- Fixed the issue of disabled Test Token button.
- Fixed the issue of delayed posts not sent.
- Fixed the empty rules being saved, preventing posts from being sent to Telegram.

## [3.0.4 - 2021-03-30](https://github.com/wpsocio/wptelegram/releases/tag/v3.0.4)

### Bug fixes

- Fixed wrong template when using CMB2 override settings.

## [3.0.3 - 2021-03-29](https://github.com/wpsocio/wptelegram/releases/tag/v3.0.3)

### Enhancements

- Improved logging for better diagnosis

### Bug fixes

- Fixed the wrong post data when importing posts
- Fixed the issue caused by upgrade for fresh installations
- Fixed the settings not saved issue
- Fixed the Update failed issue in block editor for drafts
- Fixed the fatal error for old block editor posts

## [3.0.2 - 2021-03-21](https://github.com/wpsocio/wptelegram/releases/tag/v3.0.2)

### Bug fixes

- Fixed the issue of posts not sent when using WP CLI

## [3.0.1 - 2021-03-20](https://github.com/wpsocio/wptelegram/releases/tag/v3.0.1)

### Bug fixes

- Fixed the last messed up update

## [3.0.0 - 2021-03-20](https://github.com/wpsocio/wptelegram/releases/tag/v3.0.0)

### Enhancements

- Switched to PHP namespaces
- Removed CMB2 dependency
- Improved and changed admin UI to single page
- Added option for inline button URL source
- Added option to change the inline button icon
- Better support for block editor override settings

### Bug fixes

- Fixed the YouTube links being stripped out from the content.
- Minor fixes.

## [2.2.5 - 2021-01-23](https://github.com/wpsocio/wptelegram/releases/tag/v2.2.5)

### Enhancements

- Improved the logic to decide new and existing posts
- Added support for PHP 8

### Bug fixes

- Fixed errors for PHP 8

## [2.2.4 - 2021-01-3](https://github.com/wpsocio/wptelegram/releases/tag/v2.2.4)

### Enhancements

- Added links to view logs

### Bug fixes

- Fixed the bug in notifications when email has multiple recepients.

## [2.2.3 - 2020-10-3](https://github.com/wpsocio/wptelegram/releases/tag/v2.2.3)

### Bug fixes

- Fixed 404 for CSS map files

### Enhancements

- Moved Telegram user ID field to WP Telegram Login

## [2.2.2 - 2020-08-16](https://github.com/wpsocio/wptelegram/releases/tag/v2.2.2)

### Bug fixes

- Fixed HTML characters in categories as hashtags.
- Fixed admin menu icon

## [2.2.1 - 2020-08-1](https://github.com/wpsocio/wptelegram/releases/tag/v2.2.1)

### Enhancements

- Update for WP Telegram Pro

## [2.2.0 - 2020-06-14](https://github.com/wpsocio/wptelegram/releases/tag/v2.2.0)

### Enhancements

- Unified Telegram user ID for all plugins

## [2.1.15 - 2020-04-5](https://github.com/wpsocio/wptelegram/releases/tag/v2.1.15)

### Enhancements

- Added all the registered taxonomies to macros for Message Template

## [2.1.13 - 2020-03-8](https://github.com/wpsocio/wptelegram/releases/tag/v2.1.13)

### Enhancements

- Added "Plugin generated posts" option to allow posts not created by humans.

## [2.1.12 - 2019-12-15](https://github.com/wpsocio/wptelegram/releases/tag/v2.1.12)

### Bug fixes

- Fixed double posting by block editor.

## [2.1.11 - 2019-11-15](https://github.com/wpsocio/wptelegram/releases/tag/v2.1.11)

### Bug fixes

- Fixed the text input styles.

## [2.1.10 - 2019-10-23](https://github.com/wpsocio/wptelegram/releases/tag/v2.1.10)

### Enhancements

- Updated bot token pattern to handle the latest change in tokens.

### Bug fixes

- Fixed the warning for old log files not found.

## [2.1.9 - 2019-09-10](https://github.com/wpsocio/wptelegram/releases/tag/v2.1.9)

### Enhancements

- Improved and secured logs by hashed names and by switching to wp_filesystem.
- Dropped support for PHP < 5.6 and WP < 4.7

### Bug fixes

- Prevent the notification links being previewed

## [2.1.8 - 2019-09-1](https://github.com/wpsocio/wptelegram/releases/tag/v2.1.8)

### Bug fixes

- Fixed the issue with posts not being sent when published via WP REST API

## [2.1.7 - 2019-08-19](https://github.com/wpsocio/wptelegram/releases/tag/v2.1.7)

### Bug fixes

- Fix the issue of products not being sent when published via WC REST API
- Fix CMB2 field conflict with Rank Math plugin

## [2.1.6 - 2019-07-1](https://github.com/wpsocio/wptelegram/releases/tag/v2.1.6)

### Bug fixes

- Fix the fatal error caused on post edit page.

## [2.1.5 - 2019-07-1](https://github.com/wpsocio/wptelegram/releases/tag/v2.1.5)

### Bug fixes

- Fixed the issue of unintended posts being scheduled for delay

## [2.1.4 - 2019-06-21](https://github.com/wpsocio/wptelegram/releases/tag/v2.1.4)

### Bug fixes

- Fixed the long integer chat ID issue when using Google Script
- Fixed the Notification Chat IDs sanitization issue
- Minor fixes

## [2.1.3 - 2019-04-13](https://github.com/wpsocio/wptelegram/releases/tag/v2.1.3)

### Bug fixes

- Fixed the new lines removed by classic editor

## [2.1.2 - 2019-04-4](https://github.com/wpsocio/wptelegram/releases/tag/v2.1.2)

### Enhancements

- Improved the proxy hooking

### Bug fixes

- Fixed the fatal error with helper function
- Fixed the issue with delayed posts

## [2.1.1 - 2019-03-27](https://github.com/wpsocio/wptelegram/releases/tag/v2.1.1)

### Bug fixes

- Fixed the step in delay on post edit page

## [2.1.0 - 2019-03-26](https://github.com/wpsocio/wptelegram/releases/tag/v2.1.0)

### Enhancements

- Improved the template conditional logic
- Made delay to be more granular - can be set in half a minute steps
- Added option to allow newlines in `{post_excerpt`
- Added option to send `{categories}` as hashtags

### Bug fixes

- Fixed the issue with scheduled posts not being sent to Telegram

## 2.0.19

- Added the conditional logic for Message Template

## 2.0.16

- Fixed the HTML bug in Notifications

## 2.0.15

- Fixed the PHP fatal error

## 2.0.14

- Fixed the PHP fatal error

## 2.0.13

- Added fixes for WP 5+
- Fixed double posting by block editor
- Fixed the issue with Override Options not expanding in Block Editor
- Improved the logging to include logs about featured image
- Removed the override metabox from the post types not chosen to be sent
- Added tutorial videos in the sidebar
- Updated CMB2

## 2.0.12

- Fixed the Notification issue caused by some faulty plugins
- Fixed the issue with Post to Telegram caused by Cron Control
- JS fixes

## 2.0.11

- Fixed the bug when scheduling the posts

## 2.0.10

- Delayed loading of modules to fix the translation issues
- Fixed the HTML entity issue for Markdown
- re-enabled sending password protected posts
- Added support for saving override options for Pending posts
- Minor fixes

## 2.0.9

- Fixed Send to Telegram button for Drafts
- Added support for saving override options for drafts and future posts
- Removed the ugly newline character at the beginning of the message when using Single Message with Image after the text
- Added Disable Notifications in override options
- Fixed the issue with saving of "Send files by URL" option
- Minor fixes

## 2.0.8

- Added the logging feature for debugging
- Added the option to upload the files
- Improved the proxy handling
- Changed the way Bot API creates logs
- Minor fixes

## 2.0.7

- Fixed the 404 CSS error for public.min.css
- Added the delAy options for posts
- Restored the old user profile field for Chat ID
- Added Bot Username field

## 2.0.6

- Fixed the 404 JS error for public.min.js

## 2.0.5

- Fixed the override metabox issue

## 2.0.4

- Fixed the override metabox issue caused by other JS errors

## 2.0.3

- Fixed the issue with image being sent as caption

## 2.0.2

- Fixed the issue caused by is_success()

## 2.0.0

- Major Release with full revamp
- Added modular functionality
- Removed PHP 5.3 requirement to avoid double posting

## 1.9.4

- Fixed the double posting problem caused by the last update
- Added the filter for default inline button

## 1.9.3

- Fixed the issue with Scheduled posts caused by previous update

## 1.9.2

- Fixed the issue of category/author filter for future posts
- Added the filter to explicitly change the Inline URL Button text

## 1.9.1

- Fixed the inline keyboard issue with image posts
- Fixed the double posting problem due to some plugins
- Other fixes

## 1.9.0

- Removed the API validation of bot token upon saving the settings
- Minor fixes

## 1.8.3

- Fixed the fatal error when using Google Script

## 1.8.2

- Added option to add inline button for Post URL
- Added support for WP-CLI
- Fixed the issue with spaces in WP Tags
- Minor fixes

## 1.8.1

- A few more hooks and filters
- Updated German translation, thanks to @robertskiba
- Minor fixes

## 1.8.0

- Added support for sending files along with the post.
- A few more hooks and filters
- Minor fixes

## 1.7.9

- Fixed the issue with sending test messages

## 1.7.8

- Added the support for bypassing blockage using Google App Script.
- Fixed the issue with double quotes in message template
- Minor fixes

## 1.7.7

- Fixed the issue with saving the settings with proxy

## 1.7.6

- Added support for many proxy types

## 1.7.5

- Added the hidden support for proxy
- Added hooks to bot API for modifying curl handle

## 1.7.4

- Added the latest update for Bot API Library
- Increased the default request timeout
- Added few more hooks for bot API request params

## 1.7.3

- Fixed the syntax error in previous update

## 1.7.2

- Some more control for user permissions
- Fixed the issue of bot token loss when saving the settings

## 1.7.1

- Added new filters for controlling the sent message

## 1.7.0

- Revamped Telegram Bot API Library to make it more portable
- Changed a few hooks to avoid confusion
- Added Catalan translation. Thanks to jdellund
- Minor fixes

## 1.6.5

- Enabled `parse_mode` for in image caption

## 1.6.4

- Added few more hooks for more control and customizations

## 1.6.3

- Added Russian translation. Thanks to Oxford
- Updated EmojiOne Area library to v3.2.6 to enable emoji search
- Updated Select2 library to v4.0.5

## 1.6.2

- Added method for creating API log
- Added method to modify curl handle for file uploads
- More filters to control the process
- Bug fixes

## 1.6.1

- Fixed the Fatal Error caused by WP_Error when saving the settings
- Added Portuguese Brazilian translation. Thanks to HellFive Osborn
- Fixed the issue caused by unending Markdown which stopped notifications

## 1.6.0

- Total revamp of the notification sending mechanism
- Allow users to receive email notifications on Telegram
- Added compatibility with every plugin that uses `wp_mail()` to send emails
- Fixed bugs in notification processing

## 1.5.7

- Fixed the issue of posts not being sent when published by cron
- Fixed the hyperlink issue in content URLs after the previous update
- Added more filters to control the way post_content and post_excerpt are processed

## 1.5.6

- Added German translation. Thanks to [Muffin](https://t.me/Muffin)
- Fixed post_date format and localization issue.
- Fixed shortcode issue in post_content
- Improved processing of post_content and post_excerpt
- Added option to choose the way consecutive messages are sent
- Fixed caption issue when sending image after the text
- Improved plugin strings for easy translations
- Bug fixes and performance improvements

## 1.5.4

- Added Italian translation. Thanks to [Mirko Genovese](http://www.mirkogenovese.it)
- Added Arabic translation. Thanks to @Mohamadbush and Mohammad Taher
- Fixed the HTML parsing issue when using Content before Read More tag as Excerpt Source
- Added hooks before and after sending the message
- Added `{post_date}` and `{post_date_gmt}` macros to be used in Message Template

## 1.5.3

- Added Persian translation. Thanks to [mohammadhero](https://profiles.wordpress.org/mohammadhero/)

## 1.5.2

- Added hooks and filters for post title, author, excerpt, featured_image etc.
- Final support for the search plugin

## 1.5.1

- Fixed the warning for undefined index when not using categories/terms restriction

## 1.5.0

- Added support for Read More tag to be used in Excerpt Source
- Improved Telegram API as a Library for developers to use
- Many upgrades to provide basis for future plugin(s)
- Minor fixes

## 1.4.3

- Fixed the bug with scheduled posts when using override switch

## 1.4.2

- Fixed the unwanted warning about invalid bot token

## 1.4.1

- Fixed warnings when settings not saved
- Added language pack for translations
- Minor fixes

## 1.4

- Introducing Website notifications to Telegram
- Dropped support for WordPress 3.5 and older

## 1.3.8

- Filter posts by author
- Filter posts by categories or terms of custom taxonomies
- You can now explicitly set Excerpt Source
- Performance improvements

## 1.3.7

- Delayed `save_post` hook execution to fix the issue with some custom fields
- Added filters to give you more control over macros and their values
- Added separate filters for modifying the values of individual custom fields and taxonomies
- Minor fixes

## 1.3.6

- Now Featured Image can be sent after the text
- Image and text can be send in a single message

## 1.3.5

- Now Featured Image can be sent with Caption
- Caption source can explicitly be chosen
- Added support for sending only Featured Image
- Minor fixes

## 1.3.4

- Fixed the text issue with scheduled posts

## 1.3.3

- Optimized Settings tabs for small screens
- Added tab icons to fit on small screens
- Minor fixes

## 1.3.2

- Fixed message template issue in post edit screen

## 1.3.0

- Total revamp of the settings page
- Added tabbed interface to reduce scrolling
- Added a beautiful template editor with emojis :)
- Added direct support for Custom Post Type selection
- Added the option to choose Channel/chat at the post edit screen
- Preserve override option for Scheduled (future) Posts
- Bug fixes for older WordPress versions

## 1.2.0

- Added support for PHP 5.2
- Minor bug fixes

## 1.1.0

- Added direct support for Custom Fields
- Added support for including {taxonomy} in template
- Fixed HTML issue with {content}

## 1.0.9

- Fixed HTML Parse Mode issue
- Fixed URL issue in Markdown style

## 1.0.8

- Added support for scheduled posts
- Fixed HTML Entities issue in the text

## 1.0.6

- Fixed excerpt length bug

## 1.0.5

- Minor fixes

## 1.0.4

- Updated README

## 1.0.3

- Minor fixes

## 1.0.2

- Changed the override option to make it more versatile
- Bug fixes

## 1.0.0

- Initial Release.
