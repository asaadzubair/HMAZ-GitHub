CREATE DATABASE IF NOT EXISTS smp_db;
USE smp_db;

-- Users Table (Admin & Teachers)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher') NOT NULL,
    teacher_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Teachers Table
CREATE TABLE IF NOT EXISTS teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    subjects_taught VARCHAR(255),
    classes_handled VARCHAR(255),
    photo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Students Table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    roll_number VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    class_section VARCHAR(20) NOT NULL, -- e.g., '9-A'
    dob DATE,
    contact_student VARCHAR(20),
    parent_name VARCHAR(100),
    parent_contact VARCHAR(20),
    address TEXT,
    admission_date DATE,
    status ENUM('Active', 'Graduated', 'Left') DEFAULT 'Active',
    photo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Subjects Table (For 9th & 10th)
CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    class_level VARCHAR(20) NOT NULL -- e.g., '9', '10'
);

-- Attendance Table
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    date DATE NOT NULL,
    status ENUM('Present', 'Absent', 'Leave') NOT NULL,
    marked_by INT, -- User ID
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    UNIQUE(student_id, date) -- One entry per student per day
);

-- Teacher Attendance Table
CREATE TABLE IF NOT EXISTS teacher_attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT,
    date DATE NOT NULL,
    status ENUM('Present', 'Absent', 'Leave') NOT NULL,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    UNIQUE(teacher_id, date)
);

-- Marks Table
CREATE TABLE IF NOT EXISTS marks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    subject_id INT,
    exam_type VARCHAR(50) NOT NULL, -- e.g., 'Midterm', 'Final', 'Monthly Test'
    marks_obtained DECIMAL(5,2),
    total_marks DECIMAL(5,2),
    date_entered DATE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id)
);

-- Fees Table
CREATE TABLE IF NOT EXISTS fees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    title VARCHAR(100), -- e.g., 'Tuition Fee - Jan 2026'
    total_amount DECIMAL(10,2),
    paid_amount DECIMAL(10,2) DEFAULT 0.00,
    due_date DATE,
    status ENUM('Paid', 'Pending', 'Partial') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Timetable Table
CREATE TABLE IF NOT EXISTS timetable (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_section VARCHAR(20),
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'),
    start_time TIME,
    end_time TIME,
    subject_id INT,
    teacher_id INT,
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (teacher_id) REFERENCES teachers(id)
);

-- Default Admin User (Password: admin123)
-- Using a simple MD5 for now for simplicity in this setup_script, but PHP will use password_hash
INSERT INTO users (username, password, role) VALUES ('principal', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'); 
-- Hash is for 'password' (default laravel/standard hash example) -> actually let's use a known hash for 'admin123'
-- $2y$10$Xw6/.. or just let the user register one. 
-- Let's insert a simpler one for 'admin123' generated via password_hash('admin123', PASSWORD_BCRYPT)
-- Hash for admin123: $2y$10$PrIfd.n.j/4qG.v.v.5.u.x.x.x.x.x.x.x (Placeholder)
-- Actually, let's just make a script to create the user properly or use a simple check.
-- Let's stick to a known hash. 
-- 'admin123' -> $2y$10$5N/fE/ZkZ1q.Z1q.Z1q.Z1q.Z1q.Z1q.Z1q.Z1q.Z1q. (this is fake).
-- I will leave the insert out or use a plain text check if I don't want to complicate, but for security, I will use PHP's password_verify.
-- I'll create a 'seed.php' to handle initial data properly.

TRUNCATE users;

