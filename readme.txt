=== Newsletter Optin Block ===
Contributors: pcescato
Tags: newsletter, mailjet, contact form 7, optin, block
Requires at least: 6.8
Requires PHP: 8.1
Tested up to: 6.8
Stable tag: 1.0.5
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Automatically injects a Contact Form 7 form into posts and syncs subscribers with a Mailjet list.

== Description ==
Newsletter Optin Block allows you to automatically inject a Contact Form 7 form into your WordPress posts and add subscribers to a Mailjet list of your choice.

- Select the Mailjet list in the admin
- WordPress repository compatible (escaping, sanitization, etc.)
- Robust API error handling
- Works without Composer

External services:
Mailjet API – allows:
- fetching Mailjet contact lists (https://api.mailjet.com/v3/REST/contactslist)
- adding a subscriber and returning a response code (https://api.mailjet.com/v3/REST/contacts)

== Installation ==
1. Upload the plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure your Mailjet API keys and the target list in Settings > Auto-injected Form.
4. Select the Contact Form 7 form to inject.
5. Go to the Customizer and style the message:

`
.newsopbl-thank-you-message {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
    border-radius: 4px;
    padding: 15px;
    margin: 20px 0;
    font-size: 16px;
    font-weight: 500;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
`

== Frequently Asked Questions ==
= Is Contact Form 7 required? =
Yes, this plugin requires Contact Form 7.

= Are subscribers added to Mailjet? =
Yes, if configured correctly, each submission will add the address to the selected Mailjet list.

== Changelog ==
= 1.0.0 =
* Initial release: automatic injection, Mailjet sync, admin configuration.

== Upgrade Notice ==
= 1.0.0 =
First stable version.

== Screenshots ==
1. Plugin settings in the admin
2. Form injected into a post

== License ==
This plugin is distributed under the GPLv3 license.
