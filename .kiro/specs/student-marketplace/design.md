# Design Document

## Overview

The Student Marketplace is a server-rendered Laravel 12 web application that connects Ethiopian university students with clients seeking services. The architecture follows Laravel conventions with Blade templates for views, Alpine.js for progressive enhancement, and Tailwind CSS for styling. The system emphasizes trust through Stripe Connect escrow payments, clarity via explicit order state machines, and usability through responsive design patterns.

The application is structured around seven core modules: Authentication & Accounts, Services Catalog, Orders & Lifecycle, Payments (Stripe Connect), Messaging & Notifications, Reviews & Ratings, and Admin Console. Each module encapsulates related domain logic with clear boundaries enforced through Laravel's Policy system.

## Architecture

### Technology Stack

-   **Framework**: Laravel 12.x with Blade templating engine
-   **Frontend**: Tailwind CSS 4.x + Alpine.js 3.x (no SPA, progressive enhancement only)
-   **Authentication**: Laravel Breeze (Blade variant) with email verification
-   **Database**: MySQL 8.x with Eloquent ORM
-   **Payments**: Stripe Connect (stripe/stripe-php) with destination charges and application fees
-   **File Storage**: Laravel Filesystem with local private disk and signed URLs
-   **Queue System**: Database driver (migrate to Redis if needed)
-   **Mail**: Laravel Mailables with SMTP (Gmail App Password for dev)
-   **Testing**: PHPunit with HTTP/feature tests and Stripe webhook fixtures
-   **Observability**: Laravel logging (daily driver) + Telescope (local/dev only)

### Application Layers

```
┌─────────────────────────────────────────────────┐
│  Routes (web.php)                               │
│  - Resourceful controllers                      │
│  - Blade views + occasional JSON responses      │
└─────────────────────────────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────┐
│  Controllers                                    │
│  - Request orchestration                        │
│  - Return views/redirects/JSON                  │
└─────────────────────────────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────┐
│  Form Requests                                  │
│  - Validation rules                             │
│  - Authorization checks                         │
└─────────────────────────────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────┐
│  Actions/Services                               │
│  - PlaceOrder, DeliverWork, RequestRevision     │
│  - CompleteOrder, RefundOrder, ResolveDispute   │
└─────────────────────────────────────────────────┘
```

                      ↓

┌─────────────────────────────────────────────────┐
│ Policies/Gates │
│ - Role checks (admin|student|client) │
│ - Ownership verification │
└─────────────────────────────────────────────────┘
↓
┌─────────────────────────────────────────────────┐
│ Eloquent Models │
│ - User, Service, Order, Message, Review │
│ - Payment, Dispute, Notification │
└─────────────────────────────────────────────────┘
↓
┌─────────────────────────────────────────────────┐
│ Events & Listeners │
│ - OrderPlaced → SendOrderNotification │
│ - OrderCompleted → ReleaseEscrowPayment │
│ - ReviewSubmitted → UpdateStudentRating │
└─────────────────────────────────────────────────┘
↓
┌─────────────────────────────────────────────────┐
│ Jobs (Queued) │
│ - SendOrderEmail, ProcessStripeWebhook │
│ - GeneratePayoutTransfer, PurgeExpiredDrafts │
└─────────────────────────────────────────────────┘

```

### Request Flow Example (Order Placement)

1. Client clicks "Order Now" → `GET /order/{service}` → `OrderController@create`
2. Controller loads service, checks auth/verification → returns Blade view with form
3. Client submits requirements → `POST /orders` → `StoreOrderRequest` validates
4. Controller calls `PlaceOrderAction` → creates Order (pending), initiates Stripe Checkout
5. Stripe redirects back → webhook `checkout.session.completed` → `ProcessStripeWebhook` job
6. Job updates Order, fires `OrderPlaced` event → listener sends notifications
7. Student receives email + in-app notification → accepts order → state transitions to `in_progress`

```

## Components and Interfaces

### 1. Authentication & Accounts Module

**Controllers**

-   `RegisteredUserController`: handles registration with role selection (student/client)
-   `EmailVerificationController`: Breeze default, sends/verifies email
-   `ProfileController`: student profile (bio, skills, portfolio), client profile (basic info)

**Models**

-   `User`: id, first_name, last_name, email, email_verified_at, password, role (enum: admin|student|client), bio, university, phone, avatar_path, is_active, stripe_connect_account_id, timestamps

**Policies**

-   `UserPolicy`: authorize profile updates (own profile or admin), suspend/reinstate (admin only)

**Key Behaviors**

-   Registration creates inactive account, sends verification email
-   Email verification activates account and grants role-based access
-   Students must complete Stripe Connect onboarding before accepting orders
-   Profile updates validate file uploads (avatar, portfolio samples)

### 2. Services Catalog Module

**Controllers**

-   `ServiceController`: index (search/filter), show (detail page), create, store, edit, update, destroy (soft delete)
-   `CategoryController`: admin-only CRUD for categories/tags

**Models**

-   `Service`: id, student_id, title, slug, description, category_id, tags (JSON), price (DECIMAL 10,2), delivery_days (INT), sample_work_path, is_active, timestamps
-   `Category`: id, name, slug, description, timestamps

**Policies**

-   `ServicePolicy`: create (verified student), update/delete (owner or admin), view (public if active)

**Key Behaviors**

-   Slug generation from title using `Str::slug()` with uniqueness check
-   Search uses MySQL FULLTEXT index on title/description or LIKE queries
-   Filters: category_id, price range (min/max), delivery_days (max), rating (min)
-   Sort options: rating DESC, price ASC/DESC, created_at DESC
-   Pagination: 20 items per page
-   Sample work stored on private disk, served via signed URLs

### 3. Orders & Lifecycle Module

**Controllers**

-   `OrderController`: create (requirements form), store (initiate payment), show (timeline view), update (state transitions)
-   `OrderActionController`: accept, decline, deliver, requestRevision, approve, cancel

**Models**

-   `Order`: id, service_id, student_id, client_id, price, commission, requirements (TEXT), delivery_date (computed), status (ENUM: pending|in_progress|delivered|revision_requested|completed|cancelled), revision_count (default 0), cancelled_reason, timestamps

**Actions (Service Classes)**

-   `PlaceOrderAction`: validates service availability, creates Order (pending), initiates Stripe Checkout session
-   `AcceptOrderAction`: transitions pending → in_progress, calculates delivery_date (now + delivery_days), sends notification
-   `DeclineOrderAction`: transitions pending → cancelled, initiates refund, records reason
-   `DeliverWorkAction`: transitions in_progress → delivered, attaches files, sends notification
-   `RequestRevisionAction`: transitions delivered → revision_requested (max 2 times), records feedback
-   `ApproveOrderAction`: transitions delivered → completed, triggers escrow release
-   `CancelOrderAction`: handles cancellation logic based on current state, initiates refunds

**State Machine Rules**

```
pending → in_progress (student accepts)
pending → cancelled (student declines OR 48h timeout OR client cancels)
in_progress → delivered (student submits work)
in_progress → cancelled (mutual agreement OR admin)
delivered → completed (client approves OR 5-day auto-approve)
delivered → revision_requested (client requests changes, max 2 times)
revision_requested → delivered (student resubmits)
revision_requested → dispute (client opens dispute after max revisions)
```

**Policies**

-   `OrderPolicy`: view (participant or admin), accept/decline (assigned student), deliver (student on in_progress), requestRevision (client on delivered, revision_count < 2), approve (client on delivered), cancel (participant with rules or admin)

**Key Behaviors**

-   Delivery deadline: `created_at + delivery_days` when accepted
-   Late flag: `now() > delivery_date AND status = in_progress`
-   Auto-approve job: scheduled daily, finds delivered orders > 5 days old, calls `ApproveOrderAction`
-   Auto-cancel job: scheduled hourly, finds pending orders > 48h old, calls `DeclineOrderAction` with timeout reason

### 4. Payments Module (Stripe Connect)

**Controllers**

-   `StripeConnectController`: onboarding (redirect to Stripe), return (handle completion), refresh (re-enter onboarding)
-   `StripeWebhookController`: single endpoint for all webhooks, dispatches to handlers

**Models**

-   `Payment`: id, order_id, stripe_payment_intent_id, stripe_charge_id, application_fee_id, transfer_id, amount (DECIMAL 10,2), commission (DECIMAL 10,2), net_amount (DECIMAL 10,2), status (ENUM: pending|completed|failed|refunded), processed_at, metadata (JSON), timestamps

**Actions**

-   `CreateCheckoutSessionAction`: creates Stripe Checkout session with line items, success/cancel URLs, metadata (order_id)
-   `ReleaseEscrowAction`: creates transfer to student Connect account with `application_fee_amount` for commission
-   `RefundOrderAction`: refunds payment intent with `reverse_transfer: true` and `refund_application_fee: true`

**Webhook Handlers (Jobs)**

-   `HandleCheckoutSessionCompleted`: updates Order payment status, transitions to pending, sends notifications
-   `HandlePaymentIntentSucceeded`: confirms payment capture, updates Payment record
-   `HandleChargeRefunded`: updates Payment status to refunded, updates Order if applicable
-   `HandleTransferCreated`: records transfer_id in Payment, marks as completed
-   `HandleAccountUpdated`: syncs Connect account capabilities, enables/disables order acceptance

**Key Behaviors**

-   Stripe Connect Express onboarding flow with `account_type: express`
-   Commission calculated as percentage (stored in settings table): `commission = price * commission_rate`
-   Destination charges pattern: charge client, hold in platform balance, transfer to student on completion
-   Webhook idempotency: check `stripe_events` table for processed event IDs before handling
-   Capability checks: student must have `card_payments` and `transfers` capabilities to accept orders
-   Refund logic: full refund if cancelled before in_progress, partial refund negotiated by admin if work started

### 5. Messaging & Notifications Module

**Controllers**

-   `MessageController`: index (thread list), show (thread view), store (send message)
-   `NotificationController`: index (list unread), markAsRead, markAllAsRead

**Models**

-   `Message`: id, order_id (nullable for pre-order), service_id (nullable for pre-order), sender_id, receiver_id, content (TEXT), attachment_path, is_read, timestamps
-   Uses Laravel's `notifications` table for in-app notifications

**Notifications (Mailable + Database)**

-   `OrderPlacedNotification`: sent to student when client places order
-   `OrderAcceptedNotification`: sent to client when student accepts
-   `MessageReceivedNotification`: sent to recipient with message preview
-   `WorkDeliveredNotification`: sent to client when student delivers
-   `RevisionRequestedNotification`: sent to student when client requests changes
-   `OrderCompletedNotification`: sent to both parties with review prompt
-   `PayoutReleasedNotification`: sent to student with payout details
-   `ReviewPostedNotification`: sent to student when client leaves review

**Policies**

-   `MessagePolicy`: view (sender or receiver or admin), store (order participant or pre-order inquiry)

**Key Behaviors**

-   Pre-order messages: client can message student via service listing before placing order
-   Order messages: thread created automatically on order placement
-   Content moderation: regex filter for email patterns, phone numbers, payment keywords (paypal, venmo, etc.)
-   Flagged messages: stored in `flagged_messages` table for admin review
-   Attachment validation: MIME types (pdf, doc, docx, jpg, png, zip), max 25MB
-   Notification polling: Alpine.js polls `/notifications/unread-count` every 30 seconds
-   Email throttling: max 10 emails per user per hour to prevent spam

### 6. Reviews & Ratings Module

**Controllers**

-   `ReviewController`: create (form), store (submit review), edit (within 24h), update, reply (student response)

**Models**

-   `Review`: id, order_id, reviewer_id (client), reviewee_id (student), rating (TINYINT 1-5), text (TEXT nullable), student_reply (TEXT nullable), timestamps

**Actions**

-   `SubmitReviewAction`: validates order is completed, creates review, recalculates student average rating
-   `UpdateReviewAction`: allows edit within 24h, recalculates rating
-   `ReplyToReviewAction`: student adds public reply (one-time only)

**Policies**

-   `ReviewPolicy`: create (client on completed order, one per order), update (reviewer within 24h), reply (reviewee student, once)

**Key Behaviors**

-   Average rating calculation: `AVG(rating) WHERE reviewee_id = student_id`, cached on User model
-   Rating display: stars (full, half, empty) + numeric (e.g., 4.7)
-   Review edit window: 24 hours from creation, tracked via `created_at`
-   Student reply: single TEXT field, no edit after submission
-   Review visibility: public on student profile, sorted by created_at DESC
-   Review prompt: shown on order completion page and sent via email notification

### 7. Admin Console Module

**Controllers**

-   `Admin\UserController`: index (list/search), show (detail), suspend, reinstate
-   `Admin\ServiceController`: index (moderation queue), approve, disable
-   `Admin\DisputeController`: index (open disputes), show (evidence), resolve (release/refund)
-   `Admin\CategoryController`: CRUD for categories and tags
-   `Admin\SettingsController`: edit platform settings (commission rate, timeouts)
-   `Admin\AnalyticsController`: dashboard with metrics

**Models**

-   `Dispute`: id, order_id, opened_by_id, reason (TEXT), status (ENUM: open|resolved|refunded|released), resolution_notes (TEXT), resolved_by_id, resolved_at, timestamps
-   `Setting`: key-value store for platform config (commission_rate, order_timeout_hours, auto_approve_days, max_revisions)

**Actions**

-   `SuspendUserAction`: sets is_active = false, revokes sessions, hides listings, holds payouts
-   `ReinstateUserAction`: sets is_active = true, restores access
-   `ResolveDisputeAction`: admin decision (release/refund/partial), updates Payment, closes Dispute

**Policies**

-   `AdminPolicy`: all actions require role = admin

**Key Behaviors**

-   User search: by name, email, role, registration date range
-   Service moderation: flag services with prohibited content, bulk disable by category
-   Dispute evidence: displays order timeline, messages, delivery files, both parties' statements
-   Analytics metrics:
    -   GMV (Gross Merchandise Value): SUM(price) WHERE status = completed
    -   Order count: COUNT(\*) by status
    -   Active students: COUNT(DISTINCT student_id) WHERE has active service OR recent order
    -   On-time delivery rate: COUNT(delivered <= delivery_date) / COUNT(delivered)
    -   Dispute rate: COUNT(disputes) / COUNT(completed orders)
-   Settings: stored in database, cached, invalidated on update

## Data Models

### Entity Relationship Diagram

```
User (1) ──< (M) Service
User (1) ──< (M) Order (as student)
User (1) ──< (M) Order (as client)
User (1) ──< (M) Message (as sender)
User (1) ──< (M) Message (as receiver)
User (1) ──< (M) Review (as reviewer)
User (1) ──< (M) Review (as reviewee)
User (1) ──< (M) Dispute (as opener)

Service (1) ──< (M) Order
Service (1) ──< (M) Message (pre-order)
Service (M) ──> (1) Category

Order (1) ──< (M) Message
Order (1) ──< (1) Payment
Order (1) ──< (1) Review
Order (1) ──< (1) Dispute

Category (1) ──< (M) Service
```

### Database Schema

**users**

-   id: BIGINT UNSIGNED PRIMARY KEY
-   first_name: VARCHAR(100)
-   last_name: VARCHAR(100)
-   email: VARCHAR(255) UNIQUE
-   email_verified_at: TIMESTAMP NULLABLE
-   password: VARCHAR(255)
-   role: ENUM('admin', 'student', 'client')
-   bio: TEXT NULLABLE
-   university: VARCHAR(255) NULLABLE
-   phone: VARCHAR(20) NULLABLE
-   avatar_path: VARCHAR(255) NULLABLE
-   stripe_connect_account_id: VARCHAR(255) NULLABLE
-   is_active: BOOLEAN DEFAULT TRUE
-   created_at, updated_at: TIMESTAMPS
-   INDEX(email), INDEX(role), INDEX(is_active)

**services**

-   id: BIGINT UNSIGNED PRIMARY KEY
-   student_id: BIGINT UNSIGNED FOREIGN KEY → users.id
-   title: VARCHAR(255)
-   slug: VARCHAR(255) UNIQUE
-   description: TEXT
-   category_id: BIGINT UNSIGNED FOREIGN KEY → categories.id
-   tags: JSON NULLABLE
-   price: DECIMAL(10, 2)
-   delivery_days: INT
-   sample_work_path: VARCHAR(255) NULLABLE
-   is_active: BOOLEAN DEFAULT TRUE
-   created_at, updated_at: TIMESTAMPS
-   INDEX(student_id), INDEX(category_id), INDEX(is_active), INDEX(slug)
-   FULLTEXT(title, description) -- if MySQL supports

**categories**

-   id: BIGINT UNSIGNED PRIMARY KEY
-   name: VARCHAR(100) UNIQUE
-   slug: VARCHAR(100) UNIQUE
-   description: TEXT NULLABLE
-   created_at, updated_at: TIMESTAMPS

**orders**

-   id: BIGINT UNSIGNED PRIMARY KEY
-   service_id: BIGINT UNSIGNED FOREIGN KEY → services.id
-   student_id: BIGINT UNSIGNED FOREIGN KEY → users.id
-   client_id: BIGINT UNSIGNED FOREIGN KEY → users.id
-   price: DECIMAL(10, 2)
-   commission: DECIMAL(10, 2)
-   requirements: TEXT
-   delivery_date: TIMESTAMP NULLABLE
-   status: ENUM('pending', 'in_progress', 'delivered', 'revision_requested', 'completed', 'cancelled')
-   revision_count: INT DEFAULT 0
-   cancelled_reason: TEXT NULLABLE
-   created_at, updated_at: TIMESTAMPS
-   INDEX(service_id), INDEX(student_id), INDEX(client_id), INDEX(status), INDEX(delivery_date)

**messages**

-   id: BIGINT UNSIGNED PRIMARY KEY
-   order_id: BIGINT UNSIGNED NULLABLE FOREIGN KEY → orders.id
-   service_id: BIGINT UNSIGNED NULLABLE FOREIGN KEY → services.id
-   sender_id: BIGINT UNSIGNED FOREIGN KEY → users.id
-   receiver_id: BIGINT UNSIGNED FOREIGN KEY → users.id
-   content: TEXT
-   attachment_path: VARCHAR(255) NULLABLE
-   is_read: BOOLEAN DEFAULT FALSE
-   created_at, updated_at: TIMESTAMPS
-   INDEX(order_id), INDEX(service_id), INDEX(sender_id), INDEX(receiver_id), INDEX(created_at)

**reviews**

-   id: BIGINT UNSIGNED PRIMARY KEY
-   order_id: BIGINT UNSIGNED UNIQUE FOREIGN KEY → orders.id
-   reviewer_id: BIGINT UNSIGNED FOREIGN KEY → users.id (client)
-   reviewee_id: BIGINT UNSIGNED FOREIGN KEY → users.id (student)
-   rating: TINYINT (1-5)
-   text: TEXT NULLABLE
-   student_reply: TEXT NULLABLE
-   created_at, updated_at: TIMESTAMPS
-   INDEX(order_id), INDEX(reviewee_id), INDEX(rating)

**payments**

-   id: BIGINT UNSIGNED PRIMARY KEY
-   order_id: BIGINT UNSIGNED UNIQUE FOREIGN KEY → orders.id
-   stripe_payment_intent_id: VARCHAR(255) UNIQUE
-   stripe_charge_id: VARCHAR(255) NULLABLE
-   application_fee_id: VARCHAR(255) NULLABLE
-   transfer_id: VARCHAR(255) NULLABLE
-   amount: DECIMAL(10, 2)
-   commission: DECIMAL(10, 2)
-   net_amount: DECIMAL(10, 2)
-   status: ENUM('pending', 'completed', 'failed', 'refunded')
-   processed_at: TIMESTAMP NULLABLE
-   metadata: JSON NULLABLE
-   created_at, updated_at: TIMESTAMPS
-   INDEX(order_id), INDEX(stripe_payment_intent_id), INDEX(status)

**disputes**

-   id: BIGINT UNSIGNED PRIMARY KEY
-   order_id: BIGINT UNSIGNED UNIQUE FOREIGN KEY → orders.id
-   opened_by_id: BIGINT UNSIGNED FOREIGN KEY → users.id
-   reason: TEXT
-   status: ENUM('open', 'resolved', 'refunded', 'released')
-   resolution_notes: TEXT NULLABLE
-   resolved_by_id: BIGINT UNSIGNED NULLABLE FOREIGN KEY → users.id (admin)
-   resolved_at: TIMESTAMP NULLABLE
-   created_at, updated_at: TIMESTAMPS
-   INDEX(order_id), INDEX(status), INDEX(opened_by_id)

**settings**

-   id: BIGINT UNSIGNED PRIMARY KEY
-   key: VARCHAR(100) UNIQUE
-   value: TEXT
-   type: ENUM('string', 'integer', 'decimal', 'boolean', 'json')
-   created_at, updated_at: TIMESTAMPS
-   INDEX(key)

**stripe_events** (for webhook idempotency)

-   id: BIGINT UNSIGNED PRIMARY KEY
-   stripe_event_id: VARCHAR(255) UNIQUE
-   type: VARCHAR(100)
-   processed_at: TIMESTAMP
-   created_at: TIMESTAMP
-   INDEX(stripe_event_id)

**flagged_messages** (for content moderation)

-   id: BIGINT UNSIGNED PRIMARY KEY
-   message_id: BIGINT UNSIGNED FOREIGN KEY → messages.id
-   flagged_reason: VARCHAR(255)
-   reviewed_by_id: BIGINT UNSIGNED NULLABLE FOREIGN KEY → users.id (admin)
-   reviewed_at: TIMESTAMP NULLABLE
-   created_at: TIMESTAMP
-   INDEX(message_id), INDEX(reviewed_at)

**notifications** (Laravel default)

-   id: CHAR(36) PRIMARY KEY (UUID)
-   type: VARCHAR(255)
-   notifiable_type: VARCHAR(255)
-   notifiable_id: BIGINT UNSIGNED
-   data: TEXT (JSON)
-   read_at: TIMESTAMP NULLABLE
-   created_at: TIMESTAMP
-   INDEX(notifiable_type, notifiable_id), INDEX(read_at)

## Error Handling

### Validation Errors

-   Form Requests return validation errors with 422 status
-   Blade displays errors via `@error` directive with Tailwind styling
-   Alpine.js shows inline validation feedback for real-time checks

### Application Errors

-   Service layer throws custom exceptions: `OrderStateException`, `PaymentException`, `AuthorizationException`
-   Global exception handler catches and logs errors
-   User-friendly error pages: 403 (Forbidden), 404 (Not Found), 500 (Server Error)
-   Blade error components: `<x-alert type="error">` for inline errors

### Payment Errors

-   Stripe API errors caught and logged with context (order_id, user_id)
-   Failed payments: display error message, allow retry, send admin notification
-   Webhook failures: retry with exponential backoff (Stripe handles this), log for manual review
-   Idempotency: prevent duplicate processing via `stripe_events` table

### File Upload Errors

-   Validation: MIME type, size, extension checks before storage
-   Storage failures: rollback transaction, display error, log for investigation
-   Download errors: 403 if unauthorized, 404 if file missing, signed URL expiration handled gracefully

### Queue Failures

-   Failed jobs logged to `failed_jobs` table with exception details
-   Retry strategy: 3 attempts with exponential backoff
-   Critical failures (payment processing): send admin alert via email/Slack
-   Manual retry: admin dashboard shows failed jobs with retry button

## Testing Strategy

### Unit Tests

-   Model methods: rating calculations, state transitions, date computations
-   Action classes: PlaceOrderAction, ReleaseEscrowAction, ResolveDisputeAction
-   Helper functions: slug generation, content moderation filters

### Feature Tests

**Authentication & Authorization**

-   Registration flow with email verification
-   Login/logout with role-based redirects
-   Password reset flow
-   Policy enforcement (unauthorized access returns 403)

**Service Catalog**

-   Create/edit/delete service listings
-   Search with keyword, filters, sorting
-   Pagination and result accuracy
-   Slug uniqueness and generation

**Order Lifecycle**

-   Place order → Stripe checkout → webhook → order created
-   Student accept/decline with state transitions
-   Deliver work → client review → approve/request revision
-   Auto-approve after 5 days
-   Auto-cancel pending orders after 48h timeout

**Payments**

-   Stripe Checkout session creation with correct line items
-   Webhook handling with idempotency (duplicate events ignored)
-   Escrow release on completion with correct commission calculation
-   Refund processing with reverse_transfer and refund_application_fee

**Messaging**

-   Send message with/without attachment
-   Pre-order inquiry messages
-   Content moderation flags prohibited terms
-   Notification delivery (database + email)

**Reviews**

-   Submit review on completed order (one per order)
-   Edit review within 24h window
-   Student reply (one-time)
-   Average rating recalculation

**Admin Console**

-   User suspension/reinstatement
-   Service moderation (disable listings)
-   Dispute resolution (release/refund)
-   Analytics metrics accuracy

### Integration Tests

-   Stripe Connect onboarding flow (mocked Stripe API)
-   Webhook payload processing with real Stripe event fixtures
-   File upload/download with signed URLs
-   Email delivery (using Mailtrap or Mail::fake())

### Test Data

-   Seeders for categories, sample users (admin, students, clients), services, orders
-   Factories for all models with realistic fake data
-   Stripe webhook fixtures: checkout.session.completed, payment_intent.succeeded, charge.refunded, transfer.created, account.updated

### Test Environment

-   SQLite in-memory database for speed
-   Mail::fake() for email assertions
-   Storage::fake() for file upload tests
-   Queue::fake() for job assertions
-   Stripe API mocked with Mockery or Stripe test mode

## UI/UX Design

### Layout Structure

**app.blade.php** (authenticated layout)

-   Header: logo, search bar, navigation (Dashboard, Services, Orders, Messages), notifications bell, user dropdown
-   Main content area with max-width container
-   Footer: links (About, Terms, Privacy, Contact), social media icons

**auth.blade.php** (guest layout)

-   Centered card with logo
-   Form content (login, register, verify email, reset password)
-   Footer with minimal links

### Blade Components

**Form Components**

-   `<x-input>`: text input with label, error display, Alpine.js validation
-   `<x-textarea>`: multi-line text with character counter
-   `<x-select>`: dropdown with search (Alpine.js)
-   `<x-file-input>`: file upload with preview and progress bar
-   `<x-checkbox>`: styled checkbox with label
-   `<x-button>`: primary, secondary, danger variants with loading state

**UI Components**

-   `<x-card>`: container with shadow, padding, optional header/footer
-   `<x-modal>`: Alpine.js modal with backdrop, close button
-   `<x-badge>`: status badges (pending, in_progress, completed, etc.) with color coding
-   `<x-alert>`: success, error, warning, info alerts with icons
-   `<x-pagination>`: Laravel pagination links styled with Tailwind
-   `<x-rating-stars>`: display rating with full/half/empty stars
-   `<x-avatar>`: user avatar with fallback initials

**Partials**

-   `service-card.blade.php`: listing card for search results (image, title, price, rating, student name)
-   `order-timeline.blade.php`: visual timeline of order states with icons and timestamps
-   `message-thread.blade.php`: chat-style message display with sender/receiver alignment
-   `notification-item.blade.php`: notification list item with icon, text, timestamp, read status

### Alpine.js Interactions

**Modals**

-   Service detail image gallery (click thumbnail → open modal with full image)
-   Confirm dialogs (delete service, cancel order, suspend user)

**Dropdowns**

-   User menu (profile, settings, logout)
-   Notification panel (click bell → dropdown with recent notifications)
-   Filter toggles (search page: show/hide advanced filters)

**Forms**

-   File upload preview (show selected files before submission)
-   Character counter for textareas (bio, requirements, review text)
-   Optimistic message send (append message to thread immediately, show spinner)

**Real-time Updates**

-   Notification counter polling (every 30s, update bell badge)
-   Message thread auto-scroll to bottom on new message
-   Order status badge updates (poll order status every 10s on order detail page)

### Tailwind Styling

**Color Palette**

-   Primary: Blue (trust, professionalism) - `bg-blue-600`, `text-blue-600`
-   Success: Green (completed, approved) - `bg-green-600`, `text-green-600`
-   Warning: Yellow (pending, revision) - `bg-yellow-500`, `text-yellow-600`
-   Danger: Red (cancelled, dispute) - `bg-red-600`, `text-red-600`
-   Neutral: Gray (backgrounds, borders) - `bg-gray-100`, `text-gray-700`

**Typography**

-   Headings: `font-bold text-2xl/3xl/4xl`
-   Body: `text-base text-gray-700`
-   Small text: `text-sm text-gray-500`

**Spacing**

-   Consistent padding: `p-4`, `p-6`, `p-8`
-   Margins: `mb-4`, `mt-6`, `space-y-4`

**Responsive Design**

-   Mobile-first approach with `sm:`, `md:`, `lg:` breakpoints
-   Hamburger menu for mobile navigation (Alpine.js toggle)
-   Stacked layout on mobile, grid on desktop (service cards, order timeline)

**Dark Mode**

-   Optional: `dark:` variants for all components
-   Toggle in user settings, stored in localStorage

## Security Considerations

### Authentication & Authorization

-   CSRF protection on all POST/PUT/DELETE routes (Laravel default)
-   Email verification required before listing services or placing orders
-   Password hashing with bcrypt (Laravel default)
-   Rate limiting: 5 login attempts per minute, 60 API requests per minute
-   Session timeout: 2 hours of inactivity
-   Policy checks on every controller action (authorize() or Gate::allows())

### Data Protection

-   Sensitive data encrypted at rest (Stripe keys, user emails)
-   HTTPS enforced in production (middleware redirect)
-   Signed URLs for file downloads with 1-hour expiration
-   Private disk for file storage (not publicly accessible)
-   SQL injection prevention via Eloquent parameterized queries
-   XSS prevention via Blade automatic escaping

### Payment Security

-   PCI compliance via Stripe (no card data stored on server)
-   Stripe webhook signature verification (STRIPE_WEBHOOK_SECRET)
-   Idempotent webhook processing (prevent duplicate charges/refunds)
-   Audit trail: all payment transactions logged with metadata

### Content Moderation

-   Regex filters for prohibited content (email, phone, payment keywords)
-   Flagged messages stored for admin review
-   User reporting mechanism (report service, report user)
-   Admin tools to suspend accounts and disable listings

### File Upload Security

-   MIME type validation (whitelist: pdf, doc, docx, jpg, png, zip)
-   File size limits (10MB for portfolio, 25MB for attachments)
-   Virus scanning (optional: integrate ClamAV or cloud service)
-   Filename sanitization (remove special characters, prevent path traversal)

## Performance Optimization

### Database

-   Indexes on foreign keys, status columns, search fields (see Data Models section)
-   FULLTEXT index on services.title and services.description for search
-   Query optimization: eager loading (with()) to prevent N+1 queries
-   Database connection pooling (default in Laravel)

### Caching

-   Config/route/view caching in production (`php artisan optimize`)
-   Query result caching for homepage featured services (5 minutes)
-   User rating cache (invalidate on new review)
-   Settings cache (invalidate on admin update)

### Asset Optimization

-   Tailwind CSS purge in production (remove unused classes)
-   Image optimization: compress portfolio/avatar images on upload
-   Lazy loading for images (loading="lazy" attribute)
-   CDN for static assets (optional: CloudFront, Cloudflare)

### Queue Processing

-   Background jobs for emails, webhooks, file processing
-   Queue workers: 2-4 processes depending on load
-   Job batching for bulk operations (e.g., send 100 notifications)
-   Failed job monitoring and retry strategy

### Monitoring

-   Laravel Telescope in local/dev for debugging
-   Application logging (daily driver, 14-day retention)
-   Slow query logging (queries > 1 second)
-   Error tracking (optional: Sentry, Bugsnag)
-   Uptime monitoring (optional: Pingdom, UptimeRobot)

## Deployment Architecture

### Environment Setup

**Local Development**

-   Laravel Valet or Herd (macOS) or Laragon (Windows)
-   MySQL 8.x local instance
-   Stripe test mode with test API keys
-   Mailtrap for email testing
-   Queue worker: `php artisan queue:work`

**Staging**

-   Shared hosting or VPS (DigitalOcean, Linode)
-   MySQL 8.x database
-   Stripe test mode
-   Supervisor for queue workers
-   HTTPS via Let's Encrypt

**Production**

-   VPS or managed hosting (Laravel Forge, Ploi)
-   MySQL 8.x with automated backups
-   Stripe live mode with live API keys
-   Redis for cache and queue (migrate from database driver)
-   Supervisor for queue workers (2-4 processes)
-   HTTPS enforced
-   CDN for static assets (optional)

### Deployment Process

1. Push code to Git repository (GitHub, GitLab, Bitbucket)
2. CI/CD pipeline (GitHub Actions, GitLab CI):
    - Run tests (Pest)
    - Build assets (Tailwind)
    - Deploy to staging
3. Manual approval for production deployment
4. Run migrations: `php artisan migrate --force`
5. Clear caches: `php artisan optimize:clear && php artisan optimize`
6. Restart queue workers: `php artisan queue:restart`
7. Smoke tests: verify homepage, login, order placement

### Environment Variables

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://studentmarketplace.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=marketplace
DB_USERNAME=root
DB_PASSWORD=secret

STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=noreply@studentmarketplace.com
MAIL_PASSWORD=app_password

QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## Future Enhancements (Post-MVP)

### Phase 2 Features

-   **Service Packages**: students offer tiered pricing (basic, standard, premium)
-   **Favorites/Bookmarks**: clients save services for later
-   **Advanced Search**: location-based filtering, availability calendar
-   **Student Verification**: university email verification, ID upload
-   **Bulk Orders**: clients order multiple services at once
-   **Referral Program**: students earn credits for referring new users

### Phase 3 Features

-   **Live Chat**: real-time messaging with WebSockets (Laravel Reverb)
-   **Video Calls**: integrated video consultation (Twilio, Agora)
-   **Mobile App**: React Native or Flutter app with push notifications
-   **API**: public API for third-party integrations
-   **Multi-language**: i18n support (Amharic, English)
-   **Advanced Analytics**: student earnings dashboard, client spending insights

### Technical Improvements

-   **Elasticsearch**: replace MySQL FULLTEXT for better search
-   **Redis**: migrate cache and queue from database driver
-   **CDN**: CloudFront or Cloudflare for global asset delivery
-   **Microservices**: extract payment processing to separate service
-   **GraphQL**: alternative API for mobile app
-   **AI Moderation**: automated content filtering with ML models
