<?php
include '../includes/init.php';
include '../includes/header.php';

// Get all unique genres
$genreQuery = "SELECT DISTINCT genre FROM movies ORDER BY genre";
$genreResult = $conn->query($genreQuery);
$genres = [];
while ($genre = $genreResult->fetch_assoc()) {
    $genres[] = $genre['genre'];
}

// Get all unique ratings
$ratingQuery = "SELECT DISTINCT rating FROM movies ORDER BY rating";
$ratingResult = $conn->query($ratingQuery);
$ratings = [];
while ($rating = $ratingResult->fetch_assoc()) {
    $ratings[] = $rating['rating'];
}

// Build the query based on search parameters
$where = [];
$params = [];
$types = "";

// Base query
$sql = "SELECT * FROM movies WHERE 1=1";

// Handle search by title/description
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where[] = "(title LIKE ? OR description LIKE ?)";
    $searchTerm = "%" . $_GET['search'] . "%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

// Handle genre filter
if (isset($_GET['genres']) && !empty($_GET['genres'])) {
    $genreConditions = [];
    foreach ($_GET['genres'] as $genre) {
        $genreConditions[] = "genre = ?";
        $params[] = $genre;
        $types .= "s";
    }
    $where[] = "(" . implode(" OR ", $genreConditions) . ")";
}

// Handle rating filter
if (isset($_GET['ratings']) && !empty($_GET['ratings'])) {
    $ratingConditions = [];
    foreach ($_GET['ratings'] as $rating) {
        $ratingConditions[] = "rating = ?";
        $params[] = $rating;
        $types .= "s";
    }
    $where[] = "(" . implode(" OR ", $ratingConditions) . ")";
}

// Handle status filter
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $where[] = "status = ?";
    $params[] = $_GET['status'];
    $types .= "s";
}

// Handle release date range filter
if (isset($_GET['release_start']) && !empty($_GET['release_start'])) {
    $where[] = "release_date >= ?";
    $params[] = $_GET['release_start'];
    $types .= "s";
}

if (isset($_GET['release_end']) && !empty($_GET['release_end'])) {
    $where[] = "release_date <= ?";
    $params[] = $_GET['release_end'];
    $types .= "s";
}

// Handle price range filter
if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
    $where[] = "price >= ?";
    $params[] = $_GET['min_price'];
    $types .= "d";
}

if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
    $where[] = "price <= ?";
    $params[] = $_GET['max_price'];
    $types .= "d";
}

// Handle duration range filter
if (isset($_GET['min_duration']) && !empty($_GET['min_duration'])) {
    $where[] = "duration >= ?";
    $params[] = $_GET['min_duration'];
    $types .= "i";
}

if (isset($_GET['max_duration']) && !empty($_GET['max_duration'])) {
    $where[] = "duration <= ?";
    $params[] = $_GET['max_duration'];
    $types .= "i";
}

// Add where conditions to the query
if (!empty($where)) {
    $sql .= " AND " . implode(" AND ", $where);
}

// Add sorting
$sortField = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'title';
$sortDirection = isset($_GET['sort_direction']) ? $_GET['sort_direction'] : 'ASC';

// Validate sort field to prevent SQL injection
$validSortFields = ['title', 'release_date', 'rating', 'duration', 'price'];
if (!in_array($sortField, $validSortFields)) {
    $sortField = 'title';
}

// Validate sort direction
if ($sortDirection !== 'ASC' && $sortDirection !== 'DESC') {
    $sortDirection = 'ASC';
}

$sql .= " ORDER BY " . $sortField . " " . $sortDirection;

// Execute the query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$moviesCount = $result->num_rows;
?>

<div class="container">
    <h1 class="page-header">Advanced Movie Search</h1>

    <form action="" method="GET">
        <div class="search-filters">
            <div class="row">
                <div class="col-md-12">
                    <h3>Search Criteria</h3>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="search">Title or Description</label>
                        <input type="text" id="search" name="search" class="form-control" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="status">Movie Status</label>
                        <select id="status" name="status" class="form-control">
                            <option value="">All</option>
                            <option value="now_showing" <?php echo isset($_GET['status']) && $_GET['status'] === 'now_showing' ? 'selected' : ''; ?>>Now Showing</option>
                            <option value="coming_soon" <?php echo isset($_GET['status']) && $_GET['status'] === 'coming_soon' ? 'selected' : ''; ?>>Coming Soon</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="sort_by">Sort By</label>
                        <select id="sort_by" name="sort_by" class="form-control">
                            <option value="title" <?php echo isset($_GET['sort_by']) && $_GET['sort_by'] === 'title' ? 'selected' : ''; ?>>Title</option>
                            <option value="release_date" <?php echo isset($_GET['sort_by']) && $_GET['sort_by'] === 'release_date' ? 'selected' : ''; ?>>Release Date</option>
                            <option value="rating" <?php echo isset($_GET['sort_by']) && $_GET['sort_by'] === 'rating' ? 'selected' : ''; ?>>Rating</option>
                            <option value="duration" <?php echo isset($_GET['sort_by']) && $_GET['sort_by'] === 'duration' ? 'selected' : ''; ?>>Duration</option>
                            <option value="price" <?php echo isset($_GET['sort_by']) && $_GET['sort_by'] === 'price' ? 'selected' : ''; ?>>Price</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Sort Direction</label>
                        <div>
                            <label class="radio-inline">
                                <input type="radio" name="sort_direction" value="ASC" <?php echo !isset($_GET['sort_direction']) || $_GET['sort_direction'] === 'ASC' ? 'checked' : ''; ?>> Ascending
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="sort_direction" value="DESC" <?php echo isset($_GET['sort_direction']) && $_GET['sort_direction'] === 'DESC' ? 'checked' : ''; ?>> Descending
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-9">
                    <div class="form-group">
                        <label>Genres</label>
                        <div class="genre-checkboxes">
                            <?php foreach ($genres as $genre): ?>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="genres[]" value="<?php echo htmlspecialchars($genre); ?>" 
                                    <?php echo isset($_GET['genres']) && in_array($genre, $_GET['genres']) ? 'checked' : ''; ?>>
                                <?php echo htmlspecialchars($genre); ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Price Range (₱)</label>
                        <div class="row">
                            <div class="col-xs-6">
                                <input type="number" name="min_price" class="form-control" placeholder="Min" value="<?php echo isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : ''; ?>">
                            </div>
                            <div class="col-xs-6">
                                <input type="number" name="max_price" class="form-control" placeholder="Max" value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : ''; ?>">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Duration Range (minutes)</label>
                        <div class="row">
                            <div class="col-xs-6">
                                <input type="number" name="min_duration" class="form-control" placeholder="Min" value="<?php echo isset($_GET['min_duration']) ? htmlspecialchars($_GET['min_duration']) : ''; ?>">
                            </div>
                            <div class="col-xs-6">
                                <input type="number" name="max_duration" class="form-control" placeholder="Max" value="<?php echo isset($_GET['max_duration']) ? htmlspecialchars($_GET['max_duration']) : ''; ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Release Date Range</label>
                        <div class="row">
                            <div class="col-xs-6">
                                <input type="date" name="release_start" class="form-control" value="<?php echo isset($_GET['release_start']) ? htmlspecialchars($_GET['release_start']) : ''; ?>">
                            </div>
                            <div class="col-xs-6">
                                <input type="date" name="release_end" class="form-control" value="<?php echo isset($_GET['release_end']) ? htmlspecialchars($_GET['release_end']) : ''; ?>">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Ratings</label>
                        <div>
                            <?php foreach ($ratings as $rating): ?>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="ratings[]" value="<?php echo htmlspecialchars($rating); ?>" 
                                    <?php echo isset($_GET['ratings']) && in_array($rating, $_GET['ratings']) ? 'checked' : ''; ?>>
                                <?php echo htmlspecialchars($rating); ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 text-center">
                    <button type="submit" class="btn btn-primary btn-block">Apply Filters</button>
                </div>
            </div>
        </div>
    </form>

    <div class="results-header">
        <h2>Search Results</h2>
        <div class="result-count"><?php echo $moviesCount; ?> movies found</div>
    </div>

    <?php if ($moviesCount > 0): ?>
        <div class="movie-grid">
            <?php while ($movie = $result->fetch_assoc()): ?>
                <div class="movie-card">
                    <div class="movie-poster">
                        <img src="<?php echo htmlspecialchars('../' . $movie['poster_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                        <?php if ($movie['status'] === 'coming_soon'): ?>
                            <div class="movie-badge coming-soon">Coming Soon</div>
                        <?php endif; ?>
                    </div>
                    <div class="movie-info">
                        <h4><?php echo htmlspecialchars($movie['title']); ?></h4>
                        <div class="movie-meta">
                            <?php echo htmlspecialchars($movie['genre']); ?> | <?php echo htmlspecialchars($movie['duration']); ?> min | <?php echo htmlspecialchars($movie['rating']); ?>
                        </div>
                        <div class="movie-release">
                            <strong>Release:</strong> <?php echo date('F d, Y', strtotime($movie['release_date'])); ?>
                        </div>
                        <div class="movie-actions">
                            <span class="price">₱<?php echo number_format($movie['price'], 2); ?></span>
                            <a href="viewMovie.php?movie_id=<?php echo $movie['id']; ?>" class="button">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="no-results">
            <p>No movies match your search criteria. Please try with different filters.</p>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
