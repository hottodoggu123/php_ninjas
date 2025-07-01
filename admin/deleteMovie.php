<?php
    function delete_movie($deleteMovie){    // Deleting movies on the list
                global $pdo;

                // SQL deleting movie
                $sql = "DELETE from movies WHERE title = :title";

                // Error Checking
                try{
                    $stmt = $pdo->prepare($sql);
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
?>