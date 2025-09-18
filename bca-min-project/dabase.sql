-- Workshop Registration System Database Schema

CREATE DATABASE workshop_system;
USE workshop_system;

-- Users table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Workshops table
CREATE TABLE workshops (
    workshop_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    date DATE NOT NULL,
    time TIME NOT NULL,
    location VARCHAR(200),
    seats INT DEFAULT 0,
    available_seats INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active','inactive') DEFAULT 'active'
);

-- Registrations table
CREATE TABLE registrations (
    registration_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    workshop_id INT,
    status ENUM('confirmed', 'cancelled','completed') DEFAULT 'confirmed',
    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (workshop_id) REFERENCES workshops(workshop_id) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (user_id, workshop_id)
);

-- Feedback table
CREATE TABLE feedback (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    workshop_id INT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comments TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (workshop_id) REFERENCES workshops(workshop_id) ON DELETE CASCADE,
    UNIQUE KEY unique_feedback (user_id, workshop_id)
);

-- Insert default admin user (password: admin123)
INSERT INTO users (name, email, password, role) VALUES 
('Admin User', 'admin@workshop.com', '$2y$10$8K1p/a0dF2bYHNaU1.9eqeRQfcGhG9wD7zrPBxzVWJnKdP8rZMgLS', 'admin');

-- Insert sample workshops
INSERT INTO workshops (title, description, date, time, location, seats, available_seats) VALUES 
('Web Development Bootcamp', 'Learn HTML, CSS, JavaScript and responsive design', '2025-08-15', '09:00:00', 'Conference Room A', 30, 30),
('Data Science Workshop', 'Introduction to Python, Pandas, and Machine Learning', '2025-08-20', '10:00:00', 'Computer Lab 1', 25, 25),
('Digital Marketing Seminar', 'SEO, Social Media Marketing, and Analytics', '2025-08-25', '14:00:00', 'Auditorium', 50, 50),
('Mobile App Development', 'Build mobile apps using React Native', '2025-09-01', '09:30:00', 'Conference Room B', 20, 20);

-- Insert sample participant
INSERT INTO users (name, email, password, role) VALUES 
('John Doe', 'john@example.com', '$2y$10$8K1p/a0dF2bYHNaU1.9eqeRQfcGhG9wD7zrPBxzVWJnKdP8rZMgLS', 'participant');