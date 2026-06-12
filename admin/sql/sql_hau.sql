-- SQL script to ensure all required columns exist in the tbl_home table.
-- Copy and run this script in your database manager (MariaDB/MySQL).

ALTER TABLE `tbl_home` ADD COLUMN IF NOT EXISTS `is_new_address` tinyint(4) DEFAULT 0 AFTER `ward_id`;
ALTER TABLE `tbl_home` ADD COLUMN IF NOT EXISTS `detail` text DEFAULT NULL AFTER `description`;
ALTER TABLE `tbl_home` ADD COLUMN IF NOT EXISTS `move_in_time` varchar(255) DEFAULT NULL AFTER `updated_at`;
ALTER TABLE `tbl_home` ADD COLUMN IF NOT EXISTS `electricity_price` varchar(255) DEFAULT NULL AFTER `move_in_time`;
ALTER TABLE `tbl_home` ADD COLUMN IF NOT EXISTS `water_price` varchar(255) DEFAULT NULL AFTER `electricity_price`;
ALTER TABLE `tbl_home` ADD COLUMN IF NOT EXISTS `internet_price` varchar(255) DEFAULT NULL AFTER `water_price`;
ALTER TABLE `tbl_home` ADD COLUMN IF NOT EXISTS `floors` int(11) DEFAULT NULL AFTER `internet_price`;
ALTER TABLE `tbl_home` ADD COLUMN IF NOT EXISTS `entrance` double DEFAULT NULL AFTER `media_captions`;
ALTER TABLE `tbl_home` ADD COLUMN IF NOT EXISTS `facade` double DEFAULT NULL AFTER `entrance`;
ALTER TABLE `tbl_home` ADD COLUMN IF NOT EXISTS `loanability` double NOT NULL DEFAULT 0 AFTER `facade`;

-- Additional columns from the new form
ALTER TABLE `tbl_home` ADD COLUMN IF NOT EXISTS `email_phone` varchar(255) DEFAULT NULL AFTER `contact_phone`;
ALTER TABLE `tbl_home` ADD COLUMN IF NOT EXISTS `commission_rate` double DEFAULT NULL AFTER `email_phone`;
ALTER TABLE `tbl_home` ADD COLUMN IF NOT EXISTS `start_date` date DEFAULT NULL AFTER `customer_id`;
ALTER TABLE `tbl_home` ADD COLUMN IF NOT EXISTS `end_date` date DEFAULT NULL AFTER `start_date`;
ALTER TABLE `tbl_home` ADD COLUMN IF NOT EXISTS `is_featured` tinyint(4) DEFAULT 0 AFTER `status`;

-- Create tbl_plannings table
CREATE TABLE IF NOT EXISTS `tbl_plannings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `province_id` int(10) unsigned NOT NULL,
  `area` double NOT NULL,
  `kml_file` varchar(255) DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create tbl_application_comments table for application comments
CREATE TABLE IF NOT EXISTS `tbl_application_comments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comment_date` datetime NOT NULL,
  `ticket_number` varchar(50) DEFAULT NULL,
  `member_id` bigint(20) unsigned DEFAULT NULL,
  `member_name` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `rating` tinyint(4) DEFAULT NULL,
  `images` text DEFAULT NULL,
  `suggestion_ids` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create tbl_search_suggestions table for caching search suggestion autocompletes
CREATE TABLE IF NOT EXISTS `tbl_search_suggestions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `suggestion` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL,
  `to_price` int(11) DEFAULT NULL,
  `ward_id` int(11) NOT NULL,
  `is_new_address` tinyint(4) NOT NULL DEFAULT 1,
  `listing_count` int(11) NOT NULL DEFAULT 0,
  `score` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_suggestion` (`suggestion`),
  KEY `idx_ward` (`ward_id`, `is_new_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;




-- Create tbl_plandoffices table
CREATE TABLE IF NOT EXISTS `tbl_plandoffices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `province_id` int(10) unsigned NOT NULL,
  `area` double NOT NULL,
  `kml_file` varchar(255) DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert group permission for plandoffices
INSERT INTO `tbl_group_permissions` (`name`, `display_name`, `description`, `created_at`, `updated_at`) 
VALUES ('plandoffices', 'Quản lý quy hoạch văn phòng', 'Quản lý danh sách, bản đồ quy hoạch văn phòng', NOW(), NOW());

-- Get the last inserted id for group permission and insert its permissions
SET @group_id = LAST_INSERT_ID();

INSERT INTO `tbl_permissions` (`name`, `display_name`, `description`, `group_permission_id`, `created_at`, `updated_at`) VALUES
('view', 'Xem quy hoạch văn phòng', 'Xem danh sách và bản đồ quy hoạch văn phòng', @group_id, NOW(), NOW()),
('add', 'Thêm quy hoạch văn phòng', 'Thêm quy hoạch văn phòng mới', @group_id, NOW(), NOW()),
('edit', 'Sửa quy hoạch văn phòng', 'Sửa thông tin quy hoạch văn phòng', @group_id, NOW(), NOW()),
('delete', 'Xóa quy hoạch văn phòng', 'Xóa quy hoạch văn phòng', @group_id, NOW(), NOW());

-- Allocate the new permissions to admin roles (role_id = 1 and role_id = 2)
INSERT INTO `tbl_permission_role` (`permission_id`, `role_id`, `group_permission_id`)
SELECT p.id, r.id, g.id
FROM `tbl_permissions` p
JOIN `tbl_group_permissions` g ON g.id = p.group_permission_id
CROSS JOIN `tbl_roles` r
WHERE g.name = 'plandoffices' AND r.id IN (1, 2);

-- Allocate the new permissions to user_id = 3 (if applicable)
INSERT INTO `tbl_user_permission` (`permission_id`, `user_id`, `group_permission_id`)
SELECT p.id, 3, g.id
FROM `tbl_permissions` p
JOIN `tbl_group_permissions` g ON g.id = p.group_permission_id
WHERE g.name = 'plandoffices';

-- Create tbl_history_search_home table for storing home search suggestions/keywords history
CREATE TABLE IF NOT EXISTS `tbl_history_search_home` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_client` bigint(20) unsigned NOT NULL,
  `search` varchar(255) DEFAULT NULL,
  `id_suggestions` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_client_search` (`id_client`, `search`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create tbl_plandoffice_parcels table for storing land registry plot records extracted from KML
CREATE TABLE IF NOT EXISTS `tbl_plandoffice_parcels` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `plandoffice_id` bigint(20) unsigned DEFAULT NULL,
  `so_to` varchar(50) DEFAULT NULL,
  `so_thua` varchar(50) DEFAULT NULL,
  `dien_tich` double DEFAULT NULL,
  `cong_trinh` varchar(100) DEFAULT NULL,
  `loai_dat` varchar(100) DEFAULT NULL,
  `ten_chu` varchar(255) DEFAULT NULL,
  `loai_dat_quy_hoach` text DEFAULT NULL,
  `mo_ta_thua` text DEFAULT NULL,
  `lat` double DEFAULT NULL,
  `lng` double DEFAULT NULL,
  `coords` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_plandoffice_id` (`plandoffice_id`),
  KEY `idx_to_thua` (`so_to`, `so_thua`),
  KEY `idx_lat_lng` (`lat`, `lng`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


//
INSERT INTO `tbl_options` (`id`, `name`, `value`, `autoload`) VALUES (NULL, 'link_support', '', '1'), (NULL, 'link_tvlh', '', '1');

-- Create tbl_application_comment table for application comment templates
CREATE TABLE IF NOT EXISTS `tbl_application_comment` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Group permission and permissions for application_comments settings
INSERT INTO `tbl_group_permissions` (`name`, `display_name`, `description`, `created_at`, `updated_at`) 
VALUES ('application_comments', 'Quản lý góp ý ứng dụng', 'Xem và quản lý các ý kiến đóng góp từ người dùng trên ứng dụng', NOW(), NOW());

-- Get the last inserted id for group permission and insert its permissions
SET @group_id = LAST_INSERT_ID();

INSERT INTO `tbl_permissions` (`name`, `display_name`, `description`, `group_permission_id`, `created_at`, `updated_at`) VALUES
('view', 'Xem góp ý ứng dụng', 'Xem danh sách các góp ý từ ứng dụng', @group_id, NOW(), NOW()),
('delete', 'Xóa góp ý ứng dụng', 'Xóa các góp ý ứng dụng', @group_id, NOW(), NOW());

-- Allocate the new permissions to admin roles (role_id = 1 and role_id = 2)
INSERT INTO `tbl_permission_role` (`permission_id`, `role_id`, `group_permission_id`)
SELECT p.id, r.id, g.id
FROM `tbl_permissions` p
JOIN `tbl_group_permissions` g ON g.id = p.group_permission_id
CROSS JOIN `tbl_roles` r
WHERE g.name = 'application_comments' AND r.id IN (1, 2);

-- Allocate the new permissions to user_id = 3 (if applicable)
INSERT INTO `tbl_user_permission` (`permission_id`, `user_id`, `group_permission_id`)
SELECT p.id, 3, g.id
FROM `tbl_permissions` p
JOIN `tbl_group_permissions` g ON g.id = p.group_permission_id
WHERE g.name = 'application_comments';

-- Add suggestion_ids to tbl_application_comments for storing selected templates array
ALTER TABLE `tbl_application_comments` ADD COLUMN `suggestion_ids` varchar(255) DEFAULT NULL AFTER `images`;

-- Insert options for contacts
INSERT INTO `tbl_options` (`name`, `value`, `autoload`) VALUES ('link_youtube', '', '1') ON DUPLICATE KEY UPDATE `name` = `name`;
INSERT INTO `tbl_options` (`name`, `value`, `autoload`) VALUES ('working_hours', '', '1') ON DUPLICATE KEY UPDATE `name` = `name`;
INSERT INTO `tbl_options` (`name`, `value`, `autoload`) VALUES ('contact_address', '', '1') ON DUPLICATE KEY UPDATE `name` = `name`;

-- Add lat, lng, coords columns to tbl_plandoffice_parcels for rendering map from database instead of KML
ALTER TABLE `tbl_plandoffice_parcels` ADD COLUMN IF NOT EXISTS `lat` double DEFAULT NULL AFTER `ten_chu`;
ALTER TABLE `tbl_plandoffice_parcels` ADD COLUMN IF NOT EXISTS `lng` double DEFAULT NULL AFTER `lat`;
ALTER TABLE `tbl_plandoffice_parcels` ADD COLUMN IF NOT EXISTS `coords` longtext DEFAULT NULL AFTER `lng`;
ALTER TABLE `tbl_plandoffice_parcels` ADD KEY IF NOT EXISTS `idx_lat_lng` (`lat`, `lng`);
ALTER TABLE `tbl_blog` ADD `staff_create` INT(11) NULL AFTER `updated_at`;
ALTER TABLE `tbl_blog` ADD `view` DOUBLE NOT NULL DEFAULT '0' AFTER `staff_create`;



-- 11062026 - Bổ sung các trường còn thiếu cho bảng quy hoạch (tbl_plannings)
-- Các trường tương ứng với màn hình app: Số quyết định, Quy mô, Trạng thái, Ngày phê duyệt, Loại quy hoạch, Ảnh, Mô tả, Khu vực chi tiết
ALTER TABLE `tbl_plannings` ADD COLUMN `location_text` VARCHAR(255) DEFAULT NULL COMMENT 'Khu vực chi tiết hiển thị (VD: Bình Thạnh, TP. HCM)' AFTER `province_id`;
ALTER TABLE `tbl_plannings` ADD COLUMN `decision_no` VARCHAR(255) DEFAULT NULL COMMENT 'Số quyết định (VD: QĐ số 4295/QĐ-UBND)' AFTER `location_text`;
ALTER TABLE `tbl_plannings` ADD COLUMN `scale` DOUBLE DEFAULT NULL COMMENT 'Quy mô tính bằng ha (hecta) - hiển thị trên app' AFTER `area`;
ALTER TABLE `tbl_plannings` ADD COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'approved' COMMENT 'Trạng thái: approved=Đã phê duyệt, effective=Hiệu lực, draft=Dự thảo, expired=Hết hiệu lực' AFTER `scale`;
ALTER TABLE `tbl_plannings` ADD COLUMN `approved_date` DATE DEFAULT NULL COMMENT 'Ngày phê duyệt' AFTER `status`;
ALTER TABLE `tbl_plannings` ADD COLUMN `planning_type` VARCHAR(50) NOT NULL DEFAULT 'published' COMMENT 'Loại: published=Đang công bố, draft_feedback=Dự thảo góp ý' AFTER `approved_date`;
ALTER TABLE `tbl_plannings` ADD COLUMN `image` VARCHAR(255) DEFAULT NULL COMMENT 'Ảnh đại diện quy hoạch' AFTER `planning_type`;
ALTER TABLE `tbl_plannings` ADD COLUMN `description` TEXT DEFAULT NULL COMMENT 'Mô tả quy hoạch' AFTER `image`;

-- 12062026 - Tạo bảng lưu lịch sử xem bất động sản của user client
-- Bảng này lưu mỗi lượt xem riêng biệt (không aggregate), giúp thống kê chính xác
CREATE TABLE IF NOT EXISTS `tbl_home_views` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `home_id` bigint(20) unsigned NOT NULL COMMENT 'ID bất động sản được xem',
  `id_client` bigint(20) unsigned NOT NULL COMMENT 'ID user client đã xem',
  `source` varchar(50) DEFAULT 'app' COMMENT 'Nguồn xem: app, web, share_link',
  `viewed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời điểm xem',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_home_id` (`home_id`),
  KEY `idx_client` (`id_client`),
  KEY `idx_home_client` (`home_id`, `id_client`),
  KEY `idx_viewed_at` (`viewed_at`),
  KEY `idx_home_viewed` (`home_id`, `viewed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;