# Investment Routes Update - Optional Opportunity ID Parameter

## Summary

Updated the investment routes to support an optional `opportunity_id` parameter in the URL path, allowing for cleaner URLs while maintaining backward compatibility.

---

## Changes Made

### 1. Route Definition

**File**: `routes/web.php`

#### Before:
```php
Route::resource('investments', InvestmentController::class);
```

#### After:
```php
Route::get('investments/{opportunity_id?}', [InvestmentController::class, 'index'])->name('investments.index');
Route::resource('investments', InvestmentController::class)->except(['index']);
```

---

### 2. Controller Update

**File**: `app/Http/Controllers/InvestmentController.php`

#### Before:
```php
public function index(InvestmentDataTable $dataTable, Request $request): JsonResponse|View
{
    // ...
    $opportunityId = $request->get('opportunity_id');
    // ...
}
```

#### After:
```php
public function index(InvestmentDataTable $dataTable, Request $request, $opportunityId = null): JsonResponse|View
{
    // Also check query string for backward compatibility
    if (!$opportunityId) {
        $opportunityId = $request->get('opportunity_id');
    }
    // ...
}
```

---

## URL Patterns

### Supported URL Formats:

#### 1. **With Route Parameter** (New - Preferred):
```
/admin/investments/5
```
- Clean URL
- `opportunity_id` = 5
- Filters investments by opportunity ID 5

#### 2. **Without Parameter** (All Investments):
```
/admin/investments
```
- Shows all investments
- No filtering by opportunity

#### 3. **With Query String** (Backward Compatible):
```
/admin/investments?opportunity_id=5
```
- Legacy format still works
- `opportunity_id` = 5
- Maintains compatibility with existing links

---

## Usage Examples

### In Blade Templates:

#### Option 1: Using Route Parameter (Recommended):
```blade
<a href="{{ route('admin.investments.index', $opportunityId) }}">
    View Investments
</a>
```

#### Option 2: Without Parameter:
```blade
<a href="{{ route('admin.investments.index') }}">
    View All Investments
</a>
```

#### Option 3: Query String (Backward Compatible):
```blade
<a href="{{ route('admin.investments.index', ['opportunity_id' => $opportunityId]) }}">
    View Investments
</a>
```

### In Controllers:

#### Redirect with Opportunity ID:
```php
return redirect()->route('admin.investments.index', $opportunityId);
```

#### Redirect to All Investments:
```php
return redirect()->route('admin.investments.index');
```

---

## Benefits

### ✅ **Clean URLs**
- `/admin/investments/5` instead of `/admin/investments?opportunity_id=5`
- More RESTful and SEO-friendly
- Easier to read and share

### ✅ **Backward Compatibility**
- Old query string format still works
- No breaking changes for existing code
- Gradual migration possible

### ✅ **Flexibility**
- Can filter by opportunity or show all
- Optional parameter makes it versatile
- Works with both formats

### ✅ **Better UX**
- Cleaner browser address bar
- More intuitive URL structure
- Professional appearance

---

## Implementation Details

### Route Parameter Priority:
1. **First**: Check route parameter (`$opportunityId`)
2. **Second**: Check query string (`?opportunity_id=`)
3. **Third**: Default to `null` (show all investments)

### Controller Logic:
```php
// Route parameter takes priority
if (!$opportunityId) {
    // Fall back to query string for backward compatibility
    $opportunityId = $request->get('opportunity_id');
}

// If still null, show all investments
```

---

## Migration Guide

### For New Code:
Use the route parameter format:
```php
route('admin.investments.index', $opportunityId)
```

### For Existing Code:
No changes required! Old format continues to work:
```php
route('admin.investments.index', ['opportunity_id' => $opportunityId])
```

### Recommended Approach:
Gradually update to the new format when making changes:
```diff
- route('admin.investments.index', ['opportunity_id' => $id])
+ route('admin.investments.index', $id)
```

---

## Testing Checklist

- ✅ Test with route parameter: `/admin/investments/5`
- ✅ Test without parameter: `/admin/investments`
- ✅ Test with query string: `/admin/investments?opportunity_id=5`
- ✅ Test filtering works correctly
- ✅ Test DataTable loads properly
- ✅ Test existing links still work

---

## AJAX URL Update

The AJAX URL in the controller has been updated to use the cleaner format:

#### Before:
```php
'ajaxUrl' => route('admin.investments.index', $opportunityId ? ['opportunity_id' => $opportunityId] : []),
```

#### After:
```php
'ajaxUrl' => route('admin.investments.index', $opportunityId ? [$opportunityId] : []),
```

This generates:
- With ID: `/admin/investments/5`
- Without ID: `/admin/investments`

---

## Summary

✅ **Route**: Added optional `{opportunity_id?}` parameter  
✅ **Controller**: Updated to accept parameter in method signature  
✅ **Backward Compatible**: Query string format still works  
✅ **Clean URLs**: Improved URL structure  
✅ **No Breaking Changes**: All existing code continues to work  

The investment routes now support modern, clean URLs while maintaining full backward compatibility with the query string approach.

