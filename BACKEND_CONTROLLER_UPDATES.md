# Backend Controller Updates - Investment Opportunity

## Summary of Changes

This document outlines all backend controller updates made to support the new investment opportunity field structure and actual profit protection logic.

---

## 1. InvestmentOpportunityController (Main CRUD)

**File**: `app/Http/Controllers/InvestmentOpportunityController.php`

### Changes Made:

#### ✅ **store() Method**
- **Removed**: Old field names (`expected_return_amount_by_myself`, `expected_return_amount_by_authorize`, etc.)
- **Removed**: `status` field (auto-calculated by model)
- **Added**: New field names:
  - `expected_profit` (replaces separate myself/authorize fields)
  - `expected_net_profit` (replaces separate myself/authorize fields)
  - `share_price` (corrected from `price_per_share`)
  - `shipping_fee_per_share` (corrected from `shipping_and_service_fee`)
  - `expected_delivery_date`
  - `expected_distribution_date`
- **Updated**: `reserved_shares` validation to `min:0` (was `min:1`)
- **Updated**: `fund_goal` validation with enum values: `growth`, `stability`, `income`

#### ✅ **update() Method**
- **Added**: All same field updates as `store()`
- **Added**: Validation rules for actual profit fields:
  - `actual_profit_per_share` (nullable|numeric|min:0)
  - `actual_net_profit_per_share` (nullable|numeric|min:0)
- **Added**: Protection logic:
  ```php
  // Protect actual profits from being changed once they're set
  if (!$investmentOpportunity->canEditActualProfits()) {
      unset($validated['actual_profit_per_share']);
      unset($validated['actual_net_profit_per_share']);
  }
  ```
- **Added**: Comment explaining status is auto-calculated

### Key Features:
✅ Validates new field structure  
✅ Prevents modification of actual profits once set  
✅ Removes status from user input (auto-calculated)  
✅ Supports all new date fields  

---

## 2. Admin\InvestmentOpportunityController

**File**: `app/Http/Controllers/Admin/InvestmentOpportunityController.php`

### Changes Made:

#### ✅ **recordActualProfit() Method**
- **Added**: Protection check before processing:
  ```php
  if (!$opportunity->canEditActualProfits()) {
      return response()->json([
          'success' => false,
          'message' => 'Actual profits have already been set...',
      ], 403);
  }
  ```
- **Added**: Opportunity-level update:
  ```php
  $opportunity->update([
      'actual_profit_per_share' => $request->actual_profit_per_share,
      'actual_net_profit_per_share' => $request->actual_net_profit_per_share,
  ]);
  ```
- **Enhanced**: Now updates both the opportunity AND all authorize investments

### Key Features:
✅ Prevents re-recording of actual profits  
✅ Returns 403 Forbidden if already set  
✅ Updates opportunity-level actual profits  
✅ Cascades to all authorize investments  

---

## 3. Model Method Added

**File**: `app/Models/InvestmentOpportunity.php`

### New Method:
```php
/**
 * Check if actual profits can be edited
 * Actual profits can only be edited if they haven't been set yet (are null)
 */
public function canEditActualProfits(): bool
{
    return $this->actual_profit_per_share === null 
        && $this->actual_net_profit_per_share === null;
}
```

### Usage:
This method is used across:
1. Frontend form to show/hide/disable inputs
2. Main controller to prevent updates
3. Admin controller to prevent re-recording

---

## 4. Validation Rules Summary

### **Create (store)**
```php
'name' => 'required|string|max:255',
'location' => 'nullable|string|max:255',
'description' => 'nullable|string',
'category_id' => 'required|exists:investment_categories,id',
'owner_profile_id' => 'required|exists:owner_profiles,id',
'risk_level' => 'nullable|string|in:low,medium,high',
'target_amount' => 'required|numeric|min:0',
'share_price' => 'required|numeric|min:0',
'reserved_shares' => 'required|integer|min:0',
'investment_duration' => 'nullable|integer|min:1',
'expected_profit' => 'nullable|numeric|min:0',
'expected_net_profit' => 'nullable|numeric|min:0',
'shipping_fee_per_share' => 'nullable|numeric|min:0',
'min_investment' => 'required|integer|min:1',
'max_investment' => 'nullable|integer|min:1',
'fund_goal' => 'nullable|string|in:growth,stability,income',
'guarantee' => 'nullable|string',
'show' => 'boolean',
'show_date' => 'nullable|date',
'offering_start_date' => 'nullable|date',
'offering_end_date' => 'nullable|date|after:offering_start_date',
'profit_distribution_date' => 'nullable|date',
'expected_delivery_date' => 'nullable|date',
'expected_distribution_date' => 'nullable|date',
```

### **Update (update)**
All fields from **Create** plus:
```php
'actual_profit_per_share' => 'nullable|numeric|min:0',
'actual_net_profit_per_share' => 'nullable|numeric|min:0',
```
*Note: These are validated but removed from update if already set*

### **Admin - Record Actual Profit**
```php
'actual_profit_per_share' => 'required|numeric|min:0',
'actual_net_profit_per_share' => 'required|numeric|min:0',
```
*Note: Entire request rejected with 403 if already set*

---

## 5. Data Flow

### Creating New Opportunity:
1. User submits form with expected profits
2. Controller validates input
3. Opportunity created with `status` auto-calculated
4. Actual profit fields remain `null`

### Editing Opportunity (Before Actual Profits Set):
1. User can edit all fields including expected profits
2. Actual profit section shows with editable inputs
3. User can set actual profits for first time
4. Controller validates and saves

### Editing Opportunity (After Actual Profits Set):
1. User edits opportunity
2. Actual profit section shows as read-only/disabled
3. If user somehow submits actual profit values:
   - Controller removes them from validated data
   - Update proceeds without changing actual profits
4. Data integrity maintained

### Recording Actual Profits (Admin):
1. Admin accesses record actual profit form
2. Submits actual profit values
3. Controller checks `canEditActualProfits()`
4. If already set: Returns 403 error
5. If not set: 
   - Updates opportunity-level actual profits
   - Updates all authorize investments
   - Values locked permanently

---

## 6. Security & Data Integrity

### Protection Mechanisms:

1. **Model Method**: `canEditActualProfits()`
   - Single source of truth for edit permission
   - Checks both fields are null

2. **Controller Guard** (Main):
   - Unsets actual profit fields if already set
   - Silently prevents changes

3. **Controller Guard** (Admin):
   - Explicit 403 error if already set
   - Prevents re-recording

4. **Frontend Disabled**:
   - Inputs disabled when already set
   - Visual feedback to user

### Data Flow Integrity:
✅ Actual profits can only be set once  
✅ Cannot be modified after being set  
✅ Cascades to all related investments  
✅ Maintains audit trail  

---

## 7. API Endpoints Affected

| Endpoint | Method | Changes |
|----------|--------|---------|
| `/investment-opportunity` | POST | Updated validation, removed status |
| `/investment-opportunity/{id}` | PUT | Updated validation, added protection |
| `/admin/investment-opportunity/{id}/record-actual-profit` | POST | Added protection check |

---

## 8. Breaking Changes

### ⚠️ **Field Name Changes**
Old → New:
- `price_per_share` → `share_price`
- `shipping_and_service_fee` → `shipping_fee_per_share`
- `expected_return_amount_by_myself` → Removed
- `expected_net_return_by_myself` → Removed
- `expected_return_amount_by_authorize` → `expected_profit`
- `expected_net_return_by_authorize` → `expected_net_profit`

### ⚠️ **Status Field**
- No longer accepted in create/update
- Auto-calculated by model
- Based on dates and conditions

### ⚠️ **Actual Profits**
- Can only be set once
- Cannot be modified after initial setting
- Protected at multiple levels

---

## 9. Testing Recommendations

### Test Cases:

1. ✅ **Create opportunity without actual profits** - Should succeed
2. ✅ **Edit opportunity and set actual profits (first time)** - Should succeed
3. ✅ **Try to edit actual profits after they're set** - Should be prevented
4. ✅ **Admin record actual profits (first time)** - Should succeed
5. ✅ **Admin try to re-record actual profits** - Should return 403
6. ✅ **Status auto-calculation** - Should work based on dates
7. ✅ **Validation with new field names** - Should accept correct data
8. ✅ **Validation with old field names** - Should reject

---

## 10. Migration Path

### For Existing Data:
If you have existing opportunities with the old field structure:

1. Run database migration to rename columns
2. Update any existing actual profit values
3. Test the `canEditActualProfits()` logic
4. Verify protection mechanisms work

### For API Consumers:
If you have external API consumers:

1. Update API documentation
2. Provide migration notice
3. Support both old/new field names temporarily (if needed)
4. Deprecate old field names

---

## Conclusion

All backend controllers have been updated to:
- ✅ Use new field naming convention
- ✅ Remove status from user input
- ✅ Protect actual profits from modification
- ✅ Validate all new date fields
- ✅ Maintain data integrity
- ✅ Provide clear error messages

The system now ensures that actual profits can only be set once and cannot be tampered with, maintaining the integrity of financial data.

