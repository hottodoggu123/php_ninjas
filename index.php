<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cinema Booking | Home</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header>
    <h1>Welcome to PHP Ninja Cinema</h1>
    <p>Book your favorite movie tickets now!</p>
</header>

<div class="container">
    <h2>Now Showing</h2>
    <div class="movie-list">

        <!-- Movie Card 1 -->
        <div class="movie-card">
            <img src="assets/images/movie1.jpg" alt="Movie 1">
            <h3>Spider-Man: No Way Home</h3>
            <a href="book.php?movie=spiderman" class="button">Book Now</a>
        </div>

        <!-- Movie Card 2 -->
        <div class="movie-card">
            <img src="assets/images/movie2.jpg" alt="Movie 2">
            <h3>Inside Out 2</h3>
            <a href="book.php?movie=insideout2" class="button">Book Now</a>
        </div>

        <!-- Movie Card 3 -->
        <div class="movie-card">
            <img src="assets/images/movie3.jpg" alt="Movie 3">
            <h3>Kung Fu Panda 4</h3>
            <a href="book.php?movie=kungfupanda4" class="button">Book Now</a>
        </div>

        <!-- Add more movies below if needed -->

    </div>
</div>

<footer>
    &copy; <?php echo date("Y"); ?> PHP Ninja Cinemas. All rights reserved.
</footer>

</body>
</html>
