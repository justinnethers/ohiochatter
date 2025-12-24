# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Application Overview

OhioChatter is a Laravel 10 forum application focused on Ohio communities with three main features:
1. **Forum System** - Traditional threaded discussions with reputation system
2. **Geographic Content System** - Hierarchical Ohio location guides (Region > County > City)
3. **BuckEYE Game** - Daily puzzle game with pixelated Ohio-themed images

## Architecture Specifics

### Modular Structure
Uses `app/Modules/` for feature organization:
- `BuckEYE/` - Puzzle game with Livewire components, models, and services
- `Messages/` - Private messaging system using cmgmyr/messenger package
- `VbArchive/` - Legacy vBulletin data integration

### Geographic Hierarchy
Three-tier location system with content association:
- `Region` (e.g., "Central Ohio") 
- `County` (belongs to Region)
- `City` (belongs to County)
- `Content` can be associated with any geographic level and categorized

Routes follow pattern: `/ohio/{region}/{county}/{city}` and `/ohio/guide/{region}/{county}/{city}/category/{category}`

### Forum System Features
- **Reputation System**: Users can give "reps" (positive) or "negs" (negative) via `Rep`/`Neg` models using `Reppable` trait
- **Polls**: Optional polls attached to threads via `Poll`, `PollOption`, `PollVote` models
- **Search**: Laravel Scout integration with Algolia
- **SEO**: Automatic slug generation, sitemap generation, meta tags

### BuckEYE Game Mechanics
Daily puzzle game located in `app/Services/PuzzleService.php`:
- 5 pixelation levels (most to least pixelated)
- Maximum 5 guesses per puzzle
- Tracks both authenticated (`UserGameProgress`) and anonymous (`AnonymousGameProgress`) players
- Social sharing with dynamic image generation
- Admin panel via Filament for puzzle management

### Key Livewire Components
- `BuckEyeGame` - Main puzzle interface with guess submission
- `PostComponent` - Renders forum posts with rep/neg buttons
- `QuoteButton` - Handles post quoting functionality
- `Reputation` - Manages rep/neg voting
- `ActiveUsers` - Shows real-time user activity

### Legacy Integration
Archive system handles old vBulletin URLs:
- `/forum/showthread` redirects parse thread IDs from query strings
- `VbThread`, `VbPost`, `VbUser` models for legacy data
- Archive views preserve old content structure

### Testing Setup
Uses Pest as primary testing framework with feature tests covering:
- Authentication flows
- Thread/Reply CRUD operations
- Livewire component interactions
- BuckEYE game mechanics

### Asset Pipeline
- Tailwind CSS with custom Ohio-themed styling
- Alpine.js for interactive elements
- Image processing for puzzle pixelation stored in `storage/app/pixelated/`
- Prettier formatting for blade templates and JS files