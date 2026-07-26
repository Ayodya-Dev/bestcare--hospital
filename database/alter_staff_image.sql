-- Run this on an existing database to add the staff/doctor image column.
-- (New installs already get it from schema.sql)
USE bestcare_hospital;

ALTER TABLE staff ADD COLUMN image_path VARCHAR(255) NULL;
