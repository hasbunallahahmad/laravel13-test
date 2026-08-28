# Laravel 13 Claude Code Guardrails

This package contains:
- `CLAUDE.md` — project-wide development instructions
- `.claude/skills/` — focused skills for architecture, security, API, database, authentication, testing, and production readiness.

## Installation

Extract the contents into the root of your Laravel 13 project.

Expected structure:

```text
your-project/
├── CLAUDE.md
└── .claude/
    └── skills/
        ├── laravel-architecture/
        ├── laravel-security/
        ├── laravel-api/
        ├── laravel-database/
        ├── laravel-auth/
        ├── laravel-testing/
        └── laravel-production/
```

## Important

This project intentionally does not use Filament.

The instructions assume Laravel Starter Kit, PHP 8.3+, UUID/slug identifiers, strict validation, SRP, Policies/Gates, Spatie where justified, REST API versioning, automated tests, and production security review.

Use the instructions as guardrails. Build the application phase-by-phase rather than asking Claude to generate the entire system in one operation.
