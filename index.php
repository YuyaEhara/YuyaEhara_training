<?php
session_start();
require('dbconnect.php');

$id = filter_input(INPUT_POST,'id');
$title = filter_input(INPUT_POST,'title');
$user_name = filter_input(INPUT_POST,'user_name');
$error = [];
// $error配列の初期化
// してあげないとif文が成立しない
// これが無ければ未入力の時に値が存在しないことになってエラーの元

if (!empty($_POST)) {
  // エラーの確認
  if (filter_input(INPUT_POST,'title') == '') {
    $error['title'] = 'blank';
  }
  if (filter_input(INPUT_POST,'user_name') == '') {
    $error['user_name'] = 'blank';
  }
  // DB挿入
  if (empty($error)) {
  // これが無いと空のままでもDBに挿入されてしまう。
  $statement = $db->prepare('INSERT INTO rooms SET title=?, user_name=?, modified=NOW(), created=NOW()');
  $statement->execute(
    array(
    $_POST['title'],
    $_POST['user_name'])
  );

  header('Location: http://192.168.2.52/index.php');
  exit();
 }
}
$posts = $db->query('SELECT id, title, user_name, modified FROM rooms  ORDER BY modified DESC');


?>



<!DOCTYPE html>
<html lang="ja" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>yepBBs</title>
    <link rel="stylesheet" href="stylesheet.css">
  </head>
  <body>
    <div id="wrap">
    <header>
      <a href="index.php"><h1><span>yep</span>BBs</h1></a>
      <div align="right"><?php echo date('Y/m/d') ?></div>
    </header>
      <div class="contents">
        <div class="rooms-list">
          <table>
           <tr>
             <tr><th align="left">タイトル</th> <th align="right">ルーム作成者</th> <th align="right">最終更新日</th></tr>
           </tr>
           <?php foreach ($posts as $post): ?>
             <tr class="room-detail">
               <td align="left"><a href="room.php?room_id=<?php echo htmlspecialchars($post['id'], ENT_QUOTES); ?>"><?php echo htmlspecialchars($post['title'], ENT_QUOTES); ?></a></td>
               <td align="right"><?php echo htmlspecialchars($post['user_name'], ENT_QUOTES); ?></td>
               <td align="right"><?php echo htmlspecialchars($post['modified'], ENT_QUOTES) ?></td>
             </tr>
           <?php endforeach; ?>
          </table>



        </div>

        <div class="room-Registration">
          <form action="index.php" method="post" >
            <h3>ルーム登録</h3>
            <ul>
              <input type="hidden" name="id" />
              <?php echo htmlspecialchars(filter_input(INPUT_POST,'id'), ENT_QUOTES); ?>
              <li align="left">
                <label for="title">ルーム名</label>
                <input type="text" name="title" size="35" maxlength="20"
                value="<?php echo htmlspecialchars(filter_input(INPUT_POST,'title'), ENT_QUOTES); ?>"/>
              <?php if (isset($error['title']) && ($error['title'] === 'blank')): ?>
              <!-- $error['title']が存在するか　&& $error['title']のblankが等しいか -->
                <p class="error">* 必ず入力してください</p>
              <?php endif; ?>
              </li>
              <li align="left">
                <label for="user_name">名前</label>
                <input type="text" name="user_name" size="35" maxlength="20"
                value="<?php echo htmlspecialchars(filter_input(INPUT_POST,'user_name'), ENT_QUOTES); ?>" />
                <input type="submit" value="登録" class="square_btn" />
              <?php if (isset($error['user_name']) && ($error['user_name'] === 'blank')): ?>
              <!-- $error['user_name']が存在するか　&& $error['user_name']のblankが等しいか -->
                <p class="error">* 必ず入力してください</p>
              <?php endif; ?>
              </li>
            </ul>
          </form>
        </div>
      </div>
  </div>
  </body>
</html>
