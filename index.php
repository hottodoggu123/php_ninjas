<?php
include 'includes/db.php';
include 'includes/header.php';

// Fetch movies from the database
$query = "SELECT * FROM movies";
$result = mysqli_query($conn, $query);
$nowShowing = [];
$comingSoon = [];

while($movie = mysqli_fetch_assoc($result)) {
    if ($movie['status'] === 'now_showing') {
        $nowShowing[] = $movie;
    } else {
        $comingSoon[] = $movie;
    }
}
?>

<div class="section">
    <h2>NOW SHOWING</h2>
    <div class="movie-grid">
        <?php foreach($nowShowing as $movie): ?>
            <div class="movie-card">
                <img src="<?= $movie['poster'] ?>" alt="<?= $movie['title'] ?>">
                <h3><?= $movie['title'] ?></h3>
                <a href="user/view_movie.php?id=<?= $movie['id'] ?>">Buy Tickets</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="section">
    <h2>COMING SOON</h2>
    <div class="movie-grid">
        <?php foreach($comingSoon as $movie): ?>
            <div class="movie-card">
                <img src="<?= $movie['poster'] ?>" alt="<?= $movie['title'] ?>">
                <h3><?= $movie['title'] ?></h3>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>