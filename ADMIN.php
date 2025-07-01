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
                global $pdo;

                // SQL Check if movie already exist on the database
                $checking = "SELECT COUNT(*) FROM movies WHERE title = :title";
                $checkMatch = $pdo->prepare($checking);
                $checkMatch->execute(['title' => $addedMovie]);
                $exist = $checkMatch->fetchColumn();

                // SQL adding movie
                if($exist > 0){
                    echo "Movie Exist";
                }
                else{
                    $sql = "INSERT INTO movies (title, description, genre, duration, rating, release_date, poster_url, status, price, created_at)
                        VALUES (
                                :title, 
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
                    
                    try{
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute(['title' => $addedMovie]);
                        echo "WORKED";
                    }
                    catch(PDOException $e){
                        echo "ERROR: ".$e->getmessage();
                    }
                }
            }

            function update_movie($new_movie, $remove_movie){  // Updating the movie premiered
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
            }

            add_movie("Roblox Movie 2");
        ?>

    </body>

</html>