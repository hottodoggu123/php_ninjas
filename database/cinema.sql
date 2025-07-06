CREATE DATABASE cinema_db;
USE cinema_db;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    display_name VARCHAR(100) DEFAULT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    phone_number VARCHAR(20) DEFAULT NULL,
    preferred_genres VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE movies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    genre VARCHAR(100),
    duration INT,
    rating VARCHAR(10),
    release_date DATE,
    poster_url VARCHAR(255),
    status ENUM('now_showing', 'coming_soon') DEFAULT 'now_showing',
    price DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE showtimes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    movie_id INT NOT NULL,
    show_date DATE NOT NULL,
    show_time TIME NOT NULL,
    total_seats INT DEFAULT 40,
    FOREIGN KEY (movie_id) REFERENCES movies(id)
);

CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    movie_id INT,
    showtime_id INT,
    booking_date DATE,
    show_time TIME,
    seats_booked INT,
    total_amount DECIMAL(10,2),
    payment_method ENUM('credit_card', 'debit_card', 'paypal', 'mobile_payment') DEFAULT NULL,
    payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    payment_reference VARCHAR(100) DEFAULT NULL,
    booking_status ENUM('confirmed', 'cancelled') DEFAULT 'confirmed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (movie_id) REFERENCES movies(id),
    FOREIGN KEY (showtime_id) REFERENCES showtimes(id)
);

CREATE TABLE booked_seats (
    id INT PRIMARY KEY AUTO_INCREMENT,
    showtime_id INT,
    seat_number VARCHAR(10),
    user_id INT,
    booking_id INT,
    FOREIGN KEY (showtime_id) REFERENCES showtimes(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (booking_id) REFERENCES bookings(id)
);


-- Insert sample admin user (password: admin123)
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@cinema.com', 'admin123', 'admin');

-- Insert sample movies (updated for July 6, 2025)
INSERT INTO movies (title, description, genre, duration, rating, release_date, poster_url, status, price) VALUES 
('Avatar 3', 'Return to Pandora in this third installment of the epic sci-fi series', 'Science Fiction', 160, 'PG-13', '2025-05-20', 'assets/images/avatar3.jpg', 'now_showing', 550.00),
('Mission Impossible 8', 'Ethan Hunt returns for one final mission', 'Action', 145, 'PG-13', '2025-06-15', 'assets/images/mi8.jpg', 'now_showing', 500.00),
('Inside Out 3', 'More adventures inside Riley\'s mind as she navigates young adulthood', 'Animation', 105, 'PG', '2025-06-20', 'assets/images/insideout3.jpg', 'now_showing', 480.00),
('The Batman 2', 'The Dark Knight returns to face a new threat in Gotham', 'Action', 155, 'PG-13', '2025-05-30', 'assets/images/batman2.jpg', 'now_showing', 520.00),
('Fast & Furious 11', 'The family returns for one last ride across the globe', 'Action', 140, 'PG-13', '2025-06-05', 'assets/images/ff11.jpg', 'now_showing', 510.00),
('Guardians of Tomorrow', 'A team of unlikely heroes must save humanity from an alien threat', 'Science Fiction', 135, 'PG-13', '2025-05-15', 'assets/images/guardians.jpg', 'now_showing', 530.00),
('The Haunting', 'A family moves into a house with a dark past', 'Horror', 112, 'R', '2025-06-28', 'assets/images/haunting.jpg', 'now_showing', 490.00),
('Dune: Messiah', 'The saga continues as Paul Atreides faces new challenges on Arrakis', 'Science Fiction', 165, 'PG-13', '2025-07-15', 'assets/images/dune_messiah.jpg', 'coming_soon', 550.00),
('Jurassic World: Rebirth', 'Dinosaurs once again threaten humanity in this new installment', 'Adventure', 138, 'PG-13', '2025-07-25', 'assets/images/jurassic_rebirth.jpg', 'coming_soon', 530.00),
('Frozen 3', 'Elsa and Anna embark on another magical adventure beyond Arendelle', 'Animation', 110, 'PG', '2025-08-08', 'assets/images/frozen3.jpg', 'coming_soon', 480.00);

INSERT INTO showtimes (movie_id, show_date, show_time) VALUES
(1, '2025-07-06', '14:00:00'),
(1, '2025-07-06', '17:30:00'),
(1, '2025-07-06', '21:00:00'),
(2, '2025-07-06', '15:30:00'),
(2, '2025-07-06', '19:00:00'),
(2, '2025-07-06', '22:30:00'),
(3, '2025-07-06', '13:00:00'),
(3, '2025-07-06', '16:00:00'),
(3, '2025-07-06', '19:30:00'),
(4, '2025-07-06', '14:30:00'),
(4, '2025-07-06', '18:00:00'),
(4, '2025-07-06', '21:30:00'),
(5, '2025-07-06', '15:00:00'),
(5, '2025-07-06', '18:30:00'),
(5, '2025-07-06', '22:00:00'),
(6, '2025-07-06', '14:45:00'),
(6, '2025-07-06', '17:45:00'),
(6, '2025-07-06', '20:45:00'),
(7, '2025-07-06', '16:15:00'),
(7, '2025-07-06', '19:15:00'),
(7, '2025-07-06', '22:15:00');