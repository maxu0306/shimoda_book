<!DOCTYPE html>
<html lang="ja">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=yes">
    <meta charset='utf-8' />
    <title>予約履歴の削除画面</title>
    <link rel="stylesheet" href="index_book.css" type="text/css">
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
                $erase = "履歴削除";

                $db = new PDO($dsn, $dbUser, $dbPass);
                $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                foreach ($ids as $id) {
                    $sql02 = "UPDATE orderlist SET erase=:erase WHERE id=:id and bookname=:bookname and orderID=:orderID";
                    $stmt2 = $db->prepare($sql02);

                    foreach ($booknames as $index => $bookname) {
                        $orderID = $orderIDs[$index];
                        $stmt2->bindValue(':id', $id, PDO::PARAM_INT);
                        $stmt2->bindValue(':bookname', $bookname, PDO::PARAM_STR);
                        $stmt2->bindValue(':orderID', $orderID, PDO::PARAM_STR);
                        $stmt2->bindValue(':erase', $erase, PDO::PARAM_STR);
                        $stmt2->execute();
                    }
                }
                echo "<div><h5>予約履歴の削除が完了しました。</h5></div>";
                echo '<form method="post" action="index.php">';
                echo '<div class="toppage">
                <span><input type="submit" class="god" value="TOPへ戻る"></form></span>
                </div>';
            } else {
                echo "<h5><p style='color: red;''>書籍を指定してください。</p></h5>";
                echo '<div class="toppage">
                <span><input type="button" class="god" onclick="history.back()" value="戻る"></span>
                </div>';
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