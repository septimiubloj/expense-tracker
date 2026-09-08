# Expense Tracker project conventions

Read `PLAN.md` before starting work and update its checklist after verification.
Work one milestone at a time. The foundation comes before domain implementation;
the Notion-style redesign is deferred until the financial behavior works.

Use the installed `spatie-laravel-php`, `spatie-javascript`, `spatie-security`, and
`spatie-version-control` skills for relevant work. Read their referenced guidance.
Use Laravel conventions first. Keep generated starter-kit authentication behavior
and route names consistent; use Spatie naming conventions for new domain code.
The user explicitly chose `rebuild/laravel-13` and `legacy-laravel-8`; retain these
names despite Spatie's general branch naming preference.

Money is signed integer minor units. Balances, actuals, and variances are derived
from transactions. Transfers are paired atomically and have no category. Scope
every domain query and mutation to the authenticated user's book. The reference
spreadsheet is read-only; personal financial data must never be committed.

`composer ci:check` is the full local verification command. Use focused tests
during development and run the complete checks at milestone boundaries.
