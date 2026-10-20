-- =========================================================
-- Pending-approval team cleanup
-- Pending teams must have no student tasks, files or chat.
-- =========================================================

START TRANSACTION;

-- Team 02 and Team 03 stay as pending-approval examples.
DELETE FROM `files` WHERE `group_id` IN (2, 3);
DELETE FROM `tasks` WHERE `group_id` IN (2, 3);
DELETE FROM `messages` WHERE `group_id` IN (2, 3);
DELETE FROM `activities` WHERE `group_id` IN (2, 3);

-- LAI Ka-Yu Savio stays in Team 01 only.
DELETE FROM `group_members`
WHERE `group_id` = 2 AND `student_id` = 3;

UPDATE `group_members`
SET `role` = 'leader'
WHERE `group_id` = 2 AND `student_id` = 6;

UPDATE `groups`
SET `status` = 'Waiting_Send',
    `leader_id` = 6,
    `creator_id` = 6
WHERE `id` = 2;

-- The Team 02 request is now submitted by its new leader, LEE Chun Kit.
UPDATE `group_requests`
SET `submitted_by` = 6
WHERE `id` = 1;

DELETE FROM `request_members`
WHERE `request_id` = 1 AND `student_id` = 3;

UPDATE `request_members`
SET `is_leader` = 1
WHERE `request_id` = 1 AND `student_id` = 6;

-- Add two unassigned students to the Team 02 pending example.
INSERT INTO `group_members` (`group_id`, `student_id`, `role`)
VALUES
    (2, 24, 'member'),
    (2, 25, 'member')
ON DUPLICATE KEY UPDATE `role` = VALUES(`role`);

INSERT INTO `request_members` (`request_id`, `student_id`, `is_leader`)
VALUES
    (1, 24, 0),
    (1, 25, 0)
ON DUPLICATE KEY UPDATE `is_leader` = VALUES(`is_leader`);

COMMIT;
