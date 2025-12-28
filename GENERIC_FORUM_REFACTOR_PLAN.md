# Generic Forum Software Refactor Plan

> **Generated:** December 27, 2025
> **Source Codebase:** OhioChatter (Laravel 10)
> **Goal:** Create a generic, modular forum package for small communities

---

## Table of Contents

1. [Executive Summary](#executive-summary)
2. [Current Architecture Analysis](#current-architecture-analysis)
3. [What to Port vs Build Fresh](#what-to-port-vs-build-fresh)
4. [New Project Structure](#new-project-structure)
5. [Module System Design](#module-system-design)
6. [Phase-by-Phase Implementation](#phase-by-phase-implementation)
7. [Configuration System](#configuration-system)
8. [Database Schema](#database-schema)
9. [Admin Panel Requirements](#admin-panel-requirements)
10. [Theming System](#theming-system)
11. [Files Reference from OhioChatter](#files-reference-from-ohiochatter)

---

## Executive Summary

| Aspect | OhioChatter (Current) | Target Generic Package |
|--------|----------------------|------------------------|
| **Architecture** | Monolithic with scattered modules | True modular system with service providers |
| **Branding** | Hardcoded "OhioChatter" in 50+ files | Configuration-driven, white-label ready |
| **Admin Panel** | Only Puzzle management | Full forum administration |
| **Theming** | Hardcoded Tailwind colors | CSS custom properties + theme system |
| **Authorization** | Simple `is_admin` boolean | Role-based with policies |
| **Modules** | Empty placeholder directories | Self-contained, installable packages |

### Recommendation

**Start a new repository** rather than refactoring OhioChatter. This provides:
- Clean architecture from day one
- No legacy baggage or Ohio-specific code
- Proper package structure from the start
- No risk to production OhioChatter site

---

## Current Architecture Analysis

### Core Forum System (Well-Designed, Port These)

**Models & Relationships:**
- `Forum` → hasMany `Thread`
- `Thread` → belongsTo `Forum`, hasMany `Reply`, hasOne `Poll`
- `Reply` → belongsTo `Thread`, belongsTo `User`
- `User` → hasMany `Reply`, hasMany `Thread`

**Key Features:**
- Reputation system via `Reppable` trait (polymorphic reps/negs)
- Soft deletes on threads and replies
- Reply count caching on threads
- View tracking per user (`threads_users_views` table)
- Optional polls with single/multiple choice
- Laravel Scout integration (Algolia search)

**Database Tables (Core Forum):**
```
forums          - id, name, slug, description, is_active, is_restricted, color, order
threads         - id, forum_id, user_id, title, slug, body, locked, views, replies_count, last_activity_at
replies         - id, thread_id, user_id, body, deleted_at
users           - id, username, email, is_admin, is_moderator, is_banned, reputation, post_count
reps            - id, user_id, repped_id, repped_type (polymorphic)
negs            - id, user_id, negged_id, negged_type (polymorphic)
polls           - id, thread_id, user_id, type
poll_options    - id, poll_id, label
poll_votes      - id, poll_option_id, user_id
threads_users_views - user_id, thread_id, last_view
```

### Ohio-Specific Systems (Do NOT Port)

1. **Geographic Content System**
   - Region → County → City hierarchy
   - Content attached to locations (polymorphic)
   - Routes prefixed with `/ohio`
   - All SEO hardcoded for Ohio

2. **BuckEYE Game**
   - Daily puzzle with pixelation levels
   - Guest and authenticated progress tracking
   - Ohio-themed content

3. **VbArchive**
   - Legacy vBulletin data integration
   - Specific forum ID mappings
   - Old URL redirects

### Existing Module Structure (Incomplete)

```
app/Modules/
├── BuckEYE/      # Empty placeholder directories
├── Messages/     # Partially implemented (uses cmgmyr/messenger)
└── VbArchive/    # Empty, code scattered in core
```

**Issues with current modules:**
- No module service providers
- No automatic route/view registration
- No module configuration files
- Code scattered across core app directories

---

## What to Port vs Build Fresh

### Port from OhioChatter

| Component | Location | Why Keep It |
|-----------|----------|-------------|
| `Reppable` trait | `app/Reppable.php` | Clean polymorphic reputation system |
| Forum/Thread/Reply models | `app/Models/` | Well-structured relationships |
| Rep/Neg models | `app/Models/` | Simple, effective |
| Poll system | `app/Models/Poll*.php` | Clean implementation |
| `PostComponent` Livewire | `app/Livewire/` | Good pattern for post rendering |
| `Reputation` Livewire | `app/Livewire/` | Rep/neg button logic |
| `QuoteButton` Livewire | `app/Livewire/` | Quote functionality |
| Caching patterns | Various services | Cache invalidation strategies |
| Searchable integration | Model traits | Scout/Algolia pattern |

### Build Fresh

| Component | Why Not Port |
|-----------|--------------|
| SeoService | 20+ hardcoded Ohio strings |
| Geographic system | Too specific, complex |
| BuckEYE game | Unrelated to core forum |
| VbArchive | Legacy migration specific |
| Current module structure | Incomplete, poorly designed |
| ThreadController | Hardcoded forum exclusions |
| Navigation/Footer views | Hardcoded links and branding |
| Filament resources | Only Puzzle exists |

---

## New Project Structure

```
community-forum/
├── app/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Forum.php
│   │   ├── Thread.php
│   │   ├── Reply.php
│   │   ├── Poll.php
│   │   ├── PollOption.php
│   │   ├── PollVote.php
│   │   ├── Rep.php
│   │   ├── Neg.php
│   │   └── Role.php
│   │
│   ├── Modules/
│   │   ├── Core/
│   │   │   ├── Contracts/
│   │   │   │   ├── ModuleInterface.php
│   │   │   │   └── BootableModule.php
│   │   │   ├── Support/
│   │   │   │   ├── ModuleLoader.php
│   │   │   │   └── ModuleRegistry.php
│   │   │   └── Providers/
│   │   │       └── ModuleServiceProvider.php
│   │   │
│   │   ├── Reputation/
│   │   │   ├── Traits/Reppable.php
│   │   │   ├── Models/Rep.php
│   │   │   ├── Models/Neg.php
│   │   │   ├── Livewire/ReputationButton.php
│   │   │   ├── Providers/ReputationServiceProvider.php
│   │   │   └── module.json
│   │   │
│   │   └── Messaging/
│   │       ├── Models/
│   │       ├── Http/Controllers/
│   │       ├── Providers/MessagingServiceProvider.php
│   │       └── module.json
│   │
│   ├── Traits/
│   │   ├── HasSlug.php
│   │   └── Searchable.php (or use Scout directly)
│   │
│   ├── Services/
│   │   ├── SeoService.php          # Config-driven
│   │   ├── ForumService.php
│   │   └── ThreadService.php
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── ForumController.php
│   │   │   ├── ThreadController.php
│   │   │   └── ReplyController.php
│   │   └── Requests/
│   │       ├── StoreThreadRequest.php
│   │       └── StoreReplyRequest.php
│   │
│   ├── Livewire/
│   │   ├── PostComponent.php
│   │   ├── QuoteButton.php
│   │   ├── ThreadLockToggle.php
│   │   ├── PollComponent.php
│   │   └── ActiveUsers.php
│   │
│   ├── Policies/
│   │   ├── ForumPolicy.php
│   │   ├── ThreadPolicy.php
│   │   └── ReplyPolicy.php
│   │
│   └── Filament/
│       ├── Resources/
│       │   ├── ForumResource.php
│       │   ├── ThreadResource.php
│       │   ├── ReplyResource.php
│       │   ├── UserResource.php
│       │   └── RoleResource.php
│       ├── Pages/
│       │   ├── Settings.php
│       │   └── Branding.php
│       └── Widgets/
│           ├── ForumStatsWidget.php
│           └── RecentActivityWidget.php
│
├── config/
│   ├── forum.php                   # Core forum settings
│   ├── branding.php                # Site identity
│   ├── modules.php                 # Module toggles
│   └── seo.php                     # SEO defaults
│
├── database/
│   ├── migrations/
│   └── seeders/
│
├── resources/
│   ├── css/
│   │   ├── app.css
│   │   └── theme.css               # CSS custom properties
│   ├── js/
│   │   └── app.js
│   └── views/
│       ├── layouts/
│       ├── components/
│       ├── livewire/
│       ├── forums/
│       ├── threads/
│       └── auth/
│
├── routes/
│   ├── web.php
│   └── api.php
│
└── tests/
    ├── Feature/
    └── Unit/
```

---

## Module System Design

### Module Interface

```php
<?php

namespace App\Modules\Core\Contracts;

interface ModuleInterface
{
    public function getName(): string;
    public function getVersion(): string;
    public function getDependencies(): array;
    public function isEnabled(): bool;
    public function getServiceProvider(): string;
}
```

### Module Manifest (`module.json`)

```json
{
    "name": "reputation",
    "version": "1.0.0",
    "description": "Reputation system with reps and negs",
    "provider": "App\\Modules\\Reputation\\Providers\\ReputationServiceProvider",
    "dependencies": [],
    "config": "reputation.php",
    "routes": {
        "web": "Routes/web.php"
    },
    "views": "Resources/views",
    "migrations": "Database/migrations"
}
```

### Module Service Provider Pattern

```php
<?php

namespace App\Modules\Reputation\Providers;

use Illuminate\Support\ServiceProvider;

class ReputationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/reputation.php', 'reputation');
    }

    public function boot(): void
    {
        // Routes
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');

        // Views
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'reputation');

        // Migrations
        $this->loadMigrationsFrom(__DIR__.'/../Database/migrations');

        // Livewire components
        Livewire::component('reputation-button', ReputationButton::class);
    }
}
```

### Module Loader

```php
<?php

namespace App\Modules\Core\Support;

class ModuleLoader
{
    public function discover(): array
    {
        $modules = [];
        $modulesPath = app_path('Modules');

        foreach (glob("$modulesPath/*/module.json") as $manifestPath) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            $modules[$manifest['name']] = $manifest;
        }

        return $modules;
    }

    public function register(array $manifest): void
    {
        if ($this->isEnabled($manifest['name'])) {
            app()->register($manifest['provider']);
        }
    }

    public function isEnabled(string $moduleName): bool
    {
        return config("modules.{$moduleName}.enabled", false);
    }
}
```

---

## Phase-by-Phase Implementation

### Phase 1: Project Foundation (Week 1)

**Tasks:**
- [ ] Create new Laravel 11 project
- [ ] Install dependencies (Livewire, Filament, Scout, etc.)
- [ ] Set up module system infrastructure
- [ ] Create base configuration files
- [ ] Set up Tailwind with CSS custom properties
- [ ] Create base layouts with config-driven branding

**Commands:**
```bash
composer create-project laravel/laravel community-forum
cd community-forum
composer require livewire/livewire filament/filament:^3.0 laravel/scout
composer require spatie/laravel-sitemap mews/purifier
npm install -D tailwindcss postcss autoprefixer @tailwindcss/typography @tailwindcss/forms
```

### Phase 2: Core Forum Models (Week 1-2)

**Tasks:**
- [ ] Create Forum, Thread, Reply models
- [ ] Create migrations
- [ ] Set up model relationships
- [ ] Implement soft deletes
- [ ] Add slug generation
- [ ] Create model factories for testing

### Phase 3: Reputation Module (Week 2)

**Tasks:**
- [ ] Port `Reppable` trait (cleaned up)
- [ ] Create Rep/Neg models
- [ ] Create Livewire reputation component
- [ ] Create module service provider
- [ ] Make terminology configurable (rep/like, neg/dislike)

### Phase 4: Controllers & Routes (Week 2-3)

**Tasks:**
- [ ] ForumController (index, show)
- [ ] ThreadController (CRUD)
- [ ] ReplyController (store, update, destroy)
- [ ] Set up route model binding with slugs
- [ ] Create form requests with validation

### Phase 5: Livewire Components (Week 3)

**Tasks:**
- [ ] PostComponent (post display with edit mode)
- [ ] QuoteButton (insert quotes)
- [ ] ThreadLockToggle
- [ ] PollComponent
- [ ] ActiveUsers

### Phase 6: Views & Frontend (Week 3-4)

**Tasks:**
- [ ] Create base layouts (app, guest)
- [ ] Build Blade components (buttons, inputs, modals, etc.)
- [ ] Forum listing views
- [ ] Thread show/create/edit views
- [ ] Implement theme CSS custom properties
- [ ] Mobile-responsive navigation

### Phase 7: Admin Panel (Week 4-5)

**Tasks:**
- [ ] ForumResource (CRUD forums)
- [ ] ThreadResource (moderation)
- [ ] ReplyResource (moderation)
- [ ] UserResource (user management)
- [ ] RoleResource (roles & permissions)
- [ ] Settings page (site configuration)
- [ ] Branding page (logo, colors)
- [ ] Dashboard widgets

### Phase 8: Authorization (Week 5)

**Tasks:**
- [ ] Create Role model and migration
- [ ] Implement ForumPolicy
- [ ] Implement ThreadPolicy
- [ ] Implement ReplyPolicy
- [ ] Register policies in AuthServiceProvider
- [ ] Add role-based gates

### Phase 9: Search & SEO (Week 5-6)

**Tasks:**
- [ ] Configure Laravel Scout
- [ ] Make Thread and Reply searchable
- [ ] Create search controller and views
- [ ] Build config-driven SeoService
- [ ] Add meta tags components
- [ ] Generate sitemap

### Phase 10: Testing & Documentation (Week 6)

**Tasks:**
- [ ] Feature tests for forum CRUD
- [ ] Feature tests for thread/reply operations
- [ ] Unit tests for services
- [ ] Livewire component tests
- [ ] Write installation documentation
- [ ] Write configuration guide

---

## Configuration System

### `config/forum.php`

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Forum Settings
    |--------------------------------------------------------------------------
    */

    'threads_per_page' => env('FORUM_THREADS_PER_PAGE', 20),
    'replies_per_page' => env('FORUM_REPLIES_PER_PAGE', 25),

    'allow_guest_viewing' => env('FORUM_ALLOW_GUESTS', true),
    'require_email_verification' => env('FORUM_REQUIRE_VERIFICATION', false),

    'features' => [
        'polls' => env('FORUM_ENABLE_POLLS', true),
        'signatures' => env('FORUM_ENABLE_SIGNATURES', false),
        'avatars' => env('FORUM_ENABLE_AVATARS', true),
    ],

    'moderation' => [
        'auto_lock_after_days' => env('FORUM_AUTO_LOCK_DAYS', null),
        'require_approval' => env('FORUM_REQUIRE_APPROVAL', false),
    ],
];
```

### `config/branding.php`

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Site Branding
    |--------------------------------------------------------------------------
    */

    'name' => env('FORUM_NAME', 'Community Forum'),
    'tagline' => env('FORUM_TAGLINE', 'Discuss, Share, Connect'),
    'description' => env('FORUM_DESCRIPTION', 'A place for community discussion'),

    'logo' => [
        'path' => env('FORUM_LOGO', '/images/logo.png'),
        'alt' => env('FORUM_LOGO_ALT', 'Forum Logo'),
    ],

    'copyright' => env('FORUM_COPYRIGHT', 'Community Forum'),
    'copyright_url' => env('FORUM_COPYRIGHT_URL', null),

    'social' => [
        'twitter' => env('SOCIAL_TWITTER'),
        'facebook' => env('SOCIAL_FACEBOOK'),
        'discord' => env('SOCIAL_DISCORD'),
    ],
];
```

### `config/modules.php`

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Module Configuration
    |--------------------------------------------------------------------------
    */

    'reputation' => [
        'enabled' => env('MODULE_REPUTATION', true),
        'terminology' => [
            'positive' => env('REP_POSITIVE_TERM', 'Rep'),
            'negative' => env('REP_NEGATIVE_TERM', 'Neg'),
        ],
    ],

    'messaging' => [
        'enabled' => env('MODULE_MESSAGING', true),
    ],

    // Future modules can be added here
];
```

### `config/seo.php`

```php
<?php

return [
    'site_name' => env('SEO_SITE_NAME', config('branding.name')),
    'title_separator' => env('SEO_TITLE_SEPARATOR', ' - '),
    'default_description' => env('SEO_DEFAULT_DESCRIPTION', config('branding.description')),
    'default_image' => env('SEO_DEFAULT_IMAGE', '/images/og-default.jpg'),

    'twitter' => [
        'card' => 'summary_large_image',
        'site' => env('TWITTER_SITE'),
    ],
];
```

---

## Database Schema

### Core Tables Migration

```php
// forums table
Schema::create('forums', function (Blueprint $table) {
    $table->id();
    $table->foreignId('creator_id')->nullable()->constrained('users')->nullOnDelete();
    $table->string('name');
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->string('color')->default('#3b82f6');
    $table->integer('order')->default(0);
    $table->boolean('is_active')->default(true);
    $table->boolean('is_restricted')->default(false);
    $table->timestamps();
});

// threads table
Schema::create('threads', function (Blueprint $table) {
    $table->id();
    $table->foreignId('forum_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('title');
    $table->string('slug');
    $table->longText('body');
    $table->unsignedInteger('views')->default(0);
    $table->unsignedInteger('replies_count')->default(0);
    $table->boolean('locked')->default(false);
    $table->boolean('pinned')->default(false);
    $table->timestamp('last_activity_at')->nullable();
    $table->string('meta_title')->nullable();
    $table->text('meta_description')->nullable();
    $table->timestamps();
    $table->softDeletes();

    $table->unique(['forum_id', 'slug']);
    $table->index('last_activity_at');
});

// replies table
Schema::create('replies', function (Blueprint $table) {
    $table->id();
    $table->foreignId('thread_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->longText('body');
    $table->timestamps();
    $table->softDeletes();
});

// reps table (reputation module)
Schema::create('reps', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->morphs('reppable');
    $table->timestamps();

    $table->unique(['user_id', 'reppable_id', 'reppable_type']);
});

// negs table (reputation module)
Schema::create('negs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->morphs('neggable');
    $table->timestamps();

    $table->unique(['user_id', 'neggable_id', 'neggable_type']);
});

// thread views tracking
Schema::create('thread_views', function (Blueprint $table) {
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('thread_id')->constrained()->cascadeOnDelete();
    $table->timestamp('last_viewed_at');

    $table->primary(['user_id', 'thread_id']);
});

// polls
Schema::create('polls', function (Blueprint $table) {
    $table->id();
    $table->foreignId('thread_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->enum('type', ['single', 'multiple'])->default('single');
    $table->timestamps();
});

Schema::create('poll_options', function (Blueprint $table) {
    $table->id();
    $table->foreignId('poll_id')->constrained()->cascadeOnDelete();
    $table->string('label');
    $table->timestamps();
});

Schema::create('poll_votes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('poll_option_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->timestamps();

    $table->unique(['poll_option_id', 'user_id']);
});

// roles (authorization)
Schema::create('roles', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->json('permissions')->nullable();
    $table->timestamps();
});

Schema::create('role_user', function (Blueprint $table) {
    $table->foreignId('role_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();

    $table->primary(['role_id', 'user_id']);
});
```

### User Table Additions

```php
// Add to users table migration
$table->string('username')->unique();
$table->string('avatar_path')->nullable();
$table->string('user_title')->default('Member');
$table->unsignedInteger('post_count')->default(0);
$table->decimal('reputation', 10, 2)->default(0);
$table->boolean('is_banned')->default(false);
$table->timestamp('last_activity_at')->nullable();
```

---

## Admin Panel Requirements

### Filament Resources Needed

1. **ForumResource**
   - List/create/edit/delete forums
   - Reorder forums
   - Toggle active/restricted status

2. **ThreadResource**
   - List all threads with filters
   - Edit thread title/body
   - Lock/unlock, pin/unpin
   - Soft delete/restore
   - View reply count and activity

3. **ReplyResource**
   - List replies with thread context
   - Edit reply body
   - Soft delete/restore

4. **UserResource**
   - List users with search
   - Edit profile details
   - Assign roles
   - Ban/unban
   - View post history

5. **RoleResource**
   - Create custom roles
   - Define permissions per role

### Filament Pages

1. **Settings**
   - Forum configuration options
   - Feature toggles
   - Moderation settings

2. **Branding**
   - Logo upload
   - Site name/tagline
   - Color scheme
   - Social links

### Dashboard Widgets

- Forum statistics (total threads, replies, users)
- Recent activity feed
- Active users count
- New registrations chart

---

## Theming System

### CSS Custom Properties (`resources/css/theme.css`)

```css
:root {
    /* Brand Colors */
    --color-primary: #3b82f6;
    --color-primary-hover: #2563eb;
    --color-secondary: #6b7280;

    /* Semantic Colors */
    --color-success: #10b981;
    --color-danger: #ef4444;
    --color-warning: #f59e0b;
    --color-info: #3b82f6;

    /* Background */
    --bg-primary: #111827;
    --bg-secondary: #1f2937;
    --bg-tertiary: #374151;
    --bg-elevated: #1f2937;

    /* Text */
    --text-primary: #f3f4f6;
    --text-secondary: #d1d5db;
    --text-muted: #9ca3af;
    --text-inverse: #111827;

    /* Borders */
    --border-color: #374151;
    --border-color-light: #4b5563;

    /* Form Elements */
    --input-bg: #374151;
    --input-border: #4b5563;
    --input-focus-ring: var(--color-primary);

    /* Component Specific */
    --card-bg: var(--bg-secondary);
    --nav-bg: var(--bg-primary);
    --footer-bg: var(--bg-primary);

    /* Reputation Colors */
    --rep-positive: #10b981;
    --rep-negative: #ef4444;

    /* Shadows */
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);

    /* Spacing */
    --nav-height: 4rem;
    --container-max-width: 80rem;
}

/* Light theme override */
[data-theme="light"] {
    --bg-primary: #ffffff;
    --bg-secondary: #f3f4f6;
    --bg-tertiary: #e5e7eb;
    --text-primary: #111827;
    --text-secondary: #4b5563;
    --text-muted: #6b7280;
    --border-color: #e5e7eb;
    --input-bg: #ffffff;
    --input-border: #d1d5db;
}
```

### Tailwind Configuration

```javascript
// tailwind.config.js
module.exports = {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './app/Livewire/**/*.php',
        './app/Filament/**/*.php',
    ],
    theme: {
        extend: {
            colors: {
                primary: 'var(--color-primary)',
                secondary: 'var(--color-secondary)',
                // ... map all CSS variables
            },
            backgroundColor: {
                'theme-primary': 'var(--bg-primary)',
                'theme-secondary': 'var(--bg-secondary)',
                'theme-tertiary': 'var(--bg-tertiary)',
            },
            textColor: {
                'theme-primary': 'var(--text-primary)',
                'theme-secondary': 'var(--text-secondary)',
                'theme-muted': 'var(--text-muted)',
            },
            fontFamily: {
                sans: ['var(--font-sans)', 'system-ui', 'sans-serif'],
                heading: ['var(--font-heading)', 'system-ui', 'sans-serif'],
            },
        },
    },
    plugins: [
        require('@tailwindcss/typography'),
        require('@tailwindcss/forms'),
    ],
};
```

---

## Files Reference from OhioChatter

### Worth Studying/Adapting

| File | Purpose | Notes |
|------|---------|-------|
| `app/Reppable.php` | Reputation trait | Clean polymorphic implementation |
| `app/Models/Thread.php` | Thread model | Good relationships, slugs |
| `app/Models/Reply.php` | Reply model | Cache management |
| `app/Livewire/PostComponent.php` | Post rendering | Edit mode pattern |
| `app/Livewire/Reputation.php` | Rep/neg buttons | Simple auth checks |
| `app/Livewire/QuoteButton.php` | Quoting | Event dispatch pattern |
| `app/Services/LocationService.php` | Caching | Cache invalidation strategy |
| `config/forum.php` | Configuration | Pagination settings |

### Do Not Reference (Ohio-Specific)

- `app/Services/SeoService.php` - Hardcoded strings
- `app/Http/Controllers/ThreadController.php` - Forum exclusions
- `app/Http/Controllers/LocationController.php` - Geographic
- `app/Http/Controllers/ArchiveController.php` - VbArchive
- `resources/views/ohio/` - All geographic views
- `resources/views/buckeye/` - Game views

---

## Quick Start Commands

When ready to begin:

```bash
# Create new project
composer create-project laravel/laravel community-forum
cd community-forum

# Install PHP dependencies
composer require livewire/livewire filament/filament:^3.0 laravel/scout
composer require spatie/laravel-sitemap mews/purifier
composer require --dev pestphp/pest pestphp/pest-plugin-laravel

# Install JS dependencies
npm install -D tailwindcss postcss autoprefixer
npm install -D @tailwindcss/typography @tailwindcss/forms
npm install alpinejs

# Initialize Tailwind
npx tailwindcss init -p

# Install Filament
php artisan filament:install --panels

# Create initial admin user
php artisan make:filament-user
```

---

## Notes & Decisions to Make

### Open Questions

1. **Distribution Method**
   - Full Laravel application (clone and configure)?
   - Composer package (install into existing Laravel)?
   - Docker-ready deployment?

2. **Optional Modules to Include**
   - Messaging system (private messages)?
   - Daily puzzle game (generalized)?
   - Geographic content system (generalized)?

3. **Theming Depth**
   - Full admin theme builder?
   - Config-file only?
   - CSS override approach?

4. **Target Audience**
   - Technical users (comfortable with Laravel)?
   - Non-technical (need installer wizard)?

### Naming Ideas

- CommunityHub
- OpenForum
- SimpleForum
- TownSquare
- Agora
- Discourse Lite
- ForumKit

---

*This plan was generated by analyzing the OhioChatter codebase. Update as implementation progresses.*