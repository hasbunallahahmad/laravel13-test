# Laravel Security Skill

## Purpose
Use this skill for authentication, authorization, models, migrations, APIs, uploads, forms, and administrative functionality.

## Core Rule
Security must be enforced server-side. UUIDs/slugs are not authorization mechanisms.

## Identifiers
Users should use UUID primary keys. Public content may use stable slugs. Other non-human-readable resources may use UUIDs.

## Authorization
Use Policies, Gates, permissions, and middleware as appropriate. Never rely on hidden frontend controls.

## Validation
All client input is untrusted. Use Form Requests and explicit allow-lists.

Validate type, length, format, allowed values, uniqueness, relationships, and files as applicable.

## Mass Assignment
Never use `$request->all()` for persistence. Use `$request->validated()` and explicitly control writable fields.

## SQL
Use Eloquent/query builder parameter binding. Never concatenate untrusted input into SQL.

## XSS
Treat rich HTML as untrusted. Sanitize where appropriate. Do not blindly render unsanitized HTML.

## CSRF
Do not disable CSRF protection without a documented architectural reason.

## Authentication
Use Laravel Starter Kit. Never implement custom password hashing or store plaintext passwords.

## Rate Limiting
Protect login, password reset, public APIs, expensive searches, and sensitive endpoints.

## File Uploads
Validate MIME type, extension, size, and dimensions where appropriate. Never trust original filenames. Generate storage filenames and prevent executable uploads.

## Secrets
Never expose or commit `.env`, APP_KEY, database passwords, API keys, tokens, or credentials.

## API
Use authentication when required, authorization, rate limiting, validation, pagination, and API Resources. Never expose sensitive model fields.

## Security Review
Ask:
1. Can guests access this?
2. Can unauthorized users access this?
3. Can one user modify another user's resource?
4. Can IDs be enumerated?
5. Can validation be bypassed?
6. Is mass assignment possible?
7. Are uploads safe?
8. Does the API leak sensitive information?
9. Can the endpoint be abused?
10. Should the action be auditable?
