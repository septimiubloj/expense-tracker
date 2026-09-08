# Expense Tracker Guidelines

## 1. Domain Invariants & Rules
- **Milestones**: Work one milestone at a time per `PLAN.md`. Update its checklist only after verification.
- **Money**: Signed integer minor units. Balances, actuals, and variances are derived from transactions.
- **Transfers**: Paired atomically with opposite movements; transfers have no category.
- **Scoping**: Scope every domain query and mutation to the authenticated user's book.
- **Spreadsheet**: Reference spreadsheet is read-only; personal financial data must never be committed.
- **Branches**: Work on `rebuild/laravel-13`; retain `legacy-laravel-8`.

## 2. Code Standards & Architecture
- **PHP 8.4**: Strict types, explicit parameter and return types, constructor property promotion, TitleCase Enum keys, and PHPDoc array shapes.
- **Conventions**: Laravel conventions for starter-kit auth and route names; Spatie conventions for new domain code.
- **Frontend & Routing**: Use Inertia React in `resources/js/pages/` and typed Wayfinder functions (`@/actions/` or `@/routes/`).
- **Formatting**: Run `vendor/bin/pint --dirty --format agent` on modified PHP files before finalizing changes.

## 3. Verification & Testing
- Use focused tests during development: `php artisan test --compact --filter=testName` or `vendor/bin/phpunit --filter=testName`.
- Run full verification only at milestone boundaries: `composer ci:check`.
- Activate installed domain skills (`spatie-laravel-php`, `testing-best-practices`, etc.) when working in their respective areas.

## 4. Token & Context Efficiency (Strict)
- **Terse Communication**: Direct and concise. No conversational filler, pleasantries, preambles, or unsolicited code explanations. State actions and verification results.
- **Surgical Edits**: Use targeted replacements (`replace_file_content`) instead of rewriting whole files.
- **Targeted Reads**: Inspect specific line ranges (`StartLine`/`EndLine`) instead of ingesting entire large files into context.
- **Filtered Output**: Keep terminal commands and test runs focused to avoid flooding context with verbose output.
