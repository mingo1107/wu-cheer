-- 添加 vehicle_id 和 driver_name 欄位到 earth_data_detail 表
-- 執行方式：在資料庫中直接執行此 SQL 腳本

ALTER TABLE `earth_data_detail` 
ADD COLUMN `vehicle_id` BIGINT UNSIGNED NULL COMMENT '車輛 ID' AFTER `verified_by`,
ADD COLUMN `driver_name` VARCHAR(255) NULL COMMENT '司機名字' AFTER `vehicle_id`;

