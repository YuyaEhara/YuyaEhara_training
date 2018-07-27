<?php
try {
  $db = new PDO('mysql:dbname=MyDataBase;host=127.0.0.1;charset=utf8', 'root', 'P@ssw0rd');
} catch (PDOException $e) {
  echo 'DB接続エラー: ' . $e->getMessage();
}



?>
