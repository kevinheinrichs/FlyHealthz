# FlyWP Health

Health check and status endpoint for WordPress uptime monitoring, by [FlyWP Code](https://www.flywpcode.com). Formerly FlyHealthz.

`GET|HEAD /healthz` returns `200 OK` (plain text, no-cache, noindex). Optional access key (`X-FlyWP-Health-Key` header, `?key=`, or `FLYWP_HEALTH_KEY` in wp-config.php). Optional must-use mode under Settings > FlyWP Health writes `wp-content/mu-plugins/flywp-health-loader.php`, which answers before regular plugins load; turning it off, deactivating or uninstalling removes it.

Requires WordPress 5.8+, PHP 7.4+. The wordpress.org description is in [readme.txt](readme.txt).

## Layout

| File | Purpose |
|---|---|
| `flywp-health.php` | Plugin header, bootstrap, activation/deactivation |
| `includes/endpoint.php` | `/healthz` handler, shared by plugin and MU loader |
| `includes/class-flywp-health-mu.php` | Writes/removes the MU loader (marker `flywp-health-mu-loader`) |
| `includes/class-flywp-health-admin.php` | Settings page (Settings API, `manage_options`) |
| `uninstall.php` | Removes the loader and both options |
| `bin/build-zip.sh` | Builds `dist/flywp-health.zip` for wordpress.org |

## Checks before a release

    wp plugin check flywp-health    # Plugin Check, no errors
    phpcs --standard=PHPCompatibilityWP --runtime-set testVersion 7.4- flywp-health.php includes uninstall.php
    bin/build-zip.sh

## License

GPLv2 or later.
