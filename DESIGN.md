# OhioChatter Design System

This document defines the visual design language for OhioChatter. Follow these guidelines to maintain consistency across all pages and components.

---

## Color Palette

### Primary Colors

| Name | Tailwind Class | Hex | Usage |
|------|---------------|-----|-------|
| Accent (Blue) | `accent-500` | `#3b82f6` | Primary actions, links, active states |
| Accent Light | `accent-400` | `#60a5fa` | Hover states, highlights |
| Accent Dark | `accent-600` | `#2563eb` | Button hover, emphasis |

### Neutral Colors (Steel)

| Name | Tailwind Class | Hex | Usage |
|------|---------------|-----|-------|
| Background | `steel-950` | `#020617` | Page background (darkest) |
| Surface Dark | `steel-900` | `#0f172a` | Page background gradient |
| Surface | `steel-850` | `#172033` | Card backgrounds |
| Surface Light | `steel-800` | `#1e293b` | Card backgrounds, nav |
| Border | `steel-700` | `#334155` | Borders, dividers |
| Border Light | `steel-600` | `#475569` | Hover borders |
| Text Muted | `steel-400` | `#94a3b8` | Secondary text |
| Text | `steel-300` | `#cbd5e1` | Body text |
| Text Light | `steel-200` | `#e2e8f0` | Emphasized text |
| Text Bright | `white` | `#ffffff` | Headings, important text |

### Semantic Colors

| Purpose | Color | Usage |
|---------|-------|-------|
| Positive/Rep | `emerald-400/500` | Thumbs up, success states |
| Negative/Neg | `rose-400/500` | Thumbs down, error states |
| Warning | `amber-400` | Warnings, poll indicators |

---

## Typography

### Font Families

- **Headlines**: `font-headline` (Work Sans) - Navigation, headings, UI elements
- **Body**: `font-body` (Merriweather) - Post content, paragraphs, readable text

### Heading Sizes

```html
<h1> - text-4xl font-bold text-white
<h2> - text-3xl font-bold text-white
<h3> - text-2xl font-semibold text-steel-100
```

### Text Styles

- **Page titles**: `text-white font-bold`
- **Card titles**: `text-white font-semibold text-lg md:text-xl`
- **Body text**: `text-steel-300`
- **Muted/secondary**: `text-steel-400`
- **Links**: `text-accent-400 hover:text-accent-300`
- **Usernames**: `text-accent-400 hover:text-accent-300 font-medium`

---

## Layout

### Container

```html
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
```

### Page Background

```html
<div class="min-h-screen bg-gradient-to-br from-steel-950 via-steel-900 to-steel-950">
```

### Content Cards (Main containers)

```html
<div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-8">
```

---

## Components

### Buttons

**Primary Button** (main actions):
```html
<button class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-accent-500 to-accent-600 rounded-lg font-semibold text-white tracking-wide shadow-lg shadow-accent-500/25 hover:shadow-accent-500/40 hover:from-accent-600 hover:to-accent-700 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200">
```

**Secondary Button** (secondary actions):
```html
<button class="inline-flex items-center px-4 py-2 bg-steel-800 border border-steel-600 rounded-lg font-semibold text-sm text-steel-200 tracking-wide shadow-sm hover:bg-steel-700 hover:border-steel-500 hover:text-white hover:scale-[1.02] active:scale-[0.98] transition-all duration-200">
```

**Danger Button** (destructive actions):
```html
<button class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 rounded-lg font-semibold text-xs text-white uppercase tracking-widest shadow-lg shadow-red-500/25 hover:shadow-red-500/40 hover:from-red-600 hover:to-red-700 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200">
```

### Cards

**Thread/Content Card**:
```html
<article class="group bg-gradient-to-br from-steel-800 to-steel-850 p-4 text-steel-100 font-body rounded-xl mb-3 md:mb-5 shadow-lg shadow-black/20 border border-steel-700/50 hover:border-steel-600 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 relative overflow-hidden">
    {{-- Optional: Accent stripe on left --}}
    <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-accent-400 to-accent-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

    {{-- Content --}}
</article>
```

**Post Card**:
```html
<article class="bg-gradient-to-br from-steel-800 to-steel-850 text-white mb-5 md:flex rounded-xl relative border border-steel-700/50 shadow-xl shadow-black/20 overflow-hidden">
    {{-- Subtle top accent line --}}
    <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-steel-600/50 to-transparent"></div>

    {{-- Content --}}
</article>
```

### Navigation

**Nav Container**:
```html
<nav class="fixed top-0 left-0 right-0 z-50 bg-gradient-to-r from-steel-900 via-steel-800 to-steel-900 backdrop-blur-md border-b border-steel-700/50 shadow-lg shadow-black/20">
    {{-- Blue accent line at top --}}
    <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-transparent via-accent-500 to-transparent"></div>
</nav>
```

**Nav Link (Active)**:
```html
<a class="inline-flex items-center px-1 pt-1 border-b-2 border-accent-500 text-sm font-semibold text-white tracking-wide">
```

**Nav Link (Inactive)**:
```html
<a class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium text-steel-300 tracking-wide hover:text-white hover:border-accent-400/50 transition-all duration-200">
```

### Section Headers

Use accent pill indicator for visual hierarchy:
```html
<h2 class="font-bold text-white leading-tight flex items-center gap-3">
    <span class="hidden md:inline-block w-1 h-6 bg-accent-500 rounded-full"></span>
    Section Title
</h2>
```

### Inner Content Boxes

For nested content areas (meta info, etc.):
```html
<div class="rounded-lg bg-steel-900/50 shadow-inner divide-y md:divide-y-0 divide-steel-700/50">
```

---

## Effects & Interactions

### Shadows

| Type | Class | Usage |
|------|-------|-------|
| Default | `shadow-lg shadow-black/20` | Cards, elevated elements |
| Hover | `hover:shadow-xl` | Card hover states |
| Inner | `shadow-inner` | Inset containers |

### Transitions

Standard transition for interactive elements:
```html
transition-all duration-200
```

For cards with more movement:
```html
transition-all duration-300
```

### Hover Effects

**Cards**: Subtle lift + shadow
```html
hover:shadow-xl hover:-translate-y-0.5 hover:border-steel-600
```

**Buttons**: Scale up slightly
```html
hover:scale-[1.02] active:scale-[0.98]
```

**Links**: Color change
```html
hover:text-accent-400 hover:text-accent-300
```

**Footer links**: Slide right
```html
hover:text-white hover:pl-2 transition-all duration-200
```

### Border Radius

| Element | Class |
|---------|-------|
| Cards | `rounded-xl` |
| Buttons | `rounded-lg` |
| Containers | `rounded-2xl` (large), `rounded-lg` (medium) |
| Pills/badges | `rounded-full` |

---

## Decorative Elements

### Accent Lines

**Top of section** (gradient fade):
```html
<div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-accent-500/50 to-transparent"></div>
```

**Full accent line**:
```html
<div class="h-0.5 bg-gradient-to-r from-transparent via-accent-500 to-transparent"></div>
```

### Accent Pills

Used next to headings for visual hierarchy:
```html
<span class="w-1 h-5 bg-accent-500 rounded-full"></span>
```

---

## Avatars

Standard avatar with ring:
```html
<x-avatar size="6" :avatar-path="$user->avatar_path" class="ring-2 ring-steel-700"/>
```

Sizes: `6`, `8`, `10`, `16`, `20` (default is 28)

---

## Forms

### Text Inputs

```html
<input class="bg-steel-900 border border-steel-700 text-steel-300 rounded-lg focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 transition-all duration-200">
```

---

## Do's and Don'ts

### Do
- Use `steel-*` colors for backgrounds and borders
- Use `accent-*` colors sparingly for interactive elements and highlights
- Apply gradients subtly (`from-steel-800 to-steel-850`)
- Use consistent border radius (`rounded-xl` for cards, `rounded-lg` for buttons)
- Add transitions to all interactive elements
- Use `shadow-black/20` for shadows (not colored shadows)

### Don't
- Use colored glows or shadows (keep shadows neutral)
- Use bright colors for large areas
- Mix different accent colors (stick to blue)
- Forget hover states on interactive elements
- Use sharp corners (always round them)
- Use pure black (`#000`) - use `steel-950` instead

---

## File References

- **Tailwind Config**: `tailwind.config.js` - Color definitions
- **Global CSS**: `resources/css/app.css` - Base styles, utilities
- **Components**: `resources/views/components/` - Reusable UI components
- **Layouts**: `resources/views/layouts/` - Page layouts, nav, footer
