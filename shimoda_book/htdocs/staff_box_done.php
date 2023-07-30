<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset='utf-8' />
    <title>取り寄せの完了画面</title>
    <link rel="stylesheet" href="staff_book.css" type="text/css">
</head>

<body>
    <?php

    require_once '_database_conf.php';
    require_once '_h.php';

    try {
        // POSTであれば、以下の処理が実行される
        if ($_SERVER["REQUEST_METHOD"] === 'POST') {
            // booknameという名前の変数が存在し、かつその値が配列であるかどうかを検証しています。
            if (isset($_POST["id"]) and is_array($_POST["id"])) {
                // １つまたは複数ある書籍の情報を、$selectedBooksに代入する
                $ids = $_POST["id"];
                $booknames = $_POST["bookname"];
                $orderIDs = $_POST['orderID'];
                $order_take = "受け取り完了";

                $db = new PDO($dsn, $dbUser, $dbPass);
                $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                foreach ($ids as $id) {
                    $sql02 = "UPDATE orderlist SET take=:takes WHERE id=:id and bookname=:bookname and orderID=:orderID";
                    $stmt2 = $db->prepare($sql02);
                    foreach ($booknames as $index => $bookname) {
                        $orderID = $orderIDs[$index];

                        $stmt2->bindValue(':id', $id, PDO::PARAM_INT);
                        $stmt2->bindValue(':bookname', $bookname, PDO::PARAM_STR);
                        $stmt2->bindValue(':orderID', $orderID, PDO::PARAM_STR);
                        $stmt2->bindValue(':takes', $order_take, PDO::PARAM_STR);
                        $stmt2->execute();
                    }
                }
                echo "<h5><p style='color: red;'>受け取りが完了しました。</p></h5>";
                echo "<form action='staff.php' method='post'><div class='toppage'>
                <span><input type='submit' class='god' name='staff04' value='TOPへ戻る'></span>
                </div></form>";
            } else {
                echo "<h5><p style='color: red;'>書籍を指定してください。</p></h5>";
                echo "<div class='toppage'>
                <span><input type='button' class='god' onclick='history.back()' value='戻る'></span>
                </div>";
            }
        }

        $db = null;
    } catch (Exception $e) {
        echo "エラーが発生しました。内容: " . h($e->getMessage());
        exit();
    }

    ?>
</body>

</html>