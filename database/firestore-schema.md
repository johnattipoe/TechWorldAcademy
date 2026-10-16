# Firestore collections

All records are written through the authenticated Firestore REST API from the PHP server. Firestore client rules deny browser SDK access by default. Never return account password hashes or private learner records to the browser.

## `academy_users`

Document ID is the SHA-256 hash of the normalized email address. Public registration always creates the `student` role; role assignment for staff must remain a trusted administrative operation.

- `name`: string, 1-100 characters
- `email`: normalized lowercase string
- `passwordHash`: PHP `password_hash()` output; never expose in a page or API response
- `role`: `student` or `instructor`; instructor access is granted through the protected admin workspace
- `status`: `active` or `disabled`
- `createdAt`: UTC Firestore timestamp

The learning slice provides learner registration, sign-in, sign-out and a protected dashboard. Account email verification and password recovery remain outstanding.

Instructor accounts are promoted by an authenticated administrator. Learner account status can be set to `active` or `disabled`; protected learner and instructor routes recheck the role and status against Firestore before granting access.

## `academy_settings`

The `global` document stores administrator-managed public presentation settings. It may contain the site name, public contact email/phone, website and learning-platform hero copy, an optional announcement banner and whether public learner registration is open. Do not put API keys, passwords or other secrets in this collection.

## `academy_page_content`

One document per editable page key (`about`, `courses`, `training`, `services`, `contact`, `faq`, `resources`, `guides`, `glossary`, `projects`, `business`, `universities`, `government`). Each document stores plain text fields `eyebrow`, `title`, `accent`, and `description`. The app escapes these fields when rendering. Legal policy copy is intentionally maintained in reviewed source templates.

## `academy_enrollments`

Document ID is the SHA-256 hash of `studentId:courseId`, making a learner's request for the same course idempotent.

- `studentId`: learner account document ID
- `studentName`, `studentEmail`: learner details for staff review
- `courseId`, `courseTitle`: selected catalogue course reference and title at request time
- `accessType`: `free`, `free_trial` or `paid`
- `coursePrice`, `currency`: price snapshot for paid enrollments
- `paymentStatus`, `paymentReference`: set after Paystack verifies a successful payment
- `trialStartedAt`, `trialEndsAt`: one-time UTC seven-day access window for trial courses
- `status`: `requested`, `approved` or `rejected`; verified paid enrollments are approved by the payment callback
- `requestedAt`: UTC Firestore timestamp
- `reviewedAt`: UTC Firestore timestamp when staff reviews the request

Only an `approved` enrollment grants access. Paid enrollments also require `paymentStatus=paid`; trial access ends at `trialEndsAt`. The server checks these conditions before displaying lesson data or accepting progress updates.

## `academy_payments`

Each document uses a random Paystack transaction reference as its ID. It stores `studentId`, `studentEmail`, `studentName`, `courseId`, `courseTitle`, `amountMinor`, `currency`, `status`, and UTC timestamps. Payment completion is trusted only after the server verifies the transaction with Paystack and checks its reference, amount, currency, email, and successful status.

## `academy_lessons`

- `courseId`, `courseTitle`: the published catalogue course that owns the lesson
- `title`, `content`: plain text lesson title and content; output is escaped by the web app
- `position`: positive display order; `durationMinutes`: estimated time
- `published`: only published lessons are visible to learners
- `createdAt`, `updatedAt`: UTC timestamps

## `academy_progress`

Document ID is SHA-256 of `studentId:lessonId`, so completing a lesson is idempotent.

- `studentId`, `courseId`, `lessonId`: ownership and lesson reference
- `courseTitle`, `lessonTitle`: display labels captured at completion time
- `status`: currently `completed`
- `completedAt`: UTC completion timestamp

Progress is private to its learner in application routes. Do not expose this collection through browser SDK access.

## `academy_lesson_notes`

Document ID is the SHA-256 hash of `studentId:courseId:lessonId`, so each learner has one private note per lesson.

- `studentId`, `courseId`, `lessonId`: ownership and lesson references
- `note`: learner-authored plain text, up to 5,000 characters
- `createdAt`, `updatedAt`: UTC timestamps

The PHP lesson-note endpoint requires an authenticated student, an approved course enrollment, a published lesson, and a valid CSRF token. Notes are rendered as escaped text and must not be exposed through browser SDK access or public lesson responses.

## `academy_quizzes`

- `lessonId`, `courseId`: lesson and course this assessment belongs to
- `title`: quiz title; `questions`: array of prompt, four answer options and correct option letter (`A`–`D`)
- `published`: controls learner availability; correct answers are only rendered in staff UI and never included in learner response markup
- `createdAt`, `updatedAt`: UTC timestamps

## `academy_quiz_attempts`

- `studentId`, `courseId`, `lessonId`, `quizId`: ownership and assessment references
- title fields: labels saved at submission time
- `score`, `total`: number of correct answers and number of questions
- `submittedAt`: UTC timestamp

Attempt results are checked against the signed-in learner and approved course before display. Learner submissions are graded on the server.

## `academy_assignments`

- `lessonId`, `courseId`: lesson and course the assignment belongs to
- `title`, `instructions`: staff-authored plain text
- `published`: only published assignments are visible to approved learners
- `createdAt`, `updatedAt`: UTC timestamps

## `academy_assignment_submissions`

Document ID is SHA-256 of `studentId:assignmentId`; each learner has one current response per assignment and can resubmit.

- `studentId`, `courseId`, `lessonId`, `assignmentId`: ownership and assignment references
- `response`: plain text submitted by the learner
- `status`: `submitted` or `reviewed`; `score`: optional integer from 0 to 100
- `feedback`: staff feedback; `submittedAt`, `reviewedAt`: UTC timestamps

Only the owning learner and authenticated staff routes can read submissions. Learner output is escaped before display.

## `academy_certificates`

Document ID is SHA-256 of `studentId:courseId`, ensuring one issued certificate per learner and course.

- `studentId`, `studentName`, `courseId`, `courseTitle`: learner and award details
- `certificateCode`: random public reference; `status`: `valid` or `revoked`
- `issuedAt`: UTC award timestamp; optional `revokedAt` and private `revocationReason`

Eligibility requires an approved enrollment, completion of every published lesson, at least 70% on each published lesson quiz, and at least 70/100 on each published assignment. Public verification exposes the certificate holder, course title, issue date and code; revoked awards are marked invalid and their internal reason remains private.

## `academy_password_resets`

Document ID is SHA-256 of a cryptographically random single-use token; the raw token is only sent by email and is never stored.

- `userId`: learner account reference
- `expiresAt`: one hour after creation; configure Firestore TTL on this field to remove expired records
- `used`: set true before a password is changed; `createdAt`, optional `usedAt`: UTC timestamps

## `academy_courses`

Document IDs are generated by Firestore. Public pages show published custom courses alongside the starter catalogue.

- `title`: string, 1–100 characters
- `description`: string, 20–1,000 characters
- `level`: `Beginner`, `Intermediate`, `Advanced` or `All levels`
- `duration`: string, up to 60 characters
- `accessType`: `free`, `free_trial` or `paid`
- `price`, `currency`: amount and ISO 4217 currency code; paid mobile money courses use GHS
- `icon`: short display string
- `color`: one of the supported course accent classes
- `published`: boolean
- `instructorId`, `instructorName`: optional assigned instructor account reference and display name
- `catalogueId`: optional stable public course ID when an administrator overrides a built-in starter course; preserves existing enrollments and progress

## `contact_inquiries`

Document ID is generated by Firestore.

- `name`: string, 1–100 characters
- `email`: normalized lowercase string
- `message`: string, 10–5,000 characters
- `status`: `new` or `closed`
- `createdAt`: UTC Firestore timestamp
- `updatedAt`: UTC Firestore timestamp when changed in the staff dashboard

## `newsletter_subscribers`

Document ID is the SHA-256 hash of the normalized email address to avoid duplicate subscriptions.

- `email`: normalized lowercase string
- `consentedAt`: UTC Firestore timestamp
- `status`: `active` or `unsubscribed`
- `unsubscribedAt`: UTC Firestore timestamp when opted out

Staff can unsubscribe a record from the admin dashboard. Resubscribing requires the visitor to submit the consent checkbox again.

## Public event and interest forms

These collections are written only by the server-side `PublicFeatureService`; Firestore client access remains denied by `firestore.rules`. Records are visible to authorized administrators in the **Public submissions** dashboard. The dashboard can mark records `reviewing`, `contacted` or `closed`.

- `academy_event_registrations`: event registrations
- `academy_webinar_registrations`: webinar registrations
- `academy_job_applications`: career expressions of interest; may include `profileUrl`
- `academy_scholarship_applications`: learning support applications
- `academy_partner_inquiries`: partnership enquiries
- `academy_accessibility_reports`: accessibility feedback submissions

Each document uses a generated Firestore document ID and contains `name`, normalized `email`, `details`, `reference`, `feature`, `status` (`new`, `reviewing`, `contacted` or `closed`), `privacyConsent` and `submittedAt` (UTC timestamp). These records contain personal information. Limit Firestore IAM and admin dashboard access to authorized staff, and apply the Academy's retention process when a request is complete.
