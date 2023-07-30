<!DOCTYPE html>
<html>

<head>
	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=yes">
	<meta charset="UTF-8">
	<title>下田篤書店-登録情報確認画面&エラー画面</title>
	<!-- CSSのリンク先 -->
	<link rel="stylesheet" href="index_book.css" type="text/css">
	<link rel="stylesheet" href="lightbox.css" type="text/css">
	<script src="lightbox-plus-jquery.js" type="text/javascript"></script>
	<style>
		/* スクロールバーのスタイルを定義 */
		.scrollbar-container {
			height: 250px;
			overflow-y: scroll;
			display: flex;
			flex-direction: column;
		}
	</style>
</head>

<body>
	<?php
	session_start(); // セッションを開始
	require_once '_database_conf.php';
	require_once '_h.php';

	// end.phpに情報を送る
	echo '<form method="post" action="end.php">';

	// 使用する変数を空文字で初期化
	$user_device = '';

	// HTTP リクエストヘッダーが持っているユーザーエージェントの文字列を取得
	$useragent = $_SERVER['HTTP_USER_AGENT'];

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		// フォームからのデータをセッション変数に格納
		$_SESSION['yourname'] = $_POST['yourname'];
		$_SESSION['youremail'] = $_POST['youremail'];
		$_SESSION['yourtel'] = $_POST['yourtel'];
	}

	try {
		// 名前、メールアドレス、電話番号どれか一つでも入力されていなければ、エラーが表示される
		if (empty($_SESSION['yourname']) or empty($_SESSION['youremail']) or empty($_SESSION['yourtel'])) {
			echo '<h5><p style="color: red;">個人情報の入力が完了していません。</p><h5>';
		} else {
			$errors = array(); // エラーメッセージを格納する配列

			// 氏名の検証
			if (strlen($_SESSION['yourname']) > 256) {
				$errors[] = '<h5><p style="color: red;">氏名は256文字以下で入力してください。</p></h5>';
			}

			// メールアドレスの検証
			if (strlen($_SESSION['youremail']) > 256 or !filter_var($_SESSION['youremail'], FILTER_VALIDATE_EMAIL)) {
				$errors[] = '<h5><p style="color: red;">正しい形式のメールアドレスを入力してください。</p></h5>';
			}

			// 電話番号の検証
			if (strlen($_SESSION['yourtel']) < 10 or strlen($_SESSION['yourtel']) > 13 or !preg_match('/^[0-9]+$/', $_SESSION['yourtel'])) {
				$errors[] = '<h5><p style="color: red;">正しい形式の電話番号を入力してください。</p></h5>';
			}

			if (!empty($errors)) {
				// エラーメッセージを表示
				foreach ($errors as $error) {
					echo '<p style="color: red;">' . $error . '</p>';
				}
			} else {
				// mandata.phpから情報を渡す

				$yourname = $_SESSION['yourname'];
				$youremail = $_SESSION['youremail'];
				$yourtel = $_SESSION['yourtel'];

				// 名前、メールアドレス、電話番号が全て入力されていた場合に表示される。正規の処理
				echo "<h4>登録情報の確認</h4>";
				// echo '<div><h5>入力した情報に誤りがないかご確認ください。<br>間違いがなければ「はい」をクリックしてください。</h5>';
				echo "<div><h5>入力した情報に誤りがないかご確認ください。</h5></div>";

				// 入力データを表にして表す
				echo "<table border='0' class='heighttable'>";
				echo "<tr><th>氏名</th><td>$yourname</td></tr>";
				echo "<tr><th>メールアドレス</th><td>$youremail</td></tr>";
				echo "<tr><th>電話番号</th><td>$yourtel</td></tr>";
				echo "<tr></table><br>";

				// yoyaku.phpで表示された書籍情報をここで表示する
				// yoyaku.php >>> mandata.php >>> check.phpという流れで情報が流れている 
				// mandata.phpは中継地点
				if (
					isset($_POST['img']) and isset($_POST['bookname']) and isset($_POST['stock']) and isset($_POST['date'])
					and isset($_POST['indate']) and isset($_POST['lastdate']) and isset($_POST['price']) and isset($_POST['quantity'])
				) {
					$imgs = $_POST['img'];
					$booknames = $_POST['bookname'];
					$stocks = $_POST['stock'];
					$dates = $_POST['date'];
					$indates = $_POST['indate'];
					$lastdates = $_POST['lastdate'];
					$prices = $_POST['price'];
					$quantities = $_POST['quantity'];
					$totalPrice = $_POST['totalPrice'];


					// 乱数で注文番号を生成する関数
					function generateOrderID($length = 16)
					{
						// 乱数で使用される文字
						$characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
						// 文字セットの長さが格納
						$charactersLength = strlen($characters);
						// 乱数を代入する変数
						$orderID = '';

						// 英字と数字をランダムに組み合わせてIDを生成
						for ($i = 0; $i < $length; $i++) {
							$orderID .= $characters[rand(0, $charactersLength - 1)];
						}
						// 返り値
						return $orderID;
					}

					$db = new PDO($dsn, $dbUser, $dbPass);
					$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
					$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

					do {
						// 乱数を生成
						$orderID = generateOrderID();

						// 注文番号がデータベースに存在するか確認
						$sql = 'SELECT orderID FROM orderlist WHERE orderID=:orderID';
						$stmt = $db->prepare($sql);
						$stmt->bindValue(':orderID', $orderID);
						$stmt->execute();
						$rec = $stmt->fetchAll(PDO::FETCH_ASSOC);
					} while (!empty($rec));

					// ここで新たに生成された注文番号を使用して処理を行う


					// $totaklPrice = number_format($totalPrice);
					echo "<div style='text-align: right'><h2><p>合計金額: " . h($totalPrice) . "円</p></h2>"; // 合計金額を表示
					// スクロールバー
					echo "<div class='scrollbar-container'>";

					if (strpos($useragent, 'Android') or strpos($useragent, 'Mobile') or strpos($useragent, 'iPhone') or strpos($useragent, 'iPad')) {
						// テーブルの
						echo '<table border="1" class="tabledesign_01">';
						// テーブルの属性（カラム）
						echo "<tr>
						<th class='fixed01' style='width: 150px'> </th>
						<th class='fixed01'style='width: 250px'>書籍情報</th></tr>";

						// 番号を数えるときの初期値
						$count = 0;

						foreach ($imgs as $index => $img) {
							// 番号を１、２、３と表示させるための計算式 番号を連続で格納する。＋１ずつ
							$count++;
							$bookname = $booknames[$index];
							$stock = $stocks[$index];
							$date = $dates[$index];
							$indate = $indates[$index];
							$lastdate = $lastdates[$index];
							$price = $prices[$index];
							$quantity = $quantities[$index];
							// 予約完了後の在庫＝元々あった在庫数－予約する冊数
							$stockdown = $stocks[$index] - $quantities[$index];

							// テーブルの行（タプル）
							echo "<tr>

						<td><a href='$img' data-lightbox='sample' data-title='$bookname'>
						<img src=$img alt=Book Image></a></td>
						<td><div class='No'>No.{$count}
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

							echo "<br>価格：" . number_format($price) . "円
							<br>冊数：{$quantity}</div></td>
							</tr>";

							// 16文字の注文番号を生成
							$orderID = generateOrderID();

							$_SESSION['yourname'] = $yourname;
							$_SESSION['youremail'] = $youremail;
							$_SESSION['yourtel'] = $yourtel;

							// 書籍の情報、申込日、入荷日、期限日も同様である。
							echo "<input type=hidden name=img[] value=$img>";
							echo "<input type=hidden name=bookname[] value='" . htmlspecialchars($bookname, ENT_QUOTES) . "'>";
							echo "<input type=hidden name=stock[] value=$stock>";
							echo "<input type=hidden name=date[] value=$date>";
							echo "<input type=hidden name=indate[] value=$indate>";
							echo "<input type=hidden name=lastdate[] value=$lastdate>";
							echo "<input type=hidden name=price[] value=$price>";
							echo "<input type=hidden name=quantity[] value=$quantity>";
							echo "<input type=hidden name=stockdown[] value=$stockdown>";
							// 注文番号も格納
							echo "<input type=hidden name=orderID value=$orderID>";
							echo "<input type=hidden name=totalPrice value=" . h($totalPrice) . ">";
						}

						echo "</table></div><br>";
					} else {
						// テーブルの
						echo '<table border="1" class="tabledesign">';
						// テーブルの属性（カラム）
						echo "<tr>
                    <th  class='fixed01' style='width: 20px'>No.</th>
                    <th  class='fixed01' style='width: 100px'>画像</th>
                    <th  class='fixed01'>書籍名</th>
					<th  class='fixed01' style='width: 150px'>予約方法</th>
                    <th  class='fixed01' style='width: 250px'>日付</th>
                    <th  class='fixed01' style='width: 150px'>価格</th>
                    <th  class='fixed01' style='width: 150px'>冊数</th>";
						echo "</tr>";

						// 番号を数えるときの初期値
						$count = 0;

						foreach ($imgs as $index => $img) {
							// 番号を１、２、３と表示させるための計算式 番号を連続で格納する。＋１ずつ
							$count++;
							$bookname = $booknames[$index];
							$stock = $stocks[$index];
							$date = $dates[$index];
							$indate = $indates[$index];
							$lastdate = $lastdates[$index];
							$price = $prices[$index];
							$quantity = $quantities[$index];
							// 予約完了後の在庫＝元々あった在庫数－予約する冊数
							$stockdown = $stocks[$index] - $quantities[$index];

							// テーブルの行（タプル）
							echo "<tr>
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

							echo "<td>" . number_format($price) . "円</td>
						<td>$quantity</td>
					</tr>";

							// 16文字の注文番号を生成
							$orderID = generateOrderID();

							$_SESSION['yourname'] = $yourname;
							$_SESSION['youremail'] = $youremail;
							$_SESSION['yourtel'] = $yourtel;

							// 書籍の情報、申込日、入荷日、期限日も同様である。
							echo "<input type=hidden name=img[] value=$img>";
							echo "<input type=hidden name=bookname[] value='" . htmlspecialchars($bookname, ENT_QUOTES) . "'>";
							echo "<input type=hidden name=stock[] value=$stock>";
							echo "<input type=hidden name=date[] value=$date>";
							echo "<input type=hidden name=indate[] value=$indate>";
							echo "<input type=hidden name=lastdate[] value=$lastdate>";
							echo "<input type=hidden name=price[] value=$price>";
							echo "<input type=hidden name=quantity[] value=$quantity>";
							echo "<input type=hidden name=stockdown[] value=$stockdown>";
							// 注文番号も格納
							echo "<input type=hidden name=orderID value=$orderID>";
							echo "<input type=hidden name=totalPrice value=" . h($totalPrice) . ">";
						}

						echo "</table></div><br>";
					}
				}
			}
		}
	} catch (Exception $e) {
		echo 'エラーが発生しました。内容: ' . h($e->getMessage());
		exit();
	}

	//条件分岐
	if (
		empty($yourname) or empty($youremail) or empty($yourtel)
		or strlen($yourname) > 256 or strlen($youremail) > 256 or !filter_var($youremail, FILTER_VALIDATE_EMAIL)
		or strlen($yourtel) > 13 or !preg_match('/^[0-9]+$/', $yourtel)
	) {
		// 氏名、メールアドレス、電話番号のどれかが入力されていない場合、またはそれぞれにエラーが発生している場合

		echo '<div class="toppage">
		<span><input type="button" class="god" onclick="history.back()" value="戻る"></span>
		</div>';
	} else {
		// 氏名、メールアドレス、電話番号がすべて入力され、エラーがない場合
		// 「はい」、「いいえ」ボタンが表示する　※正規の処理
		echo "<div class='toppage'>";
		echo '<span><input type="button" class="god" onclick="history.back()" value="戻る"></span>';
		echo '<span><input type="submit" class="god" value="確定"></span>';
		echo "</div>";
	}

	echo '</form>';
	// session_destroy();
	?>
</body>

</html>