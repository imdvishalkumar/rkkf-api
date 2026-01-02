# Database Migration Instructions

## Overview

All old migrations have been removed and replaced with new migrations that exactly match your existing database structure from `rkkf.sql`. These migrations preserve all field names, data types, and constraints exactly as they exist in your current database.

## Migration Files Created

The following migration files have been created to match your exact database structure:

1. `2025_01_02_100000_create_users_table.php` - Users table (Super Admin, Admin, Instructor)
2. `2025_01_02_100001_create_belt_table.php` - Belt/rank information
3. `2025_01_02_100002_create_branch_table.php` - Branch/location information
4. `2025_01_02_100003_create_students_table.php` - Students table
5. `2025_01_02_100004_create_attendance_table.php` - Attendance records
6. `2025_01_02_100005_create_coupon_table.php` - Coupon codes
7. `2025_01_02_100006_create_fees_table.php` - Fee payment records
8. `2025_01_02_100007_create_products_table.php` - Product information
9. `2025_01_02_100008_create_variation_table.php` - Product variations
10. `2025_01_02_100009_create_orders_table.php` - Order records
11. `2025_01_02_100010_create_exam_table.php` - Exam information
12. `2025_01_02_100011_create_event_table.php` - Event information
13. `2025_01_02_100012_create_enquire_table.php` - Enquiry/registration records
14. `2025_01_02_100013_create_exam_attendance_table.php` - Exam attendance
15. `2025_01_02_100014_create_exam_fees_table.php` - Exam fee payments
16. `2025_01_02_100015_create_event_attendance_table.php` - Event attendance
17. `2025_01_02_100016_create_event_fees_table.php` - Event fee payments
18. `2025_01_02_100017_create_fastrack_table.php` - Fastrack program records
19. `2025_01_02_100018_create_fastrack_attendance_table.php` - Fastrack attendance
20. `2025_01_02_100019_create_cart_table.php` - Shopping cart
21. `2025_01_02_100020_create_branch_text_table.php` - Branch text content
22. `2025_01_02_100021_create_devices_table.php` - Device registration
23. `2025_01_02_100022_create_fees_const_table.php` - Fee constants
24. `2025_01_02_100023_create_gmail_acc_table.php` - Gmail account storage
25. `2025_01_02_100024_create_guide_table.php` - Guide/documentation
26. `2025_01_02_100025_create_ins_timetable_table.php` - Instructor timetable
27. `2025_01_02_100026_create_invoice_table.php` - Invoice records
28. `2025_01_02_100027_create_leave_table.php` - Leave requests
29. `2025_01_02_100028_create_media_table.php` - Media files
30. `2025_01_02_100029_create_notification_table.php` - Notifications
31. `2025_01_02_100030_create_post_table.php` - News feed posts
32. `2025_01_02_100031_create_refund_table.php` - Refund records
33. `2025_01_02_100032_create_special_case_event_table.php` - Special event cases
34. `2025_01_02_100033_create_special_case_exam_table.php` - Special exam cases
35. `2025_01_02_100034_create_team_table.php` - Team members
36. `2025_01_02_100035_create_temp_exam_table.php` - Temporary exam records
37. `2025_01_02_100036_create_transcation_table.php` - Transaction records
38. `2025_01_02_100037_create_personal_access_tokens_table.php` - Laravel Sanctum tokens

## Important Notes

### Field Preservation
- All field names match exactly with your SQL file
- All data types match exactly (int(11), varchar(50), etc.)
- All field sizes match exactly
- All default values are preserved
- All nullable fields are preserved

### Primary Keys
- All tables use integer primary keys with auto-increment
- Primary key names match exactly (e.g., `user_id`, `student_id`, `attendance_id`)

### Data Types
- Integer fields use `integer()` (matches int(11) in SQL)
- String fields use `string()` with exact length (matches varchar in SQL)
- Date fields use `date()`
- Timestamp fields use `timestamp()` or `dateTime()` as appropriate
- Text fields use `longText()` for LONGTEXT
- Boolean fields use `tinyInteger()` for tinyint(1)

## How to Use

### Option 1: Fresh Database (Recommended for Testing)

If you want to test the migrations on a fresh database:

```bash
cd laravel-app
php artisan migrate:fresh
```

Then import your data:
```bash
mysql -u your_username -p your_database < rkkf.sql
```

### Option 2: Existing Database (Preserve Data)

If you already have the database with data:

1. **Mark migrations as run** (so Laravel knows they're already applied):
```bash
cd laravel-app
php artisan migrate:status
```

2. If tables already exist, you can manually mark migrations as run:
```bash
php artisan migrate --pretend
```

3. Or insert records into `migrations` table manually for each migration file.

### Option 3: Import SQL Directly (Skip Migrations)

If you want to use your existing database directly:

1. Import the SQL file:
```bash
mysql -u your_username -p your_database < rkkf.sql
```

2. Mark all migrations as run:
```bash
cd laravel-app
php artisan migrate:install
# Then manually insert migration records or use:
php artisan migrate --pretend
```

## Verification

After running migrations or importing data, verify the structure:

```bash
cd laravel-app
php artisan migrate:status
```

Check that all tables exist:
```sql
SHOW TABLES;
```

## Differences from Old Migrations

The new migrations:
- ✅ Use exact field names from your SQL file
- ✅ Use exact data types and sizes
- ✅ Preserve all nullable fields
- ✅ Preserve all default values
- ✅ Use integer primary keys (not `id()` which creates bigint)
- ✅ Match the exact structure for importing existing data

## Next Steps

1. Review the migrations to ensure they match your requirements
2. Test on a development database first
3. Import your existing data using the SQL file
4. Verify all tables and data are correct
5. Test your APIs with the imported data

## Troubleshooting

If you encounter issues:

1. **Table already exists**: The migrations check for existing tables. If you're importing SQL first, you may need to mark migrations as run.

2. **Data type mismatches**: All data types should match exactly. If you see errors, check the specific field in the migration file.

3. **Primary key issues**: All primary keys use `integer()` with `true` parameter to match `int(11) NOT NULL AUTO_INCREMENT`.

4. **Foreign key constraints**: The migrations don't include foreign key constraints to match your SQL file structure. Add them separately if needed.

## Support

If you need to modify any migration:
- Edit the specific migration file
- Run `php artisan migrate:refresh` (WARNING: This will drop and recreate tables, losing data)
- Or create a new migration to alter the table structure

