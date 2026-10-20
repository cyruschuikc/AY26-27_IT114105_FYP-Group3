-- =========================================================
-- Teacher chat: pinned group announcements
-- 2026-10-20
-- =========================================================

ALTER TABLE `messages`
    ADD COLUMN `is_pinned` TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN `pinned_at` DATETIME NULL,
    ADD COLUMN `pinned_by_user_id` INT(11) NULL,
    ADD KEY `idx_messages_pinned` (`group_id`, `is_pinned`),
    ADD CONSTRAINT `fk_messages_pinned_by`
        FOREIGN KEY (`pinned_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
