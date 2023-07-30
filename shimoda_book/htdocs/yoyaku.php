<!DOCTYPE html>
<html>

<head>
	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=yes">
	<meta charset="UTF-8">
	<title>下田篤書店-書籍取り置き・取り寄せ画面</title>
	<!-- CSSのリンク先 -->
	<link rel="stylesheet" href="index_book.css" type="text/css">
	<link rel="stylesheet" href="lightbox.css" type="text/css">
	<script src="lightbox-plus-jquery.js" type="text/javascript"></script>
	<style>
		/* スクロールバーのスタイルを定義 */
		.scrollbar-container {
			height: 465px;
			overflow-y: scroll;
			display: flex;
			flex-direction: column;
		}
	</style>
	<script>
		function updateTotalPrice() {
			var totalPriceElement = document.getElementById("total-price");
			var hiddenTotalPrice = document.getElementById("hidden-total-price");
			var rows = document.querySelectorAll(".book-row");
			var totalPrice = 0;

			rows.forEach(function(row) {
				var quantityInput = row.querySelector("input[name='quantity[]']");
				var priceElement = row.querySelector(".price");

				var quantity = parseInt(quantityInput.value);
				var price = parseInt(priceElement.textContent.replace(/\D/g, ''));
				totalPrice += price * quantity;
			});

			var formattedTotalPrice = formatNumber(totalPrice);
			totalPriceElement.textContent = "合計金額: " + formattedTotalPrice + "円";
			hiddenTotalPrice.value = formattedTotalPrice;
		}

		function formatNumber(number) {
			return number.toLocaleString(); // カンマを付けるためにtoLocaleString()を使用
		}

		// 初期呼び出しで合計金額のフォーマットを適用
		updateTotalPrice();

		// 合計金額の表示を更新するタイミングでカンマを付ける
		document.addEventListener("DOMContentLoaded", function() {
			updateTotalPrice();
		});
	</script>


</head>

<body>

	<?php
	require_once "_database_conf.php";
	require_once "_h.php";

	// mandata.phpに情報を送る
	echo "<form method='post' action='mandata.php'>";

	// 使用する変数を空文字で初期化
	$user_device = '';

	// HTTP リクエストヘッダーが持っているユーザーエージェントの文字列を取得
	$useragent = $_SERVER['HTTP_USER_AGENT'];

	try {
		// POSTであれば、以下の処理が実行される
		if ($_SERVER["REQUEST_METHOD"] === 'POST') {
			// booknameという名前の変数が存在し、かつその値が配列であるかどうかを検証しています。
			if (isset($_POST["bookname"]) and is_array($_POST["bookname"])) {
				// １つまたは複数ある書籍の情報を、$selectedBooksに代入する
				$selectedBooks = $_POST["bookname"];
				$db = new PDO($dsn, $dbUser, $dbPass);
				$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
				$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

				$totalPrice = 0; // 合計金額を格納する変数
				// 合計金額を表示するためのループ
				foreach ($selectedBooks as $index => $bookname) {
					$sql = "SELECT * FROM booklist WHERE bookname = :bookname";
					$stmt = $db->prepare($sql);
					$stmt->bindValue(":bookname", $bookname, PDO::PARAM_STR);
					$stmt->execute();
					// $moneyにデータベースの情報を格納
					$money = $stmt->fetch(PDO::FETCH_ASSOC);

					if ($money) {
						// データベースの中にある「price(金額)の情報」を$priceに定義している
						$price = $money["price"];
						// $quantityは冊数の意味、ユーザーが指定する冊数（yoyaku.phpで）
						$quantity = isset($_POST["quantity"][$index]) ? intval($_POST["quantity"][$index]) : 1;
						$totalPrice += $price * $quantity; // 合計金額を更新
					}
				}

				// タイトル 
				echo "<div><h4>予約の申し込み</h4></div>";
				// 合計金額を表示
				echo "<div class='h6'>※注意※<br>
				期限日を過ぎると、取り置き・取り寄せが自動的に解除されます。</div>";
				// $totalPrice = number_format($totalPrice);
				echo "<div style='text-align: right'><h2 class='total-price' id='total-price'>合計金額: " . h(number_format($totalPrice)) . "円</h2></divs>";
				// 合計金額を次のページに渡すための隠しコード
				echo "<input type='hidden' id='hidden-total-price' name='totalPrice' value=" . h(number_format($totalPrice)) . ">";
				// $_SESSION["totalPrice"] = h(number_format($totalPrice));


				echo "<div class='scrollbar-container'>";

				if (strpos($useragent, 'Android') or strpos($useragent, 'Mobile') or strpos($useragent, 'iPhone') or strpos($useragent, 'iPad')) {
					// スマートフォン/タブレットバージョンのテーブル
					echo "<table border='1' class='tabledesign_01'>";
					echo "<tr>
					<th class='fixed01' style='width: 150px'> </th>
					<th class='fixed01'style='width: 250px'>書籍情報</th></tr>";

					// 番号を数えるときの初期値
					$count = 0;

					foreach ($selectedBooks as $bookname) {
						$sql = "SELECT * FROM booklist WHERE bookname = :bookname";
						$stmt = $db->prepare($sql);
						$stmt->bindValue(":bookname", $bookname, PDO::PARAM_STR);
						$stmt->execute();
						$rec = $stmt->fetchAll(PDO::FETCH_ASSOC);

						foreach ($rec as $row) {
							// 番号を１、２、３と表示させるための計算式 番号を連続で格納する。＋１ずつ
							$count++;
							$img = $row["img"];
							$bookname = $row["bookname"];
							$stock = $row["stock"];
							$price = $row["price"];
							$date = date("Y/m/d");

							// 入荷日
							if ($stock > 0) {
								// 「在庫あり」の場合、入荷日はないため、記入なし
								$indate = "";
							} else {
								$indate = date("Y/m/d", strtotime("+7 days"));
							}
							// 期限日
							if ($stock > 0) {
								$lastdate = date("Y/m/d", strtotime("+7 days"));
							} else {
								$lastdate = date("Y/m/d", strtotime($indate . " +7 days"));
							}

							// 在庫数が０冊または１０冊以上の場合
							if ($stock == 0 or $stock >= 10) {
								$max = 10;
							}
							// 在庫数が１０冊より少ない場合
							else if ($stock < 10) {
								$max = $stock;
							}

							// テーブルの行（タプル）
							echo "<tr class='book-row'>
							<td><a href='$img' data-lightbox='sample' data-title='$bookname'>
							<img src=$img alt=Book Image></a></td>
							
							<td><div class='No'>No：{$count}
							<br><a style='color:red;'>{$bookname}</a>";
							if ($stock > 0) {
								// 在庫がある場合
								echo "<br><a style='color:blue;'>予約方法：取り置き</a>";
							} else {
								// 在庫がない場合
								echo "<br><a style='color:red;'>予約方法：取り寄せ</a>";
							}
							echo "<br><a style='font-size:16px;'>申込日：{$date}<br>";
							if ($stock == 0) {
								echo "入荷日：{$indate}";
							}
							echo "<br>期限日：{$lastdate}</a>";
							echo "<br><a class='price'>価格：" . h(number_format($price)) . "円</a>";
							echo "<br><input type='number' class='quantity' max='$max' name='quantity[]' value='1' min='1'  style='width: 45px' onchange='updateTotalPrice()'>";
							if ($stock >= 0) {
								echo "{$max}冊まで";
							}
							echo "</div></td></tr>";
						}


						// 隠しコードを次のページに送る
						echo "<input type='hidden' name='img[]' value='$img'>";
						echo "<input type='hidden' name='bookname[]' value='" . h($bookname) . "'>";
						echo "<input type='hidden' name='date[]' value='$date'>";
						echo "<input type='hidden' name='indate[]' value='$indate'>";
						echo "<input type='hidden' name='lastdate[]' value='$lastdate'>";
						echo "<input type='hidden' name='price[]' value='$price'>";
						echo "<input type='hidden' name='quantity[]' value='" . h($quantity) . "'>";
						echo "<input type='hidden' name='stock[]' value='$stock'>";
					}
					echo "</table>";
					echo "</div><p>";
				} else {
					echo "<table border='1' class='tabledesign'>";
					echo "<tr>
                    <th  class='fixed01' style='width: 20px'>No.</th>
                    <th  class='fixed01' style='width: 100px'>画像</th>
                    <th  class='fixed01'>書籍名</th>
					<th  class='fixed01' style='width: 150px'>予約方法</th>
                    <th  class='fixed01' style='width: 250px'>日付</th>
                    <th  class='fixed01' style='width: 150px'>価格</th>
                    <th  class='fixed01' style='width: 150px'>冊数</th>
                </tr>";

					// 番号を数えるときの初期値
					$count = 0;

					foreach ($selectedBooks as $bookname) {
						$sql = "SELECT * FROM booklist WHERE bookname = :bookname";
						$stmt = $db->prepare($sql);
						$stmt->bindValue(":bookname", $bookname, PDO::PARAM_STR);
						$stmt->execute();
						$rec = $stmt->fetchAll(PDO::FETCH_ASSOC);

						foreach ($rec as $row) {
							// 番号を１、２、３と表示させるための計算式 番号を連続で格納する。＋１ずつ
							$count++;
							$img = $row["img"];
							$bookname = $row["bookname"];
							$stock = $row["stock"];
							$price = $row["price"];
							$date = date("Y/m/d");

							// 入荷日
							if ($stock > 0) {
								// 「在庫あり」の場合、入荷日はないため、記入なし
								$indate = "";
							} else {
								$indate = date("Y/m/d", strtotime("+7 days"));
							}
							// 期限日
							if ($stock > 0) {
								$lastdate = date("Y/m/d", strtotime("+7 days"));
							} else {
								$lastdate = date("Y/m/d", strtotime($indate . " +7 days"));
							}

							// 在庫数が０冊または１０冊以上の場合
							if ($stock == 0 or $stock >= 10) {
								$max = 10;
							}
							// 在庫数が１０冊より少ない場合
							else if ($stock < 10) {
								$max = $stock;
							}

							// テーブルの行（タプル）
							echo "<tr class='book-row'>
							<td>$count</td>
							<td><a href='$img' data-lightbox='sample' data-title='$bookname'>
							<img src=$img alt=Book Image></a></td>
							<td>$bookname</td>";
							if ($stock > 0) {
								// 在庫がある場合
								echo "<td style='color:blue;'>取り置き</td>";
							} else {
								// 在庫がない場合
								echo "<td style='color:red;'>取り寄せ</td>";
							}
							echo "<td style='font-size:16px;'>申込日:{$date}<br>";
							if ($stock == 0) {
								echo "入荷日:{$indate}";
							}
							echo "<br>期限日:{$lastdate}</td>";
							echo "<td class='price'>" . h(number_format($price)) . "円</td>";
							echo "<td><input type='number' class='quantity' max='$max' name='quantity[]' value='1' min='1'  style='width: 45px' onchange='updateTotalPrice()'><br>";
							if ($stock >= 0) {
								echo "{$max}冊まで";
							}
							echo "</td></tr>";
						}


						// 隠しコードを次のページに送る
						echo "<input type='hidden' name='img[]' value='$img'>";
						echo "<input type='hidden' name='bookname[]' value='" . h($bookname) . "'>";
						echo "<input type='hidden' name='date[]' value='$date'>";
						echo "<input type='hidden' name='indate[]' value='$indate'>";
						echo "<input type='hidden' name='lastdate[]' value='$lastdate'>";
						echo "<input type='hidden' name='price[]' value='$price'>";
						echo "<input type='hidden' name='quantity[]' value='" . h($quantity) . "'>";
						echo "<input type='hidden' name='stock[]' value='$stock'>";
					}
					echo "</table>";
					echo "</div><p>";
				}
			} else {
				// booknameという変数が存在しない場合
				echo "<h5><p style='color: red;''>書籍を指定してください。</p></h5>";
			}
		}
		// データベース接続を解除
		$db = null;
	} catch (PDOException $e) {
		echo "<h2>エラーが発生しました。</h2>";
		echo "<p class='error'>" . $e->getMessage() . "</p>";
		exit;
	}

	// チェックボックスで選択された場合に次へボタンが表示される
	if (isset($_POST["bookname"]) and is_array($_POST["bookname"])) {
		echo "<div class='toppage'>";
		echo "<span><input type='button' class='god' onclick='history.back()' value='戻る'></span>";
		echo "<span><input type='submit' class='god' value='申し込み'></span>";
		echo "</div>";
	} else {
		// bookinfo.phpに戻る
		echo "<div class='toppage'>
		<span><input type='button' class='god' onclick='history.back()' value='戻る'></span>
		</div>";
	}
	echo "</form>";
	?>

</body>

</html>