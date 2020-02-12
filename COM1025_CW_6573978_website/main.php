<?php
    define('MYSQL_SERVER', 'localhosts');
    define('MYSQL_USER', 'root');
    define('MYSQL_PASSWORD', '');
    define('MYSQL_DATABASE', 'tournaments_db');

    $conn = new mysqli(MYSQL_SERVER, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);

    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }

    include 'update.php'; 

    $res = $conn->query('SELECT PersonID, Tag FROM Player');
    if (!$res) {
        echo 'Query failed : (' . $conn->errno . ') ' . $conn->error;
    }

    $playerTags = $res->fetch_all(MYSQLI_ASSOC);    // Fetching all PersonID and Tag rows from Player 
    $res->free();

    /* Emit a select element containing all player's tags 
    $value parameter is used to set the 'selected' attribute for a specific option element */
    function emitTagSelector($value)
    {
        global $playerTags;
        echo '<select name="Player">';
        foreach ($playerTags as $tag) {
            echo '<option ';
            echo ($tag['PersonID'] === $value) ? 'selected' : '';
            echo ' value="'. $tag['PersonID'] .'">' . $tag['Tag'];
            echo '</option>';
        }
        echo '</select>';
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title> Pretty HTML Page </title>
        <style>
            table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
            }
            th, td {
                padding: 5px;
            }
        </style>
    </head>

    <body>
        <!-- This form is handled by the code in checkbox.php which prints unmodified database tables based on input-->
        <form method="POST">
            <input type="checkbox" name="person">Person Table<br>
            <input type="checkbox" name="player">Player Table<br>
            <input type="checkbox" name="tournament">Tournaments Table<br>
            <input type="submit" style="margin-top: 5px;">
        </form>
        
        <!-- This table makes you interact with the database by being able to update, insert and delete values from the PlayerSponsors table
        All the forms inputs are handled by the code in update.php -->
        <table style="position:absolute; top: 10px; left: 600px;">
            <tr>
                <th>Tag</th>
                <th>Sponsor</th>
            </tr>
            <?php
            $res = $conn->query('SELECT PlayerID, SponsorName FROM PlayerSponsors');
            if (!$res) {
                echo 'Query failed: (' . $conn->errno . ') ' . $conn->error;
            }

            while ($row = $res->fetch_assoc()) { ?>
                <tr>
                    <form method="POST">
                        <td>
                            <?php emitTagSelector($row['PlayerID']) ?>
                        </td>
                        <td>
                            <input size="7" type="text" name="Sponsor" value="<?php echo $row['SponsorName'] ?>">
                        </td>
                        <td>
                            <input type="submit" name="action" value="update">
                            <input type="submit" name="action" value="delete">
                            <!-- _Player and _Sponsors keep track of original values for PlayerID and SponsorName
                            so they can be used in update.php to know which entry to upfate or delete in the database -->
                            <input type="hidden" name="_Player" value="<?php echo $row['PlayerID'] ?>"> 
                            <input type="hidden" name="_Sponsor" value="<?php echo $row['SponsorName'] ?>">
                        </td>
                    </form>
                </tr>
            <?php
            }
            $res->free(); ?>
            <tr>
                <form method="POST">
                    <td>
                        <?php emitTagSelector(null) ?> <!-- Default value for selected attribute is used as it is a new entry -->
                    </td>
                    <td>
                        <input size="7" type="text" name="Sponsor">
                    </td>
                    <td>
                        <input style="width: 56px;" type="submit" name="action" value="add">
                    </td>
                </form>
            </tr>
        </table>
        
        <!-- Prints PlayerSponsors table to make sure the table above is affecting values in the database -->
        <table style="position: absolute; top: 400px; left: 600px;">
            <tr>
                <th>Tag</th>
                <th>Sponsor</th>
            </tr>
            <?php
            $res = $conn->query('SELECT Tag, SponsorName FROM PlayerSponsors s INNER JOIN Player p ON s.PlayerID=p.PersonID');
            if (!$res) {
                echo 'Query failed: (' . $conn->errno . ') ' . $conn->error;
            }

            while ($row = $res->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . $row['Tag'] . '</td>';
                echo '<td>' . $row['SponsorName'] . '</td>';
                echo '</tr>';
            }
            $res->free(); ?>
        </table>
        
        <!-- This form is handled by select.php which prints processed tables based on more complicated sql statements -->
        <form style="position: absolute; right: 100px; top: 10px;" method="POST">
            <select name="data">
                <option value="standings">BBTag Standings</option>
                <option value="winners">Tekken Winners</option>
                <option value="spectators">BBtag players and spectators</option>
                <option value="pros">Tekken pros and amateurs</option>
                <option value="country_stats"> Country Stats </option>
            </select>
            <input style="margin-top: 5px;" type="submit">
        </form>

        <?php
        include 'checkbox.php';    // Code for printing the checkbox's tables
        include 'select.php';      // Code for printing the select's tables
        ?>
    </body>
</html>

<?php
$conn->close(); // End Connection every time page finished loading
?>