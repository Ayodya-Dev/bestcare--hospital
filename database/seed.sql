-- BestCare Hospital - sample data for demo
USE bestcare_hospital;

-- Password for all demo accounts is: password123
-- (hashed with PHP password_hash)

INSERT INTO users (username, password_hash, role) VALUES
('admin', '$2y$12$opyazBlyUJZTL6d0ooRGh.tRAs70sHPnCZe.znTvSAiPz0BlyNuDW', 'admin'),
('drsilva', '$2y$12$opyazBlyUJZTL6d0ooRGh.tRAs70sHPnCZe.znTvSAiPz0BlyNuDW', 'staff'),
('drfernando', '$2y$12$opyazBlyUJZTL6d0ooRGh.tRAs70sHPnCZe.znTvSAiPz0BlyNuDW', 'staff'),
('patient1', '$2y$12$opyazBlyUJZTL6d0ooRGh.tRAs70sHPnCZe.znTvSAiPz0BlyNuDW', 'patient');

INSERT INTO departments (name, description) VALUES
('General Medicine', 'General consultations and primary care'),
('Cardiology', 'Heart and blood vessel care'),
('Laboratory', 'Blood tests and lab investigations'),
('Emergency', '24-hour emergency care');

INSERT INTO staff (user_id, full_name, specialization, department_id, contact) VALUES
(2, 'Dr. Nimal Silva', 'General Physician', 1, '0712345678'),
(3, 'Dr. Kamala Fernando', 'Cardiologist', 2, '0723456789');

INSERT INTO patients (user_id, full_name, dob, gender, contact, address) VALUES
(4, 'Saman Perera', '1995-05-12', 'Male', '0771234567', 'No 12, Main Street, Matara');

INSERT INTO services (name, description, department_id, fee) VALUES
('General Consultation', 'Basic doctor consultation', 1, 1500.00),
('Specialist Consultation', 'Specialist doctor appointment', 2, 3500.00),
('Blood Test', 'Full blood count and basic labs', 3, 2000.00),
('ECG', 'Electrocardiogram heart test', 2, 2500.00),
('Emergency Care', 'Urgent emergency treatment', 4, 5000.00);