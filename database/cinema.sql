CREATE DATABASE cinema_db;
USE cinema_db;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
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

CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    movie_id INT,
    booking_date DATE,
    show_time TIME,
    seats_booked INT,
    total_amount DECIMAL(10,2),
    booking_status ENUM('confirmed', 'cancelled') DEFAULT 'confirmed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (movie_id) REFERENCES movies(id)
);

-- Insert sample admin user (password: admin123)
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@cinema.com', 'admin123', 'admin');

-- Insert sample movies
INSERT INTO movies (title, description, genre, duration, rating, release_date, poster_url, status, price) VALUES 
('How To Train Your Dragon', 'A young Viking befriends a dragon', 'Animation', 98, 'PG', '2024-06-01', 'assets/images/dragon.jpg', 'now_showing', 450.00),
('M3gan 2.0', 'The killer doll returns', 'Horror', 102, 'R', '2024-06-25', 'assets/images/megan.jpg', 'now_showing', 480.00),
('Lilo & Stitch', 'Disney classic remake', 'Family', 85, 'G', '2024-07-10', 'assets/images/lilo.jpg', 'coming_soon', 420.00),
('Ballerina', 'Action thriller', 'Action', 95, 'R', '2024-06-20', 'assets/images/ballerina.jpg', 'now_showing', 490.00),
('Only We Know', 'Romantic drama', 'Romance', 110, 'PG-13', '2024-07-05', 'assets/images/only_we_know.jpg', 'coming_soon', 430.00),
('28 Years Later', 'Zombie apocalypse sequel', 'Horror', 118, 'R', '2024-07-15', 'assets/images/28_years.jpg', 'coming_soon', 500.00);

INSERT INTO showtimes (movie_id, show_date, show_time) VALUES
(1, '2024-07-03', '14:00:00'),
(1, '2024-07-03', '17:00:00'),
(2, '2024-07-03', '19:00:00');