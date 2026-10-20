-- =========================================================
-- Replace demo task names with readable English examples
-- 2026-10-20
-- =========================================================

UPDATE `tasks`
SET `title` = 'Notification Bell Research',
    `description` = 'Review existing notification patterns and document the icon requirements for TeamBox.'
WHERE `id` = 19;

UPDATE `tasks`
SET `title` = 'Notification Bell Icon',
    `description` = 'Design the notification bell icon used in the teacher and student headers.'
WHERE `id` = 20;

UPDATE `tasks`
SET `title` = 'Bell Interaction States',
    `description` = 'Prepare hover and unread states for the notification bell and upload the final assets.'
WHERE `id` = 21;

UPDATE `activities`
SET `detail` = REPLACE(`detail`, '"GFHJK"', '"Notification Bell Icon"')
WHERE `detail` LIKE '%GFHJK%';

UPDATE `activities`
SET `detail` = REPLACE(`detail`, '"ABC"', '"Bell Interaction States"')
WHERE `detail` LIKE '%ABC%';

UPDATE `notifications`
SET `message` = REPLACE(`message`, '"GFHJK"', '"Notification Bell Icon"')
WHERE `message` LIKE '%GFHJK%';

UPDATE `notifications`
SET `message` = REPLACE(`message`, '"ABC"', '"Bell Interaction States"')
WHERE `message` LIKE '%ABC%';

UPDATE `contributions`
SET `notes` = REPLACE(`notes`, '"GFHJK"', '"Notification Bell Icon"')
WHERE `notes` LIKE '%GFHJK%';
