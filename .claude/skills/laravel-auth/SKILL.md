# Laravel Authentication & Authorization Skill

## Purpose
Use this skill for authentication, users, roles, permissions, policies, and account security.

## Authentication
Use Laravel Starter Kit. Do not replace Laravel authentication with a custom implementation unless explicitly required.

Support appropriate login, logout, password reset, email verification, and session handling.

## Users
Use UUID primary keys for users. Never expose sequential user IDs in public URLs.

## Roles
Roles represent broad responsibility categories, for example:
- Super Admin
- Admin
- Editor
- Author
- Operator

## Permissions
Permissions represent specific actions, for example:
- view news
- create news
- update news
- delete news
- publish news
- manage users
- manage roles
- manage settings

Prefer permission checks for granular authorization instead of hard-coding role names everywhere.

## Spatie Permission
Use `spatie/laravel-permission` when role/permission management is required. Do not create a second custom permission system without a strong reason.

## Policies
Protect model/resource operations with Policies.

## Privilege Escalation
Users must not be able to assign themselves privileged roles, modify permissions without authorization, or bypass policy checks.

## Account Security
Consider rate limiting, session invalidation, password confirmation for sensitive actions, email verification, and audit logging where appropriate.
