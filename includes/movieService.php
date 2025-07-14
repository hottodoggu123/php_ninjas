<?php
/**
 * Movie service class for handling movie-related operations
 */
class MovieService {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Get movies by status with error handling
     */
    public function getMoviesByStatus($status) {
        try {
            $stmt = $this->conn->prepare("SELECT id, title, poster_url, status FROM movies WHERE status = ?");
            $stmt->bind_param("s", $status);
            $stmt->execute();
            return $stmt->get_result();
        } catch (Exception $e) {
            error_log("Error fetching movies: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get movie details by ID
     */
    public function getMovieById($id) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM movies WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error fetching movie: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all movies for admin
     */
    public function getAllMovies() {
        try {
            return $this->conn->query("SELECT * FROM movies ORDER BY created_at DESC");
        } catch (Exception $e) {
            error_log("Error fetching all movies: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create new movie
     */
    public function createMovie($data) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO movies (title, description, genre, duration, rating, release_date, poster_url, status, price, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->bind_param("ssssssssd", 
                $data['title'], $data['description'], $data['genre'], 
                $data['duration'], $data['rating'], $data['release_date'],
                $data['poster_url'], $data['status'], $data['price']
            );
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error creating movie: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update movie
     */
    public function updateMovie($id, $data) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE movies SET 
                title = ?, description = ?, genre = ?, duration = ?, 
                rating = ?, release_date = ?, poster_url = ?, status = ?, price = ?
                WHERE id = ?
            ");
            
            $stmt->bind_param("sssissssdi", 
                $data['title'], $data['description'], $data['genre'], 
                $data['duration'], $data['rating'], $data['release_date'],
                $data['poster_url'], $data['status'], $data['price'], $id
            );
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating movie: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete movie and related data
     */
    public function deleteMovie($id) {
        try {
            $this->conn->begin_transaction();
            
            // Delete booked seats first
            $stmt1 = $this->conn->prepare("
                DELETE bs FROM booked_seats bs 
                JOIN bookings b ON bs.booking_id = b.id 
                WHERE b.movie_id = ?
            ");
            $stmt1->bind_param("i", $id);
            $stmt1->execute();
            
            // Delete bookings
            $stmt2 = $this->conn->prepare("DELETE FROM bookings WHERE movie_id = ?");
            $stmt2->bind_param("i", $id);
            $stmt2->execute();
            
            // Delete showtimes
            $stmt3 = $this->conn->prepare("DELETE FROM showtimes WHERE movie_id = ?");
            $stmt3->bind_param("i", $id);
            $stmt3->execute();
            
            // Delete movie
            $stmt4 = $this->conn->prepare("DELETE FROM movies WHERE id = ?");
            $stmt4->bind_param("i", $id);
            $stmt4->execute();
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Error deleting movie: " . $e->getMessage());
            return false;
        }
    }
}
?>
