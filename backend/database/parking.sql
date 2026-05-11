CREATE TABLE users (
id INT AUTO_INCREMENT PRIMARY KEY,
nama VARCHAR(100),
email VARCHAR(100) UNIQUE,
password VARCHAR(255)
);

CREATE TABLE parking_slots (
id INT AUTO_INCREMENT PRIMARY KEY,
slot_name VARCHAR(10),
status ENUM('kosong','penuh') DEFAULT 'kosong',
booked_by INT NULL,
booked_at TIMESTAMP NULL
);

INSERT INTO parking_slots (slot_name) VALUES
('A1'),('A2'),('A3'),('A4'),
('B1'),('B2'),('B3'),('B4'),
('C1'),('C2'),('C3'),('C4'),
('D1'),('D2'),('D3'),('D4'),
('E1'),('E2'),('E3'),('E4'),
('F1'),('F2'),('F3'),('F4');