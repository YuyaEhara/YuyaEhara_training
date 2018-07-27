<?php
require('dbconnect.php');

// GETとPOST送信を受け取る時
$room_id = '';
if (isset($_POST['room_id'])) {
  $room_id = $_POST['room_id'];
} else {
  $room_id = $_GET['room_id'];
}

$thread_number = filter_input(INPUT_POST,'thread_number');
$text = filter_input(INPUT_POST,'text');
$user_name = filter_input(INPUT_POST,'user_name');

// ナンバー
$sql_number = $db->prepare('SELECT COUNT(*)+1 FROM comments WHERE room_id = '. $room_id .'');
$sql_number->execute();
// selectの場合はfetch() selectの一つだけだからwhileはなし
$numbers = $sql_number->fetch(PDO::FETCH_NUM);
$number = $numbers[0];

$error = [];
// $error配列の初期化
// してあげないとif文が成立しない
// これが無ければ未入力の時に値が存在しないことになってエラーの元
if (!empty($_POST)) {
  // エラーの確認
  if (filter_input(INPUT_POST,'text') == '') {
    $error['text'] = 'blank';
  }
  if (filter_input(INPUT_POST,'user_name') == '') {
    $error['user_name'] = 'blank';
  }
//DB挿入
if (empty($error)) {
// これが無いと空のままでもDBに挿入されてしまう。
  $message = $db->prepare("INSERT INTO comments
         (room_id, thread_number, number, text, user_name, modified, created)
  VALUES (?, ?, ?, ?, ?, now(), now())");

  if (empty($_POST['thread_number'])) {
    $threadNumber = null;
  } else {
    $threadNumber = $_POST['thread_number'];
  }
  $res = $message->execute(
    array(
    $room_id,
    $threadNumber,
    $number,
    $text,
    $user_name)
  );
;header('Location: http://192.168.2.52/room.php?room_id='.$room_id.'');
exit();
  }
}

//返信
$threadNumber = '';
$sql_rpy = $db->prepare('SELECT thread_number,number FROM comments WHERE room_id = '. $room_id .'');
$sql_rpy->execute();
$comment_rpy = $sql_rpy->fetch();



// コメント表示
$sql_comment = 'SELECT number, text, user_name FROM comments WHERE room_id = '. $room_id .' ORDER BY created ASC';
$stmt = $db->prepare($sql_comment);
$stmt->execute();
//
$posts = [];
// 配列の初期化
// これが無ければ未入力の時に値が存在しないことになってエラーの元
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
     $posts[] = $row;
   }

// コメント件数
$sql_count = 'SELECT COUNT(number) FROM comments WHERE room_id = '. $room_id .'';
$statement = $db->prepare($sql_count);
$statement->execute();
?>


<!DOCTYPE html>
<html lang="ja" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>yepBBs</title>
    <link rel="stylesheet" href="roomStyle.css">
  </head>
  <body>
  <div id="room">
    <header>
      <a href="index.php"><h1><span>yep</span>BBs</h1></a>
      <div align="right"><?php echo date('Y/m/d') ?></div>
    </header>
    <div>
     <?php foreach ((array)$posts as $post): ?>
       <table>
         <tr class="tr">
           <th><?php echo htmlspecialchars($post['number'], ENT_QUOTES); ?></th>
           <th><?php echo htmlspecialchars($post['user_name'], ENT_QUOTES); ?></th>
           <?php echo "<br>"; ?>
         </tr>
       </table>
       <p class="p"><?php echo htmlspecialchars($post['text'], ENT_QUOTES); ?></p>
       <?php echo "<br>"; ?>
       <?php if ($threadNumber === $number): ?>
         <?php foreach ((array)$posts as $post): ?>
            <table>
              <tr class="tr">
                <th><?php echo htmlspecialchars($post['number'], ENT_QUOTES); ?></th>
                <th><?php echo htmlspecialchars($post['user_name'], ENT_QUOTES); ?></th>
                <?php echo "<br>"; ?>
              </tr>
            </table>
            <p class="p"><?php echo htmlspecialchars($post['text'], ENT_QUOTES); ?></p>
            <?php echo "<br>"; ?>
      　   <?php endforeach; ?>
       <?php endif; ?>
     <?php endforeach; ?>
    </div>

      <div class="comment-Registration">
       <form action="room.php" method="post" />
       <h3>コメント登録</h3>
        <ul>
          <li>
            <input type="hidden" name="room_id" value="<?php echo $room_id; ?>" />
            <label for="number">返信</label>
            <input type="hidden" name="number" />
            <?php echo htmlspecialchars(filter_input(INPUT_POST,'number'), ENT_QUOTES); ?>
            <input type="number" name="thread_number" placeholder="番号" size="30" min="1" max="99" />
            <?php echo htmlspecialchars(filter_input(INPUT_POST,'thread_number'), ENT_QUOTES); ?>
          </li>
          <li>
            <label for="text">コメント</label>
            <textarea type="text" name="text" placeholder="入力してください" cols="100" rows="10"
            value="<?php echo htmlspecialchars(filter_input(INPUT_POST,'text'), ENT_QUOTES); ?>" ></textarea>
            <?php if (isset($error['text']) && ($error['text'] === 'blank')): ?>
            <!-- $error['text']が存在するか　&& $error['text']のblankが等しいか -->
              <p class="error">* 必ず入力してください</p>
            <?php endif; ?>
          </li>
          <li>
            <label for="user_name">名前</label>
            <input type="text" name="user_name" size="40" placeholder="名前です"
            value="<?php echo htmlspecialchars(filter_input(INPUT_POST,'user_name'), ENT_QUOTES); ?>" />
            <input type="submit" value="登録" class="square_btn"/>
            <?php if (isset($error['user_name']) && ($error['user_name'] === 'blank')): ?>
            <!-- $error['user_name']が存在するか　&& $error['user_name']のblankが等しいか -->
              <p class="error">* 必ず入力してください</p>
            <?php endif; ?>
          </li>
        </ul>
      </form>
      </div>
  </div>
</body>
</html>
