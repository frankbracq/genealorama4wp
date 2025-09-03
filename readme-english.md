# Secure Iframe Embed for Genealorama

[![Latest Release](https://img.shields.io/github/v/release/frankbracq/geneapp-wp?label=Latest%20Release)](https://github.com/frankbracq/geneapp-wp/releases/latest)
[![Build](https://github.com/frankbracq/geneapp-wp/actions/workflows/tag-and-build.yml/badge.svg)](https://github.com/frankbracq/geneapp-wp/actions)
[![Download Plugin](https://img.shields.io/github/downloads/frankbracq/geneapp-wp/total?label=Download%20Plugin)](https://github.com/frankbracq/geneapp-wp/releases/latest)

**Secure Iframe Embed for Genealorama** is a WordPress plugin designed to integrate the Genealorama web application into any WordPress site through interactive and secure iframes. A demo of this integration is available at [geneapp-wp.fr](https://geneapp-wp.fr)

It allows logged-in users to **view, visualize, or interact with genealogical research results** while maintaining secure access rights.

---

## ✨ Automatic Installation

Upon activation, the plugin:
- ✅ **Automatically creates a "Genealogy" page** accessible at `/genealorama/`
- ✅ Applies a full-page template (without WordPress header/footer)
- ✅ Configures integration with the shortcode `[genealorama_embed auto_height="true"]`

**No manual configuration needed** to get started!

---

## 🔧 Features

- ✅ **Automatic dedicated page**: A `/genealorama/` page created on activation
- ✅ **Immersive template**: Full-page display without distractions
- ✅ **Flexible shortcode**: `[genealorama_embed]` usable on any page
- ✅ **Enhanced security**: HMAC-SHA256 signature for authentication
- ✅ **Members only**: Access limited to logged-in users
- ✅ **Adaptive height**: Automatic iframe resizing
- ✅ **Bidirectional communication**: Support for messages between iframe and parent site
- 🆕 **Credential validation**: Automatic and manual verification of API keys

---

## 🚀 Installation

### Quick Installation

1. [Download the latest version here](https://github.com/frankbracq/geneapp-wp/releases/latest)
2. Upload the `.zip` file via `Plugins > Add New > Upload Plugin`
3. Activate the plugin
4. **Ready!** Visit `/genealorama/` on your site

### API Keys Configuration

To activate integration with app.genealorama.com:

1. Go to **Settings > Genealorama Settings**
2. Enter your **Email** and click "Get My Credentials"
3. Credentials are automatically filled and validated
4. Click "Save Settings"

#### 🆕 Credential Validation

The plugin now displays:
- **Last validation date**: Know when your credentials were last verified
- **Status**: Visual indicator (✓ Valid or ⚠ Invalid)
- **Manual validation**: "Validate Now" button to check anytime
- **Automatic validation**: Daily background verification

---

## 📖 Usage

### Option 1: Automatic Page (Recommended)

Simply use the automatically created page:
```
https://your-site.com/genealorama/
```

This page uses an immersive template specially designed for the genealogy application.

### Option 2: Custom Integration

Use the shortcode on any page or post:

```
[genealorama_embed]
```

With options:
```
[genealorama_embed auto_height="true" fullscreen="true"]
```

### Shortcode Parameters

| Parameter | Default Value | Description |
|-----------|---------------|-------------|
| `src` | `https://app.genealorama.com/iframe-entry/` | Application URL |
| `auto_height` | `true` | Automatic height adjustment |
| `fullscreen` | `false` | Full screen mode |

---

## 🔒 Security

- **Authentication required**: Users must be logged in to WordPress
- **HMAC signature**: Each request is cryptographically signed
- **Temporal validation**: Protection against replay attacks
- **Iframe isolation**: Secure communication via postMessage
- 🆕 **Error detection**: Automatic alert if credentials are invalid

### Authentication Error Management

If credentials become invalid:
- **Administrators**: Warning message with link to settings
- **Users**: Generic message asking them to contact the administrator
- **Proactive validation**: Daily verification to detect issues

---

## 🛠️ Technical Structure

The plugin automatically creates:

```
/wp-content/plugins/secure-iframe-embed-for-genealorama/
├── assets/
│   ├── css/
│   │   └── genealorama.css          # Integration styles
│   └── js/
│       └── genealorama.js           # Frontend JavaScript
├── includes/
│   ├── signature.php                # HMAC generation
│   └── admin-settings.php           # Configuration page with validation
├── templates/
│   └── genealorama-template.php     # Full-page template
└── secure-iframe-embed-for-genealorama.php  # Main file with error handling
```

---

## 📋 Use Cases

- **Genealogical associations**: Member area with access to research
- **Family sites**: Secure sharing of family trees
- **Historical portals**: Integration of genealogical data
- **History clubs**: Access reserved for paying members

---

## 🤝 Support

- **Documentation**: [Project Wiki](https://github.com/frankbracq/geneapp-wp/wiki)
- **Issues**: [Report a bug](https://github.com/frankbracq/geneapp-wp/issues)
- **Contact**: admin@geneapp-wp.fr

---

## 📜 License

This plugin is distributed under the [GPL v2 or later](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html) license.

---

## 🔄 Changelog

See [CHANGELOG.md](CHANGELOG.md) for complete version history.

**Latest version**: v2.0.0 - Complete rebranding to Genealorama with full English translation