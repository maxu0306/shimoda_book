<!DOCTYPE html>
<html>

<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta charset="UTF-8">
	<title>下田篤書店-書籍検索画面</title>
	<!-- CSSのリンク先 -->
	<link rel="stylesheet" href="index_book.css" type="text/css">
	<!-- <link rel="stylesheet" href="test.css" type="text/css"> -->
	<style>
		/*見出しの設定*/
		.h4 {
			color: brown;
			text-align: center;
			font-size: 150px;
			padding-top: 10px;
			padding-bottom: 0px;
			background-color: beige;
		}

		.text {
			width: 30%;
			height: 40px;
			font-size: 20px;
		}

		/* スマートフォン用のスタイル */
		@media screen and (max-width: 480px) {
			.h4 {
				font-size: 30px;
				/* padding-top: 10px; */
				/* padding-bottom: 0px; */
				/* background-color: beige; */
			}

			.shimoda01 {
				width: 30px;
			}

			.shimoda02 {
				width: 30px;
			}

			.text {
				width: 60%;
				height: 40px;
				font-size: 20px;
			}
		}

		/* iPad用のスタイル */
		@media screen and (max-width: 959px) {
			.h4 {
				font-size: 50px;
				/* padding-top: 10px; */
				padding-bottom: 0px;
				/* background-color: beige; */
			}

			.shimoda01 {
				width: 150px;
			}

			.shimoda02 {
				width: 150px;
			}

			.text {
				width: 60%;
				height: 40px;
				font-size: 20px;
			}
		}
	</style>
</head>

<body>
	<?php
	// 使用する変数を空文字で初期化
	$user_device = '';

	// HTTP リクエストヘッダーが持っているユーザーエージェントの文字列を取得
	$useragent = $_SERVER['HTTP_USER_AGENT'];

	if (
		strpos($useragent, 'Android') or strpos($useragent, 'Mobile')
		or strpos($useragent, 'iPhone') or strpos($useragent, 'iPad')
	) {
		echo '<div class="h4">';
		echo '下田篤の書店</div>';
	} else {
		echo '<div class="h4"><img src="shimoda-00213.jpg" width="170px" height="160px" class="shimoda01" background="#eedcb3">下田篤の書店
		<img src="shimoda-00927.jpg" width="150px" height="160px" class="shimoda02" background="#eedcb3">
	</div>';
	}
	?>
	<!-- bookinfo.phpに情報を送る -->
	<form action="bookinfo.php" method="get">
		<!-- 書籍名から検索  -->
		<h5><label for="bookname">書籍名</label><br>
			<input type="text" name="bookname" class="text" placeholder="書籍名を入力してください" value="<?php echo isset($_GET["bookname"]) ? trim($_GET["bookname"]) : ""; ?>"><br>
		</h5>

		<!-- ジャンルを選択して検索 -->
		<h5><label for="genre">ジャンル</label><br>
			<select name="genre" class="text">
				<option value="">ジャンルを選択してください</option>
				<option value="小説">小説</option>
				<option value="学習参考書">学習参考書</option>
				<option value="漫画">漫画</option>
				<option value="スポーツ">スポーツ</option>
				<option value="コンピューター">コンピューター</option>
				<option value="ビジネス">ビジネス</option>
				<option value="暮らし">暮らし</option>
				<option value="資格">資格</option>
				<option value="絵本">絵本</option>
				<option value="音楽">音楽</option>
				<option value="ファッション雑誌">ファッション雑誌</option>
				<option value="歴史">歴史</option>
				<option value="ゲーム">ゲーム</option>
				<option value="趣味">趣味</option>
				<option value="政治">政治</option>
			</select>
			<br><br>
		</h5>
		<!-- 検索ボタン -->
		<div class="toppage">
			<span>
				<input type="submit" class="god" value="検索">
			</span>
		</div>
	</form>

	<br><br>
	<!-- 予約履歴ボタン -->
	<form method="post" action="mandata_pass.php">
		<div class="toppage">
			<span>
				<input type="submit" class="god" name="history" value="予約履歴">
			</span>
		</div>
	</form>

</body>

</html>