<?php
    include("Database.php");

    // SQL database
    $sql = "INSERT INTO movies (title, description, genre, duration, rating, release_date, poster_url, status, price)
            VALUES (
                'Roblox Movie', 
                'roblox movie yes', 
                'comedy', 
                4320, 
                'good movie', 
                '2025-06-30', 
                'https://m.media-amazon.com/images/M/MV5BNDFhZTU1MzAtODkwOC00NzkxLWE0YTEtN2Y2MGU0ODllYmFhXkEyXkFqcGc@._V1_', 
                'now_showing', 
                99.0
            )";

    mysqli_query($conn, $sql);
?>

<html>
    <head>
        <title>ADMIN TESTER</title>
    </head>

    <body>
        <?php
            $avail_movies = array("M1", "M2", "M3", "M4", "M5");
            $premiered_movies = array("M1", "M2");

            function add_movie($movie){  // Adding of movies on the list
                global $avail_movies;
                $add = true;

                foreach($avail_movies as $m){
                    if($movie == $m){
                        echo "Movie ".$m." is already on list..."."<br>";
                        $add = false;
                        break;
                    }
                }

                if($add == true){
                    $avail_movies[] = $movie;
                }

                print_r($avail_movies); // for checking
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

            add_movie("M1");

            update_movie("M8", "M9");



            mysqli_close($conn);
        ?>
    </body>

</html>