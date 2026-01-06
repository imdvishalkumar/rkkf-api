# SQL Queries for Category and Event Tables

## Quick Reference Guide

---

## 1. CREATE CATEGORIES TABLE

```sql
CREATE TABLE IF NOT EXISTS `categories` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

---

## 2. CREATE EVENT TABLE

```sql
CREATE TABLE IF NOT EXISTS `event` (
  `event_id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `venue` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `description` varchar(250) NOT NULL,
  `fees` int(11) NOT NULL DEFAULT 0,
  `fees_due_date` date NOT NULL,
  `penalty` int(10) NOT NULL DEFAULT 0,
  `penalty_due_date` date NOT NULL,
  `isPublished` tinyint(1) DEFAULT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `image` text DEFAULT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `likes` int(11) DEFAULT 0,
  `comments` int(11) DEFAULT 0,
  `shares` int(11) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`event_id`),
  KEY `idx_event_category` (`category_id`),
  CONSTRAINT `fk_event_category` FOREIGN KEY (`category_id`) 
    REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

---

## 3. INSERT CATEGORY

### Simple Insert
```sql
INSERT INTO `categories` (`name`, `description`, `active`, `created_at`, `updated_at`) 
VALUES ('Tournaments', 'Competitive tournaments', 1, NOW(), NOW());
```

### Multiple Categories
```sql
INSERT INTO `categories` (`name`, `description`, `active`, `created_at`, `updated_at`) VALUES
('Tournaments', 'Competitive tournaments and championships', 1, NOW(), NOW()),
('Workshops', 'Training workshops and skill development', 1, NOW(), NOW()),
('Camps', 'Outdoor camps and adventure activities', 1, NOW(), NOW()),
('Seminars', 'Educational seminars and conferences', 1, NOW(), NOW());
```

---

## 4. INSERT EVENT

### Method 1: Using Category Name (Subquery)
```sql
INSERT INTO `event` (
  `name`, `from_date`, `to_date`, `venue`, `type`, `description`, 
  `fees`, `fees_due_date`, `penalty`, `penalty_due_date`, 
  `isPublished`, `category_id`, `subtitle`, `active`
) VALUES (
  'State Level Karate Championship',
  '2025-03-15',
  '2025-03-17',
  'Sports Complex',
  'TOURNAMENT',
  'Annual state level karate championship',
  1500,
  '2025-03-10',
  200,
  '2025-03-12',
  1,
  (SELECT id FROM categories WHERE name = 'Tournaments' LIMIT 1),
  'Compete for State Championship',
  1
);
```

### Method 2: Using Direct Category ID
```sql
INSERT INTO `event` (
  `name`, `from_date`, `to_date`, `venue`, `type`, `description`, 
  `fees`, `fees_due_date`, `penalty`, `penalty_due_date`, 
  `isPublished`, `category_id`, `subtitle`, `active`
) VALUES (
  'Regional Tournament',
  '2025-04-10',
  '2025-04-10',
  'Regional Sports Center',
  'TOURNAMENT',
  'Regional level tournament',
  1000,
  '2025-04-05',
  150,
  '2025-04-07',
  1,
  1,  -- Direct category_id
  'Qualify for State',
  1
);
```

### Method 3: Minimal Required Fields
```sql
INSERT INTO `event` (
  `name`, `from_date`, `to_date`, `venue`, `type`, `description`, 
  `fees`, `fees_due_date`, `penalty`, `penalty_due_date`
) VALUES (
  'Basic Event',
  '2025-05-01',
  '2025-05-01',
  'Main Hall',
  'GENERAL',
  'Basic event description',
  0,
  '2025-05-01',
  0,
  '2025-05-01'
);
```

---

## 5. USEFUL SELECT QUERIES

### Get All Categories
```sql
SELECT * FROM `categories` WHERE `active` = 1;
```

### Get All Events with Category Name
```sql
SELECT 
  e.event_id,
  e.name,
  e.from_date,
  e.to_date,
  e.venue,
  e.fees,
  c.name AS category_name
FROM `event` e
LEFT JOIN `categories` c ON e.category_id = c.id
WHERE e.active = 1
ORDER BY e.from_date DESC;
```

### Get Events by Category
```sql
SELECT e.*, c.name AS category_name
FROM `event` e
INNER JOIN `categories` c ON e.category_id = c.id
WHERE c.name = 'Tournaments'
ORDER BY e.from_date DESC;
```

### Get Events by Category ID
```sql
SELECT * FROM `event` 
WHERE `category_id` = 1 
AND `active` = 1
ORDER BY `from_date` DESC;
```

---

## 6. UPDATE QUERIES

### Update Event Category
```sql
UPDATE `event` 
SET `category_id` = (SELECT id FROM categories WHERE name = 'Tournaments' LIMIT 1)
WHERE `event_id` = 1;
```

### Update Category
```sql
UPDATE `categories` 
SET `name` = 'Competitions', `description` = 'Updated description'
WHERE `id` = 1;
```

---

## 7. API Usage Examples

### Create Category via API
```bash
POST /api/categories
Content-Type: application/json
Authorization: Bearer {token}

{
  "name": "Tournaments",
  "description": "Competitive tournaments",
  "active": true
}
```

### Create Event via API
```bash
POST /api/events
Content-Type: application/json
Authorization: Bearer {token}

{
  "title": "State Level Championship",
  "event_start_datetime": "2025-03-15",
  "event_end_datetime": "2025-03-17",
  "venue": "Sports Complex",
  "type": "TOURNAMENT",
  "description": "Annual championship",
  "fees": 1500,
  "fees_due_date": "2025-03-10",
  "penalty": 200,
  "penalty_due_date": "2025-03-12",
  "category_id": 1,
  "subtitle": "Compete for State Title",
  "isPublished": true
}
```

### Get Events by Category
```bash
GET /api/events?category_id=1
Authorization: Bearer {token}
```

---

## Field Descriptions

### Categories Table
- `id` - Primary key (auto-increment)
- `name` - Category name (required)
- `description` - Category description (optional)
- `active` - Active status (default: 1)
- `created_at` - Creation timestamp
- `updated_at` - Update timestamp

### Event Table
- `event_id` - Primary key (auto-increment)
- `name` - Event name (required, max 50 chars)
- `from_date` - Event start date (required)
- `to_date` - Event end date (required)
- `venue` - Event venue (required, max 50 chars)
- `type` - Event type (required, max 50 chars)
- `description` - Event description (required, max 250 chars)
- `fees` - Event fees (default: 0)
- `fees_due_date` - Fees payment due date (required)
- `penalty` - Late penalty amount (default: 0)
- `penalty_due_date` - Penalty due date (required)
- `isPublished` - Published status (nullable)
- `category_id` - Foreign key to categories table (nullable)
- `image` - Event image URL/path (optional)
- `subtitle` - Event subtitle (optional)
- `likes` - Number of likes (default: 0)
- `comments` - Number of comments (default: 0)
- `shares` - Number of shares (default: 0)
- `active` - Active status (default: 1)

