<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset='utf-8' />
    <title>在庫登録完了画面</title>
    <link rel="stylesheet" href="staff_book.css" type="text/css">
</head>

<body>
    <?php
    require_once '_database_conf.php';
    require_once '_h.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['regist'])) {
        // 増減数を取得
        $quantities = $_POST['quantity'];
        $booknames = $_POST['bookname'];

        // 入力された冊数をチェック
        $validQuantities = array_filter($quantities, function ($quantity) {
            return intval($quantity) != 0;
        });

        if (empty($validQuantities)) {
            echo "<h5><p style='color: red;'>更新する在庫数を指定してください。</p></h5>";
            echo "<div class='toppage'>
            <span><input type='button' class='god' onclick='history.back()' value='戻る'></span>
            </div>";
            exit();
        }

        try {
            $db = new PDO($dsn, $dbUser, $dbPass);
            $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sql01 = "SELECT * FROM booklist";
            $stmt = $db->prepare($sql01);
            $stmt->execute();
            $rec = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // レコードごとに在庫数を更新
            foreach ($rec as $index => $row) {
                $stock = $row['stock']; // 現在の在庫数
                $quantity = intval($quantities[$index]); // 増減数
                $bookname = $booknames[$index];

                // 在庫数の更新
                $stock += $quantity;

                // データベースの在庫数を更新する処理
                $sql = 'UPDATE booklist SET stock = :stock WHERE bookname = :bookname';
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':stock', $stock, PDO::PARAM_INT);
                $stmt->bindValue(':bookname', $bookname, PDO::PARAM_STR);
                $stmt->execute();
            }

            echo "<h5><p style='color: red;'>書籍の在庫数が更新されました。</p></h5>";
            echo "<form method='post' action='staff.php'>";
            echo "<div class='toppage'>
            <span><input type='submit' name='staff' class='god' value='戻る'></span>
            </div>";
            echo "</form>";
        } catch (Exception $e) {
            echo "エラーが発生しました。内容: " . h($e->getMessage());
            exit();
        }

        $db = null;
    }
    ?>
</body>

</html>