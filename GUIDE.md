# GUIDE.md - Ohio Guide System Documentation

This document provides comprehensive documentation of the Ohio Guide System for LLM use when working with guide-related code.

## System Overview

The Ohio Guide System is a hierarchical content management system for location-based guides about Ohio. Users can create guides (articles, lists, reviews) associated with geographic locations (Region > County > City) and categories.

### Core Concepts

1. **Geographic Hierarchy**: Region → County → City (polymorphic `locatable` relationship)
2. **Content Types**: Articles, lists, guides with different metadata schemas
3. **Multi-Category Support**: Content belongs to multiple categories via pivot table
4. **Draft System**: Users save drafts before publishing
5. **Publishing Workflow**: Draft → Pending Review → Published

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
├── body
├── featured_image (nullable)
├── gallery (JSON, nullable)
├── metadata (JSON, nullable) - includes list_items, list_settings
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
├── body (nullable)
├── category_ids (JSON array)
├── featured_image (nullable)
├── gallery (JSON array)
├── list_items (JSON array)
├── list_settings (JSON)
└── timestamps
```

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

**ContentType** (`app/Models/ContentType.php`)
```php
// Relationships
hasMany(Content::class, 'type_id')

// Methods
validateMetadata(array $metadata)  // Validate against required_fields

// Scopes
scopeActive($query)

// Casts
required_fields → array
optional_fields → array
```

**GuideDraft** (`app/Models/GuideDraft.php`)
```php
// Relationships
belongsTo(User::class)
morphTo('locatable')

// Casts
gallery → array
list_items → array
list_settings → array
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

## Controllers

**ContentController** (`app/Modules/Geography/Http/Controllers/ContentController.php`)

Key methods:
- `index()` - Main guide landing page with featured/recent content
- `categories()` - Browse all categories
- `category($category)` - Content filtered by category
- `show($content)` - Display single guide with related content
- `region($region)` - Region guide overview with child content
- `county($region, $county)` - County guide overview
- `city($region, $county, $city)` - City guide overview
- `*Category()` methods - Location filtered by category

Uses these services:
- `GeographySeoService` - SEO metadata
- `FetchLocationHierarchy` - Validate location chain
- `FetchLocationContent` - Get content for location + children
- `FetchCategoriesForLocation` - Categories with content in location

---

## Livewire Components

### CreateGuide (`app/Livewire/CreateGuide.php`)

Main guide creation/editing component.

**Properties:**
```php
$draftId           // Editing existing draft
$title             // Guide title
$excerpt           // Short description
$body              // Main content (rich text)
$categoryIds       // Array of selected category IDs
$locatableType     // 'region', 'county', or 'city'
$locatableId       // ID of selected location
$featuredImage     // UploadedFile
$gallery           // Array of UploadedFiles

// List builder
$listEnabled       // Toggle list mode
$listIsRanked      // Numbered vs bullets
$listTitle         // List heading
$listCountdown     // Reverse order (10 to 1)
$listItems         // Array of list item data
$listItemImages    // Temporary image uploads for list items
```

**Key Methods:**
```php
mount($draftId = null)      // Initialize, load draft if editing
loadDraft()                 // Populate form from GuideDraft
saveDraft()                 // Save without publishing
submit()                    // Validate and publish
generateAiSummary()         // Auto-generate excerpt via OpenAI
addListItem()               // Add new list item
removeListItem($index)      // Remove list item
reorderListItems($order)    // Reorder via drag-drop
```

**Listens to:**
- `locationSelected` from LocationPicker
- `categoriesSelected` from CategoryPicker
- `reorderListItems` from JS drag-drop

**View:** `resources/views/livewire/create-guide.blade.php`

### MyGuides (`app/Livewire/MyGuides.php`)

User's guide dashboard with tabs.

**Properties:**
```php
$activeTab         // 'drafts', 'pending', or 'published'
$drafts            // Collection of GuideDraft
$pendingContent    // Collection of unpublished Content
$publishedContent  // Collection of published Content
```

**Methods:**
```php
loadContent()              // Fetch user's content
setTab($tab)               // Switch tabs
deleteDraft($draftId)      // Remove draft
```

**View:** `resources/views/livewire/my-guides.blade.php`

### LocationPicker (`app/Livewire/LocationPicker.php`)

Hierarchical location selector (Region > County > City).

**Properties:**
```php
$regionId, $countyId, $cityId
$regions, $counties, $cities  // Available options
$initialLocatable             // Pre-selected location (when editing)
```

**Computed:**
```php
selectedLocationProperty      // Returns ['type' => 'city', 'id' => 123]
```

**Dispatches:** `locationSelected` with type and ID

**View:** `resources/views/livewire/location-picker.blade.php`

### CategoryPicker (`app/Livewire/CategoryPicker.php`)

Multi-select hierarchical category picker.

**Properties:**
```php
$selectedCategoryIds    // Array of selected IDs
$expandedParents        // Expanded accordion sections
```

**Methods:**
```php
toggleParent($id)       // Expand/collapse parent
toggleCategory($id)     // Select/deselect category
```

**Dispatches:** `categoriesSelected` with array of IDs

**View:** `resources/views/livewire/category-picker.blade.php`

---

## Services & Actions

### Actions (Domain Logic)

**Location:** `app/Modules/Geography/Actions/Content/`

```php
CreateContent::execute(CreateContentData $data): Content
// Creates content, syncs categories, fires ContentCreated event

PublishContent::execute(Content $content): Content
// Sets published_at, fires ContentPublished event

UpdateContent::execute(Content $content, UpdateContentData $data): Content
// Updates content and syncs categories

DeleteContent::execute(Content $content): void
// Soft deletes content

FeatureContent::execute(Content $content, bool $featured): Content
// Toggles featured flag, fires ContentFeatured event
```

### DTOs

**CreateContentData** (`app/Modules/Geography/DTOs/CreateContentData.php`)
```php
class CreateContentData {
    public ?int $contentTypeId;
    public array $categoryIds;
    public string $title;
    public string $body;
    public string $locatableType;
    public int $locatableId;
    public ?string $slug;
    public ?string $excerpt;
    public ?array $metadata;
    public ?string $featuredImage;
    public ?array $gallery;
    public ?string $metaTitle;
    public ?string $metaDescription;
    public bool $featured;
    public ?Carbon $publishedAt;

    public static function fromArray(array $data): self;
    public function toArray(): array;
}
```

### Query Objects

**Location:** `app/Modules/Geography/Queries/`

```php
FetchLocationHierarchy::execute($region, $county = null, $city = null)
// Validates location chain, returns ['region' => ..., 'county' => ..., 'city' => ...]

FetchLocationContent::execute($location, $category = null)
// Gets content for location and child locations

FetchCategoriesForLocation::execute($location)
// Gets categories with published content in location

FetchFeaturedContent::execute($limit = 6)
// Gets featured published content
```

### Services

**GeographySeoService** (`app/Modules/Geography/Services/GeographySeoService.php`)
```php
forGuideIndex(): array           // SEO for /ohio/guide
forRegion($region): array        // SEO for region page
forCounty($region, $county): array
forCity($region, $county, $city): array
forCategory($category, $location = null): array
forContent($content): array      // SEO for individual guide
```

**ContentAIService** (`app/Services/ContentAIService.php`)
```php
generateSummary(string $title, string $body, ?array $listItems = null): ?string
// Uses OpenAI GPT-4o-mini to generate excerpt
```

---

## Views

### Guide Creation/Management
```
resources/views/guides/
├── create.blade.php           # Wrapper for CreateGuide component
└── my-guides.blade.php        # Wrapper for MyGuides component

resources/views/livewire/
├── create-guide.blade.php     # Full creation form
├── my-guides.blade.php        # Tabbed dashboard
├── location-picker.blade.php  # Location selector
└── category-picker.blade.php  # Category multi-select
```

### Guide Display
```
resources/views/ohio/guide/
├── index.blade.php            # Main guide landing
├── show.blade.php             # Single guide display
├── categories.blade.php       # All categories
├── category.blade.php         # Content by category
├── region.blade.php           # Region overview
├── county.blade.php           # County overview
├── city.blade.php             # City overview
├── region-category.blade.php  # Region + category filter
├── county-category.blade.php  # County + category filter
└── city-category.blade.php    # City + category filter
```

### Components
```
resources/views/components/
├── guide/card.blade.php       # Reusable guide card
└── guide-list.blade.php       # List of guide cards
```

---

## Key Features

### List Builder

Guides can include structured lists with:
- Ranked (numbered) or unranked (bullet) display
- Countdown mode (10 to 1)
- Per-item fields: title, description, image, rating (1-5), address, website

**Storage:** List data stored in `Content.metadata` as:
```json
{
  "list_items": [
    {
      "title": "Item Name",
      "description": "Description text",
      "image": "path/to/image.jpg",
      "rating": 4,
      "address": "123 Main St",
      "website": "https://example.com"
    }
  ],
  "list_settings": {
    "enabled": true,
    "is_ranked": true,
    "title": "Top 10 Places",
    "countdown": false
  }
}
```

### Multi-Category Support

Content uses a pivot table (`content_content_category`) for many-to-many category relationships.

```php
// Assigning categories
$content->contentCategories()->sync($categoryIds);

// Getting categories
$content->contentCategories; // Collection of ContentCategory
```

### Publishing Workflow

1. **Draft** - Saved in `guide_drafts` table, only visible to author
2. **Submitted** - Content created but `published_at` is null (pending review)
3. **Published** - `published_at` is set, visible to all users

```php
// Check status
$content->published_at === null  // Draft/Pending
$content->published_at !== null  // Published

// Publish
(new PublishContent)->execute($content);
```

### Content-Location Inheritance

When displaying a region, the system shows:
- Content directly associated with the region
- Content from all counties in the region
- Content from all cities in those counties

This is handled by `FetchLocationContent` query class.

---

## Categories Reference

Root categories (from seeder):
```
Food & Drink
├── Restaurants
├── Breweries & Distilleries
├── Bars & Pubs
├── Coffee & Cafes
└── Bakeries & Desserts

Outdoors & Nature
├── Hiking Trails
├── Parks
├── Camping
├── Lakes & Rivers
├── Scenic Drives
└── Fishing Spots

Arts & Culture
├── Museums
├── Theaters
├── Art Galleries
├── Historic Sites
└── Architecture

Entertainment
├── Sports
├── Live Music & Concerts
├── Festivals & Events
├── Nightlife
└── Amusement & Theme Parks

Shopping
├── Antiques & Vintage
├── Farmers Markets
├── Shopping Centers
└── Local Boutiques

Family Activities
├── Kid-Friendly Attractions
├── Playgrounds
├── Zoos & Aquariums
└── Educational Activities
```

---

## Filament Admin

**ContentResource** (`app/Filament/Resources/ContentResource.php`)

Admin panel for managing guides with:
- Form: title, categories (multi-select), type, excerpt, body (rich editor)
- Publishing controls: featured toggle, published_at datetime
- SEO fields: meta_title, meta_description
- Table with status badges, filtering, bulk actions
- Actions: publish, feature, delete

---

## Testing

**Test file:** `tests/Feature/Livewire/CreateGuideTest.php`

Key test scenarios:
- Route authentication requirements
- Draft saving (minimal and complete data)
- Draft loading and updating
- Form validation
- Image uploads
- List builder operations
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
    'body' => '<p>Content here...</p>',
    'locatableType' => 'city',
    'locatableId' => $cityId,
    'categoryIds' => [1, 5], // Restaurant, Local Favorites
    'excerpt' => 'A guide to...',
    'publishedAt' => now(),
]);

$content = (new CreateContent)->execute($data);
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

### Resolve location from slugs
```php
use App\Modules\Geography\Queries\FetchLocationHierarchy;

$hierarchy = (new FetchLocationHierarchy)->execute('central-ohio', 'franklin', 'columbus');
// Returns: ['region' => Region, 'county' => County, 'city' => City]
```