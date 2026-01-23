# 🦅 FlyHealthz

[![PHP Version](https://img.shields.io/badge/PHP-8.5%2B-blue.svg?style=flat-square)](https://www.php.net/)
[![WordPress Version](https://img.shields.io/badge/WordPress-6.0%2B-blue.svg?style=flat-square)](https://wordpress.org/)
[![License](https://img.shields.io/badge/License-GPL--2.0-green.svg?style=flat-square)](LICENSE)

**FlyHealthz** is an ultra-fast, high-performance health check endpoint for WordPress, specifically optimized for PHP 8.5+. It provides a lightweight `/healthz` route that returns `200 OK` while bypassing the heavy WordPress core for maximum speed.

---

## 🚀 Key Features

*   **⚡ Extreme Performance**: Executes as a **Must-Use (MU) Plugin**, intercepting requests before the database or heavy plugins are loaded.
*   **🛡️ Security Gate**: Protects your endpoint from bots and flooding by requiring a secret User-Agent (default: `FlyHealthz`).
*   **🤖 Auto-Installer**: Automatically deploys itself to the `mu-plugins` folder upon activation.
*   **🔄 Sync-Watcher**: Keeps the MU-plugin version in sync with the main plugin file automatically.
*   **🕵️ Crawler Protection**: Includes `X-Robots-Tag: noindex` to stay invisible to search engines.
*   **🔥 Modern Tech**: Optimized for PHP 8.5+ using `str_starts_with`, `hash_equals`, and strict typing.

---

## 🛠️ Installation

1.  **Upload** the `flyhealthz` folder to your `/wp-content/plugins/` directory.
2.  **Activate** the plugin via the WordPress 'Plugins' menu.
3.  **Done!** The plugin will automatically create `/wp-content/mu-plugins/flyhealthz.php` for maximum performance.

---

## 🛡️ Security Configuration

By default, FlyHealthz only responds if the request's **User-Agent** matches `FlyHealthz`. Any other request will receive a `403 Forbidden` response instantly, saving your server resources from bots.

### Custom Secret Agent
You can customize the secret agent string in your `wp-config.php`:

```php
define( 'FLYHEALTHZ_SECRET', 'Your-Super-Secret-String' );
```

---

## 📈 Monitoring with Uptime Kuma

To use FlyHealthz with monitoring tools like [Uptime Kuma](https://github.com/louislam/uptime-kuma), configure your monitor as follows:

*   **URL**: `https://yourdomain.com/healthz`
*   **Method**: `GET` or `HEAD`
*   **Header (JSON)**:
    ```json
    {
      "User-Agent": "FlyHealthz"
    }
    ```
*   **Keyword Monitoring**: Look for `OK` to ensure the server is responding correctly.

---

## 🔬 How it works

FlyHealthz uses a two-phase engine:
1.  **Phase 1 (The Engine)**: Intercepts the request at the very beginning of the PHP lifecycle. If the path is `/healthz`, it checks the headers and exits immediately with `OK`. No database connection is established, and no themes are loaded.
2.  **Phase 2 (The Control Layer)**: Runs only in the WordPress Admin area to manage the auto-installation, synchronization, and cleanup of the MU-plugin file.

---

## 👨‍💻 Author

**Kevin Heinrichs**
*   Website: [https://www.kevinheinrichs.com/](https://www.kevinheinrichs.com/)

---

## ⚖️ License

Distributed under the GPL-2.0+ License. See `LICENSE` for more information.
