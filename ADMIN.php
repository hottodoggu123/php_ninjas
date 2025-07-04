<!--THIS IS TESTER FOR ADMIN FUNCTIONS-->
<!--FUNCTIONS WILL BE MOVED IN SPECIFIC FILES IF DONE-->
<?php
    include 'includes/db.php';
?>

<html>
    <head>
        <title>ADMIN TESTER</title>
    </head>

    <body>
        <?php
            function add_movie($addedMovie){  // Adding of movies on the list
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
            }

            function delete_movie($deleteMovie){
                global $conn;

                $sql = "DELETE from movies WHERE title = :title";

                try{
                    $stmt = $conn->prepare($sql);
                    $stmt->execute(['title' => $deleteMovie]);

                    if($stmt->rowCount() > 0){
                        echo "MOVIE ".$deleteMovie." DELETED";
                    }
                    else{
                        echo "MOVIE NOT FOUND";
                    }
                }
                catch(PDOException $e){
                    echo "ERROR: ".$e->getMessage();
                }
            }

            function manage_movie(){
                global $conn;
                $sql = "SELECT * FROM movies";
                $result = mysqli_query($conn, $sql);
                if ($result && mysqli_num_rows($result) > 0) {
                    echo '<table border="1" cellpadding="5" cellspacing="0">';
                    echo '<tr>';
                    // Print table headers
                    while ($fieldinfo = mysqli_fetch_field($result)) {
                        echo '<th>' . htmlspecialchars($fieldinfo->name) . '</th>';
                    }
                    echo '</tr>';
                    // Print table rows
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<tr>';
                        foreach ($row as $cell) {
                            echo '<td>' . htmlspecialchars($cell) . '</td>';
                        }
                        echo '</tr>';
                    }
                    echo '</table>';
                } else {
                    echo 'No movies found.';
                }
            }

            /*function update_movie($new_movie, $remove_movie){  // Updating the movie premiered
                global $premiered_movies;
                global $avail_movies;
                $counter = 0;
                $update = false;

                //$new = $_POST["new_movie"];                   when input is added
                //$removed = $_POST["remove_movie"];            when input is added

                $new = $new_movie;            // TESTER
                $removed = $remove_movie;     // TESTER

                foreach($premiered_movies as $m){
                    if($removed == $m){
                        if(in_array($new, $avail_movies)){
                            $premiered_movies[$counter] = $new;
                            $update = true;
                            echo "Movie List updated..."."<br>";
                        }
                        else{
                            echo "Movie is not available..."."<br>";
                        }
                    }
                    $counter++;
                }

                if($update == false){
                    echo "No movie exist on the preview..."."<br>";
                }
                
                print_r($premiered_movies); // for checking
            }*/

            
            add_movie("Roblox Movie 2");
            manage_movie();
            //delete_movie("Roblox Movie 2");




            //testing commit
        ?>

    </body>

</html>