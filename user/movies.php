<?php
include '../includes/init.php';
include '../includes/header.php';

// Check which section to show
$view = isset($_GET['view']) && $_GET['view'] === 'coming-soon' ? 'coming-soon' : 'now-showing';
$status = $view === 'coming-soon' ? 'coming_soon' : 'now_showing';

// Fetch movies from the database
$movies = $movieService->getMoviesByStatus($status);
?>

<div class="container">
    <?php renderMovieStatusToggle($view); ?>
    <?php renderMovieList($movies, '../'); ?>
</div>

<?php include '../includes/footer.php'; ?>