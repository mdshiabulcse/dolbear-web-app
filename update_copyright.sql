-- SQL Script to Remove DMCA Protection and Update Copyright
-- Run this in your database via phpMyAdmin or command line

-- 1. Update the admin panel copyright text
UPDATE `settings`
SET `value` = '2026 All Rights Reserved by © Dolbear'
WHERE `title` = 'admin_panel_copyright_text';

-- 2. Clear any purchase code (if exists)
UPDATE `settings`
SET `value` = ''
WHERE `title` = 'purchase_code';

-- 3. Verify the changes
SELECT * FROM `settings` WHERE `title` IN ('admin_panel_copyright_text', 'purchase_code');
