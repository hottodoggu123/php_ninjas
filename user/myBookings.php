<?php
include '../includes/db.php';
include '../includes/header.php';
session_start();

$user_id = 1; // Placeholder user; update with $_SESSION['user_id']

$query = "SELECT b.*, m.title FROM bookings b
          JOIN movies m ON b.movie_id = m.id
          WHERE b.user_id = $user_id";
$result = mysqli_query($conn, $query);
?>

<h2>My Bookings</h2>
<table border="1" cellpadding="10">
    <tr>
        <th>Movie</th>
        <th>Seat</th>
        <th>Date</th>
    </tr>
    <?php while($row = mysqli_fetch_assoc($result)): ?>
    <tr>
        <td><?= $row['title'] ?></td>
        <td><?= $row['seat_number'] ?></td>
        <td><?= $row['booking_time'] ?></td>
    </tr>
    <?php endwhile; ?>
</table>

<?php include '../includes/footer.php'; ?>