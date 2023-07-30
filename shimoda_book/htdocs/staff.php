<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>下田篤書店-書籍検索画面</title>
    <!-- CSSのリンク先 -->
    <link rel="stylesheet" href="staff_book.css" type="text/css">
    <style>
        /*見出しの設定*/
        h4 {
            color: #000080;
            text-align: center;
            font-size: 50px;
            background-color: skyblue;
        }

        h5 {
            font-size: 50px;
        }
    </style>
</head>

<body>

    <h4>下田篤の書店-管理者画面</h4>
    <br><br>
    <!-- 在庫確認ボタン -->
    <div id="nav">
        <ul>
            <li>
                <form method="post" action="staff_bookinfo.php">
                    <input type="submit" name="staff_bookinfo" class="button" value="在庫">
                </form>
            </li>
            <!--注文履歴ボタン -->
            <li>
                <form method="post" action="staff_record.php">
                    <input type="submit" name="staff_record" class="button" value="注文の履歴">
                </form>
            </li>
        </ul>
    </div>

    <!-- 注文ボタン -->
    <div id="nav">
        <ul>
            <li>
                <form method="post" action="staff_order.php">
                    <input type="submit" name="add_order" class="button" value="注文の確認">
                </form>
            </li>
            <!-- 受取確認ボタン -->
            <li>
                <form method="post" action="staff_box.php">
                    <input type="submit" name="add_box" class="button" value="受け取り確認">
                </form>
            </li>
        </ul>
    </div>

    <?php

    require_once '_database_conf.php';
    require_once '_h.php';

    try {
        $db = new PDO($dsn, $dbUser, $dbPass);
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT * FROM orderlist WHERE CURDATE() < DATE(period) AND take = '準備中'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rec = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $db = null;

        if (empty($rec)) {
            echo "<h5><p style='color: #000080;'>書籍の注文はありません</p></h5>";
        } else {
            $count = 0;

            foreach ($rec as $row) {
                $count++;
            }
            echo "<h5><p style='color: #000080;'>書籍の注文が{$count}件あります！</p></h5>";
        }
    } catch (Exception $e) {
        echo "エラーが発生しました。内容: " . h($e->getMessage());
        exit();
    }
    ?>
</body>

</html>