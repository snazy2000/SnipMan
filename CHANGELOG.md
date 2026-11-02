# Changelog

All notable changes to SnippetMan are documented here.

This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.4.0] - 2026-11-02

Security release. Upgrading is strongly recommended: the PHP runtime shipped by
the previous images is affected by two actively exploited remote code execution
vulnerabilities.

### Security

- **Pinned PHP to 8.4.27 in both container images.** PHP releases before
  8.4.27 are affected by CVE-2026-109618 and CVE-2026-109619 — unauthenticated
  remote code execution, CVSS 9.2, both added to the CISA Known Exploited
  Vulnerabilities catalog on 2026-10-26. The queue image moved from the
  floating `php:8.4-fpm-alpine` tag to `php:8.4.27-fpm-alpine`, and the web
  image now force-upgrades the Alpine `php84` packages to `>=8.4.27` on top of
  the `trafex/php-nginx:3.10.0` base, whose bundled PHP predates the fix. Both
  images fail the build if the resulting PHP is older than 8.4.27.
- **Disabled `session.upload_progress`** in both images. It is the confirmed
  cross-request vector for CVE-2026-109619 and the only published mitigation
  for CVE-2026-115560, which has no upstream fix as of this release. It is
  enabled by default in PHP and does not require `session_start()`.
- **Raised the minimum PHP version to 8.4.27** in `composer.json`, so an
  install against a vulnerable runtime fails loudly instead of silently
  succeeding.
- **Updated `symfony/mime` to 8.1.10**, fixing CVE-2026-45070 (email header
  injection via non-token characters in MIME parameter names).
- **Updated `laravel/framework` to 13.39.0** and refreshed the remaining
  dependencies; `composer audit` now reports no advisories.
- **Fixed two DOM XSS sinks** in the snippet layout. Toast notifications
  interpolated caller-supplied text — including folder and snippet names — into
  `innerHTML`, and the drag-and-drop ghost element re-parsed snippet and folder
  titles as markup. A snippet or folder named with an HTML payload could run
  script in another team member's session. Both now use `textContent`.
- **Resolved all npm advisories**, including a critical command injection in
  `shell-quote` (reached via `concurrently`) and high-severity issues in
  `vite`, `follow-redirects` and `source-map-js`. These are build and
  development dependencies and do not ship in the published images.
- **Made API token lifetime configurable** via the new
  `SANCTUM_TOKEN_EXPIRATION` environment variable. Tokens previously never
  expired, and that remains the default when the variable is unset.
- `expose_php` is now off in both images, so responses no longer advertise the
  PHP version.

### Upgrading

The minimum PHP version is now 8.4.27. If you run SnippetMan outside the
provided containers, upgrade PHP before deploying — `composer install` will
refuse to run otherwise. Container users need only pull the new images.

If you set `SANCTUM_TOKEN_EXPIRATION`, existing tokens older than the window
you choose stop working immediately.
