# Requirements Document

## Introduction

The Student Marketplace is a trusted platform that enables Ethiopian university students to monetize their skills by offering services to clients. The system addresses the fragmented, risky, and inefficient nature of current student service discovery by providing a centralized marketplace with escrow-protected payments, standardized service listings, reputation management, and dispute resolution. The platform serves three primary user roles: Students (service providers), Clients (service buyers), and Administrators (platform moderators).

## Glossary

-   **Student Marketplace System**: The web-based platform that facilitates service discovery, ordering, payment processing, and reputation management
-   **Student**: A verified university student who creates service listings and fulfills client orders
-   **Client**: A registered user who discovers services, places orders, and pays for student work
-   **Administrator**: A platform moderator with elevated privileges to manage users, services, disputes, and system settings
-   **Service Listing**: A published offering created by a Student that includes title, description, category, price, delivery time, and portfolio samples
-   **Order**: A transaction instance between a Client and Student with defined states, requirements, deliverables, and payment terms
-   **Escrow**: A payment holding mechanism where Client funds are captured at order placement and released to the Student upon successful completion
-   **Order State**: The current status of an Order in its lifecycle (pending, in_progress, delivered, revision_requested, completed, cancelled)
-   **Revision**: A Client request for modifications to delivered work within defined limits
-   **Dispute**: A formal escalation mechanism when parties cannot resolve order issues, requiring Administrator intervention
-   **Commission**: The platform fee deducted from Student earnings, calculated as a percentage of the order price
-   **Stripe Connect**: The payment infrastructure enabling platform commission collection and Student payout distribution
-   **Review**: A Client-submitted rating (1-5 stars) and optional text feedback on a completed Order
-   **Notification**: An alert delivered via email and in-app channels for order events, messages, and system updates

## Requirements

### Requirement 1: User Registration and Authentication

**User Story:** As a prospective Student or Client, I want to register with my email address and verify my account, so that I can access platform features securely.

#### Acceptance Criteria

1. WHEN a user submits a valid email address and password during registration, THE Student Marketplace System SHALL create an inactive account and send a verification email
2. WHEN a user clicks the verification link in the email, THE Student Marketplace System SHALL activate the account and assign the selected role (Student or Client)
3. WHEN a user with an activated account submits valid credentials, THE Student Marketplace System SHALL authenticate the user and grant access to role-appropriate features
4. WHEN a user requests a password reset with a registered email, THE Student Marketplace System SHALL send a secure reset link valid for 60 minutes
5. WHEN a user attempts to perform protected actions without email verification, THE Student Marketplace System SHALL deny access and prompt for verification

### Requirement 2: Student Profile Management

**User Story:** As a Student, I want to create and maintain a comprehensive profile with my bio, skills, portfolio samples, and ratings, so that Clients can evaluate my credibility and expertise.

#### Acceptance Criteria

1. WHEN a verified Student submits profile information including bio, university, skills tags, and portfolio files, THE Student Marketplace System SHALL save the profile and display it on the Student's public page
2. WHEN a Student uploads portfolio samples with valid file types and sizes under 10MB, THE Student Marketplace System SHALL store the files securely and display them on the profile
3. WHEN a Student receives a new Review, THE Student Marketplace System SHALL recalculate the average rating and update the profile display within 5 seconds
4. WHEN a Client views a Student profile, THE Student Marketplace System SHALL display the bio, skills, portfolio samples, average rating, and the 10 most recent Reviews
5. WHEN a Student updates profile information, THE Student Marketplace System SHALL validate the changes and reflect updates immediately on the public profile

### Requirement 3: Service Listing Creation and Management

**User Story:** As a Student, I want to create, edit, and manage service listings with detailed descriptions, pricing, and delivery terms, so that Clients can discover and purchase my services.

#### Acceptance Criteria

1. WHEN a verified Student submits a Service Listing with title, description, category, tags, price, delivery days, and sample work, THE Student Marketplace System SHALL validate the data and publish the listing to the marketplace
2. WHEN a Student sets a Service Listing to inactive, THE Student Marketplace System SHALL hide the listing from search results while preserving all listing data
3. WHEN a Student edits an active Service Listing, THE Student Marketplace System SHALL update the listing and reflect changes in search results within 10 seconds
4. WHEN a Service Listing is created, THE Student Marketplace System SHALL generate a unique SEO-friendly slug from the title for the listing URL
5. WHERE a Service Listing contains prohibited content (off-platform contact information or payment terms), THE Student Marketplace System SHALL reject the submission and notify the Student of policy violations

### Requirement 4: Service Discovery and Search

**User Story:** As a Client, I want to search and filter service listings by keyword, category, price range, delivery time, and rating, so that I can quickly find services that meet my needs.

#### Acceptance Criteria

1. WHEN a Client submits a search query, THE Student Marketplace System SHALL return paginated results matching the keyword in title or description, sorted by relevance
2. WHEN a Client applies filters for category, price range, delivery time, or minimum rating, THE Student Marketplace System SHALL return only listings that satisfy all selected criteria
3. WHEN a Client selects a sort option (rating, price, or newest), THE Student Marketplace System SHALL reorder results accordingly and maintain pagination
4. WHEN search results are displayed, THE Student Marketplace System SHALL show listing cards with title, price, delivery time, Student name, average rating, and thumbnail image
5. WHEN a Client views a Service Listing detail page, THE Student Marketplace System SHALL display complete information including description, category, tags, sample work, Student profile link, and average rating

### Requirement 5: Order Placement and Escrow Payment

**User Story:** As a Client, I want to place an order by submitting requirements and prepaying into escrow, so that my payment is protected until the Student delivers satisfactory work.

#### Acceptance Criteria

1. WHEN a Client clicks "Order Now" on a Service Listing and submits requirements with optional file attachments, THE Student Marketplace System SHALL create an Order in "pending" state and display the total price including platform fees
2. WHEN a Client completes payment via Stripe Checkout, THE Student Marketplace System SHALL capture funds into escrow, update the Order status, and notify the Student within 30 seconds
3. WHEN an Order is created with escrow funding, THE Student Marketplace System SHALL calculate and record the commission amount based on platform settings
4. WHEN a Student receives an Order notification, THE Student Marketplace System SHALL provide 48 hours for the Student to accept or decline the Order
5. IF a Student does not respond to an Order within 48 hours, THEN THE Student Marketplace System SHALL automatically cancel the Order and initiate a full refund to the Client

### Requirement 6: Order Acceptance and Work Progression

**User Story:** As a Student, I want to review order requirements and accept orders I can fulfill, so that I can manage my workload and commit to realistic delivery timelines.

#### Acceptance Criteria

1. WHEN a Student accepts a pending Order, THE Student Marketplace System SHALL transition the Order to "in_progress" state, calculate the delivery deadline from delivery_days, and notify the Client
2. WHEN a Student declines a pending Order, THE Student Marketplace System SHALL cancel the Order, initiate a full refund, and notify the Client with the decline reason
3. WHILE an Order is in "in_progress" state, THE Student Marketplace System SHALL display the calculated delivery deadline and remaining time to both parties
4. WHEN the delivery deadline passes without Student submission, THE Student Marketplace System SHALL flag the Order as late and notify both parties
5. WHEN either party sends a message in the Order thread, THE Student Marketplace System SHALL deliver the message within 5 seconds and send an email notification to the recipient

### Requirement 7: Work Delivery and Client Review

**User Story:** As a Student, I want to submit completed work with files and notes, so that the Client can review my deliverables and approve the order.

#### Acceptance Criteria

1. WHEN a Student submits delivery with files and a message, THE Student Marketplace System SHALL transition the Order to "delivered" state and notify the Client immediately
2. WHEN a Client views a delivered Order, THE Student Marketplace System SHALL display the delivery files, message, and options to request revision or approve completion
3. WHEN a Client requests a revision with specific feedback, THE Student Marketplace System SHALL transition the Order to "revision_requested" state and notify the Student
4. WHEN a Student submits revised work, THE Student Marketplace System SHALL return the Order to "delivered" state and notify the Client
5. IF a Client requests more than 2 revisions, THEN THE Student Marketplace System SHALL prevent additional revision requests and require the Client to approve or open a dispute

### Requirement 8: Order Completion and Payout

**User Story:** As a Client, I want to approve completed work and trigger payment release, so that the Student receives compensation for satisfactory delivery.

#### Acceptance Criteria

1. WHEN a Client approves a delivered Order, THE Student Marketplace System SHALL transition the Order to "completed" state and initiate escrow release within 60 seconds
2. WHEN an Order is completed, THE Student Marketplace System SHALL transfer the net amount (order price minus commission) to the Student's Stripe Connect account via application_fee_amount mechanism
3. WHEN escrow funds are released, THE Student Marketplace System SHALL record the payment transaction with stripe_payment_intent_id, transfer_id, amount, commission, and net_amount
4. WHEN an Order reaches "completed" state, THE Student Marketplace System SHALL prompt both Client and Student to submit a Review within 7 days
5. IF a Client does not respond to a delivered Order within 5 days, THEN THE Student Marketplace System SHALL automatically approve the Order and release payment

### Requirement 9: Review and Rating System

**User Story:** As a Client, I want to leave a star rating and written review after order completion, so that I can share my experience and help other Clients make informed decisions.

#### Acceptance Criteria

1. WHEN a Client submits a Review with a rating (1-5 stars) and optional text for a completed Order, THE Student Marketplace System SHALL save the Review and update the Student's average rating within 10 seconds
2. WHEN a Review is submitted, THE Student Marketplace System SHALL display it on the Student's profile and notify the Student via email and in-app notification
3. WHEN a Client edits a Review within 24 hours of submission, THE Student Marketplace System SHALL update the Review and recalculate the Student's average rating
4. WHEN a Student views a Review on their profile, THE Student Marketplace System SHALL provide an option to post a single public reply visible to all profile visitors
5. THE Student Marketplace System SHALL prevent multiple Reviews per Order and SHALL prevent Reviews on Orders not in "completed" state

### Requirement 10: Messaging and Communication

**User Story:** As a Client or Student, I want to communicate securely within order threads with file attachments, so that I can clarify requirements, provide updates, and collaborate effectively.

#### Acceptance Criteria

1. WHEN a user sends a message in an Order thread with text content, THE Student Marketplace System SHALL deliver the message to the recipient and send an email notification within 30 seconds
2. WHEN a user attaches a file to a message with valid MIME type and size under 25MB, THE Student Marketplace System SHALL store the file on a private disk and make it accessible only to Order participants
3. WHERE a message contains prohibited content (email addresses, phone numbers, or off-platform payment terms), THE Student Marketplace System SHALL flag the message for Administrator review and notify the sender of policy violations
4. WHEN a user views an Order thread, THE Student Marketplace System SHALL display all messages in chronological order with sender name, timestamp, content, and attachment download links
5. WHEN a Client sends a pre-order inquiry message to a Student via a Service Listing, THE Student Marketplace System SHALL create a message thread and notify the Student via email and in-app notification

### Requirement 11: Dispute Resolution

**User Story:** As a Client or Student, I want to open a dispute when order issues cannot be resolved directly, so that an Administrator can review evidence and determine a fair resolution.

#### Acceptance Criteria

1. WHEN a Client or Student opens a Dispute on an Order with a reason description, THE Student Marketplace System SHALL create a Dispute record in "open" status and notify the Administrator within 5 minutes
2. WHEN an Administrator views a Dispute, THE Student Marketplace System SHALL display the Order timeline, message thread, delivery files, and both parties' evidence
3. WHEN an Administrator resolves a Dispute by releasing funds, THE Student Marketplace System SHALL transfer the net amount to the Student, update the Dispute status to "released", and notify both parties
4. WHEN an Administrator resolves a Dispute by refunding, THE Student Marketplace System SHALL reverse the Stripe transfer, refund the Client including application fees, update the Dispute status to "refunded", and notify both parties
5. WHILE a Dispute is in "open" status, THE Student Marketplace System SHALL hold escrow funds and prevent Order state transitions until Administrator resolution

### Requirement 12: Administrator User Management

**User Story:** As an Administrator, I want to view, suspend, and reinstate user accounts, so that I can enforce platform policies and protect the community from bad actors.

#### Acceptance Criteria

1. WHEN an Administrator views the user management dashboard, THE Student Marketplace System SHALL display a searchable, paginated list of all users with name, email, role, registration date, and account status
2. WHEN an Administrator suspends a user account, THE Student Marketplace System SHALL set is_active to false, revoke authentication, hide active Service Listings, and notify the user via email
3. WHEN an Administrator reinstates a suspended account, THE Student Marketplace System SHALL set is_active to true, restore authentication, and notify the user via email
4. WHILE a Student account is suspended, THE Student Marketplace System SHALL prevent new Order acceptance and SHALL hold pending payouts
5. WHILE a Client account is suspended, THE Student Marketplace System SHALL prevent new Order placement and SHALL allow completion of existing Orders

### Requirement 13: Administrator Service Moderation

**User Story:** As an Administrator, I want to review, approve, and disable service listings, so that I can maintain platform quality and remove policy-violating content.

#### Acceptance Criteria

1. WHEN an Administrator views the service moderation dashboard, THE Student Marketplace System SHALL display all Service Listings with title, Student name, category, price, status, and creation date
2. WHEN an Administrator disables a Service Listing, THE Student Marketplace System SHALL set is_active to false, remove it from search results, and notify the Student with the reason
3. WHEN an Administrator edits platform categories or tags, THE Student Marketplace System SHALL update the taxonomy and reflect changes in search filters within 60 seconds
4. WHEN an Administrator views a flagged Service Listing, THE Student Marketplace System SHALL display the full listing content, Student profile, and any reported policy violations
5. THE Student Marketplace System SHALL allow Administrators to bulk disable Service Listings by category or Student for policy enforcement

### Requirement 14: Administrator Analytics and Settings

**User Story:** As an Administrator, I want to view platform analytics and configure system settings, so that I can monitor business performance and adjust operational parameters.

#### Acceptance Criteria

1. WHEN an Administrator views the analytics dashboard, THE Student Marketplace System SHALL display current metrics including total GMV, order count, active Students, on-time delivery rate, and dispute rate
2. WHEN an Administrator updates the platform commission percentage, THE Student Marketplace System SHALL apply the new rate to all Orders placed after the change and notify all Students via email
3. WHEN an Administrator views analytics for a date range, THE Student Marketplace System SHALL calculate and display metrics filtered to the specified period
4. THE Student Marketplace System SHALL update analytics metrics in real-time as Orders transition states and payments are processed
5. WHEN an Administrator configures notification settings, THE Student Marketplace System SHALL apply the changes to all subsequent notification deliveries

### Requirement 15: Stripe Connect Integration

**User Story:** As a Student, I want to complete Stripe Connect onboarding, so that I can receive payouts for completed orders directly to my bank account.

#### Acceptance Criteria

1. WHEN a Student initiates Stripe Connect onboarding, THE Student Marketplace System SHALL create a Stripe Connect Express account and redirect the Student to the Stripe onboarding flow
2. WHEN a Student completes Stripe Connect onboarding, THE Student Marketplace System SHALL receive an account.updated webhook, verify capabilities, and enable the Student to accept Orders
3. WHEN a Student attempts to accept an Order without completed Stripe Connect onboarding, THE Student Marketplace System SHALL deny the action and prompt the Student to complete onboarding
4. WHEN the Student Marketplace System processes a payout, THE Student Marketplace System SHALL use application_fee_amount to collect commission and transfer the net amount to the Student's Connect account
5. WHEN a Stripe webhook is received, THE Student Marketplace System SHALL process the event idempotently using the event ID and update relevant Order or payment records within 60 seconds

### Requirement 16: File Upload and Download Security

**User Story:** As a Client or Student, I want to upload and download files securely within orders, so that my work and requirements are protected and accessible only to authorized parties.

#### Acceptance Criteria

1. WHEN a user uploads a file with a valid MIME type and size under the limit, THE Student Marketplace System SHALL store the file on a private disk inaccessible via direct URL
2. WHEN a user downloads a file from an Order or message, THE Student Marketplace System SHALL verify the user is an Order participant before serving the file via a signed, time-limited URL
3. WHEN a user attempts to download a file without authorization, THE Student Marketplace System SHALL deny access and return a 403 Forbidden response
4. THE Student Marketplace System SHALL validate uploaded files for MIME type, size, and malicious content before storage
5. WHEN a file upload fails validation, THE Student Marketplace System SHALL reject the upload and display a specific error message to the user

### Requirement 17: Notification Delivery

**User Story:** As a user, I want to receive timely notifications via email and in-app alerts for important order events and messages, so that I can respond promptly and stay informed.

#### Acceptance Criteria

1. WHEN an Order event occurs (placement, acceptance, delivery, completion), THE Student Marketplace System SHALL send an email notification and create an in-app notification within 60 seconds
2. WHEN a user receives a new message, THE Student Marketplace System SHALL send an email notification and create an in-app notification with message preview within 30 seconds
3. WHEN a user views the notifications panel, THE Student Marketplace System SHALL display unread notifications with timestamp, event type, and action link
4. WHEN a user clicks an in-app notification, THE Student Marketplace System SHALL mark it as read and navigate to the relevant Order or message thread
5. THE Student Marketplace System SHALL display an unread notification counter in the navigation header, updated in real-time as notifications are created and read

### Requirement 18: Order Cancellation

**User Story:** As a Client or Student, I want to cancel an order under specific conditions, so that I can exit transactions that are no longer viable while following fair refund policies.

#### Acceptance Criteria

1. WHEN a Client cancels a pending Order before Student acceptance, THE Student Marketplace System SHALL transition the Order to "cancelled" state and initiate a full refund within 60 seconds
2. WHEN a Student declines a pending Order, THE Student Marketplace System SHALL cancel the Order and initiate a full refund to the Client
3. WHEN a Client requests cancellation of an "in_progress" Order, THE Student Marketplace System SHALL require mutual agreement or Administrator approval before processing the cancellation
4. WHEN an Order is cancelled after work has begun, THE Student Marketplace System SHALL allow the Administrator to determine partial payment to the Student based on work completed
5. WHEN an Order is cancelled, THE Student Marketplace System SHALL record the cancellation reason and notify both parties via email with refund details

### Requirement 19: Role-Based Access Control

**User Story:** As a user with a specific role, I want access restricted to features appropriate for my role, so that platform security and data privacy are maintained.

#### Acceptance Criteria

1. WHEN a Student attempts to access Client-only features (placing orders), THE Student Marketplace System SHALL deny access and display an authorization error
2. WHEN a Client attempts to access Student-only features (creating Service Listings), THE Student Marketplace System SHALL deny access and display an authorization error
3. WHEN a non-Administrator attempts to access the admin console, THE Student Marketplace System SHALL deny access and redirect to the user dashboard
4. THE Student Marketplace System SHALL enforce ownership checks on all Order, message, and file access to ensure users can only view their own data
5. WHEN a user's role is changed by an Administrator, THE Student Marketplace System SHALL update access permissions immediately and apply them to the next request

### Requirement 20: Performance and Reliability

**User Story:** As any user, I want the platform to respond quickly and reliably, so that I can complete tasks efficiently without data loss or extended downtime.

#### Acceptance Criteria

1. WHEN a user submits a search query, THE Student Marketplace System SHALL return results within 2 seconds for datasets up to 10,000 listings
2. WHEN a user navigates to any page, THE Student Marketplace System SHALL render the page within 3 seconds on a standard broadband connection
3. THE Student Marketplace System SHALL process background jobs (emails, webhooks, file processing) within 5 minutes of queuing under normal load
4. THE Student Marketplace System SHALL maintain 99.5% uptime during business hours (8 AM - 10 PM EAT) measured monthly
5. WHEN a database transaction fails, THE Student Marketplace System SHALL roll back all changes and display a user-friendly error message without data corruption
