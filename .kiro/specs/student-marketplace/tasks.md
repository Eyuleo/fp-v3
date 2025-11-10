# Implementation Plan

-   [x] 1. Set up Laravel project foundation with authentication

-   [x] 1.1 Install Laravel 12.x and configure environment

    -   Create fresh Laravel project
    -   Configure database connection (MySQL 8.x)
    -   Set up .env file with required variables
    -   _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_

-   [x] 1.2 Install and configure Laravel Breeze with Blade

    -   Install Breeze package with Blade stack
    -   Publish authentication views and routes
    -   Configure email verification middleware
    -   _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_

-   [x] 1.3 Set up Tailwind CSS and Alpine.js

    -   Install Tailwind CSS 4.x via npm
    -   Configure Tailwind with Laravel Vite plugin
    -   Install Alpine.js 3.x
    -   Create base layout files (app.blade.php, auth.blade.php)
    -   _Requirements: All UI-related requirements_

-   [x] 1.4 Create role-based authentication system

    -   Add role enum column to users migration (admin|student|client)
    -   Modify registration to include role selection
    -   Create middleware for role-based access control
    -   Implement role-based dashboard redirects after login
    -   _Requirements: 1.1, 1.2, 19.1, 19.2, 19.3, 19.5_

-   [x] 2. Build user profile and account management

-   [x] 2.1 Create user profile migrations and models

    -   Extend users table with bio, university, phone, avatar_path, stripe_connect_account_id, is_active
    -   Create User model with relationships and accessors
    -   _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_

-   [x] 2.2 Implement student profile pages

    -   Create ProfileController with show/edit/update methods
    -   Build student profile view with bio, skills, portfolio, ratings
    -   Implement portfolio file upload with validation (10MB limit, allowed MIME types)
    -   Create avatar upload functionality
    -   _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_

-   [x] 2.3 Create UserPolicy for authorization

    -   Implement policy methods for profile updates (own profile or admin)
    -   Add authorization checks in ProfileController
    -   _Requirements: 2.5, 19.4_

-   [x] 3. Implement service catalog with categories

-   [x] 3.1 Create categories and services database schema

    -   Create categories migration (id, name, slug, description)
    -   Create services migration (id, student_id, title, slug, description, category_id, tags JSON, price, delivery_days, sample_work_path, is_active)
    -   Add indexes for performance (category_id, is_active, slug)
    -   Create Category and Service models with relationships
    -   _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_

-   [x] 3.2 Build service listing CRUD functionality

    -   Create ServiceController with index, create, store, edit, update, destroy methods
    -   Implement slug generation from title with uniqueness check
    -   Build service creation form with category dropdown, tags input, price, delivery days
    -   Add sample work file upload with validation
    -   Implement service activation/deactivation (pause listing)
    -   _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_

-   [x] 3.3 Create ServicePolicy for authorization

    -   Implement create (verified student only), update/delete (owner or admin), view (public if active)
    -   Add policy checks in ServiceController
    -   _Requirements: 3.1, 3.5, 19.1, 19.2_

-   [x] 3.4 Implement service search and filtering

    -   Build search functionality with keyword matching (title/description)
    -   Add filters for category, price range, delivery time, minimum rating
    -   Implement sort options (rating DESC, price ASC/DESC, created_at DESC)
    -   Add pagination (20 items per page)
    -   Create service-card partial for listing display
    -   _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5_

-   [x] 3.5 Create service detail page

    -   Build service show view with complete information
    -   Display student profile link, average rating, sample work
    -   Add "Order Now" button for clients
    -   Include pre-order inquiry message option
    -   _Requirements: 4.5_

-   [x] 4. Build order management and lifecycle

-   [x] 4.1 Create orders database schema and model

    -   Create orders migration (id, service_id, student_id, client_id, price, commission, requirements, delivery_date, status enum, revision_count, cancelled_reason)
    -   Add indexes for performance (service_id, student_id, client_id, status, delivery_date)
    -   Create Order model with relationships and status constants
    -   _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 6.1, 6.2, 6.3, 6.4, 6.5_

-   [x] 4.2 Implement order placement flow

    -   Create OrderController with create method (requirements form)
    -   Build requirements submission form with file upload support
    -   Display total price including platform fees
    -   Create PlaceOrderAction service class to handle order creation
    -   _Requirements: 5.1, 5.2, 5.3_

-   [x] 4.3 Create order state machine actions

    -   Implement AcceptOrderAction (pending → in_progress, calculate delivery_date)
    -   Implement DeclineOrderAction (pending → cancelled, initiate refund)
    -   Implement DeliverWorkAction (in_progress → delivered, attach files)
    -   Implement RequestRevisionAction (delivered → revision_requested, max 2 times)
    -   Implement ApproveOrderAction (delivered → completed, trigger escrow release)
    -   Implement CancelOrderAction (handle cancellation based on current state)
    -   _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 7.1, 7.2, 7.3, 7.4, 7.5, 8.1, 8.2, 8.3, 8.4, 8.5, 18.1, 18.2, 18.3, 18.4, 18.5_

-   [x] 4.4 Build order detail page with timeline

    -   Create order show view with state timeline visualization
    -   Display requirements, delivery files, revision history
    -   Add action buttons based on current state and user role (accept, decline, deliver, request revision, approve)
    -   Create order-timeline partial component
    -   _Requirements: 6.3, 7.1, 7.2, 7.3, 7.4_

-   [x] 4.5 Create OrderPolicy for authorization

    -   Implement view (participant or admin), accept/decline (assigned student), deliver (student on in_progress)
    -   Add requestRevision (client on delivered, revision_count < 2), approve (client on delivered)
    -   Add cancel authorization with state-based rules
    -   _Requirements: 6.1, 6.2, 7.1, 7.3, 18.3, 19.4_

-   [x] 4.6 Implement automated order management jobs

    -   Create AutoApproveOrdersJob (find delivered orders > 5 days, call ApproveOrderAction)
    -   Create AutoCancelPendingOrdersJob (find pending orders > 48h, call DeclineOrderAction)
    -   Schedule jobs in app/Console/Kernel.php (daily for auto-approve, hourly for auto-cancel)
    -   _Requirements: 5.5, 6.4, 8.5_

-   [x] 5. Integrate Stripe Connect for payments

-   [x] 5.1 Set up Stripe SDK and configuration

    -   Install stripe/stripe-php package
    -   Configure Stripe API keys in .env (test mode initially)
    -   Create config/stripe.php for Stripe settings
    -   _Requirements: 15.1, 15.2, 15.3, 15.4, 15.5_

-   [x] 5.2 Create payments database schema

    -   Create payments migration (id, order_id, stripe_payment_intent_id, stripe_charge_id, application_fee_id, transfer_id, amount, commission, net_amount, status enum, processed_at, metadata JSON)
    -   Create stripe_events migration for webhook idempotency
    -   Create Payment model with relationships
    -   _Requirements: 8.1, 8.2, 8.3, 15.5_

-   [x] 5.3 Implement Stripe Connect onboarding

    -   Create StripeConnectController with onboarding, return, refresh methods
    -   Build Connect Express account creation flow
    -   Create onboarding redirect to Stripe with return URL
    -   Handle onboarding completion and capability verification
    -   Store stripe_connect_account_id on User model
    -   _Requirements: 15.1, 15.2, 15.3_

-   [x] 5.4 Build Stripe Checkout integration

    -   Create CreateCheckoutSessionAction service class
    -   Implement checkout session creation with line items, metadata (order_id)
    -   Add success and cancel URLs
    -   Integrate checkout into order placement flow
    -   _Requirements: 5.2, 5.3, 8.1_

-   [x] 5.5 Implement escrow release and refund actions

    -   Create ReleaseEscrowAction (transfer to student with application_fee_amount)
    -   Create RefundOrderAction (refund with reverse_transfer and refund_application_fee)
    -   Calculate commission based on platform settings
    -   Record payment transactions in payments table
    -   _Requirements: 8.1, 8.2, 8.3, 18.4_

-   [x] 5.6 Create Stripe webhook endpoint and handlers

    -   Create StripeWebhookController with single webhook endpoint
    -   Implement webhook signature verification
    -   Create HandleCheckoutSessionCompleted job (update order, send notifications)
    -   Create HandlePaymentIntentSucceeded job (confirm payment capture)
    -   Create HandleChargeRefunded job (update payment status)
    -   Create HandleTransferCreated job (record transfer_id)
    -   Create HandleAccountUpdated job (sync Connect capabilities)
    -   Implement idempotency using stripe_events table
    -   _Requirements: 15.5, 8.3_

-   [x] 6. Build messaging and notification system

-   [x] 6.1 Create messages database schema

    -   Create messages migration (id, order_id nullable, service_id nullable, sender_id, receiver_id, content, attachment_path, is_read)
    -   Create flagged_messages migration for content moderation
    -   Add indexes for performance (order_id, service_id, sender_id, receiver_id, created_at)
    -   Create Message model with relationships
    -   _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5_

-   [x] 6.2 Implement messaging functionality

    -   Create MessageController with index (thread list), show (thread view), store (send message)
    -   Build message thread view with chat-style layout
    -   Add file attachment upload with validation (25MB limit, allowed MIME types)
    -   Create message-thread partial component
    -   Implement pre-order inquiry messages via service listings
    -   _Requirements: 10.1, 10.2, 10.4, 10.5_

-   [x] 6.3 Add content moderation for messages

    -   Create content filter helper with regex patterns (email, phone, payment keywords)
    -   Implement automatic flagging of prohibited content
    -   Store flagged messages in flagged_messages table
    -   Notify sender of policy violations
    -   _Requirements: 10.3_

-   [x] 6.4 Create MessagePolicy for authorization

    -   Implement view (sender or receiver or admin), store (order participant or pre-order inquiry)
    -   Add policy checks in MessageController
    -   _Requirements: 10.4, 19.4_

-   [x] 6.5 Implement notification system

    -   Create notification classes (OrderPlacedNotification, OrderAcceptedNotification, MessageReceivedNotification, WorkDeliveredNotification, RevisionRequestedNotification, OrderCompletedNotification, PayoutReleasedNotification, ReviewPostedNotification)
    -   Configure database and mail notification channels
    -   Build notification panel UI with unread counter
    -   Create NotificationController with index, markAsRead, markAllAsRead methods
    -   Add Alpine.js polling for unread count (every 30 seconds)
    -   _Requirements: 17.1, 17.2, 17.3, 17.4, 17.5_

-   [x] 7. Implement reviews and ratings system

-   [x] 7.1 Create reviews database schema

    -   Create reviews migration (id, order_id unique, reviewer_id, reviewee_id, rating, text, student_reply)
    -   Add index on reviewee_id for rating calculations
    -   Create Review model with relationships
    -   _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5_

-   [x] 7.2 Build review submission functionality

    -   Create ReviewController with create, store, edit, update, reply methods
    -   Build review form with star rating input and text area
    -   Implement SubmitReviewAction to create review and recalculate student average rating
    -   Add review prompt on order completion page
    -   _Requirements: 9.1, 9.2, 9.3_

-   [x] 7.3 Implement review editing and student replies

    -   Create UpdateReviewAction with 24-hour edit window check
    -   Implement ReplyToReviewAction for student public replies (one-time only)
    -   Build review edit form
    -   Add student reply form on review display
    -   _Requirements: 9.3, 9.4_

-   [x] 7.4 Create ReviewPolicy for authorization

    -   Implement create (client on completed order, one per order), update (reviewer within 24h), reply (reviewee student, once)
    -   Add policy checks in ReviewController
    -   _Requirements: 9.5, 19.4_

-   [x] 7.5 Add rating display components

    -   Create rating-stars Blade component for visual star display
    -   Calculate and cache average rating on User model
    -   Display ratings on student profiles and service listings
    -   _Requirements: 2.3, 2.4, 4.4_

-   [x] 8. Build admin console and moderation tools

-   [x] 8.1 Create admin middleware and routes

    -   Create AdminMiddleware to check role = admin
    -   Set up admin route group with middleware
    -   Create admin layout with navigation sidebar
    -   _Requirements: 12.1, 12.2, 12.3, 12.4, 12.5, 13.1, 13.2, 13.3, 13.4, 13.5, 14.1, 14.2, 14.3, 14.4, 14.5, 19.3_

-   [x] 8.2 Implement user management features

    -   Create Admin\UserController with index (list/search), show (detail), suspend, reinstate methods
    -   Build user list view with search, filters, pagination
    -   Implement SuspendUserAction (set is_active = false, revoke sessions, hide listings, hold payouts)
    -   Implement ReinstateUserAction (set is_active = true, restore access)
    -   _Requirements: 12.1, 12.2, 12.3, 12.4, 12.5_

-   [x] 8.3 Build service moderation interface

    -   Create Admin\ServiceController with index (moderation queue), approve, disable methods
    -   Build service moderation view with filters and bulk actions
    -   Add disable service functionality with reason notification
    -   _Requirements: 13.1, 13.2, 13.4, 13.5_

-   [x] 8.4 Create category and tag management

    -   Create Admin\CategoryController with CRUD operations
    -   Build category management interface
    -   Implement category/tag updates with cache invalidation
    -   _Requirements: 13.3_

-   [x] 8.5 Implement dispute resolution system

    -   Create disputes migration (id, order_id, opened_by_id, reason, status enum, resolution_notes, resolved_by_id, resolved_at)
    -   Create Dispute model with relationships
    -   Create Admin\DisputeController with index (open disputes), show (evidence), resolve methods
    -   Build dispute detail view with order timeline, messages, delivery files
    -   Implement ResolveDisputeAction (release/refund/partial, update Payment, close Dispute)
    -   _Requirements: 11.1, 11.2, 11.3, 11.4, 11.5_

-   [x] 8.6 Build analytics dashboard

    -   Create Admin\AnalyticsController with dashboard method
    -   Calculate metrics: GMV, order count by status, active students, on-time delivery rate, dispute rate
    -   Build analytics view with charts and date range filters
    -   Implement real-time metric updates
    -   _Requirements: 14.1, 14.3, 14.4_

-   [x] 8.7 Create platform settings management

    -   Create settings migration (id, key, value, type enum)
    -   Create Setting model with type casting
    -   Create Admin\SettingsController with edit, update methods
    -   Build settings form for commission rate, timeouts, limits
    -   Implement cache invalidation on settings update
    -   _Requirements: 14.2, 14.5_

-   [ ] 9. Implement file storage and security
-   [ ] 9.1 Configure Laravel Filesystem for private storage

    -   Set up private disk in config/filesystems.php
    -   Create storage directories for avatars, portfolios, samples, attachments, deliveries
    -   _Requirements: 16.1, 16.2, 16.3, 16.4, 16.5_

-   [ ] 9.2 Create file upload helper and validation

    -   Build FileUploadService with validation (MIME type, size, malicious content)
    -   Implement filename sanitization
    -   Add file storage methods for different file types
    -   _Requirements: 16.1, 16.4, 16.5_

-   [ ] 9.3 Implement signed URL file downloads

    -   Create FileDownloadController with download method
    -   Implement authorization checks (order participant or admin)
    -   Generate signed URLs with 1-hour expiration
    -   Handle file not found and unauthorized access errors
    -   _Requirements: 16.2, 16.3_

-   [ ] 10. Create reusable Blade components and UI elements
-   [ ] 10.1 Build form components

    -   Create x-input component (text input with label, error display)
    -   Create x-textarea component (with character counter)
    -   Create x-select component (dropdown with Alpine.js search)
    -   Create x-file-input component (with preview and progress bar)
    -   Create x-checkbox component (styled checkbox with label)
    -   Create x-button component (primary, secondary, danger variants with loading state)
    -   _Requirements: All form-related requirements_

-   [ ] 10.2 Create UI components

    -   Create x-card component (container with shadow, padding)
    -   Create x-modal component (Alpine.js modal with backdrop)
    -   Create x-badge component (status badges with color coding)
    -   Create x-alert component (success, error, warning, info)
    -   Create x-pagination component (styled Laravel pagination)
    -   Create x-rating-stars component (display rating with stars)
    -   Create x-avatar component (user avatar with fallback initials)
    -   _Requirements: All UI-related requirements_

-   [ ] 10.3 Build page-specific partials

    -   Create service-card partial (listing card for search results)
    -   Create order-timeline partial (visual timeline of order states)
    -   Create message-thread partial (chat-style message display)
    -   Create notification-item partial (notification list item)
    -   _Requirements: 4.4, 6.3, 10.4, 17.3_

-   [ ] 11. Add Alpine.js interactivity
-   [ ] 11.1 Implement modal interactions

    -   Add image gallery modal for service detail pages
    -   Create confirm dialog modals (delete, cancel, suspend actions)
    -   _Requirements: UI/UX requirements_

-   [ ] 11.2 Build dropdown components

    -   Create user menu dropdown (profile, settings, logout)
    -   Implement notification panel dropdown
    -   Add filter toggle dropdowns for search page
    -   _Requirements: 17.3, 17.4_

-   [ ] 11.3 Add form enhancements

    -   Implement file upload preview
    -   Add character counters for textareas
    -   Create optimistic message send (append immediately, show spinner)
    -   _Requirements: 10.1, 10.2_

-   [ ] 11.4 Implement real-time updates

    -   Add notification counter polling (every 30 seconds)
    -   Create message thread auto-scroll to bottom
    -   Add order status polling on order detail page (every 10 seconds)
    -   _Requirements: 17.5_

-   [ ] 12. Implement security measures
-   [ ] 12.1 Configure authentication security

    -   Set up rate limiting (5 login attempts per minute, 60 API requests per minute)
    -   Configure session timeout (2 hours of inactivity)
    -   Ensure CSRF protection on all mutating routes
    -   _Requirements: 1.1, 1.3, 19.5_

-   [ ] 12.2 Add authorization policies across the application

    -   Ensure all controllers use authorize() or Gate::allows()
    -   Add ownership checks on all resource access
    -   Implement role-based access control throughout
    -   _Requirements: 19.1, 19.2, 19.3, 19.4, 19.5_

-   [ ] 12.3 Implement file upload security

    -   Add MIME type whitelist validation
    -   Enforce file size limits (10MB portfolio, 25MB attachments)
    -   Implement filename sanitization to prevent path traversal
    -   _Requirements: 16.4, 16.5_

-   [ ] 12.4 Configure payment security

    -   Implement Stripe webhook signature verification
    -   Ensure idempotent webhook processing
    -   Add audit trail logging for all payment transactions
    -   _Requirements: 15.5, 8.3_

-   [ ] 13. Set up queue system and background jobs
-   [ ] 13.1 Configure queue system

    -   Set up database queue driver in config/queue.php
    -   Create jobs table migration
    -   Create failed_jobs table migration
    -   _Requirements: 17.1, 17.2_

-   [ ] 13.2 Implement email jobs

    -   Create SendOrderEmail job
    -   Create SendNotificationEmail job
    -   Configure job retry strategy (3 attempts with exponential backoff)
    -   _Requirements: 17.1, 17.2_

-   [ ] 13.3 Create webhook processing jobs

    -   Implement ProcessStripeWebhook job with idempotency
    -   Add error handling and logging for webhook failures
    -   _Requirements: 15.5_

-   [ ] 13.4 Build scheduled jobs

    -   Create GeneratePayoutTransfer job
    -   Create PurgeExpiredDrafts job
    -   Configure job scheduling in app/Console/Kernel.php
    -   _Requirements: 8.2, 8.3_

-   [ ] 14. Create database seeders and factories
-   [ ] 14.1 Build model factories

    -   Create UserFactory with role variants (admin, student, client)
    -   Create CategoryFactory
    -   Create ServiceFactory with realistic data
    -   Create OrderFactory with various states
    -   Create MessageFactory, ReviewFactory, PaymentFactory
    -   _Requirements: All testing requirements_

-   [ ] 14.2 Create database seeders

    -   Create CategorySeeder with common service categories
    -   Create AdminUserSeeder with default admin account
    -   Create DemoDataSeeder with sample students, clients, services, orders
    -   Configure DatabaseSeeder to run all seeders
    -   _Requirements: All testing requirements_

-   [ ] 15. Implement event-driven architecture
-   [ ] 15.1 Create domain events

    -   Create OrderPlaced event
    -   Create OrderAccepted event
    -   Create OrderDelivered event
    -   Create OrderCompleted event
    -   Create ReviewSubmitted event
    -   Create DisputeOpened event
    -   _Requirements: 5.2, 6.1, 7.1, 8.1, 9.1, 11.1_

-   [ ] 15.2 Build event listeners

    -   Create SendOrderNotification listener (for OrderPlaced)
    -   Create SendAcceptanceNotification listener (for OrderAccepted)
    -   Create SendDeliveryNotification listener (for OrderDelivered)
    -   Create ReleaseEscrowPayment listener (for OrderCompleted)
    -   Create UpdateStudentRating listener (for ReviewSubmitted)
    -   Create NotifyAdminOfDispute listener (for DisputeOpened)
    -   _Requirements: 5.2, 6.1, 7.1, 8.1, 9.1, 11.1_

-   [ ] 15.3 Register events and listeners

    -   Configure event-listener mappings in EventServiceProvider
    -   Ensure events are dispatched in appropriate action classes
    -   _Requirements: All event-related requirements_

-   [ ]\* 16. Write feature tests for core functionality
-   [ ]\* 16.1 Create authentication and authorization tests

    -   Test registration flow with email verification
    -   Test login/logout with role-based redirects
    -   Test password reset flow
    -   Test policy enforcement (unauthorized access returns 403)
    -   _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 19.1, 19.2, 19.3_

-   [ ]\* 16.2 Write service catalog tests

    -   Test service CRUD operations
    -   Test search with keyword, filters, sorting
    -   Test pagination and result accuracy
    -   Test slug uniqueness and generation
    -   _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 4.1, 4.2, 4.3, 4.4, 4.5_

-   [ ]\* 16.3 Create order lifecycle tests

    -   Test order placement with Stripe checkout
    -   Test student accept/decline with state transitions
    -   Test deliver work and client review flow
    -   Test revision request (max 2 times)
    -   Test auto-approve after 5 days
    -   Test auto-cancel pending orders after 48h
    -   _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 6.1, 6.2, 6.3, 6.4, 6.5, 7.1, 7.2, 7.3, 7.4, 7.5, 8.1, 8.5_

-   [ ]\* 16.4 Write payment integration tests

    -   Test Stripe Checkout session creation
    -   Test webhook handling with idempotency (duplicate events ignored)
    -   Test escrow release with correct commission calculation
    -   Test refund processing with reverse_transfer and refund_application_fee
    -   _Requirements: 8.1, 8.2, 8.3, 15.1, 15.2, 15.3, 15.4, 15.5_

-   [ ]\* 16.5 Create messaging and notification tests

    -   Test send message with/without attachment
    -   Test pre-order inquiry messages
    -   Test content moderation flags prohibited terms
    -   Test notification delivery (database + email)
    -   _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5, 17.1, 17.2, 17.3, 17.4, 17.5_

-   [ ]\* 16.6 Write review system tests

    -   Test submit review on completed order (one per order)
    -   Test edit review within 24h window
    -   Test student reply (one-time)
    -   Test average rating recalculation
    -   _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5_

-   [ ]\* 16.7 Create admin console tests

    -   Test user suspension/reinstatement
    -   Test service moderation (disable listings)
    -   Test dispute resolution (release/refund)
    -   Test analytics metrics accuracy
    -   _Requirements: 12.1, 12.2, 12.3, 12.4, 12.5, 13.1, 13.2, 13.3, 13.4, 13.5, 14.1, 14.2, 14.3, 14.4, 14.5_

-   [ ] 17. Configure production environment
-   [ ] 17.1 Set up environment configuration

    -   Create .env.example with all required variables
    -   Document environment variable requirements
    -   Configure production database settings
    -   Set up Stripe live mode configuration
    -   _Requirements: 20.1, 20.2, 20.3, 20.4, 20.5_

-   [ ] 17.2 Implement performance optimizations

    -   Add database indexes for all foreign keys and search fields
    -   Configure query result caching for homepage
    -   Set up config/route/view caching commands
    -   Implement Tailwind CSS purge for production
    -   _Requirements: 20.1, 20.2_

-   [ ] 17.3 Configure logging and monitoring

    -   Set up Laravel logging with daily driver (14-day retention)
    -   Configure error logging with context (user_id, order_id)
    -   Add slow query logging (queries > 1 second)
    -   Install and configure Telescope for local/dev only
    -   _Requirements: 20.3, 20.4, 20.5_

-   [ ] 17.4 Set up queue workers and supervisord

    -   Create supervisord configuration for queue workers
    -   Configure queue worker processes (2-4 based on load)
    -   Set up failed job monitoring
    -   Document queue worker deployment
    -   _Requirements: 17.1, 17.2, 20.3_

-   [ ] 18. Create documentation and deployment guide
-   [ ] 18.1 Write installation documentation

    -   Document local development setup steps
    -   Create database migration and seeding instructions
    -   Document Stripe test mode configuration
    -   Write troubleshooting guide for common issues
    -   _Requirements: All requirements_

-   [ ] 18.2 Create deployment documentation
    -   Document production deployment process
    -   Write server requirements and configuration guide
    -   Create deployment checklist (migrations, caches, queue restart)
    -   Document environment variable configuration
    -   _Requirements: All requirements_
