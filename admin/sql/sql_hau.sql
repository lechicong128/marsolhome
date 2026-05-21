-- //27022026
thêm tbl_category_services
tbl_services
tbl_services_images
CREATE TABLE `tbl_work_shifts` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `day_of_week` TINYINT NOT NULL COMMENT '0=Sunday, 1=Monday, ..., 6=Saturday',
  `start_time`  TIME NOT NULL,
  `end_time`    TIME NOT NULL,
  `active`      TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`  TIMESTAMP NULL,
  `updated_at`  TIMESTAMP NULL,
  UNIQUE KEY `uq_day` (`day_of_week`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- //02032026
CREATE TABLE `tbl_branches` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(255) NOT NULL COMMENT 'Tên chi nhánh',
  `phone`      VARCHAR(20)  NOT NULL COMMENT 'Số điện thoại',
  `address`    VARCHAR(500) NOT NULL COMMENT 'Địa chỉ',
  `map_link`   TEXT         NULL     COMMENT 'Link Google Map',
  `active`     TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP    NULL     DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP    NULL     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- //02032026 - Booking
CREATE TABLE `tbl_spa_bookings` (
  `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_code`   VARCHAR(30)  NOT NULL COMMENT 'Mã lịch hẹn, vd: BK-20260302-000001',
  `customer_name`  VARCHAR(255) NOT NULL COMMENT 'Tên khách hàng',
  `customer_phone` VARCHAR(20)  NOT NULL COMMENT 'Số điện thoại',
  `booking_date`   DATE         NOT NULL COMMENT 'Ngày hẹn',
  `booking_time`   TIME         NOT NULL COMMENT 'Giờ hẹn',
  `branch_id`      INT(11)      NOT NULL COMMENT 'FK → tbl_branches.id',
  `total_amount`   DECIMAL(15,0) NOT NULL DEFAULT 0 COMMENT 'Tổng tiền',
  `payment_method` INT(11) NOT NULL COMMENT 'transfer=Chuyển khoản, pay_later=Thanh toán sau',
  `payment_status` ENUM('pending','paid','pay_later') NOT NULL DEFAULT 'pending' COMMENT 'Trạng thái thanh toán',
  `status`         ENUM('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending' COMMENT 'Trạng thái lịch hẹn',
  `note`           TEXT NULL COMMENT 'Ghi chú',
  `created_at`     TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_booking_code` (`booking_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tbl_spa_booking_services` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_booking` INT(11) UNSIGNED NOT NULL COMMENT 'FK → tbl_spa_bookings.id',
  `id_service` INT(11) UNSIGNED NOT NULL COMMENT 'FK → tbl_services.id',
  `name`       VARCHAR(255) NOT NULL COMMENT 'Tên dịch vụ (snapshot)',
  `price`      DECIMAL(15,0) NOT NULL DEFAULT 0 COMMENT 'Đơn giá tại thời điểm đặt',
  `quantity`   INT(11) NOT NULL DEFAULT 1,
  `amount`     DECIMAL(15,0) NOT NULL DEFAULT 0 COMMENT 'Thành tiền (price * quantity)',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_id_booking` (`id_booking`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
ALTER TABLE `tbl_spa_bookings` ADD `id_client` INT(11) NULL AFTER `updated_at`;

CREATE TABLE `tbl_spa_payments` (
  `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_booking`     INT(11) UNSIGNED NOT NULL,
  `payment_code`   VARCHAR(30)  NOT NULL,
  `amount`         DECIMAL(15,0) NOT NULL DEFAULT 0,
  `payment_method` INT(11) NOT NULL,
  `note`           TEXT NULL,
  `status`         ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_by`    INT(11) NULL,
  `approved_at`    TIMESTAMP NULL,
  `created_at`     TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_payment_code` (`payment_code`),
  KEY `idx_id_booking` (`id_booking`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `tbl_services` CHANGE `price` `price` DOUBLE NOT NULL DEFAULT '0.00', CHANGE `discount_percent` `discount_percent` DOUBLE NOT NULL DEFAULT '0';
ALTER TABLE `tbl_spa_booking_services` ADD `discount_percent` DOUBLE NULL DEFAULT '0' AFTER `updated_at`, ADD `duration_minutes` DOUBLE NULL DEFAULT '0' AFTER `discount_percent`;
ALTER TABLE `tbl_spa_payments` ADD `id_client` INT(11) NULL AFTER `updated_at`;

-- //13032026
ALTER TABLE `tbl_spa_bookings` ADD `note_cancel` TEXT NULL AFTER `branch_id`;
thêm tbl_history_search_service
tbl_history_search_service_view
-- //16032026
ALTER TABLE `tbl_services` ADD `is_hot` TINYINT(1) NOT NULL DEFAULT 0 AFTER `active`;
-- //1803026
ALTER TABLE `tbl_spa_payments` CHANGE `amount` `amount` DOUBLE NOT NULL DEFAULT '0';
ALTER TABLE `tbl_spa_payments` ADD `amount_payment` DOUBLE NOT NULL DEFAULT '0' AFTER `id_client`;
-- //19032026
-- thêm tbl_posts
-- thêm tbl_posts_toppic
thêm tbl_post_ignores account chỉnh
tbl_post_stars
tbl_receipts
tbl_post_watchers
tbl_post_saved 
tbl_comments
tbl_user_action_logs
tbl_post_media
tbl_post_reads
tbl_post_tags
-- //20032026
tbl_reportviolation

-- // thêm 23032026
tbl_hidePost
-- //06042026
INSERT INTO `tbl_options` (`id`, `name`, `value`, `autoload`) VALUES (NULL, 'admin_email_orders', '', '1'), (NULL, 'cc_admin_email_orders', '', '1');
-- thêm tbl_email_template,tbl_cron_email
-- admin nha
-- 1. Thêm cột 'is_receive_email_spa' (nhận email booking spa) vào bảng danh sách User
ALTER TABLE `tbl_users` ADD COLUMN `is_receive_email_spa` TINYINT(1) DEFAULT 0 AFTER `email`;
-- 2. Tạo bảng quan hệ (pivot) 'tbl_user_branch' để đối chiếu 1 User nhận thông báo cho những chi nhánh nào
CREATE TABLE `tbl_user_branch` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT(20) UNSIGNED NOT NULL,
  `branch_id` BIGINT(20) UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `tbl_group_permissions` (`id`, `name`, `display_name`, `description`, `created_at`, `updated_at`) VALUES (NULL, 'Lịch hẹn spa', 'booking', NULL, NULL, NULL);
INSERT INTO `tbl_group_permissions` (`id`, `name`, `display_name`, `description`, `created_at`, `updated_at`) VALUES (NULL, 'Thanh toán lịch hẹn spa', 'payment_spa', NULL, NULL, NULL);
ALTER TABLE `tbl_users` CHANGE `lang` `lang` VARCHAR(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'vi';

-- //07042026 - Mua liệu trình spa
-- Bảng đầu phiếu mua liệu trình
CREATE TABLE `tbl_treatment_purchases` (
  `id`              INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `purchase_code`   VARCHAR(30)       NULL     COMMENT 'Mã liệu trình, vd: LT-20260407-00001',
  `treatment_name`  VARCHAR(255)      NOT NULL COMMENT 'Tên liệu trình (vd: Gói 10 buổi trị mụn)',
  `id_category`     INT(11) UNSIGNED  NOT NULL COMMENT 'FK → tbl_category_services.id (Lĩnh vực danh mục)',
  `customer_name`   VARCHAR(255)      NOT NULL COMMENT 'Tên khách hàng / thành viên',
  `customer_phone`  VARCHAR(20)       NULL     COMMENT 'Số điện thoại',
  `id_client`       INT(11)           NULL     COMMENT 'FK tùy chọn → id thành viên trên app',
  `id_branch`       INT(11) UNSIGNED  NOT NULL COMMENT 'FK → tbl_branches.id (chi nhánh áp dụng)',
  `total_sessions`  INT(11)           NOT NULL DEFAULT 1  COMMENT 'Tổng số buổi mua',
  `used_sessions`   INT(11)           NOT NULL DEFAULT 0  COMMENT 'Số buổi đã sử dụng',
  `price`           DOUBLE            NOT NULL DEFAULT 0  COMMENT 'Giá trị liệu trình',
  `status`          ENUM('active','completed','cancelled') NOT NULL DEFAULT 'active' COMMENT 'active=Đang dùng, completed=Hết buổi, cancelled=Huỷ',
  `note`            TEXT              NULL,
  `created_at`      TIMESTAMP         NULL     DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP         NULL     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_purchase_code` (`purchase_code`),
  KEY `idx_id_category` (`id_category`),
  KEY `idx_id_branch` (`id_branch`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng log từng buổi sử dụng
CREATE TABLE `tbl_treatment_sessions` (
  `id`                  INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_purchase`         INT(11) UNSIGNED NOT NULL COMMENT 'FK → tbl_treatment_purchases.id',
  `id_booking`          INT(11) UNSIGNED NULL     COMMENT 'FK → tbl_spa_bookings.id (lịch hẹn được áp dụng)',
  `id_booking_service`  INT(11) UNSIGNED NULL     COMMENT 'FK → tbl_spa_booking_services.id (mặt hàng được áp dụng)',
  `note`                TEXT             NULL     COMMENT 'Ghi chú buổi sử dụng',
  `created_by`          INT(11)          NULL     COMMENT 'Admin ghi nhận',
  `created_at`          TIMESTAMP        NULL     DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          TIMESTAMP        NULL     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_id_purchase` (`id_purchase`),
  KEY `idx_id_booking`  (`id_booking`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm cột ghi nhận liệu trình đã áp dụng vào từng mặt hàng booking
ALTER TABLE `tbl_spa_booking_services` ADD `id_treatment_purchase` INT(11) NULL DEFAULT NULL COMMENT 'FK → tbl_treatment_purchases.id (liệu trình được dùng cho mặt hàng này)' AFTER `duration_minutes`;

-- Thêm quyền cho nhóm buy_treatment
INSERT INTO `tbl_group_permissions` (`id`, `name`, `display_name`, `description`, `created_at`, `updated_at`) VALUES (NULL, 'Mua liệu trình', 'buy_treatment', NULL, NULL, NULL);
ALTER TABLE `tbl_spa_booking_services` 
ADD `id_treatment_purchase` INT(11) NULL DEFAULT NULL 
COMMENT 'FK → tbl_treatment_purchases.id (liệu trình được dùng cho mặt hàng này)' 
AFTER `duration_minutes`;
-- //08042026 - Quản lý nhập kho
CREATE TABLE `tbl_warehouse_imports` (
  `id`              INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `import_code`     VARCHAR(30)       NULL     COMMENT 'Mã phiếu nhập, vd: NK-20260408-00001',
  `import_date`     DATE              NOT NULL COMMENT 'Ngày nhập kho',
  `supplier_name`   VARCHAR(255)      NOT NULL COMMENT 'Tên nhà cung cấp',
  `note`            TEXT              NULL     COMMENT 'Ghi chú',
  `status`          TINYINT(1)        NOT NULL DEFAULT 0 COMMENT '0=Chờ duyệt, 1=Đã duyệt, 2=Đã hủy',
  `created_by`      INT(11)           NULL     COMMENT 'Admin tạo phiếu',
  `approved_by`     INT(11)           NULL     COMMENT 'Admin duyệt phiếu',
  `created_at`      TIMESTAMP         NULL     DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP         NULL     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_import_code` (`import_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tbl_warehouse_import_details` (
  `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_import`     INT(11) UNSIGNED NOT NULL COMMENT 'FK → tbl_warehouse_imports.id',
  `id_product`    INT(11) UNSIGNED NOT NULL COMMENT 'FK → tbl_products.id',
  `product_code`  VARCHAR(100)     NULL     COMMENT 'Cache mã SP',
  `product_name`  VARCHAR(255)     NULL     COMMENT 'Cache tên SP',
  `quantity`      INT(11)          NOT NULL DEFAULT 0 COMMENT 'Số lượng nhập ban đầu',
  `remaining_qty` INT(11)          NOT NULL DEFAULT 0 COMMENT 'Số lượng còn lại trong lô này',
  `created_at`    TIMESTAMP        NULL     DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP        NULL     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_id_import`  (`id_import`),
  KEY `idx_id_product` (`id_product`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tbl_warehouse_stock` (
  `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_product`  INT(11) UNSIGNED NOT NULL COMMENT 'FK → tbl_products.id',
  `quantity`    INT(11)          NOT NULL DEFAULT 0 COMMENT 'Số lượng tồn kho hiện tại (tổng)',
  `last_import` DATE              NULL    COMMENT 'Ngày nhập gần nhất',
  `created_at`  TIMESTAMP        NULL    DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP        NULL    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_id_product` (`id_product`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tbl_group_permissions` (`id`, `name`, `display_name`, `description`, `created_at`, `updated_at`) VALUES (NULL, 'Nhập kho', 'warehouse_import', NULL, NULL, NULL);
INSERT INTO `tbl_group_permissions` (`id`, `name`, `display_name`, `description`, `created_at`, `updated_at`) VALUES (NULL, 'Quản lý tồn kho', 'warehouse_stock', NULL, NULL, NULL);

-- ALTER TABLE `tbl_warehouse_import_details`
--   ADD COLUMN `product_code` VARCHAR(100) NULL AFTER `id_product`,
--   ADD COLUMN `product_name` VARCHAR(255) NULL AFTER `product_code`,
--   ADD COLUMN `remaining_qty` INT(11) NOT NULL DEFAULT 0 AFTER `quantity`;
-- UPDATE `tbl_warehouse_import_details` SET `remaining_qty` = `quantity` WHERE `remaining_qty` = 0;

ALTER TABLE tbl_branches ADD COLUMN icon VARCHAR(255) NULL;

-- //08042026 - Duyệt kho cho đơn hàng (bảng local trong DB admin, vì tbl_transaction nằm ở service khác)
CREATE TABLE IF NOT EXISTS `tbl_transaction_warehouse` (
  `id`                  INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `transaction_id`      INT(11)          NOT NULL COMMENT 'ID đơn hàng từ service account',
  `warehouse_status`    TINYINT(1)       NOT NULL DEFAULT 0 COMMENT '0=Chưa duyệt, 1=Đã duyệt kho',
  `warehouse_approved_at` TIMESTAMP      NULL     COMMENT 'Thời gian duyệt kho',
  `warehouse_approved_by` INT(11)        NULL     COMMENT 'Admin duyệt kho',
  `created_at`          TIMESTAMP        NULL     DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          TIMESTAMP        NULL     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_transaction_id` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_transaction_warehouse_details` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `transaction_id` INT(11) NOT NULL COMMENT 'ID đơn hàng',
  `id_product` INT(11) NOT NULL,
  `detail_id` INT(11) NOT NULL COMMENT 'Lô nhập kho: tbl_warehouse_import_details.id',
  `qty_take` INT(11) NOT NULL COMMENT 'Số lượng đã trừ từ lô này',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY (`transaction_id`),
  KEY (`detail_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

them warehouse_status vào tbl_transaction

-- //15042026
INSERT INTO `tbl_options` (`id`, `name`, `value`, `autoload`) VALUES (NULL, 'link_apple', '', '1'), (NULL, 'link_android', '', '1');

-- //16042026 - Quản lý mã Leader (bên DB accounts)
CREATE TABLE IF NOT EXISTS `tbl_code_leader` (
  `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`          VARCHAR(50)      NOT NULL COMMENT 'Mã leader (VD: LD-00001)',
  `status`        TINYINT(1)       NOT NULL DEFAULT 0 COMMENT '0=Chưa sử dụng, 1=Đã sử dụng',
  `customer_id`   INT(11)          NULL     COMMENT 'FK → tbl_clients.id (khách hàng được gán)',
  `note`          TEXT             NULL     COMMENT 'Ghi chú',
  `used_at`       TIMESTAMP        NULL     COMMENT 'Ngày sử dụng / gán khách hàng',
  `created_at`    TIMESTAMP        NULL     DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP        NULL     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_code` (`code`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm quyền cho module code_leader (bên DB admin)
INSERT INTO `tbl_group_permissions` (`id`, `name`, `display_name`, `description`, `created_at`, `updated_at`) VALUES (NULL, 'Quản lý mã Leader', 'code_leader', NULL, NULL, NULL);
-- //16042026 new
ALTER TABLE `tbl_users` ADD `code_introduce` VARCHAR(10) NULL DEFAULT NULL AFTER `lang`;
ALTER TABLE `tbl_clients` ADD `code_introduce_admin` VARCHAR(10) NULL AFTER `type_leader`;

-- //23042026
ALTER TABLE `tbl_spa_booking_services` ADD `booking_date` DATE NULL COMMENT 'Ngày hẹn' AFTER `id_treatment_purchase`, ADD `booking_time` TIME NULL COMMENT 'Giờ hẹn' AFTER `booking_date`;