
<?php
//echo 'GET:';
//
//echo '<pre>';
//var_dump($_GET);
//echo '</pre>';
//
//echo 'POST:';
//echo '<pre>';
//var_dump($_POST);
//echo '</pre>';
//
//$get_id = $_GET['id'];
//$post_id = $_POST['id'];
//echo 'get id:' . $get_id . '<br>';
//echo 'post id:' . $post_id . '<br>';
//
//echo '<hr>';

$id = '';
if (!empty($_POST)) {
	$id = $_POST['id'];
}
if (!empty($_GET)) {
	$id = $_GET['id'];
}
echo 'id:' . $id . '<br>';

echo '<hr>';
echo $id;
echo '<hr>';

?>

<form action="aaaa.php" method="post" />
	<input type="hidden" name="id" value="<?php echo $id; ?>">
	<input type="text" name="comment">
	<input type="submit" value="登録" />
</form>
