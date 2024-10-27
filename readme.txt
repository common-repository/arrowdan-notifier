=== ARROWDAN Notifier ===
Contributors: arbn
Tags: notifier, notification, android, ios, flutter, firebase, fcm,
Requires at least: 4.6
Tested up to: 5.5.1
Stable tag: 1.0.1
Requires PHP: 5.6.20
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Notify users using Firebase Cloud Messaging (FCM) when post is published or updated. 

== Description ==
Send notification to Android & IOS app users whenever post is published or updated using Google service, Firebase Cloud Messaging / Firebase Push Notification. By default it is set to topic post so make sure your application user subscribes to topic post.

**Features**
* Notify on post publish/update with image
* Control directly from post sidebar

== Installation ==
1. Upload the plugin files to the `/wp-content/plugins/arrowdan-notifier` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the Settings->ARROWDAN Notifier from WordPress screen to configure the plugin.
4. From the option put Firebase Server Key in Server API Key field.
5. After saving API Key you can even test notification.

== Frequently Asked Questions ==
= Can I send notification to other topic ? =
No. You can send notification only to topic post.

== Screenshots ==

== Changelog ==
= 1.0.1 =
* Notification with image and post ID added
* Option to disable Notification and Image Notification
* Notification can be directly controlled from post
= 1.0.0 =
* First version.

