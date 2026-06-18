-- ================================================================
-- Device-Level Approval: Hardware ID Unique Constraint Migration
-- Run this SQL in phpMyAdmin / MySQL when XAMPP is running
-- ================================================================

-- Step 1: Remove old unique constraint (user_id + hardware_id pair)
ALTER TABLE `device_bindings` DROP INDEX `device_bindings_user_id_hardware_id_unique`;

-- Step 2: Remove duplicate device records (keep latest per hardware_id)
-- This deletes older duplicates where same hardware_id existed for different users
DELETE d1 FROM `device_bindings` d1
INNER JOIN `device_bindings` d2
WHERE d1.hardware_id = d2.hardware_id AND d1.id < d2.id;

-- Step 3: Add unique constraint on hardware_id alone
ALTER TABLE `device_bindings` ADD UNIQUE INDEX `device_bindings_hardware_id_unique` (`hardware_id`);

-- Step 4: Allow user_id to be nullable (now just stores first registrant for audit)
ALTER TABLE `device_bindings` MODIFY `user_id` bigint UNSIGNED NULL;

-- Step 5: Mark migration as done in Laravel migrations table
INSERT INTO `migrations` (`migration`, `batch`) 
VALUES ('2026_06_18_000001_update_device_bindings_hardware_unique', 99)
ON DUPLICATE KEY UPDATE batch = 99;

-- Done! Device approval is now machine-wide.
-- When Admin approves a device (hardware_id), ALL users can login from that machine.
