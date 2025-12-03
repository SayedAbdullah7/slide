# Set Default Files for Investment Opportunities Seeder

## Overview
This seeder adds missing default media files (terms, summary, cover images, and owner avatars) to existing InvestmentOpportunities in the database.

## Purpose
When InvestmentOpportunities are created without media files, this seeder can be used to automatically attach default files to them.

## Required Files Structure
The seeder expects the following files to exist in `storage/app/seeder_files/`:

```
storage/app/seeder_files/
├── sample_terms.pdf          # Terms and conditions PDF
├── sample_summary.pdf        # Summary/overview PDF
├── covers/                    # Cover images folder
│   ├── image1.jpg
│   ├── image2.png
│   └── ...
└── avatars/                   # Owner avatar images folder
    ├── avatar1.jpg
    ├── avatar2.png
    └── ...
```

## Usage

### Run the Seeder Standalone
```bash
php artisan db:seed --class=SetDefaultFilesForInvestmentOpportunitiesSeeder
```

### Or Call from Another Seeder
```php
$this->call(SetDefaultFilesForInvestmentOpportunitiesSeeder::class);
```

## What It Does

1. **Checks for Required Files**: Validates that all sample files exist
2. **Finds Missing Files**: For each InvestmentOpportunity, checks if media files are missing:
   - Terms PDF (`terms` collection)
   - Summary PDF (`summary` collection)
   - Cover images (`cover` collection)
   - Owner avatar (`owner_avatar` collection)
3. **Adds Default Files**: Attaches the default sample files to opportunities that are missing them
4. **Reports Statistics**: Shows a summary of what was added

## Media Collections Used

Based on the `InvestmentOpportunity` model:
- **terms**: PDF file for terms and conditions (single file)
- **summary**: PDF file for summary/overview (single file)
- **cover**: Multiple cover images (2-5 images)
- **owner_avatar**: Owner profile photo (single file)

## Example Output

```
🔄 Starting to set default files for InvestmentOpportunities...
Found 15 investment opportunities to process...
✅ Added terms to opportunity #1 (مشروع Test - حالي #1)
✅ Added summary to opportunity #1 (مشروع Test - حالي #1)
✅ Added cover images to opportunity #1 (مشروع Test - حالي #1)
✅ Added owner avatar to opportunity #1 (مشروع Test - حالي #1)
...

✅ Process completed!
📊 Statistics:
   - Total opportunities processed: 15
   - Opportunities updated: 12
   - Terms files added: 12
   - Summary files added: 12
   - Cover images added: 36
   - Owner avatars added: 12
```

## Notes

- The seeder only adds files if they are missing (doesn't overwrite existing files)
- For cover images, it adds 3 random images from the covers folder
- If sample files don't exist, the seeder will show an error with instructions
- The seeder uses the same file structure as `InvestmentOpportunitySeeder`

## Related Files

- `database/seeders/SetDefaultFilesForInvestmentOpportunitiesSeeder.php` - This seeder
- `database/seeders/InvestmentOpportunitySeeder.php` - Main opportunity seeder
- `app/Models/InvestmentOpportunity.php` - The model definition
- `app/Http/Resources/InvestmentOpportunityResource.php` - API resource

