<?php
include '../includes/db.php';
include '../includes/header.php';

$id = $_GET['id'];
$query = "SELECT * FROM movies WHERE id = $id";
$result = mysqli_query($conn, $query);
$movie = mysqli_fetch_assoc($result);
?>

<h2><?= $movie['title'] ?></h2>
<img src="<?= $movie['poster'] ?>" alt="<?= $movie['title'] ?>" style="max-width:200px;">

<form action="book_ticket.php" method="post">
    <input type="hidden" name="movie_id" value="<?= $movie['id'] ?>">
    <label for="seat">Seat Number:</label>
    <input type="text" name="seat" required>
    <button type="submit">Book Ticket</button>
</form>

<?php include '../includes/footer.php'; ?>