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
    if(!isset($_SESSION["enemyId"])){
        $_SESSION["enemyId"] = -1;
    }
    if($_SESSION["enemyId"] == -1){// will be -1 if user went to this page without clicking anything in viewTable.php
        header("Location: viewTable.php");
        die;
    }
    
    $curYear = date('Y');
    $phpScript = sanitizeValue($_SERVER['PHP_SELF']);
    $health = $speed = $attackId = 0;
    $attacks = $name = $sprite = $warning = "";
    $edit = false;

    function sanitizeValue($value){
        return htmlspecialchars(stripslashes(trim($value)));
    }
    function attackDropdown($pdo){
        global $attackId;
        $str = '<option value="none"';
        if($attackId == 0){
            $str .= ' selected';
        }
        $str .= '>None</option>';
        $sql = $pdo->query("SELECT id, name, dmg FROM attacks ORDER BY name;");
        while($row = $sql->fetch()){
            $str .= '<option value="'.$row['id'].'"';
            if($row['id'] == $attackId){// $row['id'] will never be zero
                $str .= ' selected';
            }
            $str .= '>'.$row['name'].' ('.$row['dmg'].' DMG)</option>';
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
    if($_SESSION['enemyId'] != 0){
        $edit = true;
        $sql = $pdo->query("SELECT name, sprite, hp, spd, attack\$id FROM enemies WHERE id = ".$_SESSION['enemyId'].";");
        if($row = $sql->fetch()){
            $name = $row['name'];
            $sprite = $row['sprite'];
            $health = $row['hp'];
            $speed = $row['spd'];
            if($row['attack$id'] == null){
                $attackId = 0;
            }else{
                $attackId = $row['attack$id'];
            }
        }else{
            header("Location: viewTable.php");
            die;
        }
    }
    $attacks = attackDropdown($pdo);

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        if(isset($_POST["cancel"])){
            $pdo = null;
            header("Location: viewTable.php");
            die;
        }else if(isset($_POST["save"])){
            $name = sanitizeValue($_POST["name"]);
            $sprite = sanitizeValue($_POST["sprite"]);
            $health = sanitizeValue($_POST["health"]);
            $speed = sanitizeValue($_POST["speed"]);
            $attackId = sanitizeValue($_POST["attack"]);
            if(!empty($attackId)){
                if($attackId == "none"){
                    $attackId = 0;
                }else{
                    $sql = $pdo->query("SELECT id FROM attacks WHERE id = $attackId;");
                    if($sql->fetch() == false){
                        $attackId = 0;
                    }
                }
            }else{
                $attackId = 0;
            }
            $warning = "";
            if(empty($name) || empty($sprite) || (empty($health) && $health != 0) || (empty($speed) && $speed != 0)){
                $warning = "Please fill out all required fields.";
            }else{
                if(preg_match("/^\w{1,20}$/", $name) != 1 || preg_match("/^\w{1,20}$/", $sprite) != 1){
                    $warning .= "Name and Sprite must be between 1 and 20 alphanumeric characters. ";
                }
                if(!is_numeric($health)){
                    $warning .= "Health must be a number. ";
                }else if($health <= 0){
                    $warning .= "Health must be greater than zero. ";
                }
                if(!is_numeric($speed)){
                    $warning .= "Speed must be a number.";
                }else if($speed <= 0){
                    $warning .= "Speed must be greater than zero.";
                }
                if($warning == ""){
                    $sql = $pdo->query("SELECT id FROM user WHERE username = '".$_SESSION["username"]."';");
                    if($row = $sql->fetch()){
                        $uid = $row["id"];
                    }else{
                        header("Location: logOut.php");
                        die;
                    }
                    if($edit){
                        if($attackId == 0){
                            $sql = "UPDATE enemies SET name = '$name', datetime = "."CURRENT_TIMESTAMP".", user\$id = $uid, sprite = '$sprite', hp = $health, spd = $speed, attack\$id = NULL WHERE id = ".$_SESSION['enemyId'].";";
                        }else{
                            $sql = "UPDATE enemies SET name = '$name', datetime = "."CURRENT_TIMESTAMP".", user\$id = $uid, sprite = '$sprite', hp = $health, spd = $speed, attack\$id = $attackId WHERE id = ".$_SESSION['enemyId'].";";
                        }
                    }else{
                        if($attackId == 0){
                            $sql = "INSERT INTO enemies (name, datetime, user\$id, sprite, hp, spd) VALUES ('$name', "."CURRENT_TIMESTAMP".", $uid, '$sprite', $health, $speed);";
                        }else{
                            $sql = "INSERT INTO enemies (name, datetime, user\$id, sprite, hp, spd, attack\$id) VALUES ('$name', "."CURRENT_TIMESTAMP".", $uid, '$sprite', $health, $speed, $attackId);";
                        }
                    }
                    $pdo->exec($sql);
                    header("Location: viewTable.php");
                    die;
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>
            <?php
                if($edit){
                    echo "Edit Enemy";
                }else{
                    echo "Add Enemy";
                }
            ?>
        </title>
        <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    </head>
    <body class="w3-container">
        <div class="w3-card w3-green" id="heading">
            <header class="w3-container">
                <h1 class="w3-margin-left w3-show-inline-block"><?php if($edit){echo "Edit Enemy";}else{echo "Add Enemy";} ?></h1>
            </header>
        </div>
        <div class="w3-red w3-container" id="warning"><?php echo $warning; ?></div>
        <form class="w3-form w3-card" id="enemyForm" action="<?php echo $phpScript; ?>" method="POST">
            <div class="w3-container">
                <p><i style="color:red;">*</i> Required</p>
                <label for="name">Name <i style="color:red;">*</i></label>
                <input class="w3-input w3-border" type="text" name="name" id="name" autocomplete="off" autofocus value="<?php echo $name; ?>">
                <br>
                <label for="sprite">Sprite <i style="color:red;">*</i></label>
                <input class="w3-input w3-border" type="text" name="sprite" id="sprite" autocomplete="off" value="<?php echo $sprite; ?>">
                <br>
                <label for="health">Health <i style="color:red;">*</i></label>
                <input class="w3-input w3-border" type="text" name="health" id="health" value="<?php if($health > 0){echo $health;}?>">
                <br>
                <label for="speed">Speed <i style="color:red;">*</i></label>
                <input class="w3-input w3-border" type="text" name="speed" id="speed" value="<?php if($speed > 0){echo $speed;}?>">
                <br>
                <label for="attack">Attack</label>
                <select class="w3-select w3-border" name="attack" id="attack">
                    <?php echo $attacks; ?>
                </select>
            </div>
            <div class="w3-row" id="buttons">
                <br>
                <div class="w3-center">
                    <button type="submit" name="save" class="w3-btn w3-red w3-round"><?php if($edit){echo "Save";}else{echo "Create";} ?></button>
                    <button type="submit" name="reset" class="w3-btn w3-red w3-round">Reset</button>
                    <a class="w3-btn w3-red w3-round" href="viewTable.php">Cancel</a>
                </div>
                <br>
            </div>
        </form>
        <footer class="w3-center w3-bottom w3-white">Carter T. McCall - <?php echo $curYear; ?></footer>
        <script src="js/enemyForm.js"></script>
    </body>
</html>