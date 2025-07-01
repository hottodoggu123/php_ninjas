<?php
     function add_movie($addedMovie){   // Adding movies on the list
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
                else{       // THIS IS SAMPLE, TO BE CHANGED IF INPUT IS ADDED
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
                    
                    // Error Checking
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
?>