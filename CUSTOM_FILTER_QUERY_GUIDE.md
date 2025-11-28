# Custom Filter Query Guide

## Overview

When you need to apply complex query logic based on filter values (like checking relationships, custom conditions, etc.), use `Filter::selectCustom()` instead of `Filter::select()`.

## Quick Example

```php
'has_profile' => Filter::selectCustom('Has Profile', [
    'investor' => 'Has Investor Profile',
    'owner' => 'Has Owner Profile',
    'both' => 'Has Both Profiles',
    'none' => 'No Profiles'
], function ($query, $value) {
    switch ($value) {
        case 'investor':
            $query->whereHas('investorProfile')->whereDoesntHave('ownerProfile');
            break;
        case 'owner':
            $query->whereHas('ownerProfile')->whereDoesntHave('investorProfile');
            break;
        case 'both':
            $query->whereHas('investorProfile')->whereHas('ownerProfile');
            break;
        case 'none':
            $query->whereDoesntHave('investorProfile')->whereDoesntHave('ownerProfile');
            break;
    }
})
```

**That's it!** The filter appears in the frontend automatically, and your custom query logic runs on the backend.

## Syntax

```php
Filter::selectCustom(
    string $label,              // Filter label shown in UI
    array $options,             // Key-value pairs for dropdown
    callable $callback          // Your custom query logic
)
```

### Parameters

- **$label**: The label shown in the filter dropdown (e.g., "Has Profile")
- **$options**: Array of key-value pairs for the dropdown options
- **$callback**: A function that receives:
  - `$query`: The Eloquent query builder
  - `$value`: The selected filter value (one of the keys from $options)

## Common Use Cases

### 1. Relationship Checks (Has/Doesn't Have)

```php
'has_investments' => Filter::selectCustom('Has Investments', [
    'yes' => 'Has Investments',
    'no' => 'No Investments'
], function ($query, $value) {
    if ($value === 'yes') {
        $query->whereHas('investments');
    } else {
        $query->whereDoesntHave('investments');
    }
})
```

### 2. Multiple Relationships

```php
'profile_type' => Filter::selectCustom('Profile Type', [
    'investor' => 'Investor Only',
    'owner' => 'Owner Only',
    'both' => 'Both Profiles',
    'none' => 'No Profiles'
], function ($query, $value) {
    switch ($value) {
        case 'investor':
            $query->whereHas('investorProfile')
                  ->whereDoesntHave('ownerProfile');
            break;
        case 'owner':
            $query->whereHas('ownerProfile')
                  ->whereDoesntHave('investorProfile');
            break;
        case 'both':
            $query->whereHas('investorProfile')
                  ->whereHas('ownerProfile');
            break;
        case 'none':
            $query->whereDoesntHave('investorProfile')
                  ->whereDoesntHave('ownerProfile');
            break;
    }
})
```

### 3. Conditional Where Clauses

```php
'status_combo' => Filter::selectCustom('Status Combination', [
    'active_verified' => 'Active & Verified',
    'active_unverified' => 'Active & Unverified',
    'inactive_verified' => 'Inactive & Verified',
], function ($query, $value) {
    switch ($value) {
        case 'active_verified':
            $query->where('is_active', true)
                  ->whereNotNull('email_verified_at');
            break;
        case 'active_unverified':
            $query->where('is_active', true)
                  ->whereNull('email_verified_at');
            break;
        case 'inactive_verified':
            $query->where('is_active', false)
                  ->whereNotNull('email_verified_at');
            break;
    }
})
```

### 4. Date Range with Custom Logic

```php
'account_age' => Filter::selectCustom('Account Age', [
    'new' => 'New (Last 7 days)',
    'recent' => 'Recent (Last 30 days)',
    'old' => 'Older than 30 days'
], function ($query, $value) {
    switch ($value) {
        case 'new':
            $query->where('created_at', '>=', now()->subDays(7));
            break;
        case 'recent':
            $query->where('created_at', '>=', now()->subDays(30))
                  ->where('created_at', '<', now()->subDays(7));
            break;
        case 'old':
            $query->where('created_at', '<', now()->subDays(30));
            break;
    }
})
```

### 5. Complex Conditions with Subqueries

```php
'has_min_investments' => Filter::selectCustom('Investment Count', [
    'one' => 'At least 1 investment',
    'five' => 'At least 5 investments',
    'ten' => 'At least 10 investments'
], function ($query, $value) {
    $minCount = match($value) {
        'one' => 1,
        'five' => 5,
        'ten' => 10,
        default => 0
    };
    
    $query->whereHas('investments')
          ->withCount('investments')
          ->having('investments_count', '>=', $minCount);
})
```

### 6. Filter by Related Model Attributes

```php
'investor_nationality' => Filter::selectCustom('Investor Nationality', [
    'local' => 'Local (Saudi)',
    'foreign' => 'Foreign',
], function ($query, $value) {
    if ($value === 'local') {
        $query->whereHas('investorProfile', function ($q) {
            $q->where('nationality', 'SA');
        });
    } else {
        $query->whereHas('investorProfile', function ($q) {
            $q->where('nationality', '!=', 'SA');
        });
    }
})
```

## Complete Example

Here's a full UserDataTable example with custom filters:

```php
use App\Helpers\Filter;

class UserDataTable extends BaseDataTable
{
    public function filters(): array
    {
        return [
            // Regular filters
            'is_active' => Filter::select('Status', [
                '1' => 'Active',
                '0' => 'Inactive'
            ]),
            
            'created_at' => Filter::dateRange('Created Date Range'),
            
            // Custom query filter - Has Profile
            'has_profile' => Filter::selectCustom('Has Profile', [
                'investor' => 'Has Investor Profile',
                'owner' => 'Has Owner Profile',
                'both' => 'Has Both Profiles',
                'none' => 'No Profiles'
            ], function ($query, $value) {
                switch ($value) {
                    case 'investor':
                        $query->whereHas('investorProfile')
                              ->whereDoesntHave('ownerProfile');
                        break;
                    case 'owner':
                        $query->whereHas('ownerProfile')
                              ->whereDoesntHave('investorProfile');
                        break;
                    case 'both':
                        $query->whereHas('investorProfile')
                              ->whereHas('ownerProfile');
                        break;
                    case 'none':
                        $query->whereDoesntHave('investorProfile')
                              ->whereDoesntHave('ownerProfile');
                        break;
                }
            }),
            
            // Custom query filter - Account Age
            'account_age' => Filter::selectCustom('Account Age', [
                'new' => 'New (Last 7 days)',
                'recent' => 'Recent (7-30 days)',
                'old' => 'Older than 30 days'
            ], function ($query, $value) {
                match($value) {
                    'new' => $query->where('created_at', '>=', now()->subDays(7)),
                    'recent' => $query->whereBetween('created_at', [
                        now()->subDays(30),
                        now()->subDays(7)
                    ]),
                    'old' => $query->where('created_at', '<', now()->subDays(30)),
                    default => null
                };
            }),
        ];
    }
    
    public function handle()
    {
        $query = User::with(['investorProfile', 'ownerProfile']);
        
        return DataTables::of($query)
            // ... columns
            ->filter(function ($query) {
                $this->applySearch($query);
                $this->applyFilters($query); // Automatically handles custom filters!
            })
            ->make(true);
    }
}
```

## When to Use Custom Filters

### Use `Filter::selectCustom()` when:
- ✅ You need to check relationships (`whereHas`, `whereDoesntHave`)
- ✅ You need multiple conditions based on one value
- ✅ You need subqueries or complex logic
- ✅ You need to filter by related model attributes
- ✅ Standard `Filter::select()` can't handle your logic

### Use `Filter::select()` when:
- ✅ Simple exact match (`WHERE column = value`)
- ✅ Filtering by a single column value
- ✅ No relationship checks needed

## Tips & Best Practices

### 1. Use Match Expression (PHP 8+)

```php
// ✅ Modern PHP 8+ style
'status' => Filter::selectCustom('Status', [...], function ($query, $value) {
    match($value) {
        'option1' => $query->where(...),
        'option2' => $query->where(...),
        default => null
    };
})

// ✅ Traditional switch (compatible with older PHP)
'status' => Filter::selectCustom('Status', [...], function ($query, $value) {
    switch ($value) {
        case 'option1':
            $query->where(...);
            break;
        case 'option2':
            $query->where(...);
            break;
    }
})
```

### 2. Keep Logic Simple

```php
// ✅ Good - clear and simple
function ($query, $value) {
    if ($value === 'yes') {
        $query->whereHas('relation');
    }
}

// ❌ Avoid - too complex, consider extracting to a method
function ($query, $value) {
    // 50 lines of complex logic...
}
```

### 3. Extract Complex Logic to Methods

```php
class UserDataTable extends BaseDataTable
{
    public function filters(): array
    {
        return [
            'has_profile' => Filter::selectCustom('Has Profile', [...], 
                [$this, 'applyHasProfileFilter'] // 👈 Extract to method
            ),
        ];
    }
    
    protected function applyHasProfileFilter($query, $value): void
    {
        // Complex logic here
        switch ($value) {
            // ...
        }
    }
}
```

### 4. Handle All Cases

```php
// ✅ Good - handles all cases
switch ($value) {
    case 'option1':
        $query->where(...);
        break;
    case 'option2':
        $query->where(...);
        break;
    default:
        // Optionally handle unexpected values
        break;
}

// ⚠️ Better - use match with default
match($value) {
    'option1' => $query->where(...),
    'option2' => $query->where(...),
    default => null // Explicitly handle unknown values
}
```

## Troubleshooting

### Filter Not Working

**Check:**
1. Callback is callable: `is_callable($callback)`
2. Filter value is received: `dd($value)` in callback
3. Query is modified correctly: Check SQL logs

### Filter Options Not Showing

**Check:**
- Options array has correct structure: `['key' => 'Label']`
- Filter is in `filters()` method
- Frontend renders `select-custom` type (automatically handled)

### Query Not Applied

**Check:**
- `$this->applyFilters($query)` is called in `handle()` method
- Filter value is not empty
- Callback is correctly defined

## Summary

**Custom Query Filters = One Line in Frontend + Custom Logic in Backend**

```php
'filter_key' => Filter::selectCustom('Label', $options, function ($query, $value) {
    // Your custom query logic here
    // Automatically called when filter is applied!
})
```

No additional frontend code needed - it just works! 🎉

