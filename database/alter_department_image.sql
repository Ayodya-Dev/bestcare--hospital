-- Add image_path to departments for existing BestCare databases
USE bestcare_hospital;

ALTER TABLE departments
ADD COLUMN image_path VARCHAR(255) NULL;
