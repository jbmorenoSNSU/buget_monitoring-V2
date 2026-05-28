---
trigger: always_on
---

# Laravel + Vue 3 — Stack Standards v4
## STACK CONFIGURATION
Detect mode from project structure before generating code.

- **MODE A** — Laravel + Inertia.js + Vue 3: routes in `web.php`, `Inertia::render()`, no Vue Router, use `useForm()` for all forms.
- **MODE B** — Laravel API-only + Vue 3 SPA: routes in `api.php` at `/api/v1/`, Vue Router 4, shared Axios instance in `src/services/api.ts`.

Stack: Auth=Breeze/Jetstream, CSS=Tailwind v4+@theme, State=Pinia, Cache/Queue=Redis, QueueMonitor=Horizon, Search=Scout+Meilisearch, Admin=Filament, Test=Pest+Vitest+Playwright, DevDebug=Telescope, ProdMonitor=Sentry.

## DATABASE
- PostgreSQL: advanced queries, JSON, analytics, enterprise scale. MySQL: shared hosting, simpler DevOps.
- 20k+ rows: both fine. Enterprise/reporting: PostgreSQL preferred.
- Configure via environment variables only.

## LARAVEL — ROUTING & CONTROLLERS
- MODE A: `routes/web.php` → `Inertia::render('Page', [props])`.
- MODE B: `routes/api.php` versioned `/api/v1/` → API Resource classes.
- Use `Route::apiResource()` for CRUD; explicit HTTP verbs for custom actions.
- Controllers: thin, one method per action, max 20 lines, no business logic.
- Non-resource endpoints: single-action invokable (`__invoke`).
- Never return raw Eloquent models or arrays from a controller.

## LARAVEL — INERTIA.JS (MODE A)
- Always use dynamic page resolver — `eager: true` is forbidden:
  ```js
  resolve: name => import.meta.glob('./Pages/**/*.vue')[`./Pages/${name}.vue`]()
  ```
- Share global data (auth user, flash, config) via `HandleInertiaRequests` middleware only.
- Use `Inertia::lazy()` for expensive props not needed on first render.
- Use `Inertia::defer()` for data that can load after page paint.
- Max 5 props per `Inertia::render()` — extract to DTO if more needed.

## LARAVEL — VALIDATION & FORM REQUESTS
- Every endpoint with a request body requires a dedicated Form Request. No exceptions.
- Naming: `php artisan make:request {Feature}/{Action}Request`.
- `authorize()` method handles authorization — never in the controller.
- Use `Rule::exists()` and `Rule::unique()` for DB-backed checks.
- Never manually catch `ValidationException` — let it propagate.
- Complex conditional rules → custom Rule class: `php artisan make:rule`.

## LARAVEL — DTOs
- Location: `app/DTOs/{Feature}/{Name}DTO.php`. Readonly, typed properties, no setters.
- Constructed at controller boundary from validated Form Request data.
- Actions and Services always receive DTOs — never raw arrays or request objects.

## LARAVEL — ACTIONS
- Single-purpose class, one `execute()` method. Location: `app/Actions/{Feature}/{Name}Action.php`.
- Receives a DTO, returns a domain object or void. May call repositories and other actions.
- Never call controllers or HTTP classes. Use Services for multi-step orchestration.

## LARAVEL — SERVICE & REPOSITORY LAYERS
- Services in `app/Services/{Feature}Service.php` — multi-step orchestration only.
- Repositories in `app/Repositories/{Model}Repository.php` — all Eloquent queries.
- Every Repository implements `app/Interfaces/{Model}RepositoryInterface.php`.
- Bind in `app/Providers/RepositoryServiceProvider.php`:
  `$this->app->bind(AccountRepositoryInterface::class, EloquentAccountRepository::class);`
- Inject interfaces via constructor — never concrete classes.
- Never call Eloquent models directly inside controllers, actions, or services.
- Use Eloquent scopes for reusable query conditions — no inline conditional chains.
- Always eager-load with `with()`. Specify columns on relationships:
  - CORRECT: `with(['transactions:id,account_id,amount,created_at'])`
  - INCORRECT: `with(['transactions'])`

## LARAVEL — ELOQUENT & DATABASE
- Define `$fillable` explicitly on every model — never `$guarded = []`.
- Explicit foreign keys on all relationships. No exceptions:
  - CORRECT: `$this->belongsTo(AccountType::class, 'account_type_id')`
  - INCORRECT: `$this->belongsTo(AccountType::class)`
- All schema changes via migrations — never modify DB manually.
- Seeders + typed factories required for every new model.
- `SoftDeletes` on all user-generated, financial, or auditable models.
- Cast all date, boolean, JSON, decimal, and enum columns in `$casts`.
- Use backed string enums for status and type columns.
- `cursorPaginate()` for all list queries on tables over 500 rows — never `paginate()`.

### Multi-step write integrity
- Wrap every operation with 2+ DB writes in a transaction:
  `DB::transaction(fn() => [...writes...]);`
- The action or service owns the transaction boundary — never the caller.

## LARAVEL — AUTHORIZATION
- Every mutative action (`store`, `update`, `destroy`): `$this->authorize()` as line 1.
- `php artisan make:policy {Model}Policy --model={Model}`. Register in `AuthServiceProvider::$policies`.
- Never hardcode role/permission checks — use Gates or Policies. `before()` for superadmin bypass.

## LARAVEL — SEARCH & QUERY OPTIMIZATION
- No leading wildcard LIKE — causes full table scans:
  - CORRECT: `where('name', 'like', $term.'%')`
  - INCORRECT: `where('description', 'like', '%'.$term.'%')`
- Full-text search: **Laravel Scout + Meilisearch** (add `Searchable` trait, define `toSearchableArray()`, use `Model::search($term)->paginate()`).
- Fallback (small datasets): `whereFullText()` with `$table->fullText('column')` migration.
- Always `select(['col1','col2'])` — never SELECT *.
- Whitelist sortable columns in the repository — never pass raw column names from requests.
- Every filterable/sortable/searchable column migration must include an index.
- Run `explain()` on any query handling 10k+ rows before shipping.

## LARAVEL — JOBS, EVENTS & QUEUES
- Any operation >300ms → queued Job (email, PDF, bulk import/export, external API).
- `ShouldQueue` on all Listeners. `$tries=3`, `$backoff=[30,60,120]`, `$timeout` on all Jobs.
- Configure `failed_jobs` table. Horizon in staging+production.
- `Bus::chain` for ordered workflows; `Bus::batch` for parallel fan-out.

## LARAVEL — CACHING
- `Cache::remember()` always. Key: `{model}:{id}:{data}`. Always set TTL.
- Bust on create/update/delete via Eloquent observers. Redis in all non-local envs.
- Datatable key: `datatable:{model}:{md5(serialize($filters))}:{cursor}`.

## LARAVEL — DEBUGGING
**Telescope (local only)**
- Inspect queries, requests, jobs, exceptions, logs, mail.
- Any query >100ms in Telescope must be optimized before merging.
- Any N+1 detected must be fixed with eager loading before merging.
- Never commit `dd()`, `dump()`, `ray()`, `var_dump()` — enforced by linting.
- Use `Log::debug()` for dev-only output (suppressed in production by log level).

**Sentry (staging + production)**
- Every unhandled exception captured automatically via Laravel integration.
- Set release to Git commit SHA for source-map tracing.
- Enable performance monitoring with appropriate sample rate.
- Alerts within 5 minutes of a new exception type appearing in production.

**Logging**
- Use named log channels — never write directly to `laravel.log`.
- Structured context on all log calls:
  `Log::error('Payment failed', ['user_id' => $id, 'amount' => $amount]);`
- Never log passwords, tokens, card numbers, or PII.
- Use `Log::withContext()` in middleware to attach request ID and user ID to all logs.

## LARAVEL — TESTING (PEST — MANDATORY)
- Pest only — PHPUnit syntax (`$this->assert*`) is forbidden.
- Every Action: unit test with mocked repositories.
- Every Service method: unit test with mocked Actions/Repositories.
- Every Controller action: Feature test covering happy path (200/201), validation failure (422), unauthorized (401/403).
- `RefreshDatabase` or `DatabaseTransactions` in all DB-touching tests.
- `Http::fake()` for outbound HTTP; `Queue::fake()` for dispatched jobs.
- Factory states for key variations: `User::factory()->admin()->create()`.
- ≥80% coverage on Action, Service, Repository classes.
- All tests pass under 2 minutes on CI.

## LARAVEL — FILAMENT
For CRUD-heavy admin, dashboards, HR, inventory, school/gov systems.
- `php artisan make:filament-resource {Model} --generate`
- Scope `getEloquentQuery()` — never expose all records by default.
- `sortable()` and `searchable()` on indexed columns only.
- Filters: `SelectFilter`, `TernaryFilter`, `Filter::make()` — no raw query strings.
- `canCreate/Edit/Delete/View()` via Laravel Policy on all resources.
- Bulk ops → Job. Widget data must be cached — no raw aggregates on page load.

## VUE 3 — COMPONENTS
- Always `<script setup>` + Composition API — Options API is forbidden.
- One component per file, PascalCase filename.
- Structure: `Pages/` → `features/` → `components/` → `ui/`.
- `defineProps<{}>()` with TypeScript generics — no runtime declarations.
- `defineEmits<{}>()` — never use `$emit` undeclared.
- Extract non-trivial template expressions into `computed()` or composables.

**SFC size limits**
- Template: max 150 lines. Script setup: max 80 lines. Total SFC: max 250 lines.

## VUE 3 — TAILWIND CSS V4
- No arbitrary hex classes:
  - CORRECT: `class="bg-sidebar border-border"`
  - INCORRECT: `class="bg-[#090A0F] border-[#232936]"`
- All values must use `@theme` token classes from `app.css`.
- If a token doesn't exist, add it to `@theme` first — never use arbitrary values as shortcuts.
- No custom CSS unless it cannot be expressed with Tailwind utilities.
- Test dark mode variants on every new component before merging.

## VUE 3 — COMPOSABLES
- Location: `resources/js/composables/` (A) or `src/composables/` (B). Prefix: `use`.
- Single concern. Return `ref`s and `computed` — never raw primitives.
- Clean up side effects in `onUnmounted()`. Never mutate Pinia stores inside composables.

## VUE 3 — PINIA
- One store per feature. Location: `resources/js/stores/` (A) or `src/stores/` (B).
- Actions handle all async/API — never Axios in a component. Getters for derived state.
- Never mutate state directly. Define `$reset()` in every store; call on logout.

## VUE 3 — ROUTING (MODE B)
- `src/router/index.ts`, lazy-loaded components, named routes only.
- `beforeEach` for auth guards — never check auth inside a component.
- `meta: { requiresAuth: true, roles: ['admin'] }` for access control.

## VUE 3 — API LAYER (MODE B)
- Single Axios instance in `src/services/api.ts` with base URL, CSRF, interceptors.
- Request: attach `X-XSRF-TOKEN`. Response: 401→redirect login, 422→surface errors.
- `src/services/{feature}Service.ts` — plain async functions, never import stores.

## VUE 3 — DEBUGGING
- Vue Devtools for state — never `console.log`. No `console.*` commits (ESLint).
- Sentry: `Sentry.init({ app, dsn: import.meta.env.VITE_SENTRY_DSN })`.
- Capture: unhandled rejections, `app.config.errorHandler`, router errors.

## VUE 3 — TESTING
- `AccountCard.vue` → `AccountCard.spec.ts`. `createTestingPinia()` for stores.
- `vi.mock` for Axios — no real HTTP. Playwright `/e2e` for login, forms, datatables.

## VITE — ASSETS
- Fingerprinting always on. `manualChunks: { vendor: ['vue','pinia','axios'] }`.
- Fingerprinted: `Cache-Control: public, max-age=31536000, immutable`.
- Entry points: `Cache-Control: no-cache`.

## SHARED CONVENTIONS
- Dates: ISO 8601 UTC. Money: integers (centavos) in API, format in Vue via `Intl.NumberFormat`.
- Enums: Laravel backed string enums → TypeScript union types in Vue.
- File uploads: `/api/v1/uploads` — never base64 in JSON.
- Search: debounce 350ms. Loading: skeleton loaders. Errors: constants, not inline strings.
- Feature flags: Laravel Pennant.

## PROJECT FOLDER STRUCTURE
```
app/
 ├── Actions/       ├── DTOs/          ├── Services/
 ├── Repositories/  ├── Interfaces/    ├── Policies/
 ├── Jobs/          ├── Events/        ├── Listeners/
 ├── Http/
 │   ├── Controllers/  ├── Requests/  └── Resources/
 └── Models/

MODE A: resources/js/Pages/ | Components/features/ | Components/ui/ | composables/ | stores/
MODE B: src/pages/ | features/ | components/ui/ | composables/ | stores/ | services/ | router/
```
