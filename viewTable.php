<?php
    declare(strict_types = 1);
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    session_save_path("sessions");
    session_start();
    if(!isset($_SESSION["username"])){
        header("Location: index.php");
        die;
    }
    $_SESSION["enemyId"] = -1;
    
    $curYear = date('Y');
    $phpScript = sanitizeValue($_SERVER['PHP_SELF']);
    $table = "";

    function sanitizeValue($value){
        return htmlspecialchars(stripslashes(trim($value)));
    }
    function createTable($pdo){
        $str = '
        <tr>
            <th></th>
            <th>Name</th>
            <th>Sprite</th>
            <th>Health</th>
            <th>Speed</th>
            <th>Attack</th>
        </tr>';
        $sql = $pdo->query("SELECT enemies.*, attacks.name AS attackName FROM enemies LEFT JOIN attacks ON enemies.attack\$id = attacks.id ORDER BY name;");
        $totalEntries = 0;
        while($row = $sql->fetch()){
            $str .= '
                <tr>
                    <td class="w3-padding-small" style="width:1%;white-space:nowrap;"><button type="submit" class="w3-btn w3-round w3-red" name="delete" value="'.$row['id'].'"><i class="fa fa-trash"></i></button>
                    <button type="submit" class="w3-btn w3-round w3-red" name="edit" value="'.$row['id'].'"><i class="fa fa-edit"></i></button></td>
                    <td>'.$row['name'].'</td>
                    <td>'.$row['sprite'].'</td>
                    <td>'.$row['hp'].'</td>
                    <td>'.$row['spd'].'</td>';
            if($row['attack$id'] == null){
                $str .= '<td>NONE</td>';
            }else{
                $str .= '<td>'.$row['attackName'].'</td>';
            }
            $str .= '</tr>';
            $totalEntries++;
        }
        if($totalEntries == 0){
            $str .= '<tr><td></td><td>The table is empty.</td></tr>';
        }
        return $str;
    }

    try{
        require "inc.db.php";
        $pdo = new PDO(DSN, USER, PWD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }catch(PDOException $e){
        die($e->getMessage());
    }
    $table = createTable($pdo);
    
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        if(isset($_POST["create"])){
            $pdo = null;
            $_SESSION["enemyId"] = 0;
            header("Location: enemyForm.php");
            die;
        }else if(isset($_POST["edit"])){
            $pdo = null;
            $_SESSION["enemyId"] = $_POST["edit"];
            header("Location: enemyForm.php");
            die;
        }else if(isset($_POST["delete"])){
            $sql = "DELETE FROM enemies WHERE id = ".$_POST["delete"].";";
            $pdo->exec($sql);
            header("Location: viewTable.php");
            die;
        }
    }

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Enemies</title>
        <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    </head>
    <body class="w3-container" style="overflow:hidden;">
        <div class="w3-card w3-green" id="heading">
            <header class="w3-container">
            <h1 class="w3-margin-left w3-show-inline-block">Enemies</h1>
            <div class="w3-container w3-show-inline-block w3-right">
                <p class="w3-show-inline-block w3-center">Logged in as <?php echo $_SESSION["username"]; ?></p>
                <a class="w3-button w3-round w3-show-inline-block w3-center" href="logOut.php">Log Out</a>
            </div>
            </header>
        </div>
        <form action="<?php echo $phpScript; ?>" method="POST">
            <div class="w3-card" style="overflow:scroll;" id="table">
                <table class="w3-table w3-striped w3-bordered">
                    <?php echo $table; ?>
                </table>
            </div>
            <div class="w3-row" id="buttons">
                <br>
                <div class="w3-center">
                    <button type="submit" name="create" class="w3-btn w3-red w3-round">Create</button>
                </div>
                <br>
            </div>
        </form>
        <footer class="w3-center w3-bottom w3-white">Carter T. McCall - <?php echo $curYear; ?></footer>
        <script src="js/viewTable.js"></script>
    </body>
</html>