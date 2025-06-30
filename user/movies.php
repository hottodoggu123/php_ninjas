<?php
require_once '../includes/db.php';

$search = $_GET['search'] ?? '';
$genre_filter = $_GET['genre'] ?? '';
$movies = [];
$genres = [];

// Fetch all genres for filter
try {
    $stmt = $pdo->query("SELECT DISTINCT genre FROM movies WHERE genre IS NOT NULL ORDER BY genre");
    $genres = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Build query with conditional filtering
$query = "SELECT * FROM movies WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (title LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($genre_filter)) {
    $query .= " AND genre = ?";
    $params[] = $genre_filter;
}

$query .= " ORDER BY created_at DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

include '../includes/header.php';
?>

<div class="main-content">
    <h2>All Movies</h2>
    
    <!-- Search and Filter Form -->
    <form method="GET" style="background: white; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div style="display: grid; grid-template-columns: 1fr 200px auto; gap: 1rem; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="search">Search Movies:</label>
                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by title or description...">
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label for="genre">Filter by Genre:</label>
                <select id="genre" name="genre">
                    <option value="">All Genres</option>
                    <?php foreach ($genres as $genre): ?>
                        <option value="<?php echo htmlspecialchars($genre); ?>" <?php echo $genre_filter == $genre ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($genre); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="btn">Search</button>
        </div>
    </form>
    
    <?php if (empty($movies)): ?>
        <div class="alert alert-error">No movies found matching your criteria.</div>
    <?php else: ?>
        <div class="movies-grid">
            <?php foreach ($movies as $movie): ?>
                <div class="movie-card">
                    <div class="movie-rating"><?php echo htmlspecialchars($movie['rating']); ?></div>
                    <img src="../<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="movie-poster">
                    <div class="movie-info">
                        <h3 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></h3>
                        <p class="movie-genre"><?php echo htmlspecialchars($movie['genre']); ?> • <?php echo $movie['duration']; ?> mins</p>
                        <p style="color: #666; font-size: 0.9rem; margin-bottom: 1rem;">
                            <?php echo substr(htmlspecialchars($movie['description']), 0, 100) . '...'; ?>
                        </p>
                        <p style="font-weight: bold; color: #e63946; margin-bottom: 1rem;">
                            ₱<?php echo number_format($movie['price'], 2); ?>
                        </p>
                        <a href="view_movie.php?id=<?php echo $movie['id']; ?>" class="buy-tickets-btn">
                            <?php echo $movie['status'] == 'now_showing' ? 'Buy Tickets' : 'View Details'; ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>