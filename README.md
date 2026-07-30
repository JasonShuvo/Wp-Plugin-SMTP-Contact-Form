# SB Contract & Animation

A WordPress plugin that combines:

- 🎬 **Animated Hero Section** — fully configurable text, colors, fonts, background image, and animation style, placed anywhere via shortcode.
- 📧 **SMTP Configuration** — send mail through your own web-hosting mailbox or through Gmail (App Password).
- 📝 **Contract / Contact Form** — submissions are saved to the database and visible in wp-admin, with automatic **admin notification** + **user confirmation** emails (fully editable templates).
- 🔑 **Email Activation Gate** — the site owner activates the plugin using a real email address (verified with a one-time code); the plugin developer gets notified whenever a new site activates it.
- 🔄 **GitHub-based Updates** — checks this repository's Releases for new versions and offers one-click updates straight from wp-admin, no WordPress.org listing required.

---

## Installation

1. Download this repository as a ZIP (`Code → Download ZIP`), or download the latest [Release](../../releases).
2. In your WordPress admin, go to **Plugins → Add New → Upload Plugin**.
3. Upload the ZIP file and click **Activate**.
4. Go to **SB Contract & Anim → Activation** and activate the plugin with your real email address.

## Shortcodes

| Shortcode | Description |
|---|---|
| `[sb_hero]` | Displays the animated hero section using your saved settings. |
| `[sb_contract_form]` | Displays the contract/contact form. |
| `[sb_contract_form title="Let's Talk"]` | Form with a custom heading. |

Add these shortcodes to any Page, Post, or widget/block area using the WordPress Shortcode block.

## Admin Menu

Once activated, a new **SB Contract & Anim** menu appears in wp-admin with:

- **Hero Section** — title, subtitle, button text/link, background image, colors, font, animation style.
- **SMTP Settings** — enable custom SMTP, choose "Web Hosting Email" or "Gmail", host/port/encryption, username/password, from name/email, and a **Send Test Email** button.
- **Email Templates** — subject/body for the admin notification email and the user confirmation email, with `{name} {email} {phone} {subject} {message} {site_name} {site_url} {date}` placeholders.
- **Form Submissions** — table of everyone who filled out the contract form, with delete action.
- **Activation** — enter your email, receive a code, confirm to activate.
- **Shortcodes & Updates** — quick reference + GitHub update info.

## Setting up Gmail SMTP

1. Enable **2-Step Verification** on the Google account you want to send from.
2. Create an **App Password**: Google Account → Security → App passwords.
3. In **SMTP Settings**, choose provider **Gmail**, enter the Gmail address as the username and the 16-character App Password as the password.
4. Click **Send Test Email** to confirm it works.

## Setting up Web Hosting Email

1. In your hosting control panel (cPanel, Plesk, etc.), create/locate a mailbox (e.g. `contact@yourdomain.com`).
2. In **SMTP Settings**, choose provider **Web Hosting Email (custom SMTP)**.
3. Enter the SMTP host (e.g. `mail.yourdomain.com`), port (`465` for SSL or `587` for TLS), and the mailbox credentials.
4. Click **Send Test Email** to confirm it works.

## Publishing Updates via GitHub

This plugin includes a lightweight built-in updater (see `includes/class-sb-updater.php`) that checks:

```
https://api.github.com/repos/<owner>/<repo>/releases/latest
```

To ship an update to everyone who has this plugin installed:

1. Bump the `Version:` header inside `sb-contract-animation.php`.
2. Commit your changes and push to `main`.
3. Create a new **GitHub Release** with a tag that matches the new version (e.g. `1.0.1`).
4. Sites running the plugin will see an **"Update available"** notice on their Plugins page automatically, and can update with one click — no WordPress.org account needed.

> Before publishing, edit these two constants at the top of `sb-contract-animation.php`:
> - `SBCA_GITHUB_REPO` → your `username/repository`
> - `SBCA_DEVELOPER_NOTIFY_EMAIL` → the email address that should receive "new activation" notifications

## Folder Structure

```
sb-contract-animation/
├── sb-contract-animation.php     # Main plugin bootstrap
├── uninstall.php                 # Cleanup on uninstall
├── includes/
│   ├── class-sb-db.php           # Submissions database table
│   ├── class-sb-activation.php   # Email activation gate
│   ├── class-sb-smtp.php         # SMTP / Gmail mailer config
│   ├── class-sb-emails.php       # Editable email templates
│   ├── class-sb-hero.php         # Animated hero shortcode
│   ├── class-sb-form.php         # Contract form shortcode + handler
│   ├── class-sb-admin.php        # All wp-admin screens
│   └── class-sb-updater.php      # GitHub release updater
└── assets/
    ├── css/sb-style.css          # Front-end styles + animations
    ├── css/sb-admin.css          # Admin styles
    ├── js/sb-animation.js        # Scroll-triggered hero animation
    └── js/sb-admin.js            # Media uploader + color picker
```

## License

GPL v2 or later.
