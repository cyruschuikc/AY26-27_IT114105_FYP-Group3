-- =========================================================
-- TeamBox — Project creation module
-- 2026-10-20
-- =========================================================

ALTER TABLE `projects`
    ADD COLUMN `requirements` TEXT NULL AFTER `description`,
    ADD COLUMN `team_mode` ENUM('Free','Random','Teacher_Assigned') NOT NULL DEFAULT 'Free',
    ADD COLUMN `team_deadline` DATE NULL,
    ADD COLUMN `allow_cross_class` TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN `default_max_members` INT NOT NULL DEFAULT 4,
    ADD COLUMN `leader_permissions` TEXT NULL,
    ADD COLUMN `post_deadline_action` ENUM('Lock','Notify') NOT NULL DEFAULT 'Lock',
    ADD COLUMN `visibility` ENUM('All','Course') NOT NULL DEFAULT 'Course',
    ADD COLUMN `reminder_days` INT NOT NULL DEFAULT 3,
    ADD COLUMN `allow_extension` TINYINT(1) NOT NULL DEFAULT 1,
    ADD COLUMN `is_draft` TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN `last_reminder_sent` DATE NULL,
    ADD COLUMN `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ADD KEY `idx_projects_draft` (`is_draft`);

ALTER TABLE `announcements`
    ADD COLUMN `channels` VARCHAR(50) NOT NULL DEFAULT 'inapp',
    ADD COLUMN `status` ENUM('Draft','Scheduled','Sent') NOT NULL DEFAULT 'Sent',
    ADD COLUMN `scheduled_at` DATETIME NULL,
    ADD COLUMN `sent_at` DATETIME NULL,
    ADD COLUMN `email_sent_count` INT NOT NULL DEFAULT 0;

CREATE TABLE IF NOT EXISTS `project_attachments` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `project_id` INT(11) NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `display_name` VARCHAR(255) DEFAULT NULL,
    `stored_name` VARCHAR(255) NOT NULL,
    `file_size` VARCHAR(30) DEFAULT NULL,
    `file_type` VARCHAR(100) DEFAULT NULL,
    `uploaded_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `project_id` (`project_id`),
    CONSTRAINT `project_attachments_ibfk_1`
        FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `project_extension_requests` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `project_id` INT(11) NOT NULL,
    `student_id` INT(11) NOT NULL,
    `requested_end` DATE NOT NULL,
    `reason` TEXT DEFAULT NULL,
    `status` ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
    `reviewed_by` INT(11) DEFAULT NULL,
    `reviewed_at` DATETIME DEFAULT NULL,
    `review_note` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `project_id` (`project_id`),
    KEY `student_id` (`student_id`),
    KEY `reviewed_by` (`reviewed_by`),
    CONSTRAINT `project_ext_req_ibfk_1`
        FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
    CONSTRAINT `project_ext_req_ibfk_2`
        FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
    CONSTRAINT `project_ext_req_ibfk_3`
        FOREIGN KEY (`reviewed_by`) REFERENCES `teachers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
