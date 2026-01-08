-- =====================================================
-- SQL QUERIES FOR CATEGORY AND EVENT TABLES
-- =====================================================

-- =====================================================
-- 1. CREATE CATEGORIES TABLE
-- =====================================================

CREATE TABLE IF NOT EXISTS `categories` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_categories_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 2. CREATE EVENT TABLE (if not exists)
-- =====================================================

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
  KEY `idx_event_dates` (`from_date`, `to_date`),
  KEY `idx_event_published` (`isPublished`),
  CONSTRAINT `fk_event_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 3. INSERT SAMPLE CATEGORIES
-- =====================================================

INSERT INTO `categories` (`name`, `description`, `active`, `created_at`, `updated_at`) VALUES
('Tournaments', 'Competitive tournaments and championships', 1, NOW(), NOW()),
('Workshops', 'Training workshops and skill development sessions', 1, NOW(), NOW()),
('Camps', 'Outdoor camps and adventure activities', 1, NOW(), NOW()),
('Seminars', 'Educational seminars and conferences', 1, NOW(), NOW()),
('Exhibitions', 'Martial arts exhibitions and demonstrations', 1, NOW(), NOW()),
('Social Events', 'Community and social gathering events', 1, NOW(), NOW());

-- =====================================================
-- 4. INSERT SAMPLE EVENTS
-- =====================================================

-- Example 1: Tournament Event
INSERT INTO `event` (
  `name`, 
  `from_date`, 
  `to_date`, 
  `venue`, 
  `type`, 
  `description`, 
  `fees`, 
  `fees_due_date`, 
  `penalty`, 
  `penalty_due_date`, 
  `isPublished`, 
  `category_id`,
  `subtitle`,
  `active`
) VALUES (
  'State Level Karate Championship',
  '2025-03-15',
  '2025-03-17',
  'Sports Complex',
  'TOURNAMENT',
  'Annual state level karate championship for all belts',
  1500,
  '2025-03-10',
  200,
  '2025-03-12',
  1,
  (SELECT id FROM categories WHERE name = 'Tournaments' LIMIT 1),
  'Compete for State Championship Title',
  1
);

-- Example 2: Workshop Event
INSERT INTO `event` (
  `name`, 
  `from_date`, 
  `to_date`, 
  `venue`, 
  `type`, 
  `description`, 
  `fees`, 
  `fees_due_date`, 
  `penalty`, 
  `penalty_due_date`, 
  `isPublished`, 
  `category_id`,
  `subtitle`,
  `active`
) VALUES (
  'Advanced Kata Workshop',
  '2025-02-20',
  '2025-02-20',
  'Main Dojo',
  'WORKSHOP',
  'Learn advanced kata techniques from master instructors',
  500,
  '2025-02-18',
  50,
  '2025-02-19',
  1,
  (SELECT id FROM categories WHERE name = 'Workshops' LIMIT 1),
  'Master Advanced Kata Techniques',
  1
);

-- Example 3: Camp Event
INSERT INTO `event` (
  `name`, 
  `from_date`, 
  `to_date`, 
  `venue`, 
  `type`, 
  `description`, 
  `fees`, 
  `fees_due_date`, 
  `penalty`, 
  `penalty_due_date`, 
  `isPublished`, 
  `category_id`,
  `subtitle`,
  `active`
) VALUES (
  'Summer Training Camp',
  '2025-06-01',
  '2025-06-05',
  'Mountain Resort',
  'OUTDOOR CAMP',
  'Intensive 5-day summer training camp with outdoor activities',
  3500,
  '2025-05-25',
  300,
  '2025-05-28',
  1,
  (SELECT id FROM categories WHERE name = 'Camps' LIMIT 1),
  '5 Days of Intensive Training',
  1
);

-- =====================================================
-- 5. ALTERNATIVE: INSERT EVENT WITH EXPLICIT CATEGORY_ID
-- =====================================================

-- If you know the category_id (e.g., category_id = 1 for Tournaments)
INSERT INTO `event` (
  `name`, 
  `from_date`, 
  `to_date`, 
  `venue`, 
  `type`, 
  `description`, 
  `fees`, 
  `fees_due_date`, 
  `penalty`, 
  `penalty_due_date`, 
  `isPublished`, 
  `category_id`,
  `subtitle`,
  `active`
) VALUES (
  'Regional Karate Tournament',
  '2025-04-10',
  '2025-04-10',
  'Regional Sports Center',
  'TOURNAMENT',
  'Regional level tournament for qualifying to state championship',
  1000,
  '2025-04-05',
  150,
  '2025-04-07',
  1,
  1,  -- Direct category_id (assuming Tournaments has id = 1)
  'Qualify for State Championship',
  1
);

-- =====================================================
-- 6. QUERIES TO VIEW DATA
-- =====================================================

-- View all categories
SELECT * FROM `categories` WHERE `active` = 1;

-- View all events with category name
SELECT 
  e.event_id,
  e.name,
  e.subtitle,
  e.from_date,
  e.to_date,
  e.venue,
  e.type,
  e.description,
  e.fees,
  e.fees_due_date,
  e.penalty,
  e.penalty_due_date,
  e.isPublished,
  e.active,
  c.name AS category_name,
  c.description AS category_description
FROM `event` e
LEFT JOIN `categories` c ON e.category_id = c.id
WHERE e.active = 1
ORDER BY e.from_date DESC;

-- View events by category
SELECT 
  e.*,
  c.name AS category_name
FROM `event` e
INNER JOIN `categories` c ON e.category_id = c.id
WHERE c.name = 'Tournaments'
ORDER BY e.from_date DESC;

-- =====================================================
-- 7. UPDATE QUERIES
-- =====================================================

-- Update event category
UPDATE `event` 
SET `category_id` = (SELECT id FROM categories WHERE name = 'Tournaments' LIMIT 1)
WHERE `event_id` = 1;

-- Update category
UPDATE `categories` 
SET `name` = 'Competitions', `description` = 'Updated description'
WHERE `id` = 1;

-- =====================================================
-- 8. DELETE QUERIES (Use with caution!)
-- =====================================================

-- Delete event (soft delete by setting active = 0)
UPDATE `event` SET `active` = 0 WHERE `event_id` = 1;

-- Delete category (only if no events reference it)
DELETE FROM `categories` WHERE `id` = 1 AND NOT EXISTS (
  SELECT 1 FROM `event` WHERE `category_id` = 1
);


