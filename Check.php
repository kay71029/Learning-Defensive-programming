<?php
session_start();
require("MyDB.php");
header('Content-Type: text/html; charset = utf-8');

$id = substr(strip_tags(addslashes(trim($_POST['Username']))),0,20);
$pw = addslashes($_POST['Password']);
$plen = strlen($pw);
$mdpw = check($pw, $plen);
sqlCheck($mdpw, $id);
     
function check ($pw, $plen)
{
    if (!preg_match("/^(([a-z]+[0-9]+)|([0-9]+[a-z]+))[a-z0-9]*$/i",$pw) || $plen < 6 || $plen > 15) {
        echo  "密碼必須為6-15位的數字和字母的组合";
        echo '<meta http-equiv = REFRESH CONTENT=1;url=index.php>';
        exit();
     }
    $pw = md5($pw);
    return $pw;
}

function sqlCheck($mdpw, $id)
{
    $obj = new myDB();
    $sql = "SELECT * FROM `admin` where `ID` = :ID and `PW` = :PW ";
    $result = $obj->db->prepare($sql);
    $result->bindParam(':ID', $id, PDO::PARAM_STR);
    $result->bindParam(':PW', $mdpw, PDO::PARAM_STR);
    $result->execute();
    $count = $result->rowCount();
    
    try{
        if ($count != 1) {
            throw new Exception("登入失敗");
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
    $_SESSION['ac_id'] = $id;
    echo '登入成功!';
    header("Refresh:0.5; url = Main.php");
}