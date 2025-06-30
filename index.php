<?php
require_once 'includes/db.php';
include 'includes/header.php';

// Fetch movies with array implementation and looping
$now_showing = [];
$coming_soon = [];

try {
    // I/O operation - Database query
    $stmt = $pdo->query("SELECT * FROM movies ORDER BY created_at DESC");
    $all_movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Array implementation and looping
    foreach ($all_movies as $movie) {
        if ($movie['status'] == 'now_showing') {
            $now_showing[] = $movie;
        } else {
            $coming_soon[] = $movie;
        }
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<main class="main-content">
    <div class="section-header">
        <div class="section-tab active" onclick="showSection('now-showing')">NOW SHOWING</div>
        <div class="section-tab" onclick="showSection('coming-soon')">COMING SOON</div>
    </div>

    <!-- Now Showing Section -->
    <div id="now-showing" class="movies-section">
        <div class="movies-grid">
            <?php foreach ($now_showing as $movie): ?>
                <div class="movie-card">
                    <div class="movie-rating"><?php echo htmlspecialchars($movie['rating']); ?></div>
                    <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="movie-poster">
                    <div class="movie-info">
                        <h3 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></h3>
                        <p class="movie-genre"><?php echo htmlspecialchars($movie['genre']); ?> • <?php echo $movie['duration']; ?> mins</p>
                        <a href="user/view_movie.php?id=<?php echo $movie['id']; ?>" class="buy-tickets-btn">Buy Tickets</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Coming Soon Section -->
    <div id="coming-soon" class="movies-section" style="display: none;">
        <div class="movies-grid">
            <?php foreach ($coming_soon as $movie): ?>
                <div class="movie-card">
                    <div class="movie-rating"><?php echo htmlspecialchars($movie['rating']); ?></div>
                    <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="movie-poster">
                    <div class="movie-info">
                        <h3 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></h3>
                        <p class="movie-genre"><?php echo htmlspecialchars($movie['genre']); ?> • <?php echo $movie['duration']; ?> mins</p>
                        <a href="user/view_movie.php?id=<?php echo $movie['id']; ?>" class="buy-tickets-btn">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<script>
function showSection(section) {
    // Hide all sections
    document.getElementById('now-showing').style.display = 'none';
    document.getElementById('coming-soon').style.display = 'none';
    
    // Remove active class from all tabs
    const tabs = document.querySelectorAll('.section-tab');
    tabs.forEach(tab => tab.classList.remove('active'));
    
    // Show selected section and activate tab
    document.getElementById(section).style.display = 'block';
    event.target.classList.add('active');
}
</script>

<?php include 'includes/footer.php'; ?>