# Expense Tracker rebuild

## Working agreement

Keep this repository and its Git history. Rebuild the implementation from a fresh
Laravel 13 React starter kit; do not carry forward legacy controllers, migrations,
or frontend components. No deployed data needs preserving. Preserve the existing
uncommitted Composer files separately before replacing the scaffold.

Work through the milestones in order. Update this file with completed checks,
decisions, and remaining work at each checkpoint. A checked box means the result
has been implemented and verified, not merely scaffolded.

## Decisions

- Archive the current committed source as `legacy-laravel-8`.
- Work on `rebuild/laravel-13` in the existing repository.
- Target PHP 8.3–8.5, Laravel 13, React 19, TypeScript, Inertia 3, Vite, Tailwind 4.
- Use nvm and the repository's `.nvmrc` for Node 24 (minimum 24.11). CI reads the
  same file. Use SQLite locally and PHP 8.4 for the optional Sail container.
- Install `laravel/boost` and `spatie/guidelines-skills` through Composer as dev
  dependencies immediately after scaffolding. Install their Codex integration and
  read the relevant skills before writing application code.
- Store money as signed integer minor units. Never persist calculated balances,
  actuals, variances, or net totals independently.
- Books own accounts, categories, periods, and transactions; users own books.
- Transfers are an atomic pair of opposite account movements, with no category.
- The source spreadsheet stays read-only. Exclude Help and copyright tabs.
- User reprioritized design foundations on 2026-09-08. Work on the workspace design
  increment before resuming remaining functional summaries; inline editing stays deferred.

## 1. Foundation

- [x] Preserve uncommitted Composer changes and tag the legacy commit.
- [x] Create the rebuild branch and fresh official React starter-kit scaffold.
- [x] Install Laravel Boost and Spatie skills; configure Codex and read the skills.
- [x] Set supported runtime versions and local environment defaults.
- [x] Configure Sail/Docker, Composer scripts, formatting, static analysis, and CI.
- [x] Verify authentication tests, PHP analysis, frontend types, and production build.

## 2. Domain schema and ownership

- [x] Build `Book`, `Account`, `Category`, `BudgetPeriod`, `BudgetAllocation`, and
  `Transaction` with fresh migrations, relationships, factories, and constraints.
- [x] Include currency/timezone, opening balances/dates, category hierarchy,
  period dates/status, allocation amounts, and transaction date/payee/reference/
  memo/status/transfer identifier.
- [x] Enforce book ownership through scoped routes, policies, and validated inputs.
- [x] Verify schema constraints and cross-user policy isolation.

## 3. Financial behavior

- [x] Define decimal parsing, minor-unit precision, inclusive date boundaries,
  variance signs, and transfer editing/deletion rules.
- [x] Calculate opening/previous/current account balances from the ledger.
- [x] Calculate period income, expenses, net income, actuals, and remaining budget.
- [x] Implement atomic transfer creation, editing, and deletion.
- [x] Test money precision, boundaries, variances, transfers, and transaction changes.

Calculation rules: calendar dates are inclusive and represent the book's local
dates, with no timestamp conversion. Previous balances precede the start date;
current balances include the end date. Opening balances enter on `opened_on`.
Pending and cleared entries count; void entries do not. Expense refunds reduce
expense actuals. Transfers affect account balances only. Uncategorized entries
are reported separately from categorized income, expenses, and net income.
Category actuals use direct assignments, without parent rollups. Remaining is
planned minus actual; favorable variance is remaining for expenses and actual
minus planned for income. Allocations are nonnegative.

Decimal parsing accepts explicit precision (0–4, default 2) and rejects excess
precision, separators, and exponent notation. Amounts and totals are bounded by
the JavaScript safe integer limit. Currency-specific precision selection and
display formatting belong to the screen milestone. Transfer updates preserve
both entry IDs and update both legs atomically; individual-leg changes are rejected.

## 4. Functional spreadsheet-parity screens

- [x] Book selection, creation, editing, and deletion.
- [x] Budget-period selection, creation, editing, and deletion.
- [x] Account creation, editing, deletion, and opening-balance entry.
- [x] Category management, parent selection, and sort ordering.
- [x] Period allocation creation, editing, and deletion.
- [x] Transaction creation, editing, deletion, and transfer management.
- [ ] Budget-versus-actual and account previous/current balance summaries.
- [ ] Verify complete user flows, validation feedback, and empty states.

## 5. Spreadsheet importer

Source: [Copy of Weekly Money Manager](https://docs.google.com/spreadsheets/d/1eQkwAHNqfrSEclOeNrQtXrlPTrT0GDJOTqZ0M0-dIgU/edit), principally `S-1`.

- [ ] Inspect the actual workbook layout and record its mapping.
- [ ] Implement a one-time importer for accounts, categories, periods, allocations,
  and transactions, with dry-run validation and duplicate-run protection.
- [ ] Test mapping, invalid input rollback, transfers, and repeat-import handling.
- [ ] Document invocation and reconciliation; keep personal workbook data out of Git.

## 6. Hardening and handoff

- [ ] Run the complete relevant PHP tests, formatting, static analysis, frontend
  type checks, and production build.
- [ ] Document setup, calculations, importer, and verified limitations in README.
- [ ] Review ownership boundaries and financial invariants across all write paths.

## 7. Later: Notion-style UX

- [ ] Design the document-width workspace, quiet navigation, inline editing,
  database-style tables, keyboard shortcuts, command menus, and reusable blocks.

Current design increment (user priority, 2026-09-08): shared light palette and
workspace shell, real book navigation from the dashboard, transaction table polish,
reduced-motion support, and opt-in save sounds. Design rules live in `DESIGN.md`.
Verified four dashboard tests / 16 assertions including book ownership isolation,
Pint, focused PHPStan, application frontend lint, TypeScript, and production build.
Repository-wide frontend checking reports four existing floating-promise warnings
in `tests/frontend/money.test.mjs`. No full milestone verification was run.
Browser review and the later inline-editing work remain open; this does not mark
milestone 4 or 7 complete.

## Progress log

- 2026-09-06: Plan recorded. Repository inspection found uncommitted Composer
  changes toward Laravel 9; preserve them before scaffolding. No project source
  has been migrated yet.
- 2026-09-06: Created local `legacy-laravel-8` tag at `acef8ba` and
  `rebuild/laravel-13` branch. Preserved previous Composer files and `.env` in
  ignored `.legacy-backup/`. Replaced legacy source with official React starter
  kit commit `87cce8705d712629ebddd70ccfbb06592ecbaac2`.
- 2026-09-06: Installed Boost 2.7 and Spatie skills 1.1 through Composer, generated
  Codex integration, and read all four Spatie skills. Set frontend formatting to
  four spaces, single quotes, and 120 columns; enabled one PHP trait per line.
  Fixed the starter kit's email-verification enforcement and added coverage.
- 2026-09-06: Installed Node 24.20.0 using the existing nvm setup. Added `.nvmrc`,
  locked PHP dependency resolution to the PHP 8.3 platform, and configured CI for
  PHP 8.3/8.4/8.5. Verified local SQLite migrations and `composer setup`.
- 2026-09-06: Added the six ledger models, four string-backed enums, fresh
  migrations, factories, typed relationships, and ownership policies. SQLite
  migration verification and schema tests pass. The database driver does not expose
  Laravel's `Blueprint::check()` API, so period ordering and nonzero amounts remain
  application validation invariants for the request/service layer.
- 2026-09-06: Added schema and policy tests. The complete suite now passes with 43
  tests and 166 assertions; Larastan and Pint pass for the new domain code.
- 2026-09-06: Added book and nested ledger resource routes with scoped implicit
  bindings, ownership-aware form requests, controllers, and initial typed Inertia
  page shells. Cross-user binding, duplicate names, and foreign-book references
  are covered by feature tests, including allocation-to-period scoping. Wayfinder
  routes/actions are regenerated after route changes.
- 2026-09-06: Made Composer lint and static-analysis checks deterministic in the
  local restricted environment by running Pint without worker sockets and PHPStan
  with `--debug`; the full `composer ci:check` pipeline passes.

## Current checkpoint

Foundation is complete. `composer ci:check` passes: 77 PHP tests / 260 assertions,
Larastan level 7, PHP and frontend formatting/lint, TypeScript, and production build.
The final npm audit reports zero vulnerabilities after updating the transitive
Babel packages. `composer setup` also completed successfully with nvm's Node 24.

Milestones 1–3 are verified. Financial calculations, paired transfer endpoints,
money limits, and transaction date checks are implemented. Static analysis,
formatting, TypeScript, tests, and the production build pass.
The next milestone is functional screens, moved ahead of the importer with user
approval. Books, accounts, budget periods, categories, and period allocations now
have working forms, navigation, validation feedback, and empty states. Transaction
and paired transfer forms are implemented. Calculation summaries still need frontend
integration. Work remains local; nothing has been pushed.

Local verification uses PHP 8.4.8 and Node 24.20.0. The CI matrix is configured but
has not run on GitHub. Docker Compose configuration validates; the Sail image has
not been built or started. No live-browser test has been run.

- 2026-09-06: Implemented book/account forms and typed Wayfinder navigation from
  the dashboard and sidebar. Added confirmation dialogs, success/error feedback,
  and deletion guards for populated books/accounts. Opening balances use exact
  decimal parsing and currency-specific precision (including JPY and KWD).
  Verified 16 focused PHP tests / 82 assertions, frontend money tests
  (`node --test tests/frontend/money.test.mjs` under Node 24), Pint, focused
  Larastan, frontend lint/formatting, TypeScript, and production build.
  Milestone 4 remains in progress; no full milestone verification or browser test
  was run for this increment.
- 2026-09-06: Implemented budget-period forms, status editing, deletion confirmation,
  sidebar navigation, and selection into period allocations with a return link.
  Added calendar-date and book-scoped duplicate-date validation. Deletion removes
  period allocations and preserves transactions. Verified 19 focused PHP tests /
  119 assertions, Pint, focused Larastan, frontend lint/formatting, TypeScript, and
  production build. Category management is next; allocation editing remains pending.
  Milestone 4 remains in progress. No browser test or full milestone check was run.
- 2026-09-06: Implemented category creation/editing/deletion, income/expense type,
  parent selection, sort ordering, hierarchy display, and sidebar navigation.
  Added indirect-cycle validation and a deletion guard for allocated categories;
  deletion otherwise preserves transactions as uncategorized and moves children
  to the top level. Verified 18 focused PHP tests / 102 assertions, Pint, focused
  Larastan, frontend lint/formatting, TypeScript, and production build.
  Period allocation editing is next. Milestone 4 remains in progress; no browser
  test or full milestone check was run for this increment.
- 2026-09-06: Implemented allocation creation/editing/deletion with scoped category
  choices and exact currency-aware amount entry. Added duplicate-category validation
  per period, success feedback, and guidance for missing or fully allocated categories.
  Verified 33 focused PHP tests / 220 assertions, frontend money tests, Pint, focused
  Larastan, frontend lint/formatting, TypeScript, and production build.
  Transaction and transfer forms are next. Milestone 4 remains in progress; no browser
  test or full milestone check was run for this increment.
- 2026-09-07: Implemented transaction and paired transfer creation/editing/deletion,
  exact currency-aware amount entry, status and detail fields, confirmation dialogs,
  success/validation feedback, empty states, and Transactions sidebar navigation.
  Verified 23 focused PHP tests / 147 assertions, frontend money tests, Pint, focused
  Larastan, frontend lint/formatting, TypeScript, and production build.
  Budget-versus-actual and account balance summaries are next. Milestone 4 remains
  in progress; no browser test or full milestone check was run for this increment.
