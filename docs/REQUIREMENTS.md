# Requirements Template

## Purpose
- Describe the overall goal of the personal system and what problems it solves.

## User Roles
- List who will use the system (even if just one person).

## Modules / Features
- Outline planned modules (e.g., notes, tasks, finances) and high-level functionality.

## Data Model Draft
- List expected tables or entities with brief field notes.

## Workflows
- Describe how users will create, edit, search, and export data.

## Non-Functional Requirements
- Security expectations (authentication, authorization, data protection).
- Backup and restore approach.
- Audit logging needs.
- Performance considerations for shared hosting.

## Deployment Constraints
- Target: Bluehost shared hosting with MySQL managed via phpMyAdmin.
- No background workers; rely on request/response lifecycle.
- Favor minimal dependencies compatible with shared hosting.
