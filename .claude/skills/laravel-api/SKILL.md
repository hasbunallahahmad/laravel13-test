# Laravel REST API Skill

## Purpose
Use this skill for REST API design and implementation. The API will support the public website and future external integrations.

## Versioning
Public APIs should use `/api/v1/`.

## RESTful Convention
Prefer:
- GET /api/v1/news
- GET /api/v1/news/{news}
- POST /api/v1/news
- PATCH /api/v1/news/{news}
- DELETE /api/v1/news/{news}

Use nouns rather than action-heavy URLs.

## API Resources
Use Laravel API Resources. Do not blindly return raw Eloquent models.

## Validation
Use dedicated Form Requests.

## Authorization
Enforce authorization server-side for every protected endpoint.

## Pagination
Paginate collections that can become large.

## Filtering and Sorting
Only allow explicitly supported filters and sort fields. Never blindly map arbitrary query parameters to database columns.

## Responses
Use consistent response structures and proper HTTP status codes.

Typical codes:
200, 201, 204, 400, 401, 403, 404, 422, 429, 500.

Never expose stack traces, SQL, filesystem paths, environment variables, or secrets in production.

## Security
Do not return password hashes, authentication secrets, private paths, or unnecessary internal fields.

## Testing
Feature-test successful requests, validation failures, authentication, authorization, not-found cases, response structure, pagination, filtering, and security boundaries.
