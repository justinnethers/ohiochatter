# GUIDE.md - Ohio Guide System Documentation

This document provides comprehensive documentation of the Ohio Guide System for LLM use when working with guide-related code.

## System Overview

The Ohio Guide System is a hierarchical content management system for location-based guides about Ohio. Users can create guides (articles, lists, reviews) associated with geographic locations (Region > County > City) and categories.

### Core Concepts

1. **Geographic Hierarchy**: Region → County → City (polymorphic `locatable` relationship)
2. **Block-Based Content**: Guides use a flexible block system (text, image, video, carousel, list)
3. **Nested Blocks**: List items can contain nested blocks, including nested lists
4. **Multi-Category Support**: Content belongs to multiple categories via pivot table
5. **Draft System**: Users save drafts before publishing
6. **Publishing Workflow**: Draft → Pending Review → Published

---

## Database Schema

### Geographic Tables

```
regions
├── id
├── name
├── slug (route key)
├── description (nullable)
├── meta_title (nullable)
├── meta_description (nullable)
├── display_order (default: 0)
└── timestamps

counties
├── id
├── region_id (FK → regions)
├── name
├── slug (route key)
├── description (nullable)
├── demographics (JSON, nullable)
├── county_seat (nullable)
├── founded_year (nullable)
└── timestamps

cities
├── id
├── county_id (FK → counties)
├── name
├── slug (route key)
├── description (nullable)
├── is_major (boolean, default: false)
├── latitude (nullable)
├── longitude (nullable)
├── population (nullable)
├── demographics (JSON, nullable)
├── incorporated_year (nullable)
└── timestamps
```

### Content Tables

```
content
├── id
├── locatable_type (Region|County|City)
├── locatable_id
├── user_id (FK → users, author)
├── type_id (FK → content_types, nullable)
├── title
├── slug
├── excerpt (nullable)
├── blocks (JSON) - array of content blocks
├── featured_image (nullable)
├── gallery (JSON, nullable)
├── metadata (JSON, nullable) - guide-level fields (rating, website, address)
├── meta_title (nullable)
├── meta_description (nullable)
├── featured (boolean, default: false)
├── published_at (nullable)
├── timestamps
└── deleted_at (soft deletes)

content_categories
├── id
├── parent_id (FK → content_categories, nullable)
├── name
├── slug (route key)
├── description (nullable)
├── icon (nullable)
├── display_order (default: 0)
├── is_active (boolean, default: true)
└── timestamps

content_content_category (pivot)
├── id
├── content_id (FK → content)
├── content_category_id (FK → content_categories)
└── timestamps

content_types
├── id
├── name
├── slug
├── description (nullable)
├── required_fields (JSON)
├── optional_fields (JSON)
├── is_active (boolean, default: true)
└── timestamps

related_content (pivot)
├── content_id (FK → content)
├── related_content_id (FK → content)
├── weight (default: 0)
└── timestamps

guide_drafts
├── id
├── user_id (FK → users)
├── locatable_type (nullable)
├── locatable_id (nullable)
├── title
├── excerpt (nullable)
├── blocks (JSON) - array of content blocks
├── category_ids (JSON array)
├── featured_image (nullable)
├── gallery (JSON array)
├── guide_rating (nullable, 1-5)
├── guide_website (nullable)
├── guide_address (nullable)
└── timestamps
```

---

## Block System

The guide system uses a flexible block-based content structure. Each block has:
- `id` - UUID for tracking
- `type` - Block type (text, image, video, carousel, list)
- `data` - Type-specific content
- `order` - Display order

### Block Types

#### Text Block
```json
{
  "id": "uuid",
  "type": "text",
  "data": {
    "content": "Markdown or plain text content"
  },
  "order": 0
}
```

#### Image Block
```json
{
  "id": "uuid",
  "type": "image",
  "data": {
    "path": "guides/blocks/image.jpg",
    "caption": "Optional caption"
  },
  "order": 1
}
```

#### Video Block
```json
{
  "id": "uuid",
  "type": "video",
  "data": {
    "url": "https://youtube.com/watch?v=...",
    "caption": "Optional caption"
  },
  "order": 2
}
```

#### Carousel Block
```json
{
  "id": "uuid",
  "type": "carousel",
  "data": {
    "images": [
      {"path": "guides/blocks/img1.jpg", "alt": ""},
      {"path": "guides/blocks/img2.jpg", "alt": ""}
    ]
  },
  "order": 3
}
```

#### List Block
```json
{
  "id": "uuid",
  "type": "list",
  "data": {
    "title": "Top 10 Restaurants",
    "ranked": true,
    "countdown": false,
    "items": [
      {
        "id": "uuid",
        "title": "Restaurant Name",
        "description": "Description text",
        "website": "https://example.com",
        "address": "123 Main St",
        "rating": 5,
        "image": "guides/blocks/item.jpg",
        "blocks": []  // Nested blocks
      }
    ]
  },
  "order": 4
}
```

### Nested Blocks

List items can contain nested blocks of any type, including nested lists. Nested list items have full feature parity with top-level list items:
- Title, description
- Website, address
- Rating (1-5 stars)
- Image upload

---

## Component Architecture

Block rendering uses a modular component system to reduce code duplication.

### Block Renderer
**File:** `resources/views/components/blocks/renderer.blade.php`

Central dispatcher that routes to type-specific components:
```blade
<x-blocks.renderer :blocks="$blocks" mode="view" :nested="false" />
```

Props:
- `blocks` - Array of block data
- `mode` - "view" or "edit"
- `nested` - Whether rendering nested within a list item

### View Components
**Location:** `resources/views/components/blocks/view/`

- `text.blade.php` - Renders text/markdown content
- `image.blade.php` - Renders image with caption
- `video.blade.php` - Embeds YouTube/video
- `carousel.blade.php` - Image carousel with Alpine.js
- `list.blade.php` - Delegates to guide-list component

### Edit Components
**Location:** `resources/views/components/blocks/edit/`

- `text.blade.php` - Textarea input
- `image.blade.php` - File upload with preview
- `video.blade.php` - URL input
- `carousel.blade.php` - Multi-file upload

### Guide List Component
**File:** `resources/views/components/guide-list.blade.php`

Renders list items with cards, supporting:
- Ranked/unranked display
- Countdown mode
- Images, ratings, addresses
- Nested blocks via recursive renderer call
- Scaled-down styling when `nested=true`

---

## Models

### Location Models

**Region** (`app/Models/Region.php`)
```php
// Relationships
hasMany(County::class)
hasManyThrough(City::class, County::class)
morphMany(Content::class, 'locatable')

// Route key: slug
// Uses: Searchable trait
```

**County** (`app/Models/County.php`)
```php
// Relationships
belongsTo(Region::class)
hasMany(City::class)
morphMany(Content::class, 'locatable')

// Route key: slug
// Casts: demographics → array
```

**City** (`app/Models/City.php`)
```php
// Relationships
belongsTo(County::class)
morphMany(Content::class, 'locatable')

// Route key: slug
// Casts: demographics → array
```

### Content Models

**Content** (`app/Models/Content.php`)
```php
// Relationships
morphTo('locatable')  // Region, County, or City
belongsTo(User::class, 'user_id')
belongsTo(ContentType::class, 'type_id')
belongsToMany(ContentCategory::class, 'content_content_category')
belongsToMany(Content::class, 'related_content', 'content_id', 'related_content_id')

// Scopes
scopeFeatured($query)    // where('featured', true)
scopePublished($query)   // whereNotNull('published_at')

// Casts
blocks → array
gallery → array
metadata → array
published_at → datetime

// Uses: SoftDeletes, Searchable
```

**ContentCategory** (`app/Models/ContentCategory.php`)
```php
// Relationships
belongsTo(ContentCategory::class, 'parent_id')  // parent
hasMany(ContentCategory::class, 'parent_id')    // children
belongsToMany(Content::class, 'content_content_category')

// Methods
ancestors()           // Get all parent categories
getPathAttribute()    // Full category path (e.g., "Food & Drink > Restaurants")
descendants()         // Get all child categories recursively
allContent()          // Get content from this category and all descendants

// Scopes
scopeRoot($query)     // whereNull('parent_id')
scopeActive($query)   // where('is_active', true)
scopeOrdered($query)  // orderBy('display_order')

// Route key: slug
```

**GuideDraft** (`app/Models/GuideDraft.php`)
```php
// Relationships
belongsTo(User::class)
morphTo('locatable')

// Casts
blocks → array
gallery → array
category_ids → array
```

---

## Routes

**File:** `app/Modules/Geography/routes.php`

### Public Routes
```
GET  /ohio/guide                                    → ContentController@index
GET  /ohio/guide/categories                         → ContentController@categories
GET  /ohio/guide/category/{category:slug}           → ContentController@category
GET  /ohio/guide/article/{content}                  → ContentController@show
GET  /ohio/guide/{region}                           → ContentController@region
GET  /ohio/guide/{region}/{county}                  → ContentController@county
GET  /ohio/guide/{region}/{county}/{city}           → ContentController@city
GET  /ohio/guide/{region}/category/{category:slug}  → ContentController@regionCategory
GET  /ohio/guide/{region}/{county}/category/{category:slug}      → ContentController@countyCategory
GET  /ohio/guide/{region}/{county}/{city}/category/{category:slug} → ContentController@cityCategory
```

### Authenticated Routes
```
GET  /ohio/guide/create                             → guides.create view + CreateGuide Livewire
GET  /ohio/guide/my-guides                          → guides.my-guides view + MyGuides Livewire
GET  /ohio/guide/edit/{draft}                       → guides.create view + CreateGuide Livewire (with draftId)
```

---

## Livewire Components

### CreateGuide (`app/Livewire/CreateGuide.php`)

Main guide creation/editing component with block-based editor.

**Properties:**
```php
$draftId           // Editing existing draft
$title             // Guide title
$excerpt           // Short description
$categoryIds       // Array of selected category IDs
$locatableType     // 'region', 'county', or 'city'
$locatableId       // ID of selected location

// Guide-level metadata
$guideRating       // 1-5 star rating
$guideWebsite      // URL
$guideAddress      // Physical address

// File uploads
$featuredImage     // UploadedFile
$gallery           // Array of UploadedFiles
$blockImages       // Temporary uploads for block images
$nestedBlockImages // Temporary uploads for nested block images

// Block system
$blocks            // Array of block data
```

**Block Methods:**
```php
addBlock($type)                    // Add new block (text, image, video, carousel, list)
removeBlock($index)                // Remove block
moveBlockUp($index)                // Reorder
moveBlockDown($index)              // Reorder
reorderBlocks($orderedIds)         // Drag-drop reorder
toggleBlockExpanded($index)        // Expand/collapse in editor

// List block methods
addListItemToBlock($blockIndex)
removeListItemFromBlock($blockIndex, $itemIndex)
reorderListItemsInBlock($blockIndex, $orderedIds)
toggleListItemInBlock($blockIndex, $itemIndex)
setListItemRatingInBlock($blockIndex, $itemIndex, $rating)

// Nested block methods (blocks within list items)
addBlockToListItem($blockIndex, $itemIndex, $type)
removeBlockFromListItem($blockIndex, $itemIndex, $nestedBlockIndex)
getNestedBlockImageKey($blockIndex, $itemIndex, $nestedBlockIndex)
removeNestedBlockImage($blockIndex, $itemIndex, $nestedBlockIndex)

// Nested list methods (list blocks within list items)
addNestedListItem($blockIndex, $itemIndex, $nestedBlockIndex)
removeNestedListItem($blockIndex, $itemIndex, $nestedBlockIndex, $nestedItemIndex)
toggleNestedListItem($blockIndex, $itemIndex, $nestedBlockIndex, $nestedItemIndex)
setNestedListItemRating($blockIndex, $itemIndex, $nestedBlockIndex, $nestedItemIndex, $rating)
removeNestedListItemImage($blockIndex, $itemIndex, $nestedBlockIndex, $nestedItemIndex)
```

**Key Methods:**
```php
mount($draftId = null)      // Initialize, load draft if editing
loadDraft()                 // Populate form from GuideDraft
saveDraft()                 // Save without publishing
submit()                    // Validate and publish
generateAiSummary()         // Auto-generate excerpt via OpenAI
togglePreview()             // Toggle preview mode
processBlocksForSave()      // Process blocks and handle image uploads
```

**View:** `resources/views/livewire/create-guide.blade.php`

**Partials:**
- `resources/views/livewire/partials/block-list.blade.php` - List block editor
- `resources/views/livewire/partials/nested-list-block.blade.php` - Nested list editor
- `resources/views/livewire/partials/guide-preview.blade.php` - Preview mode

### LocationPicker (`app/Livewire/LocationPicker.php`)

Hierarchical location selector (Region > County > City).

**Properties:**
```php
$regionId, $countyId, $cityId
$regions, $counties, $cities  // Available options
$initialLocatable             // Pre-selected location (when editing)
```

**Dispatches:** `locationSelected` with type and ID

### CategoryPicker (`app/Livewire/CategoryPicker.php`)

Horizontal tabbed category picker with color-coded parent categories.

**Properties:**
```php
$selectedCategoryIds    // Array of selected IDs
$activeTab              // Currently active parent tab
```

**Methods:**
```php
setActiveTab($parentId)    // Switch parent tab
toggleCategory($id)        // Select/deselect category
getCategoryColor($name)    // Get color classes for parent category
```

**Color Scheme:**
- Food & Drink: amber
- Outdoors & Nature: emerald
- Arts & Culture: violet
- Entertainment: rose
- Shopping: sky
- Family: cyan

**Dispatches:** `categoriesSelected` with array of IDs

---

## Categories Reference

Root categories with subcategories (from seeder):

```
Food & Drink (amber)
├── Restaurants
├── Breweries & Distilleries
├── Wineries
├── Distilleries
├── Bars & Pubs
├── Coffee & Cafes
├── Bakeries & Desserts
├── Food Trucks
└── Ice Cream

Outdoors & Nature (emerald)
├── Hiking Trails
├── Bike Trails
├── Parks
├── Camping
├── Lakes & Rivers
├── Kayaking
├── Scenic Drives
├── Fishing Spots
├── Waterfalls
├── Gardens
├── Beaches
├── Golf
└── Caves

Arts & Culture (violet)
├── Museums
├── Theaters
├── Art Galleries
├── Historic Sites
├── Architecture
└── Street Art

Entertainment (rose)
├── Sports
├── Live Music & Concerts
├── Live Music
├── Festivals & Events
├── Nightlife
├── Amusement & Theme Parks
├── Casinos
├── Escape Rooms
├── Bowling
└── Arcades

Shopping (sky)
├── Antiques & Vintage
├── Farmers Markets
├── Shopping Centers
└── Local Boutiques

Family Activities (cyan)
├── Kid-Friendly Attractions
├── Playgrounds
├── Zoos & Aquariums
├── Educational Activities
└── Farms
```

---

## Views

### Guide Creation/Management
```
resources/views/guides/
├── create.blade.php           # Wrapper for CreateGuide component
└── my-guides.blade.php        # Wrapper for MyGuides component

resources/views/livewire/
├── create-guide.blade.php     # Full creation form with block editor
├── my-guides.blade.php        # Tabbed dashboard
├── location-picker.blade.php  # Location selector
├── category-picker.blade.php  # Tabbed category multi-select
└── partials/
    ├── block-list.blade.php         # List block editor
    ├── nested-list-block.blade.php  # Nested list editor
    └── guide-preview.blade.php      # Preview mode
```

### Guide Display
```
resources/views/ohio/guide/
├── index.blade.php            # Main guide landing
├── show.blade.php             # Single guide display (uses blocks.renderer)
├── categories.blade.php       # All categories
├── category.blade.php         # Content by category
├── region.blade.php           # Region overview
├── county.blade.php           # County overview
├── city.blade.php             # City overview
├── region-category.blade.php  # Region + category filter
├── county-category.blade.php  # County + category filter
└── city-category.blade.php    # City + category filter
```

### Block Components
```
resources/views/components/blocks/
├── renderer.blade.php         # Central block dispatcher
├── view/
│   ├── text.blade.php
│   ├── image.blade.php
│   ├── video.blade.php
│   ├── carousel.blade.php
│   └── list.blade.php
└── edit/
    ├── text.blade.php
    ├── image.blade.php
    ├── video.blade.php
    └── carousel.blade.php

resources/views/components/
├── guide/card.blade.php       # Reusable guide card
└── guide-list.blade.php       # List rendering with nested support
```

---

## Testing

**Test file:** `tests/Feature/Livewire/CreateGuideTest.php`

Key test scenarios:
- Route authentication requirements
- Draft saving (minimal and complete data)
- Draft loading and updating
- Form validation
- Image uploads
- Block system operations (add, remove, reorder)
- List block items (add, remove, reorder, rating)
- Nested blocks within list items
- Nested list items with full feature parity
- AI summary generation
- Multi-category support

Run tests:
```bash
php artisan test --filter=CreateGuideTest
```

---

## Common Tasks

### Create a new guide programmatically
```php
use App\Modules\Geography\Actions\Content\CreateContent;
use App\Modules\Geography\DTOs\CreateContentData;

$data = CreateContentData::fromArray([
    'title' => 'Best Restaurants in Columbus',
    'blocks' => [
        [
            'id' => Str::uuid()->toString(),
            'type' => 'text',
            'data' => ['content' => 'Introduction text...'],
            'order' => 0,
        ],
        [
            'id' => Str::uuid()->toString(),
            'type' => 'list',
            'data' => [
                'title' => 'Top 10 Restaurants',
                'ranked' => true,
                'countdown' => false,
                'items' => [
                    [
                        'id' => Str::uuid()->toString(),
                        'title' => 'Restaurant Name',
                        'description' => 'Amazing food...',
                        'rating' => 5,
                        'website' => 'https://example.com',
                        'address' => '123 Main St',
                        'blocks' => [],
                    ],
                ],
            ],
            'order' => 1,
        ],
    ],
    'locatableType' => 'city',
    'locatableId' => $cityId,
    'categoryIds' => [1, 5],
    'excerpt' => 'A guide to...',
    'publishedAt' => now(),
]);

$content = (new CreateContent)->execute($data);
```

### Render blocks in a view
```blade
{{-- In a Blade template --}}
<x-blocks.renderer :blocks="$content->blocks" mode="view" />

{{-- For nested blocks (smaller styling) --}}
<x-blocks.renderer :blocks="$item['blocks']" mode="view" :nested="true" />
```

### Fetch content for a location
```php
use App\Modules\Geography\Queries\FetchLocationContent;

$content = (new FetchLocationContent)->execute($city);
// or with category filter
$content = (new FetchLocationContent)->execute($city, $category);
```

### Get hierarchical categories
```php
$rootCategories = ContentCategory::root()->active()->ordered()->with('children')->get();
```
