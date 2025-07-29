CREATE TABLE patients (
    patient_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') DEFAULT 'Other',
    date_of_birth DATE,
    phone_number VARCHAR(20),
    email VARCHAR(255) UNIQUE, -- Email should be unique for each patient
    address TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
CREATE TABLE appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status ENUM('Scheduled', 'Completed', 'Cancelled', 'No-Show') DEFAULT 'Scheduled',
    reason_for_visit TEXT,
    dentist_id INT, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE
    -- FOREIGN KEY (dentist_id) REFERENCES users(user_id) ON DELETE SET NULL -- Will add after users table
);
CREATE TABLE treatment_plans (
    treatment_plan_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    plan_name VARCHAR(255) NOT NULL, -- e.g., "Comprehensive Dental Care"
    start_date DATE,
    end_date DATE,
    status ENUM('Active', 'Completed', 'Archived') DEFAULT 'Active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE
);
CREATE TABLE treatments (
    treatment_id INT AUTO_INCREMENT PRIMARY KEY,
    treatment_plan_id INT NOT NULL,
    exam_date DATE NOT NULL,
    tooth_number VARCHAR(50), -- Can be a single tooth (e.g., '12') or a range (e.g., 'Upper Left Quadrant')
    diagnosis VARCHAR(255) NOT NULL,
    ada_code VARCHAR(50), -- American Dental Association code for procedures
    treatment_description TEXT NOT NULL,
    cost DECIMAL(10, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (treatment_plan_id) REFERENCES treatment_plans(treatment_plan_id) ON DELETE CASCADE
);

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(200) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL, -- Store hashed passwords, not plain text
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE, -- Email should be unique for each user
    phone_number VARCHAR(20),
    role ENUM('Admin', 'Dentist', 'Receptionist') DEFAULT 'Receptionist',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
ALTER TABLE appointments
ADD CONSTRAINT fk_dentist
FOREIGN KEY (dentist_id) REFERENCES users(user_id) ON DELETE SET NULL;

CREATE TABLE patient_images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL, -- Path to the stored image file on the server
    description TEXT, -- Description of the image (e.g., "X-ray of tooth 12")
    uploaded_by INT, -- User who uploaded the image
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(user_id) ON DELETE SET NULL
);
INSERT INTO patients (first_name, last_name, gender, date_of_birth, phone_number, email, address) VALUES
('John', 'Hopkins', 'Male', '1974-05-15', '+1 (314) 925-6925', 'john.hopkins@example.com', '123 Main St, Anytown, USA'),
('Andrew', 'Ronaldson', 'Male', '1988-11-20', '+1 (555) 123-4567', 'andrew.r@example.com', '456 Oak Ave, Somewhere, USA'),
('Andrew', 'Rover', 'Male', '1990-01-01', '+1 (555) 987-6543', 'andrew.rover@example.com', '789 Pine Rd, Nowhere, USA');

INSERT INTO users (username, password_hash, first_name, last_name, email, phone_number, role) VALUES
('test.admin', 'e10adc3949ba59abbe56e057f20f883e', 'Test', 'Admin', 'test.admin@example.com', '+212600000000', 'Admin'),
('test.dentist', 'e10adc3949ba59abbe56e057f20f883e', 'Test', 'Dentist', 'test.dentist@example.com', '+212611111111', 'Dentist'); --test123

INSERT INTO appointments (patient_id, appointment_date, appointment_time, status, reason_for_visit, dentist_id) VALUES
(1, '2025-06-30', '10:00:00', 'Scheduled', 'Routine Check-up', 1),
(2, '2025-06-20', '11:00:00', 'Scheduled', 'Toothache', 1);

INSERT INTO treatment_plans (patient_id, plan_name, start_date, status) VALUES
(1, 'Advanced Tooth Decay Management', '2022-07-01', 'Active'),
(2, 'Routine Dental Check-up and Cleaning', '2023-01-15', 'Active');

INSERT INTO treatments (treatment_plan_id, exam_date, tooth_number, diagnosis, ada_code, treatment_description, cost) VALUES
(1, '2022-07-04', NULL, 'Advanced Tooth Decay', 'D2391', 'Resin Based Composite One Surface, Posterior', 250.00),
(2, '2022-07-04', '3', 'Impacted Wisdom Tooth', 'D7210', 'Surgical Removal of Impacted Tooth', 1200.00),
(1, '2022-07-05', '32', 'Uncrowned Root Canal', 'D3330', 'Endodontic Retreatment - Molar', 1300.00),
(2, '2022-07-05', NULL, 'Missing Teeth', 'D6058', 'Abutment Supported Crown - Porcelain Fused to Metal', 850.00),
(1, '2022-07-06', '14', 'Periodontal Disease', 'D4341', 'Periodontal Scaling and Root Planing - Four or More Teeth', 350.00),
(2, '2022-07-06', '7', 'Chipped Incisor', 'D2960', 'Resin-Based Composite Veneer - Direct', 400.00);