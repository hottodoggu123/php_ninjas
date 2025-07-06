<?php
/**
 * User service class for handling user-related operations
 */
class UserService {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Get user information by ID
     */
    public function getUserById($id) {
        try {
            $stmt = $this->conn->prepare("SELECT id, username, display_name, email FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error fetching user: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user statistics
     */
    public function getUserStats($userId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT 
                    COUNT(DISTINCT b.id) as total_bookings,
                    COUNT(DISTINCT b.movie_id) as total_movies,
                    SUM(CASE WHEN b.payment_status = 'completed' AND b.booking_status != 'cancelled' THEN b.total_amount ELSE 0 END) as total_spent
                FROM bookings b
                WHERE b.user_id = ?
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error fetching user stats: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get upcoming bookings for user
     */
    public function getUpcomingBookings($userId, $limit = 5) {
        try {
            $stmt = $this->conn->prepare("
                SELECT 
                    b.id, 
                    m.title, 
                    m.poster_url,
                    s.show_date, 
                    s.show_time,
                    b.seats_booked,
                    b.total_amount,
                    b.payment_status,
                    b.booking_status,
                    GROUP_CONCAT(bs.seat_number ORDER BY bs.seat_number ASC) AS seats
                FROM bookings b
                JOIN movies m ON b.movie_id = m.id
                JOIN showtimes s ON b.showtime_id = s.id
                LEFT JOIN booked_seats bs ON b.id = bs.booking_id
                WHERE b.user_id = ? AND s.show_date >= CURDATE() AND b.booking_status != 'cancelled'
                GROUP BY b.id
                ORDER BY s.show_date ASC, s.show_time ASC
                LIMIT ?
            ");
            $stmt->bind_param("ii", $userId, $limit);
            $stmt->execute();
            return $stmt->get_result();
        } catch (Exception $e) {
            error_log("Error fetching upcoming bookings: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get past bookings for user
     */
    public function getPastBookings($userId, $limit = 5) {
        try {
            $stmt = $this->conn->prepare("
                SELECT 
                    b.id, 
                    m.title,
                    m.poster_url, 
                    s.show_date, 
                    s.show_time,
                    b.seats_booked,
                    b.total_amount,
                    b.payment_status,
                    b.booking_status,
                    GROUP_CONCAT(bs.seat_number ORDER BY bs.seat_number ASC) AS seats
                FROM bookings b
                JOIN movies m ON b.movie_id = m.id
                JOIN showtimes s ON b.showtime_id = s.id
                LEFT JOIN booked_seats bs ON b.id = bs.booking_id
                WHERE b.user_id = ? AND s.show_date < CURDATE()
                GROUP BY b.id
                ORDER BY s.show_date DESC, s.show_time DESC
                LIMIT ?
            ");
            $stmt->bind_param("ii", $userId, $limit);
            $stmt->execute();
            return $stmt->get_result();
        } catch (Exception $e) {
            error_log("Error fetching past bookings: " . $e->getMessage());
            return false;
        }
    }
}
?>
