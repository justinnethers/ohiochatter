# OhioChatter Design System

> **For LLMs**: Read this entire file before implementing. Apply these patterns consistently to any pages or components you modify.

---

## Quick Reference - Common Patterns

### Page Structure Template

Every page should follow this structure:

```blade
<x-app-layout>
    <x-slot name="title">Page Title</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-white leading-tight flex items-center gap-3">
                <span class="hidden md:inline-block w-1 h-6 bg-accent-500 rounded-full"></span>
                Page Title
            </h2>
            {{-- Optional: Action button or back link --}}
        </div>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-8 md:mt-4">
            {{-- Page content here --}}
        </div>
    </div>
</x-app-layout>
```

### Color Rules

| Use Case | Classes |
|----------|---------|
| Page/card backgrounds | `bg-steel-800`, `bg-steel-850`, `bg-steel-900` |
| Input backgrounds | `bg-steel-950` (darkest, for inset effect) |
| Primary text | `text-white` or `text-steel-100` |
| Secondary text | `text-steel-300` |
| Muted text | `text-steel-400` |
| Links | `text-accent-400 hover:text-accent-300` |
| Borders | `border-steel-700/50` or `border-steel-600` |
| Primary actions | `bg-gradient-to-r from-accent-500 to-accent-600` |

### Replace These Old Classes

| Old (gray-based) | New (steel-based) |
|------------------|-------------------|
| `bg-gray-700` | `bg-steel-800` |
| `bg-gray-800` | `bg-steel-850` or `bg-steel-900` |
| `bg-gray-900` | `bg-steel-950` |
| `text-gray-100/200` | `text-steel-100` or `text-white` |
| `text-gray-300` | `text-steel-300` |
| `text-gray-400` | `text-steel-400` |
| `border-gray-600/700` | `border-steel-600` or `border-steel-700` |
| `text-blue-500` | `text-accent-400` |
| `hover:text-blue-*` | `hover:text-accent-300` |
| `focus:border-indigo-*` | `focus:border-accent-500` |
| `focus:ring-indigo-*` | `focus:ring-accent-500/20` |
| `dark:*` prefixes | Remove (not needed, always dark) |

---

## Components Reference

### Buttons

**Primary Button** - Use `<x-primary-button>`:
```blade
<x-primary-button>
    <svg class="w-4 h-4 mr-2" ...></svg>
    Button Text
</x-primary-button>
```

**Secondary Button** - Use `<x-secondary-button>`:
```blade
<x-secondary-button type="button" onclick="...">
    Cancel
</x-secondary-button>
```

**Danger Button** - Use `<x-danger-button>`:
```blade
<x-danger-button>Delete</x-danger-button>
```

### Form Inputs

**Text Input** - Use `<x-text-input>`:
```blade
<div>
    <x-input-label for="name" class="mb-2">Label</x-input-label>
    <x-text-input id="name" name="name" value="{{ old('name') }}" required />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>
```

**Select** - Use `<x-select>`:
```blade
<div>
    <x-input-label for="type" class="mb-2">Type</x-input-label>
    <x-select id="type" name="type">
        <option value="a">Option A</option>
        <option value="b">Option B</option>
    </x-select>
</div>
```

**Checkbox**:
```blade
<label class="inline-flex items-center text-steel-200 cursor-pointer">
    <input type="checkbox" name="option" value="1"
           class="rounded border-steel-600 bg-steel-800 text-accent-500 focus:ring-2 focus:ring-accent-500/20 focus:ring-offset-steel-900">
    <span class="ml-3 font-medium">Checkbox label</span>
</label>
```

**WYSIWYG Editor**:
```blade
<x-wysiwyg id="body" name="body" />
```

### Cards

**Content/Thread Card** (with hover effects):
```blade
<article class="group bg-gradient-to-br from-steel-800 to-steel-850 p-4 md:p-5 text-steel-100 rounded-xl mb-3 md:mb-4 shadow-lg shadow-black/20 border border-steel-700/50 hover:border-steel-600 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 relative overflow-hidden">
    {{-- Accent stripe (shows on hover) --}}
    <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-accent-400 to-accent-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

    {{-- Card content --}}
</article>
```

**Static Card** (no hover):
```blade
<div class="bg-gradient-to-br from-steel-800 to-steel-850 p-4 md:p-6 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50">
    {{-- Content --}}
</div>
```

**Post Card** - Use components:
```blade
<x-post.card>
    <x-post.owner :owner="$post->user" :username="$post->user->username" ... />
    <div class="flex-1 flex flex-col relative">
        <x-post.header :date="$post->created_at" />
        <div class="prose prose-invert prose-lg p-4 md:p-8 flex-1 post-body">
            {!! $post->body !!}
        </div>
    </div>
</x-post.card>
```

### Breadcrumbs

```blade
<x-breadcrumbs :items="[
    ['title' => 'Section', 'url' => '/section'],
    ['title' => 'Subsection', 'url' => '/section/sub'],
    ['title' => 'Current Page'],  {{-- No URL = current page (accent colored) --}}
]"/>
```

### Dropdowns

```blade
<x-dropdown align="right" width="48">
    <x-slot name="trigger">
        <button class="...">Trigger</button>
    </x-slot>
    <x-slot name="content">
        <x-dropdown-link :href="route('...')">Link 1</x-dropdown-link>
        <x-dropdown-link :href="route('...')">Link 2</x-dropdown-link>
    </x-slot>
</x-dropdown>
```

### Modals

```blade
<x-danger-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-delete')">
    Delete
</x-danger-button>

<x-modal name="confirm-delete" :show="false" focusable>
    <form method="post" action="..." class="p-6">
        @csrf
        @method('delete')

        <h2 class="text-lg font-semibold text-white">Confirm Delete</h2>
        <p class="mt-2 text-sm text-steel-400">Are you sure?</p>

        <div class="mt-6 flex justify-end gap-3">
            <x-secondary-button x-on:click="$dispatch('close')">Cancel</x-secondary-button>
            <x-danger-button>Delete</x-danger-button>
        </div>
    </form>
</x-modal>
```

### Inner Content Box

For metadata sections or nested containers:
```blade
<div class="rounded-lg bg-steel-900/50 shadow-inner p-3">
    {{-- Content --}}
</div>
```

### User Pills

For participant lists or selected users:
```blade
<div class="flex flex-wrap gap-2">
    @foreach($users as $user)
        <div class="flex items-center gap-2 bg-steel-900/50 rounded-full px-3 py-1.5 border border-steel-700/30">
            <x-avatar size="5" :avatar-path="$user->avatar_path" />
            <span class="text-sm text-steel-200">{{ $user->username }}</span>
        </div>
    @endforeach
</div>
```

### Empty States

```blade
<div class="bg-gradient-to-br from-steel-800 to-steel-850 p-6 text-steel-300 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50 text-center">
    <svg class="w-12 h-12 mx-auto mb-3 text-steel-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        {{-- Icon path --}}
    </svg>
    No items found.
</div>
```

### Back Links

```blade
<a href="{{ route('...') }}" class="text-steel-300 hover:text-white transition-colors flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
    </svg>
    Back to Section
</a>
```

### Unread/Notification Indicators

**Pulsing dot** (for unread items):
```blade
<span class="w-2 h-2 rounded-full bg-accent-500 animate-pulse"></span>
```

**Badge on avatar** (for counts):
```blade
<div class="relative">
    <x-avatar size="6" :avatar-path="$user->avatar_path"/>
    @if($unreadCount > 0)
        <span class="absolute -top-1 -right-1 h-3 w-3 rounded-full bg-red-500 ring-2 ring-steel-800"></span>
    @endif
</div>
```

---

## Typography

| Element | Classes |
|---------|---------|
| Page heading | `font-bold text-xl text-white` |
| Section heading | `text-lg font-semibold text-white` |
| Card title | `text-lg md:text-xl font-semibold text-white` |
| Body text | `text-steel-300` |
| Small/muted text | `text-sm text-steel-400` |
| Links | `text-accent-400 hover:text-accent-300 transition-colors` |
| Usernames | `text-accent-400 hover:text-accent-300 font-medium` |
| Labels | `font-semibold text-steel-200` |
| Uppercase labels | `text-sm font-semibold text-steel-400 uppercase tracking-wide` |

---

## Spacing & Layout

- Form field spacing: `space-y-6` between fields
- Card spacing: `mb-3 md:mb-4` or `space-y-4`
- Button gaps: `gap-3`
- Padding: `p-4 md:p-6` for cards, `p-2 md:p-8` for containers
- Labels above inputs: `class="mb-2"` on label

---

## Transitions

Always add transitions to interactive elements:
- Standard: `transition-colors duration-200`
- Cards: `transition-all duration-300`
- Buttons: `transition-all duration-200`

---

## Implementation Checklist

When updating a page:

1. [ ] Replace `bg-gray-*` with `bg-steel-*`
2. [ ] Replace `text-gray-*` with `text-steel-*`
3. [ ] Replace `border-gray-*` with `border-steel-*`
4. [ ] Replace `blue/indigo` focus states with `accent`
5. [ ] Remove all `dark:` prefixes
6. [ ] Use proper page structure with container
7. [ ] Add header with accent pill indicator
8. [ ] Use `<x-text-input>`, `<x-select>`, `<x-input-label>` for forms
9. [ ] Use `<x-primary-button>`, `<x-secondary-button>` for actions
10. [ ] Add hover states and transitions to interactive elements
11. [ ] Use gradient backgrounds for cards: `from-steel-800 to-steel-850`
12. [ ] Add proper shadows: `shadow-lg shadow-black/20`
13. [ ] Use rounded corners: `rounded-xl` for cards, `rounded-lg` for buttons/inputs

---

## File Locations

- **Components**: `resources/views/components/`
- **Layouts**: `resources/views/layouts/`
- **Tailwind Config**: `tailwind.config.js`
- **Global CSS**: `resources/css/app.css`
