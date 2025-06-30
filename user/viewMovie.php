<?php
require_once '../includes/db.php';

$movie_id = $_GET['id'] ?? 0;

try {
    $stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?");
    $stmt->execute([$movie_id]);
    $movie = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$movie) {
        header('Location: movies.php');
        exit();
    }
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

include '../includes/header.php';
?>

<div class="main-content">
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 3rem; margin-bottom: 2rem;">
        <div>
            <img src="../<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" style="width: 100%; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
        </div>
        
        <div>
            <h1 style="color: #333; margin-bottom: 1rem;"><?php echo htmlspecialchars($movie['title']); ?></h1>
            
            <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                <span style="background: #ffcc00; color: #333; padding: 0.3rem 0.8rem; border-radius: 5px; font-weight: bold;">
                    <?php echo htmlspecialchars($movie['rating']); ?>
                </span>
                <span style="background: #e63946; color: white; padding: 0.3rem 0.8rem; border-radius: 5px;">
                    <?php echo ucfirst(str_replace('_', ' ', $movie['status'])); ?>
                </span>
            </div>
            
            <div style="margin-bottom: 2rem;">
                <p><strong>Genre:</strong> <?php echo htmlspecialchars($movie['genre']); ?></p>
                <p><strong>Duration:</strong> <?php echo $movie['duration']; ?> minutes</p>
                <p><strong>Release Date:</strong> <?php echo date('F j, Y', strtotime($movie['release_date'])); ?></p>
                <p><strong>Price:</strong> ₱<?php echo number_format($movie['price'], 2); ?></p>
            </div>
            
            <div style="margin-bottom: 2rem;">
                <h3>Description</h3>
                <p style="line-height: 1.6; color: #666;"><?php echo nl2br(htmlspecialchars($movie['description'])); ?></p>
            </div>
            
            <div>
                <?php if ($movie['status'] == 'now_showing'): ?>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="book_ticket.php?id=<?php echo $movie['id']; ?>" class="btn" style="margin-right: 1rem;">Book Tickets</a>
                    <?php else: ?>
                        <a href="../login.php" class="btn" style="margin-right: 1rem;">Login to Book</a>
                    <?php endif; ?>
                <?php else: ?>
                    <button class="btn" disabled>Coming Soon</button>
                <?php endif; ?>
                <a href="movies.php" class="btn btn-secondary">Back to Movies</a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>