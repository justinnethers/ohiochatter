# Ohio Wordle Game - Implementation Status

**Working Title:** OhioWordle (final name TBD)
**Game Type:** Daily word puzzle with Ohio-themed answers
**Mechanics:** 6 guesses, variable word length, dictionary validation

---

## What's Built (Phase 1 - Backend)

### Core Services

**WordleService** (`app/Modules/OhioWordle/Services/WordleService.php`)
- `calculateFeedback(guess, answer)` - Returns array of 'correct', 'present', 'absent' for each letter
- `getTodaysWord()` - Get the daily puzzle word (cached 1 hour)
- `getUserProgress(user)` - Get/create authenticated user game state
- `getGuestProgress()` - Get guest game state via session
- `processGuess(user, guess)` - Main game logic: validates, calculates feedback, updates progress/stats
- `loadWordStats(word)` - Aggregate statistics for a word (cached 5 minutes)

**DictionaryService** (`app/Modules/OhioWordle/Services/DictionaryService.php`)
- `isValidWord(word, length)` - Validate against English + Ohio dictionaries
- `getWordsOfLength(length)` - Get all valid words of specific length
- `getEnglishWords()` / `getOhioWords()` - Load word lists

### Database Schema

```
wordle_words
├── id, word (unique), word_length (indexed)
├── category, hint, difficulty
├── publish_date (unique), is_active

wordle_user_progress
├── user_id, word_id (unique together)
├── solved, attempts, guesses_taken
├── guesses (JSON), feedback (JSON)
├── completed_at

wordle_anonymous_progress
├── word_id, session_id (unique together)
├── ip_address, user_agent
├── solved, attempts, guesses (JSON), feedback (JSON)

wordle_user_stats
├── user_id (unique)
├── games_played, games_won
├── current_streak, max_streak
├── guess_distribution (JSON), last_played_date
```

### Models

| Model | Location |
|-------|----------|
| `WordleWord` | `app/Modules/OhioWordle/Models/WordleWord.php` |
| `WordleUserProgress` | `app/Modules/OhioWordle/Models/WordleUserProgress.php` |
| `WordleAnonymousProgress` | `app/Modules/OhioWordle/Models/WordleAnonymousProgress.php` |
| `WordleUserStats` | `app/Modules/OhioWordle/Models/WordleUserStats.php` |

### Factories

| Factory | Location |
|---------|----------|
| `WordleWordFactory` | `database/factories/WordleWordFactory.php` |
| `WordleUserProgressFactory` | `database/factories/WordleUserProgressFactory.php` |
| `WordleAnonymousProgressFactory` | `database/factories/WordleAnonymousProgressFactory.php` |
| `WordleUserStatsFactory` | `database/factories/WordleUserStatsFactory.php` |

### Tests (63 passing)

| Test File | Tests | Coverage |
|-----------|-------|----------|
| `FeedbackAlgorithmTest.php` | 18 | Letter matching, duplicates, variable lengths, case handling |
| `DictionaryServiceTest.php` | 16 | Word validation, Ohio words, caching |
| `WordleServiceTest.php` | 29 | Game flow, progress tracking, stats, validation |

### Dictionary Files

- `storage/app/dictionary/english.txt` - Common English words
- `storage/app/dictionary/ohio.txt` - Ohio-specific words (cities, landmarks, people)

**Note:** These files are in `storage/app/` which is gitignored. They need to be created manually or via a seeder. Current files exist locally but are not tracked in git.

### Module Registration

- `OhioWordleServiceProvider` registered in `config/app.php`
- Services registered as singletons

---

## What Needs to Be Built (Phase 2 - Frontend)

### Livewire Components

**OhioWordleGame** (`app/Modules/OhioWordle/Livewire/OhioWordleGame.php`)
- Main game component
- Properties: word, gameState, currentGuess, keyboard states
- Methods: mount, addLetter, removeLetter, submitGuess
- Dispatches `gameCompleted` event

**OhioWordleUserStats** (`app/Modules/OhioWordle/Livewire/OhioWordleUserStats.php`)
- Stats display component
- Listens for `gameCompleted` event
- Shows games played, win %, streaks, guess distribution

### Controller

**OhioWordleController** (`app/Modules/OhioWordle/Http/Controllers/OhioWordleController.php`)
- `index()` - Main game page
- `guestPlay()` - Guest access
- `stats()` - User statistics page (auth required)

### Routes

```php
GET /ohiowordle        → index
GET /ohiowordle/play   → guestPlay
GET /ohiowordle/stats  → stats (auth)
```

### Blade Views

```
resources/views/ohiowordle/
├── index.blade.php              # Main game page

resources/views/livewire/
├── ohio-wordle-game.blade.php   # Game component
└── ohio-wordle-user-stats.blade.php
```

### UI Components

**Game Grid**
- Dynamic columns based on word length
- 6 rows for guesses
- Letter cells with color feedback
- Flip animation on reveal

**Virtual Keyboard**
- QWERTY layout
- Color-coded by letter state (correct/present/absent/unused)
- Enter and Backspace keys
- Physical keyboard support

**Color Scheme (Ohio State)**
| State | Color | Tailwind |
|-------|-------|----------|
| Correct | Scarlet | `bg-red-600` |
| Present | Gray | `bg-gray-400` |
| Absent | Dark | `bg-gray-700` |

### Admin (Filament)

**WordleWordResource** (`app/Modules/OhioWordle/Filament/Resources/WordleWordResource.php`)
- CRUD for words
- Schedule publish dates
- Bulk import capability
- Category/difficulty management

**Widgets**
- `TodaysWordleStatsWidget` - Dashboard stats for today's puzzle

### Additional Features

- Social sharing (emoji grid results)
- SEO integration
- Navigation link in site header

---

## Key Design Decisions

1. **Variable word length** - Any length word works, grid adapts dynamically
2. **Combined dictionary** - English words + Ohio-specific terms for guess validation
3. **TDD approach** - All features built test-first
4. **Module structure** - Follows existing BuckEYE pattern

---

## Running Tests

```bash
# Run OhioWordle tests only
php artisan test --filter=OhioWordle

# Run all tests
php artisan test
```

## Migration

```bash
php artisan migrate
```
