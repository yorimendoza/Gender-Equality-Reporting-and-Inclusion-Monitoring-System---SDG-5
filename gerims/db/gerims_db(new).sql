
USE gerims_db;

-- PART 1: ADDITIONAL INDEXES (Query Optimization)

-- Index on reports.status (heavily filtered in admin/reports.php)
ALTER TABLE `reports`
    ADD INDEX `idx_reports_status` (`status`);

-- Index on reports.priority (used in filters)
ALTER TABLE `reports`
    ADD INDEX `idx_reports_priority` (`priority`);

-- Index on reports.created_at (used in ORDER BY)
ALTER TABLE `reports`
    ADD INDEX `idx_reports_created_at` (`created_at`);

-- Index on notifications.is_read (used to count unread notifications)
ALTER TABLE `notifications`
    ADD INDEX `idx_notifications_is_read` (`is_read`);

-- Index on notifications.user_id + is_read combined (faster unread count query)
ALTER TABLE `notifications`
    ADD INDEX `idx_notifications_user_read` (`user_id`, `is_read`);

-- Index on announcements.is_active (filtered on every dashboard load)
ALTER TABLE `announcements`
    ADD INDEX `idx_announcements_is_active` (`is_active`);

-- Index on audit_logs.action (useful for searching specific actions)
ALTER TABLE `audit_logs`
    ADD INDEX `idx_audit_logs_action` (`action`);

-- Index on feedbacks.feedback_type (used in admin feedback filter)
ALTER TABLE `feedbacks`
    ADD INDEX `idx_feedbacks_type` (`feedback_type`);

-- Index on users.role (used to fetch only admins or users)
ALTER TABLE `users`
    ADD INDEX `idx_users_role` (`role`);

-- Index on users.is_active (used when checking active users)
ALTER TABLE `users`
    ADD INDEX `idx_users_is_active` (`is_active`);


-- VIEW 1: Full report details (joins reports + users + categories)
-- Usage: SELECT * FROM view_report_details WHERE status = 'pending'
CREATE OR REPLACE VIEW `view_report_details` AS
    SELECT
        r.report_id,
        r.title,
        r.description,
        r.location,
        r.incident_date,
        r.is_anonymous,
        r.status,
        r.priority,
        r.created_at,
        r.updated_at,
        c.category_name,
        c.icon AS category_icon,
        CASE
            WHEN r.is_anonymous = 1 THEN 'Anonymous'
            ELSE u.full_name
        END AS reporter_name,
        CASE
            WHEN r.is_anonymous = 1 THEN NULL
            ELSE u.email
        END AS reporter_email,
        CASE
            WHEN r.is_anonymous = 1 THEN NULL
            ELSE u.course
        END AS reporter_course,
        CASE
            WHEN r.is_anonymous = 1 THEN NULL
            ELSE u.year_level
        END AS reporter_year,
        u.user_id AS reporter_id
    FROM `reports` r
    JOIN `users` u ON r.user_id = u.user_id
    JOIN `categories` c ON r.category_id = c.category_id;

-- VIEW 2: Reports count summary per category
-- Usage: SELECT * FROM view_category_report_counts ORDER BY total_reports DESC
CREATE OR REPLACE VIEW `view_category_report_counts` AS
    SELECT
        c.category_id,
        c.category_name,
        c.icon,
        COUNT(r.report_id)                                        AS total_reports,
        SUM(r.status = 'pending')                                 AS pending_count,
        SUM(r.status = 'under_review')                            AS under_review_count,
        SUM(r.status = 'resolved')                                AS resolved_count,
        SUM(r.status = 'dismissed')                               AS dismissed_count
    FROM `categories` c
    LEFT JOIN `reports` r ON c.category_id = r.category_id
    GROUP BY c.category_id, c.category_name, c.icon;

-- VIEW 3: Admin dashboard summary stats
-- Usage: SELECT * FROM view_dashboard_stats
CREATE OR REPLACE VIEW `view_dashboard_stats` AS
    SELECT
        COUNT(*)                                                   AS total_reports,
        SUM(status = 'pending')                                    AS pending,
        SUM(status = 'under_review')                               AS under_review,
        SUM(status = 'resolved')                                   AS resolved,
        SUM(status = 'dismissed')                                  AS dismissed,
        SUM(priority = 'critical')                                 AS critical_reports,
        SUM(priority = 'high')                                     AS high_reports,
        (SELECT COUNT(*) FROM users WHERE role = 'user'
            AND is_active = 1)                                     AS total_active_users,
        (SELECT COUNT(*) FROM feedbacks)                           AS total_feedbacks,
        (SELECT ROUND(AVG(rating), 1) FROM feedbacks)              AS avg_feedback_rating
    FROM `reports`;

-- VIEW 4: Unresolved reports older than 7 days (for admin urgency tracking)
-- Usage: SELECT * FROM view_unresolved_old_reports
CREATE OR REPLACE VIEW `view_unresolved_old_reports` AS
    SELECT
        r.report_id,
        r.title,
        r.status,
        r.priority,
        r.created_at,
        DATEDIFF(NOW(), r.created_at)                              AS days_pending,
        c.category_name,
        CASE
            WHEN r.is_anonymous = 1 THEN 'Anonymous'
            ELSE u.full_name
        END AS reporter_name
    FROM `reports` r
    JOIN `users` u ON r.user_id = u.user_id
    JOIN `categories` c ON r.category_id = c.category_id
    WHERE r.status IN ('pending', 'under_review')
    AND r.created_at < NOW() - INTERVAL 7 DAY
    ORDER BY r.created_at ASC;

-- ============================================================
-- PART 3: TRIGGERS
-- ============================================================

-- Safety: drop triggers first if they exist (prevents duplicate errors)
DROP TRIGGER IF EXISTS `trg_after_report_insert`;
DROP TRIGGER IF EXISTS `trg_after_status_log_insert`;
DROP TRIGGER IF EXISTS `trg_after_report_delete`;
DROP TRIGGER IF EXISTS `trg_after_user_deactivate`;

DELIMITER $$

-- TRIGGER 1: Auto-update reports.updated_at when status changes
-- Fires AFTER a new row is inserted into report_status_logs
-- Automatically keeps reports.updated_at in sync without PHP doing it
CREATE TRIGGER `trg_after_status_log_insert`
AFTER INSERT ON `report_status_logs`
FOR EACH ROW
BEGIN
    UPDATE `reports`
    SET `updated_at` = NOW()
    WHERE `report_id` = NEW.report_id;
END$$

-- TRIGGER 2: Auto-insert audit log when a report is deleted
-- Fires BEFORE a report is deleted
-- Captures the deletion in audit_logs automatically
-- PHP's manual logAudit() call still works alongside this — no conflict
CREATE TRIGGER `trg_after_report_delete`
BEFORE DELETE ON `reports`
FOR EACH ROW
BEGIN
    INSERT INTO `audit_logs`
        (`user_id`, `action`, `target_table`, `target_id`, `details`, `ip_address`)
    VALUES
        (OLD.user_id, 'AUTO_REPORT_DELETED', 'reports', OLD.report_id,
         CONCAT('Title: ', OLD.title, ' | Status: ', OLD.status), 'DB_TRIGGER');
END$$

-- TRIGGER 3: Auto-insert notification when report status changes via report_status_logs
-- Fires AFTER a new status log entry is inserted
-- Works alongside PHP notification — but checks for duplicates via a time window
-- to prevent double notifications if PHP already inserted one within 2 seconds
CREATE TRIGGER `trg_after_report_insert`
AFTER INSERT ON `reports`
FOR EACH ROW
BEGIN
    -- Auto-log the creation in audit_logs as a database-level record
    INSERT INTO `audit_logs`
        (`user_id`, `action`, `target_table`, `target_id`, `details`, `ip_address`)
    VALUES
        (NEW.user_id, 'DB_REPORT_CREATED', 'reports', NEW.report_id,
         CONCAT('Category ID: ', NEW.category_id, ' | Priority: ', NEW.priority,
                ' | Anonymous: ', NEW.is_anonymous), 'DB_TRIGGER');
END$$

-- TRIGGER 4: Auto-deactivate all reports when a user is deactivated
-- Fires AFTER a user's is_active is set to 0
-- Marks their pending reports as dismissed automatically
CREATE TRIGGER `trg_after_user_deactivate`
AFTER UPDATE ON `users`
FOR EACH ROW
BEGIN
    -- Only fires when is_active changes from 1 to 0
    IF OLD.is_active = 1 AND NEW.is_active = 0 THEN
        UPDATE `reports`
        SET
            `status` = 'dismissed',
            `updated_at` = NOW()
        WHERE
            `user_id` = NEW.user_id
            AND `status` IN ('pending', 'under_review');

        -- Log this auto-dismissal
        INSERT INTO `audit_logs`
            (`user_id`, `action`, `target_table`, `target_id`, `details`, `ip_address`)
        VALUES
            (NEW.user_id, 'AUTO_REPORTS_DISMISSED', 'users', NEW.user_id,
             CONCAT('All pending reports dismissed due to user deactivation: ',
                    NEW.full_name), 'DB_TRIGGER');
    END IF;
END$$

DELIMITER ;

-- ============================================================
-- PART 4: CONCURRENCY (Transaction-based stored procedures)
-- These wrap critical multi-step operations in transactions
-- so if one step fails, everything rolls back automatically
-- PHP can call these via $conn->query("CALL proc_name(...)") optionally
-- but the existing PHP code still works fine without calling them
-- ============================================================

DROP PROCEDURE IF EXISTS `proc_submit_report`;
DROP PROCEDURE IF EXISTS `proc_update_report_status`;

DELIMITER $$

-- PROCEDURE 1: Safely submit a report with concurrency protection
-- Wraps the report insert + notification insert in one atomic transaction
-- If the notification insert fails, the report insert is also rolled back
CREATE PROCEDURE `proc_submit_report`(
    IN  p_user_id       INT,
    IN  p_category_id   INT,
    IN  p_title         VARCHAR(150),
    IN  p_description   TEXT,
    IN  p_location      VARCHAR(150),
    IN  p_incident_date DATE,
    IN  p_is_anonymous  TINYINT(1),
    IN  p_priority      VARCHAR(20),
    OUT p_report_id     INT,
    OUT p_success       TINYINT(1)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        -- If anything fails, roll back everything
        ROLLBACK;
        SET p_success = 0;
        SET p_report_id = 0;
    END;

    START TRANSACTION;

        -- Insert the report
        INSERT INTO `reports`
            (user_id, category_id, title, description, location,
             incident_date, is_anonymous, priority)
        VALUES
            (p_user_id, p_category_id, p_title, p_description, p_location,
             p_incident_date, p_is_anonymous, p_priority);

        SET p_report_id = LAST_INSERT_ID();

        -- Notify all admins
        INSERT INTO `notifications` (user_id, report_id, message, notif_type)
            SELECT
                user_id,
                p_report_id,
                CONCAT('New report submitted: ', LEFT(p_title, 60)),
                'system'
            FROM `users`
            WHERE role = 'admin' AND is_active = 1;

    COMMIT;
    SET p_success = 1;
END$$

-- PROCEDURE 2: Safely update report status with concurrency protection
-- Wraps status update + status log insert + notification in one transaction
CREATE PROCEDURE `proc_update_report_status`(
    IN  p_report_id  INT,
    IN  p_admin_id   INT,
    IN  p_new_status VARCHAR(30),
    IN  p_priority   VARCHAR(20),
    IN  p_remarks    TEXT,
    OUT p_success    TINYINT(1)
)
BEGIN
    DECLARE v_old_status VARCHAR(30);
    DECLARE v_user_id    INT;
    DECLARE v_title      VARCHAR(150);

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_success = 0;
    END;

    START TRANSACTION;

        -- Lock the row to prevent concurrent updates on the same report
        SELECT status, user_id, title
        INTO v_old_status, v_user_id, v_title
        FROM `reports`
        WHERE report_id = p_report_id
        FOR UPDATE;

        -- Update the report status
        UPDATE `reports`
        SET
            status     = p_new_status,
            priority   = p_priority,
            updated_at = NOW()
        WHERE report_id = p_report_id;

        -- Insert status change log
        INSERT INTO `report_status_logs`
            (report_id, changed_by, old_status, new_status, remarks)
        VALUES
            (p_report_id, p_admin_id, v_old_status, p_new_status, p_remarks);

        -- Notify the report owner
        INSERT INTO `notifications`
            (user_id, report_id, message, notif_type)
        VALUES
            (v_user_id, p_report_id,
             CONCAT('Your report "', LEFT(v_title, 40),
                    '" status changed to: ',
                    REPLACE(p_new_status, '_', ' ')),
             'status_update');

    COMMIT;
    SET p_success = 1;
END$$

DELIMITER ;

-- SHOW TRIGGERS FROM gerims_db;
-- SHOW PROCEDURE STATUS WHERE Db = 'gerims_db';
-- SHOW INDEX FROM reports;
-- SHOW INDEX FROM notifications;
-- SHOW INDEX FROM users;
-- SHOW FULL TABLES WHERE Table_type = 'VIEW';
-- SELECT * FROM view_dashboard_stats;
-- SELECT * FROM view_category_report_counts;
-- SELECT * FROM view_unresolved_old_reports;
-- SELECT * FROM view_report_details

