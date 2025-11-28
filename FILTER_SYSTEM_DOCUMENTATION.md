# Reusable Filter System Documentation

## Overview

The enhanced Filter system allows you to create reusable filters for any DataTable with just **one line** in the `filters()` method. Filters are automatically:
- ✅ Rendered in the frontend filter menu
- ✅ Applied to backend queries
- ✅ Sent via AJAX to the server
- ✅ Reset with the reset button

## Quick Start

### Basic Usage

In any DataTable class, just define your filters:

```php
use App\Helpers\Filter;

class MyDataTable extends BaseDataTable
{
    public function filters(): array
    {
        return [
            'status' => Filter::select('Status', ['1' => 'Active', '0' => 'Inactive']),
            'created_at' => Filter::date('Created Date'),
            'email' => Filter::text('Email'),
        ];
    }
    
    public function handle()
    {
        $query = Model::query();
        
        return DataTables::of($query)
            // ... columns
            ->filter(function ($query) {
                $this->applySearch($query);
                $this->applyFilters($query); // 👈 Just add this line!
            })
            ->make(true);
    }
}
```

**That's it!** The filter automatically appears in the frontend and filters data on the backend.

## Available Filter Types

### 1. Select Filter (Dropdown)

```php
'status' => Filter::select('Status', [
    '1' => 'Active',
    '0' => 'Inactive',
    '2' => 'Pending'
])
```

**Backend:** Exact match (`WHERE column = value`)

---

### 2. Boolean Filter

```php
'is_registered' => Filter::boolean('Is Registered')
```

**Options:** Yes (1) / No (0)  
**Backend:** Exact match (`WHERE column = 1/0`)

---

### 3. Date Filter

```php
// Basic date
'created_at' => Filter::date('Created Date')

// With min/max dates
'created_at' => Filter::date('Created Date', 'today', '2024-01-01')

// Using 'today' keyword
'created_at' => Filter::date('Created Date', 'today')
```

**Backend:** Date match (`WHERE DATE(column) = date`)

---

### 4. Date Range Filter

```php
'created_at' => Filter::dateRange('Created Date Range', 'today')
```

**Frontend:** Shows "From" and "To" date inputs  
**Backend:** Date range (`WHERE DATE(column) >= from AND DATE(column) <= to`)

---

### 5. Text Filter

```php
// Basic text
'email' => Filter::text('Email')

// With placeholder
'email' => Filter::text('Email', 'Enter email address...')
```

**Backend:** LIKE search (`WHERE LOWER(column) LIKE '%value%'`)

---

### 6. Number Filter

```php
// Basic number
'id' => Filter::number('User ID')

// With min/max
'age' => Filter::number('Age', 18, 100)
```

**Backend:** Exact match (`WHERE column = value`)

---

### 7. Range Filter (Number Range)

```php
// Basic range
'amount' => Filter::range('Amount Range')

// With min/max limits
'price' => Filter::range('Price Range', 0, 1000000)
```

**Frontend:** Shows "Min" and "Max" inputs  
**Backend:** Range (`WHERE column >= min AND column <= max`)

---

## Advanced Usage

### Custom Column Names

If your filter key doesn't match the database column:

```php
'user_status' => Filter::select('Status', [...], 'is_active')
//                                     ↑ Filter key  ↑ Database column
```

### Using Multiple Filters

```php
public function filters(): array
{
    return [
        'status' => Filter::select('Status', ['1' => 'Active', '0' => 'Inactive']),
        'created_at' => Filter::dateRange('Created Between'),
        'email' => Filter::text('Email', 'Search by email...'),
        'age' => Filter::range('Age Range', 18, 100),
        'is_verified' => Filter::boolean('Is Verified'),
    ];
}
```

### Combined with Search

Filters work seamlessly with the global search:

```php
->filter(function ($query) {
    $this->applySearch($query);      // Global search
    $this->applyFilters($query);     // Column filters
})
```

## Examples by Use Case

### User DataTable Example

```php
public function filters(): array
{
    return [
        'is_active' => Filter::select('Status', [
            '1' => 'Active',
            '0' => 'Inactive'
        ]),
        'created_at' => Filter::date('Created Date', 'today'),
        'active_profile_type' => Filter::select('Active Profile', [
            'investor' => 'Investor',
            'owner' => 'Owner'
        ]),
        // Email search (uncomment to enable)
        // 'email' => Filter::text('Email', 'Enter email...'),
    ];
}
```

### Transaction DataTable Example

```php
public function filters(): array
{
    return [
        'type' => Filter::select('Transaction Type', [
            'deposit' => 'Deposit',
            'withdraw' => 'Withdraw',
        ]),
        'confirmed' => Filter::boolean('Status'),
        'amount' => Filter::range('Amount Range', 0, 1000000),
        'created_at' => Filter::dateRange('Transaction Date'),
    ];
}
```

### Investment DataTable Example

```php
public function filters(): array
{
    return [
        'status' => Filter::select('Status', [
            'active' => 'Active',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ]),
        'amount' => Filter::range('Investment Amount', 1000, 1000000),
        'created_at' => Filter::date('Investment Date'),
        'risk_level' => Filter::select('Risk Level', [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
        ]),
    ];
}
```

## Frontend Rendering

Filters are **automatically rendered** in the filter dropdown menu based on their type:

- **Select/Boolean:** Dropdown with options
- **Date:** Single date picker
- **Date Range:** Two date pickers (From/To)
- **Text:** Text input field
- **Number:** Number input field
- **Range:** Two number inputs (Min/Max)

## Backend Processing

Filters are **automatically applied** to queries based on their type:

| Filter Type | SQL Query |
|------------|-----------|
| `select`, `boolean` | `WHERE column = value` |
| `text` | `WHERE LOWER(column) LIKE '%value%'` |
| `date` | `WHERE DATE(column) = date` |
| `date-range` | `WHERE DATE(column) >= from AND DATE(column) <= to` |
| `number` | `WHERE column = value` |
| `range` | `WHERE column >= min AND column <= max` |

## JavaScript Integration

Filters are automatically handled in JavaScript:

```javascript
// Filters are collected in ajax.data callback
ajax: {
    data: (d) => {
        // Automatically collects:
        // - Regular filters: d['filter_key'] = value
        // - Range filters: d['filter_key_min'] = min, d['filter_key_max'] = max
        // - Date range: d['filter_key_from'] = from, d['filter_key_to'] = to
    }
}
```

## Best Practices

### 1. Use Descriptive Labels
```php
// ✅ Good
'created_at' => Filter::date('Created Date', 'today')

// ❌ Bad
'created_at' => Filter::date('Date')
```

### 2. Provide Clear Options
```php
// ✅ Good
'status' => Filter::select('Status', [
    '1' => 'Active',
    '0' => 'Inactive'
])

// ❌ Bad
'status' => Filter::select('Status', ['1', '0'])
```

### 3. Set Appropriate Limits
```php
// ✅ Good
'age' => Filter::number('Age', 18, 120)
'price' => Filter::range('Price', 0, 1000000)

// ❌ Bad
'age' => Filter::number('Age')  // No limits
```

### 4. Group Related Filters
```php
public function filters(): array
{
    return [
        // Status filters
        'is_active' => Filter::select('Active Status', [...]),
        'is_verified' => Filter::boolean('Verified'),
        
        // Date filters
        'created_at' => Filter::dateRange('Created Date Range'),
        
        // Search filters
        'email' => Filter::text('Email'),
        'name' => Filter::text('Name'),
    ];
}
```

## Troubleshooting

### Filter Not Appearing

**Check:** Filter is defined in `filters()` method
```php
public function filters(): array
{
    return [
        'my_filter' => Filter::select('My Filter', [...])
    ];
}
```

### Filter Not Working

**Check:** `applyFilters()` is called in `handle()` method
```php
->filter(function ($query) {
    $this->applyFilters($query); // 👈 Must be called
})
```

### Wrong Column Filtered

**Solution:** Use custom column parameter
```php
'filter_key' => Filter::select('Label', [...], 'database_column_name')
```

### Range Filter Not Working

**Check:** Both min and max values are sent
- Frontend automatically sends `_min` and `_max` parameters
- Backend automatically processes them

## Migration Guide

### Old Manual Filtering

```php
// ❌ Old way - manual filtering
->filter(function ($query) {
    $filters = request()->input('filters', []);
    if (!empty($filters['status'])) {
        $query->where('status', $filters['status']);
    }
    if (!empty($filters['created_at'])) {
        $query->whereDate('created_at', $filters['created_at']);
    }
})
```

### New Auto Filtering

```php
// ✅ New way - automatic filtering
public function filters(): array
{
    return [
        'status' => Filter::select('Status', [...]),
        'created_at' => Filter::date('Created Date'),
    ];
}

->filter(function ($query) {
    $this->applyFilters($query); // 👈 One line!
})
```

## Summary

**To add a filter to any DataTable:**

1. Add filter definition in `filters()` method
2. Call `$this->applyFilters($query)` in `handle()` method
3. **Done!** Filter appears automatically in frontend and works on backend

**One line = Complete filter with frontend + backend!**

```php
'status' => Filter::select('Status', ['1' => 'Active', '0' => 'Inactive'])
```

That's it! 🎉

