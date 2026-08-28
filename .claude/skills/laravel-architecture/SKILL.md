# Laravel Architecture Skill

## Purpose
Use this skill when designing or modifying Laravel application architecture.

## Principles
Follow Laravel conventions and Single Responsibility Principle (SRP).

Preferred flow:
HTTP Request → Form Request → Controller → Action/Service → Model → Database

API flow:
HTTP Request → Form Request → Controller → Action/Service → API Resource → JSON Response

## Controllers
Keep controllers thin. They should primarily receive requests, authorize, invoke an Action/Service, and return a response.

Avoid business logic, complex queries, file processing, notifications, and external integrations inside controllers.

## Actions
Use Actions for meaningful business operations such as:
- CreateNews
- UpdateNews
- DeleteNews
- PublishNews
- CreateUser
- AssignUserRole

Each Action should have one clear responsibility.

## Services
Use Services for reusable domain operations that do not naturally belong to one Action. Do not create Services merely to move a few lines of code.

## Models
Models should contain relationships, casts, scopes, and model-specific behavior. Avoid turning models into large business-logic containers.

## Policies
Protected resources must have appropriate authorization through Policies/Gates/permissions.

## Database
Use UUIDs where external exposure of sequential IDs is undesirable, foreign keys, unique constraints, and indexes based on query patterns.

## Avoid Overengineering
Do not automatically create repositories, DTOs, interfaces, or abstractions. Introduce them only when they solve a concrete problem.

## Architecture Review
Before implementation identify:
1. Domain
2. Data model
3. Authorization
4. Validation
5. Business operation
6. HTTP interface
7. API interface
8. Testing requirements
