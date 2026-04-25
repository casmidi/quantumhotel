# Quantum Hotel - AGENTS

Last updated: 2026-04-25

## Start Here

- Active project: `D:\laravel\quantum`
- Read `PROJECT-CHECKPOINT.md` first, but only the relevant section.
- Current public domain: `https://quantum.or.id`
- Laravel app is expected on `127.0.0.1:8001`

## Context-Saving Rules

- Always skip `vendor/`, `node_modules/`, `storage/logs/`, and `bootstrap/cache/` unless the user explicitly asks or a concrete error requires it.
- Do not dump whole large files. Read small slices with targeted search first.
- Do not reread all of `PROJECT-CHECKPOINT.md` in the same thread unless there is a new update.
- When searching, prefer narrow patterns and app folders such as `app/`, `resources/`, `routes/`, and `config/`.
- For SQL Server schema checks, prefer focused `INFORMATION_SCHEMA` queries instead of broad Laravel inspection commands.

## CRUD Standard

- For CRUD controllers, always check whether the request is browser/web or API/JSON.
- Public CRUD actions should receive `Request $request` explicitly when practical.
- Browser/web requests should keep the normal Laravel flow: Blade view, redirect, and flash message.
- API/JSON requests should return JSON with `success`, `message`, and `data` instead of HTML or redirects.
- Prefer shared controller helpers such as `respond()`, `respondAfterMutation()`, and `respondError()` so the response shape stays consistent.
- Protected routes should follow the same split: browser redirects to login, API gets JSON `401`.
- Dedicated API endpoints should live in `routes/api.php` under `/api/v1/...`.
- For Postman-friendly protected API access, use bearer token auth instead of relying on web session cookies.
- Legacy CRUD tables should use an integer identity column named `id`, while the business code field such as `Kode`, `KodeBrg`, `Nofak`, `RegNo`, or `RegNo2` stays unique.

## Date Format Standard

- All visible browser date inputs must display `dd-MM-yyyy`.
- Backend/controller payloads may continue using ISO `yyyy-MM-dd` when Laravel validation or legacy SQL logic requires it.
- When adding new Blade date fields, prefer the existing hidden ISO value plus visible `dd-MM-yyyy` text pattern, or rely on the global layout date-input converter for simple `input[type="date"]` fields.
- Printed reports and human-facing date labels should use `dd-MM-yyyy` unless a specific external integration requires another format.

## Current Focus

- Main active module: `/checkin`
- Latest known checkin update: primary `Package Code` input now uses `PackageCodeList[]` so the first room detail matches controller payload parsing.
- Do not create fake transaction data unless the user explicitly approves a test record.

## Safety

- The git working tree may already be dirty.
- Do not touch unrelated local changes.
- Never use `git reset --hard` or `git checkout -- .` unless the user explicitly asks.

## Verification Style

- Prefer lightweight checks first, such as:
  - `php -l` on changed PHP files
  - `artisan route:list --path=checkin`
  - targeted page checks only when needed
