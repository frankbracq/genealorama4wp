=== Secure Iframe Embed for Genealorama ===
Contributors: fbracq
Tags: iframe, embed, genealogy, secure, authentication
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 2.2.1
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Secure iframe integration to embed the Genealorama web application into WordPress sites with dedicated page templates and credential validation.

== Description ==

**Secure Iframe Embed for Genealorama** is a WordPress plugin designed to integrate the Genealorama web application into any WordPress site through interactive and secure iframes.

It allows logged-in users to **view, visualize, or interact with genealogical research results** while maintaining secure access rights.

= ✨ Automatic Installation =

Upon activation, the plugin:
✅ **Automatically creates a "Genealogy" page** accessible at `/genealorama/`
✅ Applies a full-page template (without WordPress header/footer)
✅ Configures integration with the shortcode `[genealorama_embed auto_height="true"]`

**No manual configuration needed** to get started!

= 🔧 Features =

- ✅ **Automatic dedicated page**: A `/genealorama/` page created on activation
- ✅ **Immersive template**: Full-page display without distractions  
- ✅ **Flexible shortcode**: `[genealorama_embed]` usable on any page
- ✅ **Enhanced security**: HMAC-SHA256 signature for authentication
- ✅ **Members only**: Access limited to logged-in users
- ✅ **Adaptive height**: Automatic iframe resizing
- ✅ **Bidirectional communication**: Support for messages between iframe and parent site
- 🆕 **Credential validation**: Automatic and manual verification of API keys

= 🔒 Security =

- **Authentication required**: Users must be logged in to WordPress
- **HMAC signature**: Each request is cryptographically signed
- **Temporal validation**: Protection against replay attacks
- **Iframe isolation**: Secure communication via postMessage
- 🆕 **Error detection**: Automatic alert if credentials are invalid

= 📖 Usage =

Use the shortcode on any page or post:
`[genealorama_embed]`

With options:
`[genealorama_embed auto_height="true" fullscreen="true"]`

= Shortcode Parameters =

* `src` - Application URL (default: `https://genealogie.app/iframe-entry/`)
* `auto_height` - Automatic height adjustment (default: `true`)
* `fullscreen` - Full screen mode (default: `false`)

== Installation ==

1. Download the latest version from the releases page
2. Upload the `.zip` file via Plugins > Add New > Upload Plugin
3. Activate the plugin
4. **Ready!** Visit `/genealorama/` on your site

= API Keys Configuration =

To activate integration with genealogie.app:

1. Go to **Settings > Genealorama Settings**
2. Enter your **Email** and click "Get My Credentials"
3. Credentials are automatically filled and validated
4. Click "Save Settings"

== Frequently Asked Questions ==

= Do I need to configure anything after installation? =

No! The plugin automatically creates a genealogy page at `/genealorama/` with the proper template. Just activate and visit the page.

= Can I use the shortcode on multiple pages? =

Yes, you can use `[genealorama_embed]` on any page or post.

= Is the integration secure? =

Yes, the plugin uses HMAC-SHA256 signatures for authentication and requires users to be logged in to WordPress.

== External Services ==

This plugin connects to external services to provide genealogy visualization functionality:

= Genealorama Application Service =
* **Purpose**: Provides the main genealogy application interface through a secure iframe
* **Service URL**: https://genealogie.app
* **Data sent**: User authentication tokens, timestamps, and signatures for secure access
* **When**: Every time a user loads the genealogy interface
* **Terms of Service**: https://genealorama.com/terms
* **Privacy Policy**: https://genealorama.com/privacy

= Partner Registration API =
* **Purpose**: Registers WordPress sites as authorized partners for the genealogy service
* **Service URL**: https://partner-registration.genealogie.app
* **Data sent**: Site domain, admin email, WordPress version, plugin version
* **When**: During initial setup when admin clicks "Get My Credentials"
* **Terms of Service**: https://genealorama.com/terms
* **Privacy Policy**: https://genealorama.com/privacy

These external connections are required for the plugin to function properly and provide secure access to genealogy services.

== Screenshots ==

1. Plugin settings page with credential validation
2. Full-page genealogy interface
3. Shortcode integration example

== Changelog ==

= 2.1.4 =
* Fixed text domain to match plugin slug
* Improved build process and GitHub Actions
* Enhanced asset management using WordPress uploads directory
* Better compliance with WordPress.org guidelines

= 2.0.0 =
* Complete rebranding to Genealorama
* Full English translation
* Improved WordPress standards compliance
* Fixed wp_enqueue_scripts implementation
* Enhanced security and authentication

= 1.9.1 =
* Modern admin interface and technical fixes
* Enhanced credential validation system
* Improved error handling and user feedback
* Security enhancements

= 1.9.0 =
* Added automatic credential validation
* Enhanced admin interface
* Improved error messaging
* Better security measures

== Upgrade Notice ==

= 2.0.0 =
Major update with complete rebranding, full English translation, and improved WordPress standards compliance. This version resolves all WordPress.org review requirements.