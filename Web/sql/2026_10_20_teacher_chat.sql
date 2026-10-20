-- =========================================================
-- Teacher group chat
-- 2026-10-20
-- =========================================================

ALTER TABLE `messages`
    MODIFY `sender_id` INT(11) NULL,
    ADD COLUMN `sender_user_id` INT(11) NULL AFTER `sender_id`,
    ADD KEY `idx_messages_sender_user` (`sender_user_id`),
    ADD CONSTRAINT `fk_messages_sender_user`
        FOREIGN KEY (`sender_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
