<?php
include '../includes/init.php';
include '../includes/header.php';

// Check which section to show
$view = isset($_GET['view']) && $_GET['view'] === 'coming-soon' ? 'coming-soon' : 'now-showing';
$status = $view === 'coming-soon' ? 'coming_soon' : 'now_showing';

// Fetch movies from the database
$stmt = $conn->prepare("SELECT id, title, poster_url, status FROM movies WHERE status = ?");
$stmt->bind_param("s", $status);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container">
    <h2>
        <?php if ($view === 'now-showing'): ?>
            <span class="toggle-active">Now Showing</span>
            <span class="toggle-divider">|</span>
            <a href="?view=coming-soon" class="toggle-inactive">Coming Soon</a>
        <?php else: ?>
            <a href="?view=now-showing" class="toggle-inactive">Now Showing</a>
            <span class="toggle-divider">|</span>
            <span class="toggle-active">Coming Soon</span>
        <?php endif; ?>
    </h2>

    <div class="movie-list">
        <?php while ($movie = $result->fetch_assoc()): ?>
            <a href="viewMovie.php?movie_id=<?php echo $movie['id']; ?>" style="text-decoration: none; color: inherit;">
                <div class="movies-card">
                    <div class="poster-section">
                        <img src="../<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                    </div>
                    <div class="title-section">
                        <h3><?php echo htmlspecialchars($movie['title']); ?></h3>
                    </div>
                </div>
            </a>
        <?php endwhile; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>