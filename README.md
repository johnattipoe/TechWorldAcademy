# TechWorld Academy

A PHP 8 application for TechWorld Academy's public course catalogue, training pages, contact and newsletter forms, and staff dashboard. Server-side data is stored in Firestore through Google's HTTPS REST API using the private service account.

## Local setup

1. Install PHP 8.0+ with `mbstring`, OpenSSL, cURL and `fileinfo`, plus Composer and Node.js.
2. Run `composer install` and `npm install`.
3. Copy `.env.example` to `.env`. Set `FIREBASE_PROJECT_ID` and `FIREBASE_CREDENTIALS` to a service-account JSON file stored outside `public/`. Grant that account only the Firestore access it needs.
4. Generate a newsletter signing secret with `php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"` and set it as `NEWSLETTER_UNSUBSCRIBE_SECRET`. Set `APP_URL` to the local origin.
5. Run `npm run build`, `composer test`, and `composer setup-check`. Use `composer firestore-check` to verify read-only access to the configured Firestore project.
6. Start the public website and learning platform with one server: `php -S 127.0.0.1:8000 -t public public/router.php`.
7. Open `http://127.0.0.1:8000/` for the public website. Click **Open learning platform** in its navbar to open the learning catalogue at `http://127.0.0.1:8000/learning/` in a new tab. The `.env.example` uses this same local origin for both site URLs.

The application needs a PHP-capable host; Firebase Hosting static hosting alone cannot run its PHP handlers. Apache deployments can use `public/.htaccess` for clean URLs, including the Resources routes. Keep `.env` and service-account JSON outside the public document root and out of source control.

## Deploy to Render

The repository includes a Dockerfile and `render.yaml` Blueprint. Push the project to a Git repository, create a Render Blueprint from it, and choose the `techworld-academy` service. The Blueprint uses the Free plan for a staging deploy; change the plan in Render if you need an always-on production service.

For separate production sites, deploy this repository as two Render web services: `techworld-academy` for the public website and a second service such as `techworld-learning` for the learning platform. Attach separate domains (for example `www.example.com` and `learn.example.com`). On the website service set `APP_URL` and `PUBLIC_WEBSITE_URL` to the website origin and `LEARNING_PLATFORM_URL` to the learning origin. On the learning service set `APP_URL` and `LEARNING_PLATFORM_URL` to the learning origin and `PUBLIC_WEBSITE_URL` to the website origin. Set these URL variables to HTTPS. Both services need the same Firebase and email configuration; use separate session/rate-limit storage or an appropriate shared service if running multiple instances.

In each Render service's settings, set `FIREBASE_PROJECT_ID` and upload the Firebase service-account JSON as a secret file named `firebase-service-account.json`. `FIREBASE_CREDENTIALS` is configured to `/etc/secrets/firebase-service-account.json`. Set `NEWSLETTER_UNSUBSCRIBE_SECRET` and the one-time `ADMIN_EMAIL` / `ADMIN_PASSWORD_HASH` bootstrap values. Set the R2 variables only if the application will use R2 objects.

If learner registration returns “We could not create your account right now,” open the Render service's **Logs** and find `Learning account creation failed`. Registration saves accounts to Firestore, so verify the secret file is present at `/etc/secrets/firebase-service-account.json`, `FIREBASE_PROJECT_ID` matches the JSON file's `project_id`, the Firestore database exists, and the service account has the `Cloud Datastore User` (`roles/datastore.user`) role. After changing environment variables or secret files, save and redeploy the service. Do not paste service-account JSON into logs, tickets, or chat.

Paid courses use Paystack hosted checkout with the Ghana mobile money channel. Add the Paystack **secret** key as `PAYSTACK_SECRET_KEY` in `.env` for local use and in the Render environment for both production services. Set up your receiving/settlement account and mobile money providers in the Paystack dashboard; learners enter and authorize their own mobile money number during checkout. The server unlocks courses only after it independently verifies the Paystack transaction reference, amount, currency and learner email. For local callback testing, use a public HTTPS tunnel URL in `LEARNING_PLATFORM_URL`.

The Render Free plan has an ephemeral filesystem and spins down when idle. This app uses local files for sessions and rate limits, so those reset after a restart or spin-down; the blueprint is suitable for staging, not a production launch. For production, use a paid single-instance web service with a persistent disk mounted at `/var/www/html/storage`, or move session and rate-limit state to shared storage. Render disks cannot be shared between instances or used with a scaled service.

Contact notifications and newsletter welcome emails use Resend's HTTPS API when `RESEND_API_KEY` is set. Create a sending-only API key, verify a domain in Resend, then configure `RESEND_API_KEY`, `CONTACT_EMAIL` (the inbox that should receive enquiries), and `MAIL_FROM` (an address on the verified sending domain). Add these as Render environment variables; never commit the API key. Without a Resend key, the app falls back to PHP `mail()`, which requires a working mail transport on the host and is not configured in the included Render container. A successful API response means the provider accepted the email for delivery; check provider logs and the recipient's spam folder if it does not arrive.

## Configuration and operations

Set `APP_ENV=production` and `APP_URL` to the canonical HTTPS origin in production. Configure Firebase credentials, a strong `ADMIN_PASSWORD_HASH` generated with PHP `password_hash()`, a valid `ADMIN_EMAIL`, a strong `NEWSLETTER_UNSUBSCRIBE_SECRET`, and the email settings above. The first successful sign-in at `/admin/` with that email and password creates or promotes the matching learning account to admin in Firestore and locks the bootstrap. Remove `ADMIN_PASSWORD_HASH` after bootstrap; future admin access uses the learning account password and stored admin role. Contact requests are saved even if notification email delivery fails; failures are logged. New newsletter subscribers receive a welcome email with a signed unsubscribe link. The host must keep `storage/framework/sessions/` and `storage/framework/rate-limits/` writable. File-based rate limits are intended for a single application host; use shared rate-limit storage if deploying multiple instances.

The staff dashboard uses a shared password and supports inquiry review, course publishing, and manual subscriber management. Keep the password private and rotate it when staff access changes. The newsletter unsubscribe page requires confirmation by POST, so email link scanners do not unsubscribe recipients automatically.

Firestore client rules deny direct browser access. The PHP server uses its service account, protected by server IAM. Starter courses remain available when Firebase is not configured, but form submissions require Firestore.

Cloudflare R2 is the object-storage provider. `App\Services\StorageService\StorageService` uses R2's S3-compatible API with SigV4 signing for private object uploads, downloads and deletion. Set `CLOUDFLARE_R2_ACCOUNT_ID`, `CLOUDFLARE_R2_BUCKET`, `CLOUDFLARE_R2_ACCESS_KEY_ID`, and `CLOUDFLARE_R2_SECRET_ACCESS_KEY` from an R2 API token; optionally set `CLOUDFLARE_R2_PUBLIC_URL` only when objects should be publicly addressable. Do not put these credentials in browser code. The adapter is ready for server-side upload features; no public upload endpoint is enabled by default.

## Pages

- `/`, `/courses/`, `/training/`, `/services/`
- `/resources/`, `/resources/guides/`, `/resources/glossary/`, `/resources/projects/`
- `/about/`, `/contact/`, `/faq/`, `/privacy/`, `/terms/`
- `/events/`, `/webinars/`, `/blog/`, `/careers/`, `/partners/`, `/help/`
- `/community/`, `/success-stories/`, `/pricing/`, `/scholarships/`, `/locations/`
- `/accessibility/`, `/status/`
- `/newsletter/unsubscribe/`
- `/admin/` (uses the same learning account login; initial admin is bootstrapped once from `ADMIN_EMAIL` and `ADMIN_PASSWORD_HASH`)

Public event and webinar registration, career interest, scholarship, partnership and accessibility forms are rate limited, CSRF protected and stored in separate Firestore collections. Admin staff can review them at `/admin/?tab=public-submissions` and update each record's follow-up status. See `database/firestore-schema.md` for collection fields. Event dates are announced after registration interest; career forms are expressions of interest rather than posted vacancies, and scholarship requests do not guarantee funding.

## Online learning platform

- `/register/` creates a student-only account. Passwords are hashed with PHP `password_hash()` before storage in the private Firestore `academy_users` collection.
- `/login/` and `POST /logout/` manage the learner session. Authentication forms use CSRF protection and file-based rate limits.
- `/student/` requires a signed-in student session and shows the course catalogue. Learners can request a course place; staff review requests at `/admin/?tab=enrollments`.
- Staff manage lessons at `/admin/?tab=lessons`. Approved learners can open published lessons at `/student/learn/`, mark lessons complete, and review their private progress at `/student/progress/`.
- Staff can author one multiple-choice quiz per lesson at `/admin/?tab=quizzes`. Approved learners can take published lesson quizzes, see their server-graded score and review past results at `/student/assessments/`.
- Staff can publish a practical assignment per lesson at `/admin/?tab=assignments`; they review learner responses and leave scores and feedback at `/admin/?tab=submissions`. Learners can revisit their work from `/student/assignments/`.
- Certificates at `/student/certificates/` require every published lesson complete, each published quiz at 70% or higher, and each published assignment reviewed at 70/100 or higher. Public verification pages use `/certificates/verify/?id=...`.
- Staff can revoke awards at `/admin/?tab=certificates`; verification pages support browser print and Save as PDF. Learners can request single-use password reset emails from `/forgot-password/` and update their password at `/reset-password/`.
- Admin staff can manage learner and instructor roles at `/admin/?tab=users`, then assign instructor accounts to courses from `/admin/?tab=courses`. To onboard an instructor, create their account through registration, promote it in the admin account list, assign one or more courses, then have them sign out and sign in again. Instructors are redirected to `/instructor/`, where they can view assigned course activity, add lessons to assigned courses, and review learner submissions with scores and feedback. Quiz and assignment authoring remains in the admin workspace.
- The admin control center at `/admin/` links to the public site, learning platform and every management area. `/admin/?tab=settings` controls the shared site name, contact details, public announcement, both homepage hero messages and whether new learner registration is open. `/admin/?tab=courses` can also override or unpublish built-in course listings while keeping their existing learner progress attached. `/admin/?tab=users` can promote or disable accounts; disabled users lose protected access immediately. Configure private service credentials in the host environment, never in website settings.
- Paystack checkout and server-side payment verification are implemented for GHS mobile money, but production use still requires provider credentials, merchant setup and a live end-to-end payment review. Email verification, social sign-in, video hosting and production end-to-end acceptance are not implemented.
- Public registration cannot choose or elevate account roles. Only the protected admin area can promote learner accounts to instructor.

Learner accounts require working Firestore access. Back up and protect the Firestore project, restrict service-account IAM, and keep Firestore client rules denying browser access to private collections.

Review course availability and legal/privacy wording for the operating location before launch. Serve the site over HTTPS, verify storage permissions, and review dependency advisories before deployment.
