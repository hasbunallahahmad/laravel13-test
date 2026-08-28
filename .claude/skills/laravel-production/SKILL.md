# Laravel Production Readiness Skill

## Purpose
Use this skill for deployment and production security reviews.

## Environment
Verify production uses:
- APP_ENV=production
- APP_DEBUG=false

Never commit `.env`.

## Secrets
Protect APP_KEY, database credentials, API keys, tokens, and other secrets.

## Database
Production should have strong credentials, restricted network access, backups, suitable indexes, and disciplined migration practices.

## HTTP Security
Review HTTPS, secure cookies, sessions, CSRF, security headers, and rate limiting.

## Authentication
Review login throttling, password reset, email verification, session handling, and account enumeration risks.

## Authorization
Audit every administrative resource for privilege boundaries and cross-user access.

## Uploads
Review MIME/extension validation, size limits, storage location, generated filenames, executable-file prevention, and private/public storage boundaries.

## API
Review authentication, authorization, rate limits, pagination, filter allow-lists, sensitive fields, and error disclosure.

## Logging
Never log passwords, tokens, API keys, or sensitive credentials.

## Dependencies
Inspect Composer dependencies and security advisories. Remove unnecessary packages.

## Deployment Checklist
Verify tests, safe migrations, APP_DEBUG=false, protected secrets, correct storage permissions, queues, scheduler, cache, rate limiting, authorization tests, and upload-security tests.
