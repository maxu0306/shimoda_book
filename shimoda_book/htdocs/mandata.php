<!DOCTYPE html>
<html>

<head>
	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=yes">
	<meta charset="UTF-8">
	<title>下田篤書店-個人情報入力画面</title>
	<!-- CSSのリンク先 -->
	<link rel="stylesheet" href="index_book.css" type="text/css">

</head>

<body>
	<?php
	require_once '_database_conf.php';
	require_once '_h.php';

	// タイトル
	echo "<h4>個人情報の入力</h4>";

	echo '<div><h5>氏名，メールアドレス，電話番号を入力してください。</h5></div>';

	// check.phpに情報を送る
	echo '<form method="POST" action="check.php">';
	try {
		// 氏名、メールアドレス、電話番号の入力フォーム
		echo '<table border="0" class="heighttableform">';
		echo '<tr><th>氏名:</th>
			<td><input type="text" name="yourname" placeholder="氏名を入力してください" style="width:99%; height: 52px; font-size:20px"></td></tr>
			<tr><th>メールアドレス:</th>
			<td><input type="email" name="youremail" placeholder="メールアドレスを入力してください" style="width:99%; height: 52px; font-size:20px"></td></tr>
			<tr><th>電話番号:</th>
			<td><input type="tel" name="yourtel" placeholder="電話番号を入力してください" style="width:99%; height: 52px; font-size:20px"></td><tr>
			</table>';
		echo "<br><br><br><br>";

		if (
			// yoyaku.phpのそれぞれの変数がある時、処理される　
			// ※ない時は、処理を実行しない
			isset($_POST['img']) and isset($_POST['bookname']) and isset($_POST['stock']) and isset($_POST['date'])
			and isset($_POST['indate']) and isset($_POST['lastdate']) and isset($_POST['price']) and isset($_POST['quantity'])
		) {
			// 書籍情報が複数冊ある可能性がある（書籍１冊＝９つの情報）×３冊＝２７つの情報
			// $booknamesの「Ｓ」は複数形のＳ
			$imgs = $_POST['img'];
			$booknames = $_POST['bookname'];
			$stocks = $_POST['stock'];
			$dates = $_POST['date'];
			$indates = $_POST['indate'];
			$lastdates = $_POST['lastdate'];
			$prices = $_POST['price'];
			$quantities = $_POST['quantity'];
			$totalPrice = $_POST['totalPrice'];

			foreach ($imgs as $index => $img) {
				$bookname = $booknames[$index];
				$stock = $stocks[$index];
				$date = $dates[$index];
				$indate = $indates[$index];
				$lastdate = $lastdates[$index];
				$price = $prices[$index];
				$quantity = $quantities[$index];

				// データを送るためのコード、ブラウザには表示されない
				echo "<input type=hidden name=img[] value=$img>";
				echo "<input type=hidden name=bookname[] value='" . h($bookname) . "'>";
				echo "<input type=hidden name=date[] value=$date>";
				echo "<input type=hidden name=indate[] value=$indate>";
				echo "<input type=hidden name=lastdate[] value=$lastdate>";
				echo "<input type=hidden name=price[] value=$price>";
				echo "<input type=hidden name=quantity[] value=$quantity>";
				echo "<input type=hidden name=stock[] value=$stock>";
				echo "<input type=hidden name=totalPrice value=" . h($totalPrice) . ">";
			}
		}
	} catch (Exception $e) {
		echo 'エラーが発生しました。内容: ' . h($e->getMessage());
		exit();
	}
	echo "<div class='toppage'>";
	echo '<span><input type="button" class="god" onclick="history.back()" value="戻る"></span>';
	echo '<span><input type="submit" class="god" value="次へ" /></span>';
	echo "</div";
	echo '</form>';
	?>
</body>

</html>