<?php
    global $conn;

    // SQL Check if movie already exist on the database
    $checking = "SELECT COUNT(*) FROM movies WHERE title = ?";
    $check_stmt = mysqli_prepare($conn, $checking);
    mysqli_stmt_bind_param($check_stmt, "s", $addedMovie);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_bind_result($check_stmt, $exist);
    mysqli_stmt_fetch($check_stmt);
    mysqli_stmt_close($check_stmt);

    // SQL adding movie
    if($exist > 0){
        echo "Movie Exist";
    }
    else{
        $sql = "INSERT INTO movies (title, description, genre, duration, rating, release_date, poster_url, status, price, created_at)
                VALUES (
                    ?, 
                    'roblox movie yes', 
                    'comedy', 
                    120, 
                    'G', 
                    '2025-06-30', 
                    'https://m.media-amazon.com/images/M/MV5BNDFhZTU1MzAtODkwOC00NzkxLWE0YTEtN2Y2MGU0ODllYmFhXkEyXkFqcGc@._V1_', 
                    'now_showing', 
                    99.0,
                    NOW()
                )";
                    
        $insert_stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($insert_stmt, "s", $addedMovie);
        
        if(mysqli_stmt_execute($insert_stmt)){
            echo "WORKED";
        }
        else{
            echo "ERROR: ".mysqli_error($conn);
        }
                   
        mysqli_stmt_close($insert_stmt);
    }
?>