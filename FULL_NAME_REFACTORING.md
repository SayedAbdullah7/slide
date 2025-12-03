# Full Name Refactoring Summary

## Overview
This document summarizes the refactoring that moved `full_name` from the `users` table to the `investor_profiles` table. This change ensures that investor-specific personal information is properly stored in the investor profile, while owner profiles use their `business_name` for identification.

## Changes Made

### 1. Database Schema Changes

#### Migration 1: Add full_name to investor_profiles table
- **File**: `2025_10_20_221554_add_full_name_to_investor_profiles_table.php`
- **Changes**:
  - Added `full_name` (string, nullable) column to `investor_profiles` table
- **Rationale**: Personal name information belongs to investor profiles specifically

#### Migration 2: Make full_name nullable in users table
- **File**: `2025_10_20_221555_make_full_name_nullable_in_users_table.php`
- **Changes**:
  - Made `full_name` nullable in `users` table
- **Rationale**: Keep column for backward compatibility but allow NULL values as profiles now hold the actual names

### 2. Model Updates

#### InvestorProfile Model
- **File**: `app/Models/InvestorProfile.php`
- **Changes**:
  - Added `full_name` to `$fillable` array
  - Updated PHPDoc annotations to include `full_name` property
  - Added `whereFullName()` method annotation

#### User Model
- **File**: `app/Models/User.php`
- **Changes**:
  - Updated `getDisplayNameAttribute()` method with priority logic:
    1. Try `investorProfile->full_name` first
    2. Try `ownerProfile->business_name` second
    3. Fallback to `user->full_name` (for legacy data)
    4. Fallback to email → phone → "User #ID"
- **Benefits**: Automatic name resolution regardless of profile type

### 3. Service Layer Updates

#### UserAuthService
- **File**: `app/Services/UserAuthService.php`
- **Changes**:
  - Removed `full_name` from user registration data
- **Rationale**: Name is now set during profile creation, not user creation

#### ProfileFactoryService
- **File**: `app/Services/ProfileFactoryService.php`
- **Changes**:
  - `createInvestor()`: Now accepts and stores `full_name` in investor profile
- **Rationale**: Investor-specific data handled at profile creation time

### 4. Controller Updates

#### UserAuthController
- **File**: `app/Http/Controllers/Api/UserAuthController.php`
- **Changes**:
  - **New User Registration**: `full_name` is now required only when `profile=investor`
  - **Adding Profile**: `full_name` is now required only when `profile=investor`
  - **User Creation**: Removed `full_name` parameter from user registration call

### 5. View Updates

#### User Show View
- **File**: `resources/views/pages/user/show.blade.php`
- **Changes**:
  - Updated avatar alt text to use `$user->display_name`
  - Updated symbol label initials to use `$user->display_name`
  - Updated header to use `$user->display_name`
  - Updated personal information section to show `$user->investorProfile?->full_name ?? 'N/A'`

### 6. Seeder Updates

#### UserSeeder
- **File**: `database/seeders/UserSeeder.php`
- **Changes**:
  - Removed `full_name` from all `User::create()` calls
  - Added `full_name` to all `InvestorProfile::create()` calls
  - Admin user no longer has full_name
  - Owner users don't have full_name (they use `business_name` instead)

## API Changes

### Registration Endpoint: POST `/api/auth/register`

#### Investor Registration
```json
{
  "session_token": "xxx",
  "email": "investor@example.com",
  "profile": "investor",
  "full_name": "John Doe",      // ← Now required for investors
  "national_id": "123456789",
  "birth_date": "1990-01-01",
  "answers": [...]
}
```

#### Owner Registration
```json
{
  "session_token": "xxx",
  "email": "owner@example.com",
  "profile": "owner",
  "business_name": "ABC Company",  // ← Business name, not full_name
  "tax_number": "TAX123456",
  "answers": [...]
}
```

#### Adding Investor Profile (Authenticated)
```json
{
  // No session_token needed when authenticated
  "profile": "investor",
  "full_name": "Jane Smith",    // ← Required
  "national_id": "987654321",
  "birth_date": "1985-05-15"
}
```

## Data Flow

### Before Refactoring
```
User Registration → User Table (with full_name)
                  → Profile Creation (investor/owner)
```

### After Refactoring
```
User Registration → User Table (without full_name)
                  → Investor Profile (with full_name)
                  → Owner Profile (with business_name only)
```

## Display Name Logic

The User model now has intelligent name resolution:

```php
$user->display_name  // Returns:
  1. investorProfile->full_name (if exists)
  2. ownerProfile->business_name (if exists)
  3. user->full_name (legacy data)
  4. user->email
  5. user->phone
  6. "User #" . id
```

This ensures that:
- Investors see their personal full name
- Owners see their business name
- Legacy data still works
- All users have a displayable name

## Benefits

1. **Better Data Organization**: Personal names are in investor profiles where they belong
2. **Clear Separation**: Investors have personal names, owners have business names
3. **Backward Compatibility**: Legacy `full_name` in users table still works
4. **Automatic Resolution**: `display_name` attribute handles all cases automatically
5. **Type-Appropriate**: Each profile type has appropriate naming fields

## Migration Instructions

### For Fresh Installation
```bash
php artisan migrate
php artisan db:seed --class=UserSeeder
```

### For Existing Database
1. **Backup your database first!**
2. Run migrations:
```bash
php artisan migrate
```

**Note**: The migrations will:
- Add `full_name` column to `investor_profiles` table
- Make `full_name` nullable in `users` table
- Existing `full_name` data in `users` table remains but should be migrated to profiles

### Data Migration Script (if needed)

If you have existing users with data, create a data migration:

```php
use App\Models\User;

// Migrate existing investor users
User::whereHas('investorProfile')
    ->whereNotNull('full_name')
    ->each(function ($user) {
        $user->investorProfile->update([
            'full_name' => $user->full_name
        ]);
    });
```

## Testing Checklist

- [x] New investor registration requires full_name
- [x] Owner registration does NOT require full_name
- [x] Existing user adding investor profile requires full_name
- [x] Display name shows correctly for investors
- [x] Display name shows business_name for owners
- [x] User show page displays investor name correctly
- [x] Seeders create proper data structure
- [x] Migrations run successfully
- [x] No linter errors

## Breaking Changes

⚠️ **Important**: This is a breaking change for:

1. **API Clients**: Must now send `full_name` when registering investors
2. **Views**: Direct access to `$user->full_name` may return null for new users
3. **Code**: Any code expecting `full_name` on User model should use `display_name` or `investorProfile->full_name`

### Migration Guide for API Clients

**Before:**
```json
{
  "full_name": "John Doe",    // Was on user level
  "profile": "investor"
}
```

**After:**
```json
{
  "profile": "investor",
  "full_name": "John Doe",    // Now profile-specific
  "national_id": "123456789",
  "birth_date": "1990-01-01"
}
```

### Migration Guide for Code

**Before:**
```php
$userName = $user->full_name;
```

**After:**
```php
// Best practice - works for all profile types
$userName = $user->display_name;

// Or specific to investors
$userName = $user->investorProfile?->full_name ?? 'N/A';
```

## Files Changed

### Migrations (2 files)
- `database/migrations/2025_10_20_221554_add_full_name_to_investor_profiles_table.php`
- `database/migrations/2025_10_20_221555_make_full_name_nullable_in_users_table.php`

### Models (2 files)
- `app/Models/User.php` (updated `getDisplayNameAttribute()`)
- `app/Models/InvestorProfile.php` (added `full_name`)

### Services (2 files)
- `app/Services/UserAuthService.php` (removed `full_name` from user creation)
- `app/Services/ProfileFactoryService.php` (added `full_name` to investor creation)

### Controllers (1 file)
- `app/Http/Controllers/Api/UserAuthController.php` (updated validation rules)

### Views (1 file)
- `resources/views/pages/user/show.blade.php` (uses `display_name` and `investorProfile->full_name`)

### Seeders (1 file)
- `database/seeders/UserSeeder.php` (moved `full_name` to investor profiles)

## Complete Profile Fields Overview

### User Table
- ✅ `phone` (required)
- ✅ `email` (optional)
- ✅ `password` (optional)
- ✅ `full_name` (nullable - legacy only)
- ✅ `active_profile_type`
- ✅ `is_active`
- ✅ `is_registered`

### InvestorProfile Table
- ✅ `user_id` (required)
- ✅ `full_name` (nullable) ← **NEW**
- ✅ `birth_date` (nullable)
- ✅ `national_id` (nullable)
- ✅ `extra_data` (nullable)

### OwnerProfile Table
- ✅ `user_id` (required)
- ✅ `business_name` (required - NOT NULL)
- ✅ `tax_number` (required - NOT NULL)
- ✅ `goal` (nullable)

---

**Date**: October 20, 2025  
**Status**: ✅ Completed and Migrated  
**Migrations Run**: Successfully applied to database  
**Linter Status**: ✅ No errors


