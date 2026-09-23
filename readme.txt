=== FlyWP Health ===
Contributors: flywpcode
Tags: health check, uptime, monitoring, healthcheck, status
Requires at least: 5.8
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight health check and status endpoint for uptime monitoring. /healthz answers "OK" in milliseconds, optionally before other plugins load.

== Description ==

FlyWP Health adds a fast **health check endpoint** to your WordPress site: `https://example.com/healthz`. Point any **uptime monitoring** service at it (Uptime Kuma, UptimeRobot, Better Stack, Pingdom, StatusCake, Kubernetes or load balancer probes) and get a clear answer: HTTP 200 with the body `OK` when WordPress runs and its database is reachable.

Unlike checking your homepage, the **status endpoint** does not render a theme, does not touch page caches and does not show up in analytics. It stops WordPress right after it has answered.

= Features =

* **Health check endpoint** at `/healthz` for GET and HEAD requests, plain `OK` response.
* **Must-use mode (optional)**: answers before other plugins load, so the check is faster and still works when another plugin breaks. Off by default, one checkbox to turn it on.
* **Optional access key**: require an `X-FlyWP-Health-Key` header (or `?key=`) so only your monitor gets `OK`; everyone else gets HTTP 403.
* **Private by design**: no versions, paths, plugin lists or server details in the response. Sent with `no-cache` and `X-Robots-Tag: noindex`.
* **No tracking, no external requests, no ads.** The plugin never contacts any external service.
* Works in subdirectory installs (`https://example.com/blog/healthz`).

= Monitor setup =

* URL: `https://example.com/healthz`
* Method: GET or HEAD
* Expected: status 200, keyword `OK`
* With an access key: header `X-FlyWP-Health-Key: your-key`

== Installation ==

1. Install and activate FlyWP Health from Plugins > Add New, or upload the `flywp-health` folder to `/wp-content/plugins/`.
2. Open `https://your-site/healthz` – it answers `OK`.
3. Optional: go to Settings > FlyWP Health to turn on must-use mode or set an access key.

== Frequently Asked Questions ==

= What does "OK" mean? =

PHP runs, WordPress has loaded and connected to its database. If the database is down, WordPress fails before the endpoint and your monitor gets an error status instead of `OK`.

= What is must-use mode? =

When you turn it on, the plugin writes a small loader file to `wp-content/mu-plugins/flywp-health-loader.php`. WordPress loads must-use plugins before regular plugins, so the health check answers earlier and keeps working even if a regular plugin causes a fatal error. The loader only includes this plugin's endpoint file. Turning the setting off, deactivating or deleting the plugin removes the file.

= Can I protect the endpoint? =

Yes. Set an access key under Settings > FlyWP Health, or define it in `wp-config.php`:

`define( 'FLYWP_HEALTH_KEY', 'your-long-random-key' );`

Requests without the key get HTTP 403. Prefer the `X-FlyWP-Health-Key` header; the `?key=` parameter is for monitors that cannot send headers and may appear in server logs.

= Does it work with page caching or a CDN? =

The response is sent with `Cache-Control: no-cache, must-revalidate, max-age=0, no-store, private`. If your CDN caches everything regardless, add a bypass rule for `/healthz`.

= Does the plugin send data anywhere? =

No. There is no tracking, no telemetry and no call to any external service.

= Does it replace the WordPress Site Health screen? =

No. Site Health checks your configuration from the dashboard. FlyWP Health gives external monitors a fast yes/no signal that your site is up.

== Changelog ==

= 1.0.0 =
* First release on WordPress.org.
* Health check endpoint `/healthz` (GET and HEAD) with plain `OK` response.
* Optional must-use mode with automatic cleanup on disable, deactivation and uninstall.
* Optional access key via settings or the `FLYWP_HEALTH_KEY` constant.

== Upgrade Notice ==

= 1.0.0 =
First release.
