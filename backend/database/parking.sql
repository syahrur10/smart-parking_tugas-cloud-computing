CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    photo VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS parking_slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slot_id VARCHAR(10) UNIQUE NOT NULL,
    status ENUM('available','occupied') DEFAULT 'available',
    booked_by VARCHAR(100) DEFAULT NULL,
    booked_at TIMESTAMP NULL
);

CREATE TABLE IF NOT EXISTS vehicle_uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plate_number VARCHAR(20) NOT NULL,
    vehicle_image VARCHAR(500) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO parking_slots (slot_id) VALUES
('A1'),('A2'),('A3'),('A4'),
('B1'),('B2'),('B3'),('B4'),
('C1'),('C2'),('C3'),('C4'),
('D1'),('D2'),('D3'),('D4'),
('E1'),('E2'),('E3'),('E4'),
('F1'),('F2'),('F3'),('F4');