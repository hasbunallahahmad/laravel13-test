# Laravel 13 Backend Engineering Instructions

## Project Context

This project is a Laravel 13 application intended to serve as the backend and content management system for the main website of Dinas Arsip dan Perpustakaan Kota Semarang.

The backend must be secure, maintainable, scalable, API-ready, and suitable for a production government website.

The project does NOT use Filament.

Laravel Starter Kit is used for authentication and application scaffolding.

---

# 1. PRIMARY OBJECTIVES

Prioritize:
1. Security
2. Maintainability
3. Correct authorization
4. Strict validation
5. Separation of responsibilities
6. API readiness
7. Performance
8. Testability
9. Laravel best practices
10. Production readiness

Do not prioritize implementation speed over architectural correctness.

---

# 2. TECHNOLOGY BASELINE

Use:
- Laravel 13
- PHP 8.3+
- Laravel Starter Kit
- Blade and/or Livewire where appropriate
- RESTful API
- UUID where appropriate
- Slug where appropriate
- Spatie ecosystem where justified
- Laravel Policies and Gates
- Form Requests
- API Resources
- Laravel validation
- Database transactions
- Queues where appropriate
- Caching where appropriate

Do NOT install Filament.

Do NOT install unnecessary third-party packages.

Before adding a package, determine whether Laravel already provides the required functionality and verify compatibility and maintenance.

---

# 3. SECURITY-FIRST PRINCIPLE

Security must be considered before implementation.

UUIDs and slugs are not authorization mechanisms.

Every protected operation must verify authorization using Policies, Gates, permissions, authentication, middleware, and ownership checks where applicable.

---

# 4. IDENTIFIER POLICY

Do not expose sequential integer IDs through public URLs or APIs when avoidable.

Users should use UUID primary keys.

Public content should generally use slugs when the entity naturally has a human-readable identity.

Examples:
- News → slug
- Pages → slug
- Categories → slug
- Tags → slug
- Media → UUID
- Private resources → UUID

Do not create redundant identifiers without architectural justification.

---

# 5. SLUG POLICY

Slugs must be validated, normalized, unique where required, safe, collision-resistant, and stable.

Do not blindly regenerate slugs on every update.

Consider SEO and historical URL stability.

---

# 6. VALIDATION POLICY

Never trust client input.

Use Form Requests for HTTP validation.

Validation should consider:
- types
- length limits
- allow-listed values
- uniqueness
- authorization
- relationships
- files
- MIME types
- file size
- URL format
- enums where appropriate

Avoid permissive validation when stronger validation is appropriate.

---

# 7. MASS ASSIGNMENT

Never use `$request->all()` for persistence.

Prefer `$request->validated()` and explicitly control writable fields.

---

# 8. SRP / SEPARATION OF RESPONSIBILITIES

Single Responsibility Principle is mandatory.

Preferred flow:

Request
→ Controller
→ Action/Service
→ Model
→ Database

Controllers must remain thin.

Use Actions for discrete business operations.

Examples:
- CreateNews
- UpdateNews
- DeleteNews
- PublishNews
- CreateUser
- AssignRole

Do not create unnecessary abstractions.

---

# 9. CONTROLLERS

Controllers should primarily:
1. receive the request
2. authorize
3. call an Action/Service
4. return a response

Avoid business logic, complex queries, file processing, notifications, and external API logic inside controllers.

---

# 10. DATABASE

Prioritize:
- referential integrity
- UUID consistency
- indexes
- unique constraints
- foreign keys
- correct data types
- justified nullable fields
- timestamps
- SoftDeletes only when justified

Important uniqueness rules should exist at database level as well as application validation.

---

# 11. N+1 PREVENTION

Avoid N+1 queries.

Use eager loading where appropriate and paginate large result sets.

---

# 12. API ARCHITECTURE

Public APIs should use:
`/api/v1/...`

Use:
- API Resources
- Form Requests
- authentication middleware
- authorization
- rate limiting
- pagination
- filtering allow-lists
- sorting allow-lists
- consistent response structures
- correct HTTP status codes

Do not expose passwords, hashes, tokens, secrets, private paths, or unnecessary internal fields.

---

# 13. AUTHENTICATION & AUTHORIZATION

Use Laravel Starter Kit for browser authentication.

Use Policies/Gates and Spatie Permission where appropriate.

Authentication asks "Who are you?"
Authorization asks "Are you allowed to do this?"

Both must be enforced server-side.

---

# 14. SPATIE ECOSYSTEM

Potentially useful packages:
- spatie/laravel-permission
- spatie/laravel-activitylog
- spatie/laravel-medialibrary

Before installing:
1. Check whether Laravel already provides the capability.
2. Check Laravel 13/PHP compatibility.
3. Check maintenance status.
4. Check security history.
5. Confirm actual project need.

---

# 15. ACTIVITY LOGGING

Important administrative actions should be auditable.

Examples:
- user creation
- role/permission changes
- content creation/modification/deletion
- publishing/unpublishing
- important security events

Never log passwords, tokens, API secrets, or credentials.

---

# 16. FILE UPLOAD SECURITY

Uploaded files are untrusted.

Validate MIME type, extension, size, and dimensions where applicable.

Never trust original filenames.

Generate storage filenames.

Do not permit executable uploads.

Use private storage for sensitive documents when appropriate.

---

# 17. FRONTEND CONTENT MANAGEMENT

The backend is the source of truth for public website content.

Eventually administrators should be able to manage:
- pages
- news
- announcements
- agendas
- banners
- galleries
- documents
- navigation
- footer
- contact information
- social media links
- site settings

Avoid hardcoding public content into Blade templates.

---

# 18. ERROR HANDLING

Never expose stack traces, SQL, filesystem paths, environment variables, or secrets in production.

API errors should be structured and consistent.

---

# 19. TRANSACTIONS

Use database transactions when multiple related database operations must succeed or fail together.

Do not wrap every operation in transactions without reason.

---

# 20. PERFORMANCE

Consider:
- N+1 queries
- indexes
- eager loading
- pagination
- caching
- query complexity
- unnecessary API requests
- large file processing

Do not implement complex optimization without a concrete reason.

---

# 21. TESTING

Important business behavior must have automated tests.

Prefer:
- Feature tests for HTTP/API/auth/authorization/database behavior
- Unit tests for isolated business logic

At minimum test:
- authentication
- authorization
- validation
- CRUD
- policies
- API responses
- permission boundaries
- UUID behavior
- slug uniqueness
- file upload restrictions

Security-sensitive behavior requires regression tests.

---

# 22. CODE QUALITY

Follow Laravel conventions.

Prefer:
- meaningful names
- strict typing where appropriate
- return types
- dependency injection
- small methods
- small classes
- explicit behavior

Avoid:
- giant controllers
- giant models
- duplicated logic
- unnecessary helpers
- premature abstractions

---

# 23. BEFORE MODIFYING CODE

Before implementing a feature:
1. Inspect project structure.
2. Inspect related migrations.
3. Inspect models.
4. Inspect routes.
5. Inspect middleware.
6. Inspect policies.
7. Inspect tests.
8. Reuse existing abstractions where appropriate.
9. Identify security implications.
10. Then implement.

Never blindly overwrite existing code.

---

# 24. MIGRATION SAFETY

For a new blank project, modifying initial migrations before deployment is acceptable.

For an existing/shared/production database, prefer new migrations rather than rewriting historical migrations.

Always consider data preservation and rollback behavior.

---

# 25. DEPENDENCY SAFETY

Before installing/upgrading packages:
- inspect composer.json
- inspect composer.lock
- verify Laravel/PHP compatibility
- check security advisories

Do not make forceful dependency changes merely to make installation succeed.

---

# 26. GIT SAFETY

Before destructive operations:
- inspect git status
- inspect current diff
- avoid overwriting unrelated work
- do not reset user changes
- do not delete files unless necessary

---

# 27. ARTISAN SAFETY

Before destructive commands such as `migrate:fresh` or `db:wipe`, determine environment and consequences.

Never destroy a database without explicit confirmation.

---

# 28. IMPLEMENTATION WORKFLOW

For every feature:
1. Understand requirements
2. Inspect codebase
3. Identify domain
4. Design database changes
5. Design authorization
6. Design validation
7. Implement migration
8. Implement model
9. Implement policy
10. Implement Form Request
11. Implement Action/Service
12. Implement controller
13. Implement API Resource if required
14. Implement routes
15. Implement tests
16. Run formatting/static analysis if available
17. Run relevant tests
18. Review security
19. Review performance
20. Summarize changes

---

# 29. DO NOT OVERENGINEER

Do not automatically create Repository interfaces, DTOs, Service interfaces, Events, Listeners, Traits, or Helpers.

Introduce abstractions only when they solve a concrete problem.

---

# 30. RESPONSE FORMAT

Before modifying files, briefly explain:
- what will be changed
- why
- security implications

After implementation report:
- files created
- files modified
- migrations
- packages added
- tests executed
- security considerations
- remaining recommendations

Never claim a test or command succeeded unless actually executed.

---

# 31. CRITICAL RULE

When uncertain, inspect the codebase first.

If a requirement conflicts with Laravel conventions or security best practices, explain the conflict and propose the safer implementation.

Security and data integrity take priority over convenience.
