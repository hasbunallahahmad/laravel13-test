# Laravel Database Skill

## Purpose
Use this skill for migrations, models, relationships, indexes, seeders, and database queries.

## Primary Keys
Use UUIDs for entities that should not expose sequential identifiers. Keep UUID foreign-key relationships consistent.

## Slugs
Use unique slugs for SEO-oriented entities such as News, Page, Category, and Tag where appropriate. Enforce important uniqueness at database level.

## Foreign Keys
Use proper foreign keys and deliberate delete behavior. Do not automatically cascade everything.

## Indexes
Consider indexes for foreign keys, slugs, status, published_at, and frequently filtered fields. Base indexing on query patterns.

## Soft Deletes
Use SoftDeletes only when recovery/audit requirements justify it.

## Performance
Avoid N+1 queries, use eager loading when appropriate, paginate large result sets, and avoid selecting unnecessary columns when performance matters.

## Seeders
Seeders should be safe to rerun where practical, deterministic where useful, environment-aware, and free of real secrets.

Development seeders may use fake data. Production seeders must not create insecure default credentials.
