# Pick 'Em Module

A sports prediction system where users pick winners from matchups and earn points based on correct predictions.

## Overview

Pick 'Ems are prediction games with pairs of options (e.g., "Bengals vs Browns"). Users select their predicted winner
for each matchup before a deadline. After games complete, admins mark winners and users earn points for correct picks.

## Database Schema

### Tables

```
pickem_groups
├── id (PK)
├── name (string) - e.g., "NFL 2024 Season"
├── slug (string, unique)
├── description (text, nullable)
├── timestamps
└── soft_deletes

pickems
├── id (PK)
├── user_id (FK → users) - creator
├── pickem_group_id (FK → pickem_groups, nullable)
├── title (string)
├── slug (string, unique)
├── body (text, nullable) - description/rules
├── scoring_type (enum: simple, weighted, confidence)
├── picks_lock_at (timestamp, nullable) - deadline
├── is_finalized (boolean, default false)
├── timestamps
└── soft_deletes

pickem_matchups
├── id (PK)
├── pickem_id (FK → pickems, cascade)
├── option_a (string) - e.g., "Bengals"
├── option_b (string) - e.g., "Browns"
├── description (string, nullable) - e.g., "Week 1 - Sunday 1pm"
├── points (int, default 1) - for weighted mode
├── display_order (int, default 0)
├── winner (enum: a, b, push, nullable)
└── timestamps

pickem_picks
├── id (PK)
├── user_id (FK → users, cascade)
├── pickem_matchup_id (FK → pickem_matchups, cascade)
├── pick (enum: a, b)
├── confidence (int, nullable) - 1-N for confidence mode
├── timestamps
└── unique constraint: [user_id, pickem_matchup_id]

pickem_comments
├── id (PK)
├── pickem_id (FK → pickems, cascade)
├── user_id (FK → users, cascade)
├── body (text)
├── timestamps
└── soft_deletes
```

### Relationships

```
PickemGroup hasMany Pickem
Pickem belongsTo PickemGroup (optional)
Pickem belongsTo User (owner)
Pickem hasMany PickemMatchup
Pickem hasMany PickemComment
PickemMatchup belongsTo Pickem
PickemMatchup hasMany PickemPick
PickemPick belongsTo PickemMatchup
PickemPick belongsTo User
PickemComment belongsTo Pickem
PickemComment belongsTo User
```

## Scoring Modes

### Simple

- 1 point per correct pick
- Max score = number of matchups

### Weighted

- Admin assigns point value to each matchup
- Higher-stakes games worth more points
- Max score = sum of all matchup points

### Confidence

- User assigns confidence values 1-N to N matchups
- Each value used exactly once
- Earn the confidence value for correct picks
- Max score = 1+2+3+...+N = N(N+1)/2

### Push Handling

When a matchup results in a "push" (tie), ALL picks for that matchup are marked correct.

## File Structure

```
app/Modules/Pickem/
├── Http/Controllers/
│   ├── PickemController.php      # Public routes (index, show, group)
│   └── PickemAdminController.php # Admin routes (index, groups, create, edit)
├── Models/
│   ├── PickemGroup.php           # Group model with getLeaderboard()
│   ├── Pickem.php                # Main model with getUserScore(), isLocked()
│   ├── PickemMatchup.php         # Matchup with getWinnerLabel()
│   ├── PickemPick.php            # User pick with isCorrect()
│   └── PickemComment.php         # Discussion comments
├── Services/
│   └── PickemScoringService.php  # Score calculation (currently minimal)
├── PickemServiceProvider.php     # Registers routes and services
├── routes.php                    # Route definitions
└── README.md                     # This file
```

## Livewire Components

Located in `app/Livewire/`:

| Component            | Purpose                                                                                    |
|----------------------|--------------------------------------------------------------------------------------------|
| `PickemGame`         | Main picking interface - displays matchups, handles pick submission, confidence assignment |
| `PickemLeaderboard`  | Shows rankings for a single pickem                                                         |
| `PickemComments`     | Discussion with add/delete functionality                                                   |
| `PickemGroupManager` | Admin CRUD for groups                                                                      |
| `PickemAdminManager` | Admin list of all pickems with filters                                                     |
| `PickemEditor`       | Admin create/edit pickems, manage matchups, set winners                                    |

## Routes

```
GET  /pickems                      → pickem.index (list all)
GET  /pickems/groups/{group:slug}  → pickem.group (group leaderboard)
GET  /pickems/admin                → pickem.admin.index (admin list)
GET  /pickems/admin/groups         → pickem.admin.groups (manage groups)
GET  /pickems/admin/create         → pickem.admin.create (new pickem)
GET  /pickems/admin/{pickem}/edit  → pickem.admin.edit (edit pickem)
GET  /pickems/{pickem:slug}        → pickem.show (single pickem)
```

**Note:** The wildcard `{pickem:slug}` route must be defined LAST to prevent catching "admin" or "groups" as slugs.

## Key Model Methods

### Pickem

```php
isLocked(): bool        // True if picks_lock_at is in the past
isActive(): bool        // Opposite of isLocked
getUserScore(User): int // Calculate user's score based on scoring_type
getMaxPossibleScore(): int // Maximum achievable score
path(): string          // URL to this pickem
```

### PickemGroup

```php
getLeaderboard(): Collection // Returns users ranked by cumulative score across all pickems in group
```

### PickemMatchup

```php
getWinnerLabel(): ?string      // "Option A name", "Option B name", "Push (Tie)", or null
getPickCountForOption(string): int // Count of picks for 'a' or 'b'
```

### PickemPick

```php
isCorrect(): ?bool      // True if correct, false if wrong, null if no winner set
getPointsEarned(): int  // Points earned (depends on scoring mode)
```

## Admin Workflow

1. **Create Group** (optional): `/pickems/admin/groups` → "New Group"
2. **Create Pick 'Em**: `/pickems/admin/create`
    - Set title, description, scoring type, lock time
    - Add matchups with Option A vs Option B
3. **Users Submit Picks**: Before `picks_lock_at` deadline
4. **Mark Winners**: Edit pickem → click A/B/Push for each matchup
5. **Finalize**: Click "Finalize Pick 'Em" (auto-locks if not already locked)

## Authorization

- Public routes: Anyone can view pickems and leaderboards
- Pick submission: Authenticated users only, before lock time
- Admin routes: Users with `is_admin = true` only
- Admin check in Livewire components via `checkAdmin()` method in `mount()`

## Views

Located in `resources/views/`:

```
pickem/
├── index.blade.php       # List all pickems
├── show.blade.php        # Single pickem with game + comments
├── group.blade.php       # Group view with leaderboard
└── admin/
    ├── index.blade.php   # Admin dashboard
    ├── groups.blade.php  # Manage groups
    ├── create.blade.php  # Create pickem
    └── edit.blade.php    # Edit pickem

livewire/
├── pickem-game.blade.php
├── pickem-leaderboard.blade.php
├── pickem-comments.blade.php
├── pickem-group-manager.blade.php
├── pickem-admin-manager.blade.php
└── pickem-editor.blade.php
```

## Tests

Located in `tests/Feature/Pickem/PickemTest.php`:

- Model relationships and factories
- Locking behavior (past/future/null lock times)
- Scoring calculations (all 3 modes)
- Push handling
- Leaderboard generation (single and group)
- Pick constraints (one per user per matchup)
- Comment CRUD

Run with: `php artisan test --filter=Pickem`

## Factories

Located in `database/factories/`:

- `PickemGroupFactory`
- `PickemFactory`
- `PickemMatchupFactory`
- `PickemPickFactory`
- `PickemCommentFactory`

Models use `newFactory()` method to point to correct factory location since they're in a module namespace.

## Service Provider

`PickemServiceProvider` registered in `config/app.php`:

- Registers `PickemScoringService` as singleton
- Loads routes from `routes.php`
