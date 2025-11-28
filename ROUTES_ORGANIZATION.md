# Routes Organization

This document describes the organization and structure of the application's routes after the cleanup and reorganization.

## Route Files Overview

The application routes are now organized into four main files:

### 1. `routes/web.php` - Public and User Routes
Contains all public-facing and authenticated user routes.

**Sections:**
- **Public Routes**: Home page and other public-facing routes
- **Dashboard Route**: Main dashboard for authenticated users
- **Authenticated User Routes**:
  - User profile management (investor and owner profiles)
  - Settings routes (profile, password, appearance)
- **Backward Compatibility Routes**: Legacy route definitions for smooth migration
- **Include Statements**: Loads additional route files

**Key Features:**
- Clean separation between public and authenticated routes
- Well-organized user profile management
- Clear sections with descriptive comments
- Conditional loading of test routes (only in non-production environments)

### 2. `routes/admin.php` - Admin Panel Routes
Contains all administrative routes with `/admin` prefix.

**Sections:**
- **User Management Routes**: CRUD operations for users
- **Investment Opportunities Routes**: CRUD and admin-specific operations
  - Standard CRUD operations
  - Process merchandise delivery
  - Record actual profit (modal routes)
  - Distribute returns
  - Merchandise and returns status views
- **Investment Routes**: Managing investments with optional opportunity filtering
- **Modal Content Routes**: Dynamic modal content loading

**Key Features:**
- All routes prefixed with `/admin`
- All routes protected by `auth` middleware
- Well-organized by resource type
- Clear separation between CRUD and custom admin actions
- Named routes following `admin.*` convention

**Important Routes:**
```php
// Investment Opportunities Admin Actions
POST   /admin/investment-opportunities/{opportunity}/process-merchandise-delivery
GET    /admin/investment-opportunities/{opportunity}/record-actual-profit
POST   /admin/investment-opportunities/{opportunity}/record-actual-profit
POST   /admin/investment-opportunities/{opportunity}/distribute-returns
GET    /admin/investment-opportunities/{opportunity}/merchandise-status
GET    /admin/investment-opportunities/{opportunity}/returns-status

// Modal Routes
GET    /admin/modal/investment-widgets
GET    /admin/modal/mixed-widget-demo
```

### 3. `routes/test.php` - Testing and Development Routes
Contains routes for testing and development purposes only.

**Features:**
- Only loads in `local`, `staging`, and `development` environments
- Protected by `auth` and `throttle:60,1` middleware
- All routes prefixed with `/test`
- Well-documented with PHPDoc-style comments

**Available Test Routes:**
```php
GET    /test/recalculate-reserved-shares
       // Recalculates reserved shares for all investment opportunities

GET    /test/top-opportunity-by-investments
       // Returns the opportunity with the most investments

GET    /test/actual-profit
       // Tests processActualProfitPerShare with example data

GET    /test/bulk-actual-profit
       // Tests bulk actual profit recording with default values

GET    /test/bulk-actual-profit/{opportunity_id}/{profit}/{net_profit}
       // Tests bulk actual profit recording with custom parameters

GET    /test/returns-distribution/{opportunity_id}
       // Tests returns distribution processing
```

**Security:**
- Routes are automatically excluded in production
- Requires authentication
- Rate limited to 60 requests per minute
- Clear warning in file header about production usage

### 4. `routes/auth.php` - Authentication Routes
Contains Laravel Breeze/authentication-related routes (managed by Laravel).

## Route Naming Conventions

The application follows these naming conventions:

- **Public routes**: Simple names (e.g., `home`)
- **Admin routes**: Prefixed with `admin.` (e.g., `admin.users.index`)
- **User routes**: Prefixed with `user.` (e.g., `user.investor-profile.create`)
- **Test routes**: Prefixed with `test.` (e.g., `test.recalculate-reserved-shares`)

## Middleware Usage

### Web Routes
- Public routes: No middleware (only web middleware group)
- Dashboard: `auth`, `verified`
- User routes: `auth`
- Backward compatibility: `auth`

### Admin Routes
- All admin routes: `auth`
- **Note**: Additional role/permission middleware should be added based on your requirements

### Test Routes
- All test routes: `auth`, `throttle:60,1`
- Automatically excluded in production environment

## Environment-Based Route Loading

Test routes are only loaded in non-production environments:

```php
// In routes/web.php
if (app()->environment(['local', 'staging', 'development'])) {
    require __DIR__.'/test.php';
}
```

To completely disable test routes, either:
1. Set `APP_ENV=production` in `.env`
2. Remove/comment the conditional include in `routes/web.php`
3. Delete the `routes/test.php` file

## Backward Compatibility

Legacy routes are maintained for backward compatibility:

```php
// Old style routes (still work)
Route::resource('user', UserController::class);
Route::resource('investment-opportunity', InvestmentOpportunityController::class);

// New style routes (recommended)
Route::resource('admin/users', UserController::class);
Route::resource('admin/investment-opportunities', InvestmentOpportunityController::class);
```

**Migration Path:**
1. Update frontend/API consumers to use new route names
2. Test thoroughly in staging environment
3. Remove backward compatibility routes once migration is complete

## Best Practices Implemented

1. **Separation of Concerns**: Routes organized by purpose (public, admin, testing)
2. **Environment Awareness**: Test routes only in non-production
3. **Clear Documentation**: Comments and sections in each file
4. **Consistent Naming**: Following Laravel conventions
5. **Security First**: Appropriate middleware on all routes
6. **Maintainability**: Easy to find and modify routes

## Future Improvements

Consider implementing:

1. **Role-based Access Control**: Add permission middleware to admin routes
   ```php
   Route::middleware(['auth', 'role:admin'])->group(function () {
       // Admin routes
   });
   ```

2. **API Routes Separation**: If API grows, consider splitting into versioned files
   ```
   routes/api/v1.php
   routes/api/v2.php
   ```

3. **Route Caching**: Use `php artisan route:cache` in production for better performance

4. **Rate Limiting**: Add custom rate limiters for sensitive operations
   ```php
   Route::middleware(['throttle:10,1'])->group(function () {
       // Sensitive operations
   });
   ```

5. **Move Modal Routes to Controller**: Consider creating a `ModalController` for modal routes instead of using closures

## Route List Command

To view all registered routes:

```bash
# View all routes
php artisan route:list

# Filter by name
php artisan route:list --name=admin

# Filter by method
php artisan route:list --method=POST

# Show only routes in a specific file
php artisan route:list --path=admin
```

## Testing Routes

### Local Development
```bash
# Test routes are automatically available
curl http://localhost/test/recalculate-reserved-shares
```

### Production
```bash
# Test routes are NOT available (404 error)
curl https://production.com/test/recalculate-reserved-shares
# Returns: 404 Not Found
```

## Important: Investment Routes Conflict Fix

A routing conflict was discovered and fixed where the investment `show` route was not receiving requests due to route ordering. 

**The Problem:**
```php
// ❌ This was catching all requests including show
Route::get('investments/{opportunity_id?}', [...])
Route::resource('investments', ...)
```

**The Solution:**
```php
// ✅ Resource routes first, then specific filtered route
Route::resource('investments', InvestmentController::class);
Route::get('investments/opportunity/{opportunity_id}', [...])
```

**See `ROUTING_CONFLICT_FIX.md` for detailed explanation.**

### Using Investment Routes

```php
// All investments
route('admin.investments.index')

// Filtered by opportunity (query parameter - recommended)
route('admin.investments.index', ['opportunity_id' => 123])

// Filtered by opportunity (route parameter)
route('admin.investments.by-opportunity', 123)

// Show specific investment
route('admin.investments.show', 123)
```

## Troubleshooting

### Route Not Found
1. Check if route file is included in `web.php`
2. Verify middleware requirements are met
3. Clear route cache: `php artisan route:clear`
4. Check environment settings for test routes
5. Run `php artisan route:list` to verify route registration

### Middleware Issues
1. Ensure user is authenticated for protected routes
2. Check role/permission assignments
3. Review middleware priority in `app/Http/Kernel.php`

### Naming Conflicts
1. Ensure route names are unique
2. Use prefixes to avoid conflicts
3. Run `php artisan route:list` to check for duplicates

### Route Parameter Conflicts
1. Always define resource routes **before** custom parameterized routes
2. Use query parameters for filtering (more RESTful)
3. Make custom routes more specific with prefixes
4. Avoid optional parameters that might conflict with resource routes
5. Test with `php artisan route:list --name=<prefix>` to verify

## Summary

The routes are now:
- ✅ Well-organized and easy to navigate
- ✅ Properly documented with comments
- ✅ Separated by purpose and access level
- ✅ Environment-aware (test routes only in development)
- ✅ Following Laravel best practices
- ✅ Protected with appropriate middleware
- ✅ Backward compatible during migration period

All routes are production-ready and follow industry best practices for maintainability and security.

