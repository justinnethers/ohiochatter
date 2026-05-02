# Quality Audit — 2026-05-02

Snapshot of the codebase quality findings from the audit on 2026-05-02. Items
are ordered by **risk × ease** and are intended to be worked through using
red-green-refactor (Redd → Cody → Marty).

**Overall score at audit time:** 72 / 100

| Dimension | Score |
|---|---|
| Organization / Modularity | 78 |
| Code Quality | 70 |
| Security | 60 |
| Testing | 70 |
| Performance / Caching | 78 |
| Dependencies | 80 |

---

## Work Queue

Each item has a status, a target file/area, what to test, and the acceptance
criteria. Update the status as we go.

Status legend: `[ ] todo` · `[~] in progress` · `[x] done` · `[-] skipped`

---

### [x] 1. Remove `dd()` in `PollComponent::test()`
- **Owner:** Justin (manual)
- **File:** `app/Livewire/PollComponent.php:98-102`
- **Risk:** Public Livewire action takes the page down with a debug dump.
- **Fix:** Delete the `test()` method. Also delete commented
  `dd($vbUser)` at `app/Http/Controllers/Auth/RegisteredUserController.php:45`.
- **Test:** N/A (deletion). Optionally add a Pest test that asserts the
  component has no `test` method.

---

### [x] 2. Validate `/upload-image` endpoints
- **Files:** `routes/web.php:35-45` and `routes/web.php:86-98` (duplicate route)
- **Risk:** Authenticated users can upload arbitrary files — no mime/size/ext
  validation, two duplicate route definitions.
- **TDD plan:**
  - **Redd:** Feature test covering
    - rejects non-image uploads (e.g. `.php`, `.exe`)
    - rejects oversized uploads (> 5 MB)
    - rejects when unauthenticated (already covered by middleware, but pin it)
    - accepts a valid image and returns the expected JSON shape
    - the route is reachable at exactly one path (no duplicate handler)
  - **Cody:** Move the closure into `App\Http\Controllers\UploadImageController`
    backed by a `App\Http\Requests\UploadImageRequest` with rules:
    `image|max:5120|mimes:jpg,jpeg,png,webp,gif`. Delete the duplicate route.
  - **Marty:** Look for other inline closures that should follow the same
    pattern; check whether storage path/disk should be configurable.
- **Acceptance:** All Redd tests green, Pint clean, single named route.

---

### [ ] 3. Replace `protected $guarded = []` with explicit `$fillable`
- **Files:** 20+ models — `Thread`, `Reply`, `Poll`, `PollOption`, `PollVote`,
  `Content`, `ContentCategory`, `ContentType`, `Region`, `County`, `City`,
  `Search`, `Rep`, `Neg`, `Pickem`, `PickemComment`, `PickemPick`,
  `PickemGroup`, `WordioValidGuess`, `WordleUserStats`, etc.
- **Risk:** Mass-assignment foot-gun. Combined with `Thread::store` returning
  the model as JSON and several Actions hand-building arrays, an attacker can
  set `user_id`, `is_locked`, `is_pinned`, `created_at`, `is_admin`, etc.
- **TDD plan:**
  - **Redd:** For each model that takes user input, add a test that asserts
    sensitive columns are NOT mass-assignable (e.g.
    `Thread::create(['is_locked' => true, ...])` should not persist
    `is_locked`). Group as a single dataset-driven test.
  - **Cody:** Convert each model from `$guarded = []` to an explicit
    `$fillable` listing only user-supplied fields.
  - **Marty:** Remove now-redundant defensive code in Actions (e.g. explicit
    `user_id => auth()->id()` if it's now blocked anyway), and check
    factories still work.
- **Acceptance:** All existing tests still pass; new mass-assignment tests
  green; no model uses `$guarded = []`.

---

### [ ] 4. Centralize admin authorization
- **Files:** Inline `is_admin` checks in 9+ Livewire components:
  `PickemEditor:82`, `PickemAdminManager:26`, `PickemGroupManager:29`,
  `ThreadAdminToolbar:97`, `ThreadLockToggle:43`, `PostComponent:34`,
  `PickemComments:53`, plus admin-only Filament panels.
- **Risk:** One missed check = privilege escalation. No single place to audit.
- **TDD plan:**
  - **Redd:** For each admin-only Livewire action, add a test that confirms
    a non-admin authenticated user gets a 403 (or the action no-ops).
  - **Cody:**
    - Add `Gate::before` admin bypass in `AppServiceProvider` (or
      `AuthServiceProvider`).
    - Add `App\Http\Middleware\EnsureAdmin` for HTTP routes.
    - Replace inline `auth()->user()?->is_admin` with `$this->authorize('admin')`
      or a shared trait.
  - **Marty:** Sweep for any remaining inline `is_admin` reads; collapse the
    `PostComponent::canEdit` logic into a Policy method.
- **Acceptance:** No inline `is_admin` reads in Livewire components; all
  admin actions guarded by gate/policy/middleware.

---

### [ ] 5. Fix cache-staleness in `CreateThread::updateCache()`
- **File:** `app/Actions/Threads/CreateThread.php:55-63`
- **Risk:** `Cache::rememberForever('thread-{id}-latest-post', ...)` is never
  invalidated by replies/edits — stale "latest post" data forever.
- **TDD plan:**
  - **Redd:** Test that adding a reply (or editing the thread) invalidates
    `thread-{id}-latest-post` and `forum-{id}-latest-post`.
  - **Cody:** Either (a) add the forgets to `InvalidateThreadCaches` and
    invoke from `Reply::saved/deleted`, or (b) just remove the cache writes
    if nothing reads them.
  - **Marty:** Audit other `rememberForever` callers (`User::hasRepliedTo`)
    for the same issue.
- **Acceptance:** Cache reads are always fresh post-reply.

---

### [ ] 6. Audit `{!! $body !!}` blade outputs (XSS surface)
- **Files:**
  - `resources/views/messages/show.blade.php:47` — DM body
  - `resources/views/livewire/pickem-comments.blade.php:121` — comment body
  - `resources/views/pickem/show.blade.php:101` — pickem body
  - `resources/views/components/blocks/view/text.blade.php:5` — guide block
  - `resources/views/components/guide-list.blade.php:71` — guide description
  - (`post-component.blade.php:83` uses `formatted_body` — confirm sanitized)
- **Risk:** Stored XSS via untrusted user input.
- **TDD plan:**
  - **Redd:** Feature tests that submit `<script>alert(1)</script>` (and
    `<img src=x onerror=...>`) into each field and assert the rendered HTML
    does NOT contain executable script tags / event handlers.
  - **Cody:** Pipe each through `s9e/text-formatter` (already a dependency)
    or HTMLPurifier; switch the blade to `{{ }}` where the source is plain
    text; standardize a `formatted_body` accessor pattern across models.
  - **Marty:** Extract the formatter wiring into a trait or value object
    so models share the implementation.
- **Acceptance:** All 6 Redd tests green; no raw user input rendered with
  `{!! !!}` without going through the formatter.

---

### [ ] 7. Break up god components — `CreateGuide.php` (640) / `EditGuide.php` (405)
- **Files:** `app/Livewire/CreateGuide.php`, `app/Livewire/EditGuide.php`
- **Risk:** Maintainability, duplication between Create and Edit.
- **TDD plan:**
  - **Redd:** Lock current behavior with feature tests covering:
    draft load/save, block CRUD (text/image/carousel), file upload, AI assist
    invocation, notification dispatch on submit. (Mostly missing today.)
  - **Cody:** Extract:
    - `App\Livewire\Concerns\ManagesGuideBlocks` trait
    - `App\Actions\Guides\HandleGuideImages` action
    - Shared `App\Livewire\Forms\GuideForm` (Livewire 3 form object) for
      Create + Edit
  - **Marty:** Collapse duplication; same treatment for `SeoService` (537)
    and `GeographySeoService` (501) — extract per-page-type strategies.
- **Acceptance:** No file in `app/Livewire/` over ~250 lines; tests still green.

---

### [ ] 8. Fix N+1 deletes
- **Files:** `app/Models/Thread.php:33`, `app/Reppable.php:16-17,36,41,52,57`,
  `app/Modules/Pickem/Models/Pickem.php:44`
- **Risk:** Performance — large threads issue 100s of DELETE statements.
- **TDD plan:**
  - **Redd:** Test that deleting a thread with N replies issues O(1) (or
    O(chunks)) DELETE queries, not O(N). Use `DB::enableQueryLog()`.
  - **Cody:** Replace `$thread->replies->each->delete()` with
    `$thread->replies()->delete()` (or chunk if model events are needed).
  - **Marty:** Apply the same pattern to all `Reppable` cleanup; ensure no
    behavior is lost (e.g. if a `deleting` event handler on `Reply` matters,
    keep `chunk()` form).
- **Acceptance:** Query count test green; existing tests still pass.

---

### [ ] 9. Add tests for the Modules
- **Files:** `app/Modules/Geography/`, `app/Modules/BuckEYE/`,
  `app/Modules/OhioWordle/`, `app/Modules/Pickem/` —
  `RobustAnswerCheck` (390 lines) is the highest priority.
- **Risk:** Silent regressions in user-facing daily-puzzle features.
- **TDD plan:**
  - **Redd:** Pest tests for each Module's core service. Specifically:
    - `RobustAnswerCheck`: simple variations, Levenshtein boundary cases,
      OpenAI fallback (mocked), cache hit path.
    - `WordioService`, `DictionaryService`, `WordRotationService`.
    - `PuzzleService::getTodaysPuzzle` and stat aggregation.
    - `Geography\Services\GeographySeoService` JSON-LD shape.
  - **Cody / Marty:** Address whatever the new tests surface.
- **Acceptance:** Test count rises meaningfully; each Module has at least one
  unit test covering its primary service.

---

### [ ] 10. Consolidate `ThreadController::index`
- **File:** `app/Http/Controllers/ThreadController.php:25-88`
- **Risk:** Maintainability — page-1 and page-N branches duplicate the entire
  query plus auth/Politics-exclusion logic.
- **TDD plan:**
  - **Redd:** Tests that pin behavior — page 1 cached for guests vs
    authenticated, page > 1 not cached, Politics excluded for guests, SEO
    canonical changes per page.
  - **Cody:** Extract `private function baseQuery(bool $isAuthenticated)`
    and a single pagination path; cache at the query level only.
  - **Marty:** Apply the same pattern to `ForumController::show` and
    `HomeController` — they all repeat this style.
- **Acceptance:** No duplicated query logic; tests green.

---

## Quick wins (not on the TDD queue)
- [x] Delete `PollComponent::test()` — see item 1
- [ ] Delete commented `dd($vbUser)` in `RegisteredUserController.php:45`

---

## How to use this queue

For each item:
1. **Redd** — write the failing test(s) first. Commit on red.
2. **Cody** — implement just enough to make Redd's tests green. Commit.
3. **Marty** — refactor without changing behavior. Run the tests after each
   change. Commit.

Run the focused test suite between phases:
```bash
php artisan test --compact --filter=<TestName>
```

End each item with `vendor/bin/pint --dirty`.
