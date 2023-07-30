<!DOCTYPE html>
<html>

<head>
	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=yes">
	<meta charset="UTF-8">
	<title>下田篤書店-個人情報照会画面</title>
	<link rel="stylesheet" href="index_book.css" type="text/css">
</head>

<body>
	<h4>個人情報照会</h4>
	<div>
		<h5>予約の申し込み時に入力した氏名、メールアドレス、電話番号を入力してください。</h5>
		<form method="POST" action="history.php">
			<table border="0" class="heighttableform">
				<tr>
					<th>氏名:</th>
					<td><input type="text" name="yourname" placeholder="氏名を入力してください" style="width:99%; height: 52px; font-size:20px"></td>
				</tr>
				<tr>
					<th>メールアドレス:</th>
					<td><input type="email" name="youremail" placeholder="メールアドレスを入力してください" style="width:99%; height: 52px; font-size:20px"></td>
				</tr>
				<tr>
					<th>電話番号:</th>
					<td><input type="tel" name="yourtel" placeholder="電話番号を入力してください" style="width:99%; height: 52px; font-size:20px"></td>
			</table>
	</div>

	<?php
	echo "<br><br><br><br>";
	echo "<div class='toppage'>";
	echo "<span><input type=button class='god' onclick=history.back() value=戻る></span>";
	echo '<span><input type="submit" class="god" value="次へ" /></div></span>';
	echo "</div>";
	?>
	</form>
</body>

</html>