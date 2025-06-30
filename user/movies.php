<?php
include '../includes/db.php';
include '../includes/header.php';

$query = "SELECT * FROM movies WHERE status = 'now_showing'";
$result = mysqli_query($conn, $query);
?>

<h2>All Movies</h2>
<div class="movie-grid">
    <?php while($movie = mysqli_fetch_assoc($result)): ?>
        <div class="movie-card">
            <img src="<?= $movie['poster'] ?>" alt="<?= $movie['title'] ?>">
            <h3><?= $movie['title'] ?></h3>
            <a href="view_movie.php?id=<?= $movie['id'] ?>">View Details</a>
        </div>
    <?php endwhile; ?>
</div>

<?php include '../includes/footer.php'; ?>