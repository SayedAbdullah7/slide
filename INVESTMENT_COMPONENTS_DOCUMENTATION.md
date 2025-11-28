# Investment Reusable Components

## Overview
This document describes **truly reusable** Blade components that can be used anywhere in the application for displaying metrics, cards, and financial data.

## Philosophy
✅ **Keep components generic and flexible** - Can be reused across different pages
✅ **Avoid over-specialization** - Don't create components for specific use cases
✅ **Use composition** - Build complex UIs by combining simple components

## Component Structure

### 1. Base Components

#### `x-investment.summary-card`
**Location:** `resources/views/components/investment/summary-card.blade.php`

A wrapper component for creating consistent card layouts.

**Props:**
- `title` (string, default: 'Card Title') - Card header title
- `subtitle` (string, default: 'Card subtitle') - Card header subtitle
- `class` (string, default: 'col-xl-4') - Bootstrap column class

**Usage:**
```blade
<x-investment.summary-card title="My Card" subtitle="Description" class="col-xl-4">
    <!-- Card content here -->
</x-investment.summary-card>
```

---

#### `x-investment.metric-item`
**Location:** `resources/views/components/investment/metric-item.blade.php`

A reusable component for displaying a metric with icon, title, subtitle, and value/badge.

**Props:**
- `icon` (string, default: 'ki-chart-simple') - KTIcons icon class
- `iconPaths` (int, default: 2) - Number of SVG paths in the icon
- `color` (string, default: 'primary') - Bootstrap color theme
- `title` (string, default: 'Metric Title') - Main title
- `subtitle` (string, default: 'Metric description') - Subtitle/description
- `value` (string, default: '0') - Display value
- `link` (string, default: '#') - Optional link URL
- `badge` (slot, optional) - Custom badge HTML
- `isLast` (bool, default: false) - Remove bottom margin if last item
- `symbolSize` (string, default: '50px') - Icon container size
- `iconSize` (string, default: '2x') - Icon font size

**Usage:**
```blade
<x-investment.metric-item
    icon="ki-check-circle"
    :iconPaths="2"
    color="success"
    title="Approved"
    subtitle="Active investments"
    :value="100"
/>
```

---

#### `x-investment.financial-metric-item`
**Location:** `resources/views/components/investment/financial-metric-item.blade.php`

Similar to metric-item but optimized for financial data with flexible value display.

**Props:**
- `icon` (string, default: 'ki-dollar')
- `iconPaths` (int, default: 3)
- `color` (string, default: 'success')
- `title` (string, default: 'Financial Metric')
- `subtitle` (string, default: 'Description')
- `value` (string, default: '0.00')
- `badge` (string, optional) - Additional badge HTML
- `isLast` (bool, default: false)
- `symbolSize` (string, default: '40px')

**Usage:**
```blade
<x-investment.financial-metric-item
    icon="ki-dollar"
    :iconPaths="3"
    color="success"
    title="Total Invested"
    subtitle="Cumulative amount"
>
    <span class="text-gray-800 fw-bold fs-4 d-block">${{ number_format($amount, 2) }}</span>
    <span class="text-muted fw-semibold fs-7">50% of target</span>
</x-investment.financial-metric-item>
```

---

### 2. Composing Components

Instead of creating specialized cards, compose the base components together:

```blade
{{-- Create any type of card by composing base components --}}
<x-investment.summary-card title="Your Title" subtitle="Your Subtitle">
    <x-investment.metric-item
        icon="ki-check-circle"
        :iconPaths="2"
        color="success"
        title="Custom Metric"
        subtitle="Any description"
        :value="100"
    />
    
    <x-investment.financial-metric-item
        icon="ki-dollar"
        color="primary"
        title="Financial Data"
        subtitle="Any amount"
    >
        <span>${{ number_format($amount, 2) }}</span>
    </x-investment.financial-metric-item>
</x-investment.summary-card>
```

This approach is:
- ✅ More flexible
- ✅ Truly reusable across different pages
- ✅ Easier to maintain
- ✅ Avoids creating too many specialized components

---

## Complete Example

### Before (Original Code)
```blade
<div class="row g-5 g-xl-8 mb-7">
    <div class="col-xl-4">
        <div class="card card-flush h-xl-100">
            <div class="card-header pt-7">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-800">Investment Status</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">Status breakdown</span>
                </h3>
            </div>
            <div class="card-body pt-6">
                <div class="d-flex align-items-center mb-7">
                    <!-- ... 50+ lines of repetitive HTML ... -->
                </div>
            </div>
        </div>
    </div>
    <!-- ... More cards with similar structure ... -->
</div>
```

### After (Using Reusable Components)
```blade
<div class="row g-5 g-xl-8 mb-7">
    {{-- Status Card --}}
    <x-investment.summary-card title="Investment Status" subtitle="Status breakdown">
        <x-investment.metric-item
            icon="ki-check-circle" :iconPaths="2" color="success"
            title="Approved" subtitle="Active investments"
            :value="$approvedInvestments"
        />
        <x-investment.metric-item
            icon="ki-timer" :iconPaths="3" color="warning"
            title="Pending Review" subtitle="Awaiting approval"
            :value="$pendingInvestments"
        />
        <x-investment.metric-item
            icon="ki-cross-circle" :iconPaths="2" color="danger"
            title="Rejected" subtitle="Declined requests"
            :value="$rejectedInvestments" :isLast="true"
        />
    </x-investment.summary-card>

    {{-- Financial Card --}}
    <x-investment.summary-card title="Financial Overview" subtitle="Profit & returns">
        <x-investment.financial-metric-item
            icon="ki-dollar" :iconPaths="3" color="success"
            title="Total Invested" subtitle="Cumulative amount"
        >
            <span class="fw-bold fs-4">${{ number_format($totalInvested, 2) }}</span>
            <span class="text-muted fs-7">{{ $percentage }}% of target</span>
        </x-investment.financial-metric-item>
    </x-investment.summary-card>
</div>
```

## Benefits

✅ **Truly Reusable**: Only 3 generic components that work anywhere
✅ **Flexible**: Compose them in different ways for different use cases
✅ **Maintainable**: Simple components are easy to update
✅ **No Over-Engineering**: Avoided creating too many specific components
✅ **Code Reduction**: Reduced duplicated HTML significantly
✅ **Consistency**: Ensures UI consistency across the application

## File Locations

All component files are located in:
```
/resources/views/components/investment/
├── summary-card.blade.php          (Card wrapper)
├── metric-item.blade.php           (Generic metric with icon)
└── financial-metric-item.blade.php (Financial metric display)
```

**Only 3 components!** Everything else is built by composing these.

## Usage in Controllers

No changes required in controllers. All components work with the existing data structure passed from `InvestmentController::index()`.

## Icon Reference

Common KTIcons used:
- `ki-check-circle` (2 paths) - Success/Approved
- `ki-timer` (3 paths) - Pending/Waiting
- `ki-cross-circle` (2 paths) - Rejected/Error
- `ki-user` (2 paths) - User/Profile
- `ki-verify` (2 paths) - Verified/Authorized
- `ki-calculator` (2 paths) - Calculation
- `ki-dollar` (3 paths) - Money/Currency
- `ki-chart-line-up` (2 paths) - Growth/Profit
- `ki-finance-calculator` (7 paths) - Financial Calculations

## Color Themes

Available Bootstrap color themes:
- `primary` (blue)
- `success` (green)
- `warning` (yellow)
- `danger` (red)
- `info` (cyan)
- `dark` (black/dark gray)

