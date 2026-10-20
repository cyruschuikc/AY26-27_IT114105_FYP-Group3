-- =========================================================
-- Demo task attachments for Team 01 and Team 02
-- 2026-10-20
-- =========================================================

UPDATE `tasks`
SET `title` = 'Prototype Review',
    `description` = 'Review the latest prototype and collect feedback for the next iteration.'
WHERE `id` = 18;

UPDATE `activities`
SET `detail` = 'created task "Prototype Review"'
WHERE `id` = 26;

UPDATE `notifications`
SET `message` = 'You have been assigned "Prototype Review".'
WHERE `id` = 12;

INSERT INTO `files`
    (`project_id`, `group_id`, `task_id`, `uploader_id`, `file_name`, `display_name`,
     `notes`, `stored_name`, `file_size`, `file_type`, `status`, `uploaded_at`,
     `reviewed_by`, `reviewed_at`, `review_note`)
VALUES
    (1, 1, 1, 1, 'Project_Proposal.txt', 'Project Proposal', NULL,
     'demo_project_proposal_v1.txt', '12 KB', 'TXT', 'Rejected', '2026-09-23 10:00:00',
     1, '2026-09-24 09:00:00', 'Please add the project scope and timeline.'),

    (1, 1, 1, 1, 'Project_Proposal.txt', 'Project Proposal', NULL,
     'demo_project_proposal_v2.txt', '18 KB', 'TXT', 'Accepted', '2026-09-27 15:30:00',
     1, '2026-09-28 10:00:00', 'Scope and timeline look good.'),

    (1, 1, 2, 2, 'Requirement_Specification.txt', 'Requirement Specification', NULL,
     'demo_requirement_spec.txt', '9 KB', 'TXT', 'Unviewed', '2026-10-03 11:00:00',
     NULL, NULL, NULL),

    (1, 1, 3, 3, 'Dashboard_Wireframe.png', 'Dashboard Wireframe', NULL,
     'demo_dashboard_wireframe.png', '240 KB', 'PNG', 'Unviewed', '2026-10-08 14:20:00',
     NULL, NULL, NULL),

    (1, 1, 3, 3, 'UI_Flow.png', 'UI Flow', NULL,
     'demo_ui_flow.png', '180 KB', 'PNG', 'Unviewed', '2026-10-09 16:00:00',
     NULL, NULL, NULL),

    (1, 1, 4, 4, 'Database_Schema.sql', 'Database Schema', NULL,
     'demo_database_schema.sql', '22 KB', 'SQL', 'Unviewed', '2026-10-12 09:40:00',
     NULL, NULL, NULL),

    (1, 1, 19, 2, 'Notification_Research_Notes.txt', 'Notification Research Notes', NULL,
     'demo_notification_research.txt', '6 KB', 'TXT', 'Unviewed', '2026-10-18 17:10:00',
     NULL, NULL, NULL),

    (1, 2, 5, 3, 'Android_Prototype.zip', 'Android Prototype', NULL,
     'demo_android_prototype_v1.zip', '1.2 MB', 'ZIP', 'Rejected', '2026-10-14 10:00:00',
     1, '2026-10-15 09:30:00', 'Build fails on the login screen.'),

    (1, 2, 5, 3, 'Android_Prototype.zip', 'Android Prototype', NULL,
     'demo_android_prototype_v2.zip', '1.3 MB', 'ZIP', 'Unviewed', '2026-10-19 11:20:00',
     NULL, NULL, NULL);
