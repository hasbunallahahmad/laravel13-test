# Laravel Testing Skill

## Purpose
Testing is part of implementation, not an optional final step.

## Feature Tests
Prefer Feature tests for:
- HTTP endpoints
- authentication
- authorization
- APIs
- database interactions

## Unit Tests
Use Unit tests for isolated business logic and pure transformations.

## Security Tests
Protected resources should test:
1. guest access
2. authenticated access
3. authorized access
4. unauthorized access

## Validation Tests
Test missing required fields, invalid types, invalid formats, excessive lengths, duplicates, and invalid relationships.

## UUID Tests
Verify UUID generation, persistence, route binding, API serialization, and relationships.

## API Tests
Test status codes, response structure, validation, authentication, authorization, pagination, filtering, and not-found cases.

## Regression Tests
For security bugs, create a regression test that reproduces the issue, implement the fix, and verify the corrected behavior.
