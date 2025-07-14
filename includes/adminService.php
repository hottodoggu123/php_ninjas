<?php
/**
 * Admin service class for handling admin-specific operations
 */
class AdminService {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Get dashboard statistics with optimized queries
     */
    public function getDashboardStats() {
        try {
            // Single query to get all counts
            $stats = $this->conn->query("
                SELECT 
                    (SELECT COUNT(*) FROM movies) as movie_count,
                    (SELECT COUNT(*) FROM users WHERE role != 'admin') as user_count,
                    (SELECT COUNT(*) FROM bookings) as booking_count,
                    (SELECT COUNT(*) FROM movies WHERE status = 'now_showing') as now_showing_count,
                    (SELECT COUNT(*) FROM movies WHERE status = 'coming_soon') as coming_soon_count,
                    (SELECT COALESCE(SUM(total_amount), 0) FROM bookings WHERE booking_status = 'confirmed') as total_revenue
            ")->fetch_assoc();
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error fetching dashboard stats: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get recent bookings with optimized query
     */
    public function getRecentBookings($limit = 5) {
        try {
            $stmt = $this->conn->prepare("
                SELECT b.id, u.username, m.title, b.booking_date as show_date, 
                       b.show_time, b.seats_booked as seats, b.total_amount, b.created_at 
                FROM bookings b
                JOIN users u ON b.user_id = u.id
                JOIN movies m ON b.movie_id = m.id
                ORDER BY b.created_at DESC
                LIMIT ?
            ");
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            return $stmt->get_result();
        } catch (Exception $e) {
            error_log("Error fetching recent bookings: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get recently added movies with optimized query
     */
    public function getRecentMovies($limit = 5) {
        try {
            $stmt = $this->conn->prepare("
                SELECT id, title, poster_url, status, created_at
                FROM movies
                ORDER BY created_at DESC
                LIMIT ?
            ");
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            return $stmt->get_result();
        } catch (Exception $e) {
            error_log("Error fetching recent movies: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all showtimes with movie details
     */
    public function getAllShowtimes() {
        try {
            $result = $this->conn->query("
                SELECT s.id, s.movie_id, s.show_date, s.show_time, 
                       s.total_seats, m.title as movie_title,
                       (SELECT COUNT(*) FROM bookings b WHERE b.showtime_id = s.id
                        AND b.booking_status = 'confirmed') as booked_seats
                FROM showtimes s
                JOIN movies m ON s.movie_id = m.id
                ORDER BY s.show_date ASC, s.show_time ASC
            ");
            return $result;
        } catch (Exception $e) {
            error_log("Error fetching all showtimes: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Search showtimes by movie title
     */
    public function searchShowtimes($searchTerm) {
        try {
            $stmt = $this->conn->prepare("
                SELECT s.id, s.movie_id, s.show_date, s.show_time, 
                       s.total_seats, m.title as movie_title,
                       (SELECT COUNT(*) FROM bookings b WHERE b.showtime_id = s.id
                        AND b.booking_status = 'confirmed') as booked_seats
                FROM showtimes s
                JOIN movies m ON s.movie_id = m.id
                WHERE m.title LIKE ?
                ORDER BY s.show_date ASC, s.show_time ASC
            ");
            $searchPattern = '%' . $searchTerm . '%';
            $stmt->bind_param("s", $searchPattern);
            $stmt->execute();
            return $stmt->get_result();
        } catch (Exception $e) {
            error_log("Error searching showtimes: " . $e->getMessage());
            return false;
        }
    }
}
?>
