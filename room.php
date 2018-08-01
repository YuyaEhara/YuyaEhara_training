<?php
require_once('dbconnect.php');
// GETとPOST送信を受け取る時
$room_id = '';
if (isset($_POST['room_id'])) {
  $room_id = $_POST['room_id'];
} else {
  $room_id = $_GET['room_id'];
}

$text = filter_input(INPUT_POST,'text');
$user_name = filter_input(INPUT_POST,'user_name');

// ナンバー
$sql_number = $db->prepare('SELECT COUNT(*)+1 FROM comments WHERE room_id = '. $room_id .'');
$sql_number->execute();
// selectの場合はfetch() selectの一つだけだからwhileはなし
$numbers = $sql_number->fetch(PDO::FETCH_NUM);
// fetchで指定した型(NUMとかASSOCとか)を[]に書いたげる
$number = $numbers[0];
// ここまでテンプレ

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
header('Location: http://192.168.2.52/room.php?room_id='.$room_id.'');
exit();
  }
}

// コメント表示
// メインコメントだけ表示
$sql = 'SELECT number, text, user_name, modified FROM comments WHERE room_id = '. $room_id .' AND thread_number is null ORDER BY created ASC';
$stmt = $db->prepare($sql);
$stmt->execute();
//配列の初期化
$posts = [];
// これが無ければ未入力の時に値が存在しないことになってエラーの元
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
     $posts[] = $row;
}

//返信
// 配列の初期化 ru-pu
$result = [];
// まずthreadnumberと番号が同じものをSELECTしてprepareしてexecuteして、ループするごとに返信が分けられるようにしてる
foreach ($posts as $value) {
    $sql = 'SELECT thread_number,number,text,user_name,modified FROM comments
        WHERE room_id = '. $room_id .' AND thread_number is not null AND thread_number = '.$value['number'].' ORDER BY created ASC';
    $stmt = $db->prepare($sql);
    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $result[$row['thread_number']][] = $row;
    }
}
// コメント件数
$sql = 'SELECT COUNT(*) FROM comments WHERE room_id = '.$room_id.'';
$stmt = $db->prepare($sql);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_NUM);
$comment_count = $row[0];
?>

<!DOCTYPE html>
<html lang="ja" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>yepBBs</title>
    <link rel="stylesheet" href="roomStyle.css">
  </head>
  <body>
    <?php if ((int)$comment_count >= 100): ?>
      <p class="error">コメントが100件になりました。新しいRoomを作成してください。</p>
    <?php endif; ?>
  <div id="room">
  <header>
    <div style="display: inline-block;"><a href="index.php"><h1><span>yep</span>BBs</h1></a></div>
    <div class="time" align="right" style="display: inline-block;" ><?php echo date('Y/m/d') ?></div>
  </header>
  <div class="comment">
    <?php foreach ((array)$posts as $post): ?>
      <div class = "main-comment box-comment">
        <h2>
        <span class="bbs-number"><?php echo htmlspecialchars($post['number'], ENT_QUOTES); ?></span>
        <span class="bbs-name"><?php echo htmlspecialchars($post['user_name'], ENT_QUOTES); ?></span>
        <span class="bbs-date"><?php echo htmlspecialchars(date("Y/m/d H:i",strtotime($post['modified'])), ENT_QUOTES); ?></span>
        </h2>
        <p><?php echo htmlspecialchars($post['text'], ENT_QUOTES); ?></p>
      </div>
      <?php if (isset($result[$post['number']])): ?>
        <?php foreach ((array)$result[$post['number']] as $key => $value): ?>
          <div class = "rpy-comment box-comment">
            <h2>
            <span class="bbs-number"><?php echo htmlspecialchars($value['number'], ENT_QUOTES); ?></span>
            <span class="bbs-name"><?php echo htmlspecialchars($value['user_name'], ENT_QUOTES); ?></span>
            <span class="bbs-date"><?php echo htmlspecialchars(date("Y/m/d H:i",strtotime($value['modified'])), ENT_QUOTES); ?></span>
            </h2>
            <p><?php echo htmlspecialchars($value['text'], ENT_QUOTES); ?></p>
          </div>
          <?php echo "<br>"; ?>
        <?php endforeach; ?>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
  <div class="comment-Registration">
    <form action="room.php" method="post" />
    <h3>コメント登録</h3>
    <?php if ((int)$comment_count >= 100): ?>
      <p class="error">コメントが100件になりました。新しいRoomを作成してください。</p>
    <?php else: ?>
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
          <input type="text" name="user_name" size="40" maxlength="20" placeholder="名前です"
          value="<?php echo htmlspecialchars(filter_input(INPUT_POST,'user_name'), ENT_QUOTES); ?>" />
          <input type="submit" value="登録" class="square_btn"/>
          <?php if (isset($error['user_name']) && ($error['user_name'] === 'blank')): ?>
            <!-- $error['user_name']が存在するか　&& $error['user_name']のblankが等しいか -->
            <p class="error">* 必ず入力してください</p>
          <?php endif; ?>
        </li>
      <?php endif; ?>
    </ul>
  </form>
  </div>
</div>
</body>
</html>
