# Profile Fields Refactoring Summary

## Overview
This document summarizes the major refactoring that moved user-specific fields from the `users` table to their respective profile tables, and made certain owner profile fields required.

## Changes Made

### 1. Database Schema Changes

#### Migration 1: Add fields to `investor_profiles` table
- **File**: `2025_10_20_213534_add_birth_date_and_national_id_to_investor_profiles_table.php`
- **Changes**:
  - Added `birth_date` (date, nullable) column
  - Added `national_id` (string, nullable) column
- **Rationale**: Investor-specific personal information should be stored in the investor profile

#### Migration 2: Make owner profile fields required
- **File**: `2025_10_20_213535_make_business_name_and_tax_number_required_in_owner_profiles_table.php`
- **Changes**:
  - Made `business_name` NOT NULL (required)
  - Made `tax_number` NOT NULL (required)
- **Rationale**: These are essential fields for business owners and should be mandatory

#### Migration 3: Remove fields from `users` table
- **File**: `2025_10_20_213536_remove_birth_date_and_national_id_from_users_table.php`
- **Changes**:
  - Removed `birth_date` column
  - Removed `national_id` column
- **Rationale**: These fields are now in the investor profile table

### 2. Model Updates

#### InvestorProfile Model
- **File**: `app/Models/InvestorProfile.php`
- **Changes**:
  - Added `birth_date` to `$fillable` array
  - Added `national_id` to `$fillable` array
  - Added cast for `birth_date` as 'date'
  - Updated PHPDoc annotations

#### User Model
- **File**: `app/Models/User.php`
- **Changes**:
  - Removed `birth_date` from `$fillable` array
  - Removed `national_id` from `$fillable` array
  - Removed `birth_date` cast from `casts()` method
  - Updated PHPDoc annotations

### 3. Service Layer Updates

#### UserAuthService
- **File**: `app/Services/UserAuthService.php`
- **Changes**:
  - Removed `national_id` from user registration
  - Removed `birth_date` from user registration
- **Rationale**: These fields are now handled during profile creation

#### ProfileFactoryService
- **File**: `app/Services/ProfileFactoryService.php`
- **Changes**:
  - `createInvestor()`: Now accepts and stores `birth_date` and `national_id`
  - `createOwner()`: Now accepts and stores `business_name` (required field)
- **Rationale**: Profile-specific data is now handled at profile creation time

### 4. Controller Updates

#### UserAuthController
- **File**: `app/Http/Controllers/Api/UserAuthController.php`
- **Changes**:
  - **Registration validation updated**:
    - For new users (investor): `national_id` and `birth_date` required only if profile is 'investor'
    - For new users (owner): `business_name` and `tax_number` required only if profile is 'owner'
    - For existing users adding profiles: Same validation rules apply
  - **Session token made optional** for authenticated users adding a new profile
  - **User creation**: Removed `birth_date` and `national_id` from user data
- **Route**: Added `optional.auth` middleware to `/api/auth/register` route

### 5. View Updates

#### User Show View
- **File**: `resources/views/pages/user/show.blade.php`
- **Changes**:
  - Changed `$user->national_id` to `$user->investorProfile?->national_id`
  - Changed `$user->birth_date` to `$user->investorProfile?->birth_date`
- **Rationale**: Display investor-specific fields from the investor profile

### 6. Seeder Updates

#### UserSeeder
- **File**: `database/seeders/UserSeeder.php`
- **Changes**:
  - Removed `birth_date` and `national_id` from all User creation calls
  - Added `birth_date` and `national_id` to InvestorProfile creation calls
  - Owner profiles now include `business_name` (required field)

## API Changes

### Registration Endpoint: POST `/api/auth/register`

#### New User Registration (Investor)
```json
{
  "session_token": "xxx",
  "full_name": "John Doe",
  "email": "john@example.com",
  "answers": [...],
  "profile": "investor",
  "national_id": "123456789",  // Required for investor
  "birth_date": "1990-01-01"   // Required for investor
}
```

#### New User Registration (Owner)
```json
{
  "session_token": "xxx",
  "full_name": "Jane Doe",
  "email": "jane@example.com",
  "answers": [...],
  "profile": "owner",
  "business_name": "ABC Company",  // Required for owner
  "tax_number": "TAX123456"        // Required for owner
}
```

#### Adding Profile (Authenticated User)
```json
{
  // session_token is optional if authenticated
  "profile": "owner",
  "business_name": "XYZ Corp",  // Required for owner
  "tax_number": "TAX789012"     // Required for owner
}
```

## Data Flow

### Before Refactoring
```
User Registration → User Table (with birth_date, national_id)
                  → Profile Creation (minimal data)
```

### After Refactoring
```
User Registration → User Table (basic info only)
                  → Investor Profile (with birth_date, national_id)
                  → Owner Profile (with business_name, tax_number - both required)
```

## Benefits

1. **Better Data Organization**: Profile-specific data is stored in respective profile tables
2. **Cleaner User Model**: User table contains only core authentication data
3. **Type Safety**: Investor vs Owner data is properly separated
4. **Validation**: Required fields are enforced at the database level
5. **Flexibility**: Users can have different data requirements based on their profile type
6. **Optional Auth**: Authenticated users can add new profiles without re-verification

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
- Add new columns to `investor_profiles`
- Make columns required in `owner_profiles`
- Remove columns from `users` table
- Existing data in `users` table will be lost for `birth_date` and `national_id` fields

### Data Migration Script (if needed)
If you have existing data that needs to be migrated, create a migration to:
1. Copy `birth_date` and `national_id` from `users` to corresponding `investor_profiles`
2. Populate `business_name` in `owner_profiles` if missing

## Testing Checklist

- [ ] New investor registration with birth_date and national_id
- [ ] New owner registration with business_name and tax_number
- [ ] Existing user adding investor profile
- [ ] Existing user adding owner profile
- [ ] Authenticated user adding profile without session_token
- [ ] User show page displays investor fields correctly
- [ ] Owner profile requires business_name and tax_number
- [ ] Validation errors for missing required fields

## Breaking Changes

⚠️ **Important**: This is a breaking change for:
1. Any API clients expecting `birth_date` and `national_id` in user data
2. Any code accessing `$user->birth_date` or `$user->national_id` directly
3. Owner profiles without `business_name` or `tax_number`

### Migration Guide for API Clients
- Update registration payloads to include investor/owner specific fields
- Update user data parsing to read from `investorProfile` or `ownerProfile`
- Handle authenticated profile addition without session_token

## Files Changed

### Migrations (3 files)
- `database/migrations/2025_10_20_213534_add_birth_date_and_national_id_to_investor_profiles_table.php`
- `database/migrations/2025_10_20_213535_make_business_name_and_tax_number_required_in_owner_profiles_table.php`
- `database/migrations/2025_10_20_213536_remove_birth_date_and_national_id_from_users_table.php`

### Models (3 files)
- `app/Models/User.php`
- `app/Models/InvestorProfile.php`
- `app/Models/OwnerProfile.php` (no changes, but business_name/tax_number now required at DB level)

### Services (2 files)
- `app/Services/UserAuthService.php`
- `app/Services/ProfileFactoryService.php`

### Controllers (1 file)
- `app/Http/Controllers/Api/UserAuthController.php`

### Routes (1 file)
- `routes/api.php`

### Views (1 file)
- `resources/views/pages/user/show.blade.php`

### Seeders (1 file)
- `database/seeders/UserSeeder.php`

### Deleted Files (1 file)
- `database/migrations/2025_10_18_205435_create_payment_webhooks_table.php` (duplicate migration)

---

**Date**: October 20, 2025  
**Status**: ✅ Completed and Migrated  
**Migrations Run**: Successfully applied to database







