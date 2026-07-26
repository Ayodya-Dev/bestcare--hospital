-- Run once if your database was created before staff_type existed
USE bestcare_hospital;

ALTER TABLE staff
ADD COLUMN staff_type ENUM('Doctor', 'Hospital Staff', 'Other') NOT NULL DEFAULT 'Doctor';
