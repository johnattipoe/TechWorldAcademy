# TechWorld Academy

A PHP 8 application for TechWorld Academy's public course catalogue, training pages, contact and newsletter forms, and staff dashboard. Server-side data is stored in Firestore through Google's HTTPS REST API using the private service account.

## Local setup

1. Install PHP 8.0+ with `mbstring`, OpenSSL, cURL and `fileinfo`, plus Composer and Node.js.
2. Run `composer install` and `npm install`.
3. Copy `.env.example` to `.env`. Set `FIREBASE_PROJECT_ID` and `FIREBASE_CREDENTIALS` to a service-account JSON file stored outside `public/`. Grant that account only the Firestore access it needs.
4. Generate a newsletter signing secret with `php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"` and set it as `NEWSLETTER_UNSUBSCRIBE_SECRET`. Set `APP_URL` to the local origin.
5. Run `npm run build`, `composer test`, and `composer setup-check`. Use `composer firestore-check` to verify read-only access to the configured Firestore project.
6. Start the local site with `php -S 127.0.0.1:8000 -t public public/router.php`.

The application needs a PHP-capable host; Firebase Hosting static hosting alone cannot run its PHP handlers. Apache deployments can use `public/.htaccess` for clean URLs, including the Resources routes. Keep `.env` and service-account JSON outside the public document root and out of source control.

## Deploy to Render

The repository includes a Dockerfile and `render.yaml` Blueprint. Push the project to a Git repository, create a Render Blueprint from it, and choose the `techworld-academy` service. The Blueprint uses the Free plan for a staging deploy; change the plan in Render if you need an always-on production service.

In the Render service settings, set `APP_URL` to the service's HTTPS URL, set `FIREBASE_PROJECT_ID`, and upload the Firebase service-account JSON as a secret file named `firebase-service-account.json`. `FIREBASE_CREDENTIALS` is configured to `/etc/secrets/firebase-service-account.json`. Set `NEWSLETTER_UNSUBSCRIBE_SECRET` and `ADMIN_PASSWORD_HASH` to strong generated values. Set the R2 variables only if the application will use R2 objects.

The Render Free plan has an ephemeral filesystem and spins down when idle. This app uses local files for sessions and rate limits, so those reset after a restart or spin-down; the blueprint is suitable for staging, not a production launch. For production, use a paid single-instance web service with a persistent disk mounted at `/var/www/html/storage`, or move session and rate-limit state to shared storage. Render disks cannot be shared between instances or used with a scaled service.

Email delivery also needs a production transport. `EmailService` currently calls PHP `mail()`, and the container does not include a mail server. Render Free services also block outbound SMTP on ports 25, 465 and 587. Configure an HTTP email provider integration before relying on contact notifications or newsletter welcome emails.

## Configuration and operations

Set `APP_ENV=production` and `APP_URL` to the canonical HTTPS origin in production. Configure Firebase credentials, a strong `ADMIN_PASSWORD_HASH` generated with PHP `password_hash()`, a strong `NEWSLETTER_UNSUBSCRIBE_SECRET`, and a valid `MAIL_FROM`. Configure the host's mail transport. Contact requests are saved even if notification email delivery fails; failures are logged. New newsletter subscribers receive a welcome email with a signed unsubscribe link. The host must keep `storage/framework/sessions/` and `storage/framework/rate-limits/` writable. File-based rate limits are intended for a single application host; use shared rate-limit storage if deploying multiple instances.

The staff dashboard uses a shared password and supports inquiry review, course publishing, and manual subscriber management. Keep the password private and rotate it when staff access changes. The newsletter unsubscribe page requires confirmation by POST, so email link scanners do not unsubscribe recipients automatically.

Firestore client rules deny direct browser access. The PHP server uses its service account, protected by server IAM. Starter courses remain available when Firebase is not configured, but form submissions require Firestore.

Cloudflare R2 is the object-storage provider. `App\Services\StorageService\StorageService` uses R2's S3-compatible API with SigV4 signing for private object uploads, downloads and deletion. Set `CLOUDFLARE_R2_ACCOUNT_ID`, `CLOUDFLARE_R2_BUCKET`, `CLOUDFLARE_R2_ACCESS_KEY_ID`, and `CLOUDFLARE_R2_SECRET_ACCESS_KEY` from an R2 API token; optionally set `CLOUDFLARE_R2_PUBLIC_URL` only when objects should be publicly addressable. Do not put these credentials in browser code. The adapter is ready for server-side upload features; no public upload endpoint is enabled by default.

## Pages

- `/`, `/courses/`, `/training/`, `/services/`
- `/resources/`, `/resources/guides/`, `/resources/glossary/`, `/resources/projects/`
- `/about/`, `/contact/`, `/faq/`, `/privacy/`, `/terms/`
- `/newsletter/unsubscribe/`
- `/admin/` (requires `ADMIN_PASSWORD_HASH`)

Review course availability and legal/privacy wording for the operating location before launch. Serve the site over HTTPS, verify storage permissions, and review dependency advisories before deployment.

## Dependency security status

The vulnerable Firebase Admin and Google Cloud SDK dependency tree has been removed. Firestore calls now use the authenticated REST API, implemented with PHP cURL and OpenSSL, so the vulnerable JWT, Protobuf and Guzzle packages are no longer installed. The Composer lock file contains no third-party packages. `npm audit` currently reports no advisories; rerun it before deployment.
