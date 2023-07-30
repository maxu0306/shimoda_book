<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=yes">
    <meta charset="UTF-8">
    <title>下田篤書店-予約完了画面</title>
    <!-- CSSのリンク先 -->
    <link rel="stylesheet" href="index_book.css" type="text/css">
    <style>
        /*見出しの設定*/
        h4 {
            color: brown;
            text-align: center;
            font-size: 50px;
            background-color: beige;
        }

        .text {
            width: 30%;
            height: 40px;
            font-size: 20px;
        }

        /* スマートフォン用のスタイル */
        @media screen and (max-width: 480px) {
            h4 {
                color: brown;
                text-align: center;
                font-size: 20px;
                background-color: beige;
            }
        }

        /* iPad用のスタイル */
        @media screen and (max-width: 959px) {
            h4 {
                color: brown;
                text-align: center;
                font-size: 50px;
                background-color: beige;
            }
        }
    </style>
</head>

<body>
    <?php
    session_start(); // セッションを開始
    require_once '_database_conf.php';
    require_once '_h.php';

    try {
        // check.phpから渡された情報をそれぞれ変数に格納する
        /*$yourname = $_POST['yourname'];
        $youremail = $_POST['youremail'];
        $yourtel = $_POST['yourtel'];*/

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // フォームからのデータをセッション変数に格納
            $yourname = $_SESSION['yourname'];
            $youremail = $_SESSION['youremail'];
            $yourtel = $_SESSION['yourtel'];
        }

        $imgs = $_POST['img'];
        $booknames = $_POST['bookname'];
        $stocks = $_POST['stock'];
        $dates = $_POST['date'];
        $indates = $_POST['indate'];
        $lastdates = $_POST['lastdate'];
        $prices = $_POST['price'];
        $quantities = $_POST['quantity'];
        $stockdowns = $_POST['stockdown'];
        $orderID = $_POST['orderID'];
        $totalPrice = $_POST['totalPrice'];
        $take = "準備中";
        $erase = "表示";

        $db = new PDO($dsn, $dbUser, $dbPass);
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // データをorderlistテーブルにも挿入
        $sql02 = 'INSERT INTO orderlist (img, bookname, orderdate, receive, period, price, number, stock, name, email, tel, orderID, take, erase) 
                VALUES (:img, :bookname, :orderdate, :receive, :period, :price, :number, :stock, :name, :email, :tel, :orderID, :take, :erase)';
        $stmt2 = $db->prepare($sql02);

        // データをbooklistテーブルに挿入、historylistとは別の処理のため
        $sql03 = 'UPDATE booklist SET stock=:stockdown WHERE bookname=:bookname';
        $stmt3 = $db->prepare($sql03); // 別の変数 $stmt2 を使用する

        foreach ($imgs as $index => $img) {
            $number = $index + 1;
            $bookname = $booknames[$index];
            $date = $dates[$index];
            $indate = $indates[$index];
            $lastdate = $lastdates[$index];
            $price = $prices[$index];
            // 予約した冊数だけ在庫数を減らす。取り寄せの場合は在庫数が変化しない-------------------
            // 冊数
            $quantity = $quantities[$index];
            // 現状の在庫数
            $stock = $stocks[$index];
            // 予約完了した後の在庫数（負の値の場合は0とする）
            $stockdown = max($stockdowns[$index], 0);

            // orderlistに格納
            $stmt2->bindValue(':img', $img, PDO::PARAM_STR);
            $stmt2->bindValue(':bookname', $bookname, PDO::PARAM_STR);
            $stmt2->bindValue(':orderdate', $date, PDO::PARAM_STR);
            $stmt2->bindValue(':receive', $indate, PDO::PARAM_STR);
            $stmt2->bindValue(':period', $lastdate, PDO::PARAM_STR);
            $stmt2->bindValue(':price', $price, PDO::PARAM_INT);
            $stmt2->bindValue(':number', $quantity, PDO::PARAM_INT);
            if ($stock > 0) {
                // 在庫がある場合
                $INstock = "取り置き";
                $stmt2->bindValue(':stock', $INstock, PDO::PARAM_STR);

                $stmt3->bindValue(':bookname', $bookname, PDO::PARAM_STR);
                $stmt3->bindValue(':stockdown', $stockdown, PDO::PARAM_INT);

                $stmt3->execute();
            } else {
                // 在庫がない場合
                $NOstock = "取り寄せ";
                $stmt2->bindValue(':stock', $NOstock, PDO::PARAM_STR);
            }
            // orderlistテーブルに格納
            $stmt2->bindValue(':name', $yourname, PDO::PARAM_STR);
            $stmt2->bindValue(':email', $youremail, PDO::PARAM_STR);
            $stmt2->bindValue(':tel', $yourtel, PDO::PARAM_INT);
            $stmt2->bindValue(':orderID', $orderID, PDO::PARAM_STR);
            $stmt2->bindValue(':take', $take, PDO::PARAM_STR);
            $stmt2->bindValue(':erase', $erase, PDO::PARAM_STR);

            $stmt2->execute();
        }
        // 注文番号の表示
        echo "<h4>予約が完了しました。<br>";
        echo "注文番号は{$orderID}です。</h4>";
        // echo "合計金額は " . h($totalPrice) . "円です。</h4>";
        // index.phpに戻る
        echo '<form method="post" action="index.php">
        <div class="toppage">
        <span><input type="submit" class="god" value="TOPへ戻る"></span></div>';
        echo "</form>";
        $db = null;
    } catch (Exception $e) {
        echo 'エラーが発生しました。内容: ' . h($e->getMessage());
        exit();
    }
    session_destroy();
    ?>

</body>

</html>