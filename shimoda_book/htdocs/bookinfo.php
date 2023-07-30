<!DOCTYPE html>
<html>

<head>
	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=yes">
	<meta charset="UTF-8">
	<title>下田篤書店-書籍情報画面</title>
	<!-- CSSのリンク先 -->
	<link rel="stylesheet" href="index_book.css" type="text/css">
	<link rel="stylesheet" href="lightbox.css" type="text/css">
	<script src="lightbox-plus-jquery.js" type="text/javascript"></script>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<style>
		/* スクロールバーのスタイルを定義 */
		.scrollbar-container {
			height: 435px;
			overflow-y: scroll;
			display: flex;
			flex-direction: column;
		}
	</style>
	<script>
		function toggleCheckAll() {
			var checkboxes = document.querySelectorAll('input[type="checkbox"]');
			var toggleButton = document.querySelector('input[type="button"]');

			var isChecked = checkboxes[0].checked;
			for (var i = 0; i < checkboxes.length; i++) {
				checkboxes[i].checked = !isChecked;
			}

			toggleButton.value = isChecked ? '一括チェック' : 'チェック解除';
		}

		document.addEventListener('DOMContentLoaded', function() {
			var checkboxes = document.querySelectorAll('input[type="checkbox"]');
			var toggleButton = document.querySelector('input[type="button"]');

			for (var i = 0; i < checkboxes.length; i++) {
				checkboxes[i].addEventListener('change', function() {
					var checkedCount = 0;
					for (var j = 0; j < checkboxes.length; j++) {
						if (checkboxes[j].checked) {
							checkedCount++;
						}
					}

					toggleButton.value = checkedCount > 0 ? 'チェック解除' : '一括チェック';
				});
			}
		});
	</script>

</head>

<body>
	<!-- <div class="title">下田篤の書籍リスト</div> -->
	<?php

	require_once '_database_conf.php';
	require_once '_h.php';

	// index.phpで検索された書籍を$booknameに格納
	$bookname = $_GET['bookname'] ?? '';

	// ジャンルを指定する場合は$genreという変数に格納
	$genre = $_GET['genre'] ?? '';

	// 使用する変数を空文字で初期化
	$user_device = '';

	// HTTP リクエストヘッダーが持っているユーザーエージェントの文字列を取得
	$useragent = $_SERVER['HTTP_USER_AGENT'];

	try {
		$db = new PDO($dsn, $dbUser, $dbPass);
		$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		if (!empty($bookname) or !empty($genre)) {
			// 書籍名を入力し、ジャンルの指定して検索する場合
			$sql = 'SELECT * FROM booklist WHERE 1=1';
			$sql .= !empty($bookname) ? " AND bookname LIKE :bookname" : "";
			$sql .= !empty($genre) ? " AND genre = :genre" : "";

			$stmt = $db->prepare($sql);
			if (!empty($bookname)) {
				$stmt->bindValue(':bookname', '%' . $bookname . '%');
			}
			if (!empty($genre)) {
				$stmt->bindValue(':genre', $genre);
			}
			$stmt->execute();
			$rec = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}

		$db = null;

		// 該当する書籍がなければ表示。未記入も同様ああ
		if (empty($rec)) {
			// 書籍名とジャンルをどちらも指定しない場合の処理
			if (empty($bookname) and empty($genre)) {
				echo "<h5><p style='color: red;'>書籍名とジャンルを指定してください。</p></h5>";

				echo "<div class='toppage'><span><button class='god' onclick=history.back()>TOPへ戻る</button></span></div>";

				// echo "<div><button class='size' onclick=history.back()>TOPへ戻る</button><br><br></div>";
			} else {
				// 該当する書籍がない場合の処理
				echo "<h5><p style='color: red;'>お探しの書籍はありませんでした。</p></h5>";
				// トップページに戻るボタン
				echo "<div class='toppage'><span><button class='god' onclick=history.back()>TOPへ戻る</button></span></div>";
				// echo "<div><button class='size' onclick=history.back()>TOPへ戻る</button><br><br></div>";
			}
		} else {
			echo '<form method=post action="yoyaku.php">';

			// タイトル
			echo "<h4>下田篤の書籍リスト</h4>";

			echo "<table border='0' class='minitable' cellspacing='0'>";
			echo "<tr><th>検索キーワード</th><th>検索ジャンル</th></tr>";
			echo "<td>$bookname</td><td>$genre</td>";
			echo "</table><br>";

			if (strpos($useragent, 'Android') or strpos($useragent, 'Mobile') or strpos($useragent, 'iPhone') or strpos($useragent, 'iPad')) {
				// スマートフォン/タブレットバージョンのテーブル
				echo "<th class='fixed01' style='width: 30px'><input type='button' 
				class='god2' onclick='toggleCheckAll()' value='一括チェック'></th>";
				// スクロールバー
				echo "<div class='scrollbar-container'>";
				echo "<table border='1' class='tabledesign_01'>";
				echo "<tr>
						<th class='fixed01' style='width: 150px'> </th>
						<th class='fixed01'style='width: 250px'>書籍情報</th></tr>";

				$count = 0;

				foreach ($rec as $row) {
					$count++;
					$img = ($row['img']);
					$bookname = ($row['bookname']);
					$genre = ($row['genre']);
					$company = ($row['company']);
					$author = ($row['author']);
					$sellday = ($row['sellday']);
					$price = ($row['price']);
					$stock = ($row['stock']);

					echo "<tr>
					<td><a class='img' href='$img' data-lightbox='sample' data-title='$bookname'>
					<img src=$img alt=Book Image></a></td>
					<td><div class='No'>No.{$count}
					<br><a style='color:red;'>{$bookname}</a>
					<br>ジャンル：{$genre}
					<br>出版社：{$company}
					<br>著者：{$author}
					<br>発売日：{$sellday}
					<br>価格：" . number_format($price) . "円";

					if ($stock > 0) {
						echo "<br><a style='color:blue;'>＜在庫あり＞</a></br>";
					} else {
						echo "<br><a style='color:red;'>＜在庫なし＞</a></br>";
					}
					echo "<input type='checkbox' class='checkbox' name='bookname[]' value='$bookname'></div></td></tr>";
				}

				echo "</table>";

				echo "</div><p>";
				echo "<p style='font-size:23px; text-align: center;'>{$count}件検索されました。</p>";

				// 予約の申し込みボタンを押す
				echo "<div class='toppage'>";
				echo "<span><input type=button class='god' onclick=history.back() value=戻る></span>";
				echo "<span><input class='god' type='submit' value='選択'></span>";
				echo "</div>";
				echo "</form>";
			} else {

				// PCバージョンのテーブル
				// スクロールバーの設定
				echo "<div class='scrollbar-container'>";
				echo "<table border='1' class='tabledesign'>";
				echo "<tr>
						<th class='fixed01'>No.</th>
						<th class='fixed01' style='width: 100px'>画像</th>
						<th class='fixed01'>書籍名</th>
						<th class='fixed01' style='width: 150px'>ジャンル</th>
						<th class='fixed01' style='width: 100px'>出版社</th>
						<th class='fixed01' style='width: 100px'>著者</th>
						<th class='fixed01' style='width: 100px'>発売日</th>
						<th class='fixed01' style='width: 100px'>価格</th>
						<th class='fixed01' style='width: 100px'>在庫状況</th>
						<th class='fixed01' style='width: 100px'><input type='button' class='god2' onclick='toggleCheckAll()' value='一括チェック'></th>
					</tr>";

				$count = 0;

				foreach ($rec as $row) {
					$count++;
					$img = ($row['img']);
					$bookname = ($row['bookname']);
					$genre = ($row['genre']);
					$company = ($row['company']);
					$author = ($row['author']);
					$sellday = ($row['sellday']);
					$price = ($row['price']);
					$stock = ($row['stock']);

					echo "<tr>
							<td>$count</td>
							<td><a href='$img' data-lightbox='sample' data-title='$bookname'>
							<img src=$img alt=Book Image></a></td>
							<td>$bookname</td>
							<td>$genre</td>
							<td>$company</td>
							<td>$author</td>
							<td>$sellday</td>
							<td>" . number_format($price) . "円</td>";

					if ($stock > 0) {
						echo "<td style='color: blue;'>在庫あり</td>";
					} else {
						echo "<td style='color: red;'>在庫なし</td>";
					}

					echo "<td><input type='checkbox' class='checkbox' name='bookname[]' value='$bookname'></td>
						</tr>";
				}

				echo "</table>";


				echo "</div><p>";
				echo "<p style='font-size:23px; text-align: center;'>{$count}件検索されました。</p>";

				// 予約の申し込みボタンを押す
				echo "<div class='toppage'>";
				echo "<span><input type=button class='god' onclick=history.back() value=戻る></span>";
				echo "<span><input class='god' type='submit' value='選択'></span>";
				echo "</div>";
				echo "</form>";
			}
		}
	} catch (Exception $e) {
		echo "エラーが発生しました。内容: " . h($e->getMessage());
		exit();
	}

	?>

</body>

</html>