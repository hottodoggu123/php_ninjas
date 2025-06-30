<?php
session_start();
include('../includes/db.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

// Handle movie deletion
if (isset($_GET['delete'])) {
    $movie_id = (int)$_GET['delete'];
    
    // First delete related bookings (cascade delete)
    $delete_bookings = "DELETE FROM bookings WHERE movie_id = $movie_id";
    mysqli_query($conn, $delete_bookings);
    
    // Then delete the movie
    $delete_movie = "DELETE FROM movies WHERE id = $movie_id";
    if (mysqli_query($conn, $delete_movie)) {
        $_SESSION['message'] = "Movie deleted successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting movie!";
        $_SESSION['message_type'] = "error";
    }
    header('Location: manage_movies.php');
    exit();
}

// Search and filter functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter_genre = isset($_GET['genre']) ? $_GET['genre'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

// Build query with conditions
$where_conditions = array();
if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $where_conditions[] = "(title LIKE '%$search%' OR director LIKE '%$search%')";
}
if (!empty($filter_genre)) {
    $filter_genre = mysqli_real_escape_string($conn, $filter_genre);
    $where_conditions[] = "genre = '$filter_genre'";
}
if (!empty($filter_status)) {
    $filter_status = mysqli_real_escape_string($conn, $filter_status);
    $where_conditions[] = "status = '$filter_status'";
}

$where_clause = '';
if (count($where_conditions) > 0) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get movies with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$query = "SELECT * FROM movies $where_clause ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
$result = mysqli_query($conn, $query);
$movies = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Count total movies for pagination
$count_query = "SELECT COUNT(*) as total FROM movies $where_clause";
$count_result = mysqli_query($conn, $count_query);
$total_movies = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_movies / $per_page);

// Get distinct genres for filter dropdown
$genres_query = "SELECT DISTINCT genre FROM movies WHERE genre IS NOT NULL AND genre != '' ORDER BY genre";
$genres_result = mysqli_query($conn, $genres_query);
$genres = mysqli_fetch_all($genres_result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Movies - Cinema Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .manage-movies {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .search-filters {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .filter-row {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            min-width: 150px;
        }
        
        .filter-group label {
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        
        .filter-group input,
        .filter-group select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056b3;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }
        
        .movies-table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .movie-poster {
            width: 60px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-upcoming {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .actions {
            display: flex;
            gap: 5px;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }
        
        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            text-decoration: none;
            color: #333;
            border-radius: 4px;
        }
        
        .pagination .current {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        .pagination a:hover {
            background: #f8f9fa;
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #007bff;
        }
        
        .stat-label {
            color: #666;
            margin-top: 5px;
        }
        
        .message {
            padding: 12px 20px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        @media (max-width: 768px) {
            .header-section {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-row {
                flex-direction: column;
            }
            
            .movies-table {
                overflow-x: auto;
            }
            
            table {
                min-width: 800px;
            }
        }
    </style>
</head>
<body>
    <?php include('../includes/header.php'); ?>
    
    <div class="manage-movies">
        <div class="header-section">
            <h1>Manage Movies</h1>
            <a href="add_movie.php" class="btn btn-success">Add New Movie</a>
        </div>
        
        <?php
        // Display messages
        if (isset($_SESSION['message'])) {
            $message_type = $_SESSION['message_type'] ?? 'success';
            echo "<div class='message $message_type'>" . $_SESSION['message'] . "</div>";
            unset($_SESSION['message'], $_SESSION['message_type']);
        }
        ?>
        
        <?php
        // Display statistics
        $stats_queries = array(
            'Total Movies' => "SELECT COUNT(*) as count FROM movies",
            'Active Movies' => "SELECT COUNT(*) as count FROM movies WHERE status = 'active'",
            'Upcoming Movies' => "SELECT COUNT(*) as count FROM movies WHERE status = 'upcoming'",
            'Total Bookings' => "SELECT COUNT(*) as count FROM bookings"
        );
        
        echo "<div class='stats-cards'>";
        foreach ($stats_queries as $label => $query) {
            $result = mysqli_query($conn, $query);
            $count = mysqli_fetch_assoc($result)['count'];
            echo "<div class='stat-card'>";
            echo "<div class='stat-number'>$count</div>";
            echo "<div class='stat-label'>$label</div>";
            echo "</div>";
        }
        echo "</div>";
        ?>
        
        <!-- Search and Filter Section -->
        <div class="search-filters">
            <form method="GET" action="">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="search">Search Movies:</label>
                        <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" 
                               placeholder="Title or Director">
                    </div>
                    
                    <div class="filter-group">
                        <label for="genre">Genre:</label>
                        <select id="genre" name="genre">
                            <option value="">All Genres</option>
                            <?php foreach ($genres as $genre): ?>
                                <option value="<?= htmlspecialchars($genre['genre']) ?>" 
                                        <?= $filter_genre === $genre['genre'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($genre['genre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="status">Status:</label>
                        <select id="status" name="status">
                            <option value="">All Status</option>
                            <option value="active" <?= $filter_status === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $filter_status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            <option value="upcoming" <?= $filter_status === 'upcoming' ? 'selected' : '' ?>>Upcoming</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="manage_movies.php" class="btn" style="background: #6c757d; color: white; margin-top: 5px;">Clear</a>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Movies Table -->
        <div class="movies-table">
            <?php if (count($movies) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Poster</th>
                            <th>Title</th>
                            <th>Director</th>
                            <th>Genre</th>
                            <th>Duration</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Show Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movies as $movie): ?>
                            <tr>
                                <td>
                                    <?php if ($movie['poster']): ?>
                                        <img src="../uploads/<?= htmlspecialchars($movie['poster']) ?>" 
                                             alt="<?= htmlspecialchars($movie['title']) ?>" 
                                             class="movie-poster">
                                    <?php else: ?>
                                        <div class="movie-poster" style="background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #666;">
                                            No Image
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($movie['title']) ?></strong>
                                    <br>
                                    <small style="color: #666;">
                                        Added: <?= date('M j, Y', strtotime($movie['created_at'])) ?>
                                    </small>
                                </td>
                                <td><?= htmlspecialchars($movie['director']) ?></td>
                                <td><?= htmlspecialchars($movie['genre']) ?></td>
                                <td><?= htmlspecialchars($movie['duration']) ?> min</td>
                                <td>₱<?= number_format($movie['price'], 2) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $movie['status'] ?>">
                                        <?= ucfirst($movie['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($movie['show_date']): ?>
                                        <?= date('M j, Y', strtotime($movie['show_date'])) ?>
                                        <br>
                                        <small><?= date('g:i A', strtotime($movie['show_time'])) ?></small>
                                    <?php else: ?>
                                        <span style="color: #999;">Not set</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="edit_movie.php?id=<?= $movie['id'] ?>" 
                                           class="btn btn-warning btn-sm" title="Edit">Edit</a>
                                        <a href="?delete=<?= $movie['id'] ?>" 
                                           class="btn btn-danger btn-sm" title="Delete"
                                           onclick="return confirm('Are you sure you want to delete this movie? This will also delete all related bookings.')">
                                           Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="padding: 40px; text-align: center; color: #666;">
                    <h3>No movies found</h3>
                    <p>Try adjusting your search criteria or add some movies to get started.</p>
                    <a href="add_movie.php" class="btn btn-success">Add Your First Movie</a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&genre=<?= urlencode($filter_genre) ?>&status=<?= urlencode($filter_status) ?>">
                        &laquo; Previous
                    </a>
                <?php endif; ?>
                
                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                for ($i = $start_page; $i <= $end_page; $i++):
                ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&genre=<?= urlencode($filter_genre) ?>&status=<?= urlencode($filter_status) ?>">
                            <?= $i ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&genre=<?= urlencode($filter_genre) ?>&status=<?= urlencode($filter_status) ?>">
                        Next &raquo;
                    </a>
                <?php endif; ?>
            </div>
            
            <div style="text-align: center; margin-top: 10px; color: #666;">
                Showing <?= count($movies) ?> of <?= $total_movies ?> movies 
                (Page <?= $page ?> of <?= $total_pages ?>)
            </div>
        <?php endif; ?>
    </div>
    
    <?php include('../includes/footer.php'); ?>
</body>
</html>