---
trigger: always_on
---

# Laravel + Vue 3 — Stack Standards v4.2 (condensed)
> Condensed for a 12,000-character limit. Carries forward the Ponytail Repository carve-out, the softened Inertia prop-count rule, and the split 300ms (queue-by-nature vs. SLA-budget) rule. No other content changed from v4.

## STACK CONFIG
Detect mode from project structure first.
- **MODE A** — Inertia: routes in `web.php`, `Inertia::render()`, no Vue Router, `useForm()` for forms.
- **MODE B** — API+SPA: routes in `api.php` at `/api/v1/`, Vue Router 4, shared Axios in `src/services/api.ts`.

Stack: Auth=Breeze/Jetstream, CSS=Tailwind v4+@theme, State=Pinia, Cache/Queue=Redis, QueueMonitor=Horizon, Search=Scout+Meilisearch, Admin=Filament, Test=Pest+Vitest+Playwright, DevDebug=Telescope, ProdMonitor=Sentry.

## DATABASE
- PostgreSQL for advanced queries/JSON/analytics/enterprise scale; MySQL for shared hosting/simpler DevOps. Both fine at 20k+ rows. Configure via env vars only.

## ROUTING & CONTROLLERS
- MODE A: `web.php` → `Inertia::render('Page', [props])`. MODE B: `api.php` versioned `/api/v1/` → API Resource classes.
- `Route::apiResource()` for CRUD; explicit verbs for custom actions.
- Controllers thin: one method per action, max 20 lines, no business logic. Non-resource endpoints use invokable `__invoke`.
- Never return raw Eloquent models or arrays from a controller.

## INERTIA.JS (MODE A)
- Dynamic page resolver only — `eager: true` forbidden: `resolve: name => import.meta.glob('./Pages/**/*.vue')[\`./Pages/${name}.vue\`]()`
- Share global data (auth, flash, config) only via `HandleInertiaRequests` middleware.
- `Inertia::lazy()` for expensive props not needed on first render; `Inertia::defer()` for post-paint data.
- Group props that share a shape or lifecycle into a DTO; don't force a flat prop count. If props are crossing ~5-7 and clearly belong together, that's the signal to group them — not the count alone.

## VALIDATION & FORM REQUESTS
- Every endpoint with a request body needs a dedicated Form Request — no exceptions. `php artisan make:request {Feature}/{Action}Request`.
- `authorize()` handles authorization, never the controller. Use `Rule::exists()`/`Rule::unique()` for DB checks.
- Never manually catch `ValidationException` — let it propagate. Complex conditional rules → custom Rule class.

## DTOs
- `app/DTOs/{Feature}/{Name}DTO.php`. Readonly, typed properties, no setters.
- Constructed at controller boundary from validated Form Request data. Actions/Services always receive DTOs, never raw arrays or request objects.

## ACTIONS
- Single-purpose class, one `execute()` method, in `app/Actions/{Feature}/{Name}Action.php`.
- Receives a DTO, returns a domain object or void. May call repositories and other actions, never controllers/HTTP classes. Use Services for multi-step orchestration.

## SERVICE & REPOSITORY LAYERS
- Services: `app/Services/{Feature}Service.php`, multi-step orchestration only.
- Repositories: `app/Repositories/{Model}Repository.php`, all Eloquent queries; each implements `app/Interfaces/{Model}RepositoryInterface.php`, bound in `RepositoryServiceProvider`. Inject interfaces via constructor, never concrete classes.
- Never call Eloquent directly inside controllers, actions, or services. Use scopes for reusable query conditions, not inline conditional chains.
- Always eager-load with `with()` and specify columns on relationships, e.g. `with(['transactions:id,account_id,amount,created_at'])`.
- **Ponytail note:** Repository+Interface is mandatory once a model has non-trivial query logic, multiple consumers, or a realistic chance of swapped persistence. For a genuinely simple CRUD-only model, a Repository may wrap Eloquent directly without a bespoke Interface (Eloquent is already the abstraction) — default to writing the Interface when unsure.

## ELOQUENT & DATABASE
- Explicit `$fillable` on every model, never `$guarded = []`. Explicit foreign keys on all relationships (e.g. `belongsTo(AccountType::class, 'account_type_id')`).
- Schema changes via migrations only. Seeders + typed factories required per model. `SoftDeletes` on user-generated/financial/auditable models.
- Cast date/boolean/JSON/decimal/enum columns in `$casts`; use backed string enums for status/type columns.
- `cursorPaginate()` for list queries over 500 rows — never `paginate()`.
- **Transactions:** wrap any operation with 2+ writes in `DB::transaction()`; the action/service owns the boundary, never the caller.

## AUTHORIZATION
- Every mutative action (`store`/`update`/`destroy`) calls `$this->authorize()` as line 1.
- `make:policy {Model}Policy --model={Model}`, registered in `AuthServiceProvider::$policies`. Never hardcode role checks — use Gates/Policies; `before()` for superadmin bypass.

## SEARCH & QUERY OPTIMIZATION
- No leading-wildcard LIKE (causes full table scans) — anchor the term instead (`'term%'` not `'%term%'`).
- Full-text: Scout + Meilisearch (`Searchable` trait, `toSearchableArray()`, `Model::search($term)->paginate()`); fallback for small datasets is `whereFullText()`.
- Always `select(['col1','col2'])`, never `SELECT *`. Whitelist sortable columns in the repository — never raw column names from requests.
- Every filterable/sortable/searchable column's migration must include an index. Run `explain()` on queries handling 10k+ rows before shipping.

## JOBS, EVENTS & QUEUES
- Queue by nature, not by typical latency: email, PDF, bulk import/export, and any call to a third-party service you don't control → queued Job, regardless of how fast that service usually responds (the risk is the tail latency, not the median).
- Separately, the 300ms figure in API response budgets is an SLA threshold for your own endpoints, not a queueing trigger — don't use it to decide whether an external call needs to be async.
- `ShouldQueue` on all Listeners; `$tries=3`, `$backoff=[30,60,120]`, `$timeout` on all Jobs. Configure `failed_jobs`; Horizon in staging+prod.
- `Bus::chain` for ordered workflows, `Bus::batch` for parallel fan-out.

## CACHING
- `Cache::remember()` always, key pattern `{model}:{id}:{data}`, always set TTL. Bust via Eloquent observers on create/update/delete. Redis in all non-local envs.
- Datatable key: `datatable:{model}:{md5(serialize($filters))}:{cursor}`.

## DEBUGGING
**Telescope (local only):** inspect queries/requests/jobs/exceptions/logs/mail. Any query >100ms or N+1 detected must be fixed before merging. Never commit `dd()`, `dump()`, `ray()`, `var_dump()` (lint-enforced). Use `Log::debug()` for dev-only output.
**Sentry (staging+prod):** unhandled exceptions auto-captured via Laravel integration; release = Git commit SHA; performance monitoring on; alerts within 5 min of a new exception type.
**Logging:** named log channels only, never write to `laravel.log` directly. Structured context on every call, e.g. `Log::error('Payment failed', ['user_id'=>$id,'amount'=>$amount])`. Never log passwords/tokens/card numbers/PII. `Log::withContext()` in middleware for request/user ID on all logs.

## TESTING (PEST — MANDATORY)
- Pest only; PHPUnit-style `$this->assert*` is forbidden.
- Every Action: unit test with mocked repositories. Every Service method: unit test with mocked Actions/Repositories.
- Every Controller action: Feature test covering happy path (200/201), validation failure (422), unauthorized (401/403).
- `RefreshDatabase`/`DatabaseTransactions` in all DB-touching tests. `Http::fake()` for outbound HTTP, `Queue::fake()` for jobs. Factory states for key variations.
- ≥80% coverage on Action/Service/Repository classes (Ponytail's trivial-one-liner exemption applies — see Global Standards). All tests pass under 2 minutes on CI.

## FILAMENT
For CRUD-heavy admin/dashboards/HR/inventory/school/gov systems.
- `make:filament-resource {Model} --generate`. Scope `getEloquentQuery()` — never expose all records by default.
- `sortable()`/`searchable()` only on indexed columns. Filters via `SelectFilter`/`TernaryFilter`/`Filter::make()`, no raw query strings.
- `canCreate/Edit/Delete/View()` via Policy on all resources. Bulk ops → Job; widget data must be cached, no raw aggregates on page load.

## VUE 3 — COMPONENTS
- `<script setup>` + Composition API only — Options API forbidden. One component per file, PascalCase filename.
- Structure: `Pages/` → `features/` → `components/` → `ui/`. `defineProps<{}>()`/`defineEmits<{}>()` with TS generics, no runtime declarations, never undeclared `$emit`.
- Extract non-trivial template expressions into `computed()` or composables.
- **SFC size limits:** template ≤150 lines, script setup ≤80 lines, total ≤250 lines.

## VUE 3 — TAILWIND CSS V4
- No arbitrary hex classes (`bg-[#090A0F]`) — use `@theme` token classes only (`bg-sidebar`). Add the token first if it doesn't exist.
- No custom CSS unless it can't be expressed in Tailwind utilities. Test dark mode on every new component before merging.

## VUE 3 — COMPOSABLES
- `resources/js/composables/` (A) or `src/composables/` (B), prefix `use`. Single concern, return refs/computed, never raw primitives.
- Clean up side effects in `onUnmounted()`. Never mutate Pinia stores inside a composable.

## VUE 3 — PINIA
- One store per feature in `resources/js/stores/` (A) or `src/stores/` (B). Actions handle all async/API — never Axios in a component. Getters for derived state.
- Never mutate state directly. Define `$reset()` in every store; call on logout.

## VUE 3 — ROUTING (MODE B)
- `src/router/index.ts`, lazy-loaded components, named routes only. `beforeEach` for auth guards, never check auth inside a component. `meta: { requiresAuth, roles }` for access control.

## VUE 3 — API LAYER (MODE B)
- Single Axios instance in `src/services/api.ts` (base URL, CSRF, interceptors). Attach `X-XSRF-TOKEN`; 401→redirect login, 422→surface errors.
- `src/services/{feature}Service.ts`: plain async functions, never import stores.

## VUE 3 — DEBUGGING
- Vue Devtools for state, never `console.log`; no `console.*` commits (ESLint). Sentry via `Sentry.init({ app, dsn })`. Capture unhandled rejections, `app.config.errorHandler`, router errors.

## VUE 3 — TESTING
- `AccountCard.vue` → `AccountCard.spec.ts`. `createTestingPinia()` for stores, `vi.mock` for Axios (no real HTTP). Playwright `/e2e` for login, forms, datatables.

## VITE — ASSETS
- Fingerprinting always on. `manualChunks: { vendor: ['vue','pinia','axios'] }`. Fingerprinted assets: `Cache-Control: public, max-age=31536000, immutable`. Entry points: `no-cache`.

## SHARED CONVENTIONS
- Dates: ISO 8601 UTC. Money: integers (centavos) in API, format via `Intl.NumberFormat` in Vue.
- Enums: Laravel backed string enums → TS union types. File uploads via `/api/v1/uploads`, never base64 in JSON.
- Search debounce 350ms. Loading: skeleton loaders. Errors: constants, not inline strings. Feature flags: Laravel Pennant.

## FOLDER STRUCTURE
```
app/: Actions/ DTOs/ Services/ Repositories/ Interfaces/ Policies/ Jobs/ Events/ Listeners/
app/Http/: Controllers/ Requests/ Resources/
app/Models/

MODE A: resources/js/Pages/ | Components/features/ | Components/ui/ | composables/ | stores/
MODE B: src/pages/ | features/ | components/ui/ | composables/ | stores/ | services/ | router/
```