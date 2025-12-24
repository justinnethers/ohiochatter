# REFACTOR.md - SOLID Principles Analysis

This document catalogs refactoring opportunities to improve adherence to SOLID principles in the OhioChatter codebase.

---

## Small Refactors

### 1. Remove Debug Code from PollComponent
**File:** `app/Livewire/PollComponent.php`
**Principle:** General code quality
**Issue:** Contains 5 `\Log::info()` calls and a `test()` method with `dd()` statement that should not be in production code.
**Fix:** Remove logging statements (lines 40-42, 45, 58, 62, 100) and delete the `test()` method (lines 74-78).

---

### 2. Extract Authorization Logic to Policy
**File:** `app/Livewire/PostComponent.php:28`
**Principle:** SRP, OCP
**Issue:** Inline authorization check:
```php
$this->canEdit = $this->post->owner->id === \Auth::id() ||
                 \Auth::user() && \Auth::user()->is_admin;
```
**Fix:** Create `PostPolicy` with `update()` method, use `$user->can('update', $post)`.

---

### 3. Use Constructor Injection in ContentController
**File:** `app/Http/Controllers/ContentController.php`
**Principle:** DIP
**Issue:** Repeated `app(LocationService::class)` calls in methods (lines 103, 123, 143, 168, 189, 214).
**Fix:** Inject `LocationService` via constructor:
```php
public function __construct(private LocationService $locationService) {}
```

---

### 4. Extract Hardcoded User Exclusion to Config
**File:** `app/Services/PuzzleService.php`
**Principle:** OCP
**Issue:** Hardcoded `user_id != 1` to exclude admin from stats (lines 24, 31, 33, 46-48, 75-77).
**Fix:** Add `config('buckeye.excluded_user_ids')` array and use `whereNotIn()`.

---

### 5. Extract Hardcoded Forum Restrictions
**File:** `app/Http/Controllers/ThreadController.php`
**Principle:** OCP
**Issue:** Hardcoded forum exclusions:
- Line 31: `where('name', '!=', 'Politics')`
- Line 96: `forum->id == 3`

**Fix:** Add `is_restricted` boolean column to forums table, or create `config('forum.guest_restricted_forums')`.

---

### 6. Replace Auth Facade in Reppable Trait
**File:** `app/Reppable.php`
**Principle:** DIP
**Issue:** Direct `auth()->id()` and `auth()->user()` calls (lines 33, 35, 49, 51).
**Fix:** Accept user as parameter:
```php
public function rep(?User $user = null): void
{
    $user = $user ?? auth()->user();
    // ...
}
```

---

### 7. Extract Game Difficulty Constants to Config
**File:** `app/Livewire/BuckEyeGame.php`
**Principle:** OCP
**Issue:** Hardcoded `MAX_GUESSES = 5` and `PIXELATION_LEVELS = 5` (lines 19-20).
**Fix:** Move to `config('buckeye.max_guesses')` and `config('buckeye.pixelation_levels')`.

---

## Medium Refactors

### 8. Split Reppable Trait into Focused Traits
**File:** `app/Reppable.php` (72 lines)
**Principle:** SRP, ISP
**Issue:** Single trait handles:
- Model lifecycle (deleting related reps/negs)
- Rep/neg creation with toggle logic
- Checking methods (`isReppedBy`, `isNeggedBy`)

**Fix:** Split into:
```
app/Traits/
├── HasReputation.php      # Relationships only (reps(), negs())
├── Reppable.php           # Operations (rep(), neg())
└── ReputationQueries.php  # Checking methods
```

---

### 9. Consolidate ContentController Location Methods
**File:** `app/Http/Controllers/ContentController.php` (223 lines)
**Principle:** SRP, DRY
**Issue:** 9 nearly identical methods for different location hierarchies:
- `region()`, `regionCategory()`
- `county()`, `countyCategory()`
- `city()`, `cityCategory()`

**Fix:** Create single polymorphic method:
```php
public function locationContent(Request $request, string $type, ...$slugs)
{
    $location = $this->locationService->resolveLocation($type, $slugs);
    $content = $this->locationService->getContent($location, $request->category);
    return view('content.location', compact('location', 'content'));
}
```

---

### 10. Create GameProgressInterface for Auth/Guest Parity
**Files:** `app/Livewire/BuckEyeGame.php`, `app/Services/PuzzleService.php`
**Principle:** LSP
**Issue:** `UserGameProgress` and `AnonymousGameProgress` are handled with different code paths throughout:
- Lines 52-64 in BuckEyeGame: separate methods for each
- Lines 101-137 in BuckEyeGame: duplicated guess processing logic

**Fix:** Create interface and adapter:
```php
interface GameProgressInterface {
    public function getAttempts(): int;
    public function getGuesses(): array;
    public function isComplete(): bool;
    public function recordGuess(string $guess, bool $correct): void;
}
```

---

### 11. Extract Statistics Calculation from PuzzleService
**File:** `app/Services/PuzzleService.php` (lines 20-104)
**Principle:** SRP
**Issue:** `loadUserStats()` method is 84 lines with complex statistics aggregation for both authenticated and anonymous users.
**Fix:** Create `GameStatsService`:
```php
class GameStatsService {
    public function getAuthenticatedStats(User $user, Puzzle $puzzle): UserStats;
    public function getAnonymousStats(Puzzle $puzzle): AnonymousStats;
    public function getGuessDistribution(Puzzle $puzzle): array;
}
```

---

### 12. Create LocationInterface for Polymorphic Behavior
**File:** `app/Services/LocationService.php`
**Principle:** OCP, DIP
**Issue:** Hardcoded type checks for Region, County, City (lines 185-207):
```php
if ($type === City::class) { ... }
if ($type === County::class) { ... }
```

**Fix:** Create interface:
```php
interface Locatable {
    public function getParent(): ?Locatable;
    public function getChildren(): Collection;
    public function getCacheKey(): string;
}
```
Implement on Region, County, City models.

---

### 13. Refactor ThreadController Caching Logic
**File:** `app/Http/Controllers/ThreadController.php` (lines 17-62)
**Principle:** SRP
**Issue:** `index()` method mixes cache management, auth checks, query building, and pagination.
**Fix:** Extract to `ThreadCacheService`:
```php
class ThreadCacheService {
    public function getCachedThreads(User $user = null, int $page = 1): LengthAwarePaginator;
    public function invalidateThreadCache(): void;
}
```

---

### 14. Create ReputationService to Replace Trait Logic
**File:** `app/Reppable.php`
**Principle:** SRP, DIP
**Issue:** `rep()` and `neg()` methods (lines 31-61) contain duplicate toggle logic and business rules.
**Fix:** Create service:
```php
class ReputationService {
    public function giveRep(Reppable $model, User $user): void;
    public function giveNeg(Reppable $model, User $user): void;
    public function toggleReputation(Reppable $model, User $user, string $type): void;
}
```

---

## Large Refactors

### 15. Extract Image Processing from BuckEyeGameController
**File:** `app/Http/Controllers/BuckEyeGameController.php` (lines 102-205)
**Principle:** SRP
**Issue:** `socialImage()` method is 103 lines of GD image manipulation code including:
- Image loading with type detection (lines 126-149)
- Blur algorithm implementation (lines 143-179)
- Output formatting and caching (lines 180-205)

**Fix:** Create dedicated services:
```
app/Services/Image/
├── ImageProcessingService.php   # Main orchestrator
├── ImageLoaderInterface.php     # Load images by type
├── BlurEffectService.php        # Blur algorithm
└── ImageCacheService.php        # Storage/caching
```

Example:
```php
class ImageProcessingService {
    public function __construct(
        private ImageLoaderInterface $loader,
        private BlurEffectService $blur,
        private ImageCacheService $cache
    ) {}

    public function generateSocialImage(Puzzle $puzzle, int $level): string;
}
```

---

### 16. Implement Repository Pattern for Models
**Files:** Multiple controllers and services
**Principle:** DIP
**Issue:** Direct Eloquent model usage throughout:
- `Thread::where()` in ThreadController
- `Puzzle::getTodaysPuzzle()` in PuzzleService
- `UserGameProgress::where()` in BuckEyeGame component

**Fix:** Create repositories:
```
app/Repositories/
├── Contracts/
│   ├── ThreadRepositoryInterface.php
│   ├── PuzzleRepositoryInterface.php
│   └── GameProgressRepositoryInterface.php
├── EloquentThreadRepository.php
├── EloquentPuzzleRepository.php
└── EloquentGameProgressRepository.php
```

Bind in `AppServiceProvider`:
```php
$this->app->bind(ThreadRepositoryInterface::class, EloquentThreadRepository::class);
```

---

### 17. Refactor BuckEyeGame Component Architecture
**File:** `app/Livewire/BuckEyeGame.php` (167 lines)
**Principle:** SRP, DIP
**Issue:** Component handles:
- Game state initialization (lines 41-65)
- Progress reconstruction (lines 68-81)
- Guess processing with auth/guest branching (lines 90-142)
- Anonymous progress persistence (lines 144-161)
- Stats display (lines 83-88)

**Fix:** Split into:
```
app/Livewire/BuckEye/
├── BuckEyeGame.php           # Main component (orchestration only)
├── GameStateManager.php      # State initialization & management
├── GuessProcessor.php        # Guess validation & recording
└── StatsDisplay.php          # Stats modal component

app/Services/BuckEye/
├── GameSessionService.php    # Handles auth/guest sessions
├── ProgressTracker.php       # Progress persistence
└── GuessValidator.php        # Answer checking
```

---

### 18. Create Event-Based Cache Invalidation System
**File:** `app/Services/LocationService.php` (lines 185-207)
**Principle:** SRP, OCP
**Issue:** Manual recursive cache clearing is complex and fragile:
```php
public function clearLocationCache(string $type, Model $model): void
{
    if ($type === City::class) {
        // Clear city, county, region caches...
    }
}
```

**Fix:** Implement event-driven approach:
```php
// Events
class LocationContentUpdated {
    public function __construct(public Locatable $location) {}
}

// Listener
class InvalidateLocationCache {
    public function handle(LocationContentUpdated $event): void {
        $location = $event->location;
        while ($location) {
            Cache::forget($location->getCacheKey());
            $location = $location->getParent();
        }
    }
}
```

---

### 19. Refactor PuzzleService into Focused Services
**File:** `app/Services/PuzzleService.php` (197 lines)
**Principle:** SRP
**Issue:** Service handles multiple concerns:
- Statistics calculation (lines 20-104)
- Guess processing (lines 106-156)
- Progress retrieval/creation (lines 158-175)
- Image URL handling (lines 191-196)

**Fix:** Split into focused services:
```
app/Services/BuckEye/
├── PuzzleService.php         # Core puzzle retrieval
├── GameStatsService.php      # All statistics calculations
├── GameProgressService.php   # Progress CRUD operations
├── GuessProcessingService.php # Guess validation & state updates
└── PuzzleImageService.php    # Image URL generation
```

---

### 20. Implement Service Container Bindings with Interfaces
**Files:** All services
**Principle:** DIP
**Issue:** Controllers depend on concrete service classes:
```php
public function __construct(private PuzzleService $puzzleService) {}
```

**Fix:** Create interfaces and bind in service provider:
```php
// Interfaces
interface PuzzleServiceInterface {
    public function getTodaysPuzzle(): ?Puzzle;
    public function processGuess(Puzzle $puzzle, User $user, string $guess): GuessResult;
}

// AppServiceProvider
public function register(): void
{
    $this->app->bind(PuzzleServiceInterface::class, PuzzleService::class);
    $this->app->bind(LocationServiceInterface::class, LocationService::class);
    // etc.
}
```

---

## Summary

| Size | Count | Estimated Impact |
|------|-------|------------------|
| Small | 7 | Quick wins, immediate code quality improvements |
| Medium | 7 | Moderate restructuring, improved testability |
| Large | 6 | Architectural improvements, long-term maintainability |

### Recommended Priority Order

**Phase 1 - Foundation:**
1. Small refactors #1-7 (clean up obvious issues)
2. Medium #8 (split Reppable trait)
3. Medium #12 (LocationInterface)

**Phase 2 - Services:**
4. Large #15 (image processing extraction)
5. Medium #11 (GameStatsService)
6. Large #19 (PuzzleService split)

**Phase 3 - Architecture:**
7. Large #16 (repository pattern)
8. Large #17 (BuckEyeGame refactor)
9. Large #20 (interface bindings)
10. Large #18 (event-based caching)
