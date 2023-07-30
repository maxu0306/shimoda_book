<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset='utf-8' />
    <title>受け取り確認画面</title>
    <link rel="stylesheet" href="staff_book.css" type="text/css">
    <link rel="stylesheet" href="lightbox.css" type="text/css">
    <script src="lightbox-plus-jquery.js" type="text/javascript"></script>
    <style>
        /* スクロールバーのスタイルを定義 */
        .scrollbar-container {
            height: 570px;
            overflow-y: scroll;
            display: flex;
            flex-direction: column;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
    <?php

    require_once '_database_conf.php';
    require_once '_h.php';

    echo "<h4>受け取りの確認</h4>";

    // 検索フォーム
    echo "<form method='post' action=''>";
    echo "<label>注文番号:</label>";
    echo "<input type='text' name='orderID' style='width:auto;'>";
    echo " ";
    echo "<label>書籍の状態:</label>";
    echo "<select name='take' style='width:auto;'>";
    echo "<option value=''></option>";
    echo "<option value='取り置き済'>取り置き済</option>";
    echo "<option value='取り寄せ済'>取り寄せ済</option>";
    echo "</select>";
    echo " ";
    echo "<input type='submit' value='検索' style='width:auto; background: black; color:white;'>";
    echo "</form>";
    echo "<p>";

    echo "<form method='post' action='staff_box_done.php'>";

    try {
        $db = new PDO($dsn, $dbUser, $dbPass);
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $orderID = $_POST['orderID'] ?? '';
        $take = $_POST['take'] ?? '';

        if (!empty($take) or !empty($orderID)) {
            $sql = "SELECT * FROM orderlist WHERE take = '取り置き済' OR take = '取り寄せ済'";
            $sql .= !empty($orderID) ? " AND orderID = :orderID" : "";

            if ($take == "取り置き済") {
                $sql .= !empty($take) ? " AND take = '取り置き済' " : "";
            } else if ($take == "取り寄せ済") {
                $sql .= !empty($take) ? " AND take = '取り寄せ済' " : "";
            }

            $stmt = $db->prepare($sql);

            if (!empty($orderID)) {
                $stmt->bindValue(':orderID', $orderID);
            }
            $stmt->execute();
            $rec = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $sql = "SELECT * FROM orderlist WHERE take='取り置き済' OR take='取り寄せ済'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $rec = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        $db = null;

        if (empty($rec)) {
            echo "<h5><p style='color:red;'>お探しの書籍はありません</p></h5>";
            echo "<div class='toppage'>";
            echo "<span><input type='submit' formaction='staff.php' class='god' name='staff' value='戻る'></span>";
            echo "</div>";
        } else {
            echo "<div class='scrollbar-container'>";
            echo "<table border='1' class='tabledesign'>";
            echo "<tr>
      <th class='fixed01' style='width: 20px'>No.</th>
      <th class='fixed01' style='width: 100px'>画像</th>
      <th class='fixed01'>書籍名</th>
      <th class='fixed01' style='width: 200px'>日付</th>
      <th class='fixed01' style='width: 100px'>価格</th>
      <th class='fixed01' style='width: 100px'>冊数</th>
      <th class='fixed01' style='width: 120px'>状態</th>
      <th class='fixed01' style='width: 120px'>注文番号</th>
      <th class='fixed01' style='width: 100px'>";
            echo '<input type="button" class="god2" onclick="toggleCheckAll()" value="一括チェック">';
            echo "</th></tr>";

            $count = 0;

            foreach ($rec as $row) {
                $count++;
                $id = $row["id"];
                $img = $row["img"];
                $bookname = $row["bookname"];
                $orderdate = $row["orderdate"];
                $receive = $row["receive"];
                $period = $row["period"];
                $price = $row["price"];
                $number = $row["number"];
                $stock = $row["stock"];
                $yourname = $row["name"];
                $youremail = $row["email"];
                $yourtel = $row["tel"];
                $orderID = $row["orderID"];
                $take = $row["take"];
                // $totalPrice = $row["totalPrice"];

                echo "<tr class='book-row'>
        <td>$count</td>
        <td><a href='$img' data-lightbox='sample' data-title='$bookname'>
							<img src=$img alt=Book Image></a></td>
        <td>$bookname</td>";
                echo "<td style='font-size:16px;'>申込日:{$orderdate}<br>";
                if ($stock == '取り寄せ') {
                    echo "入荷日:{$receive}";
                }
                echo "<br>期限日:{$period}</td>";
                echo "<td class='price'>" . h(number_format($price)) . "円</td>
        <td>$number</td>";

                if ($stock == "取り置き") {
                    echo "<td style='color:blue'>$take</td>";
                } else if ($stock == "取り寄せ") {
                    echo "<td style='color:red'>$take</td>";
                }
                echo "<td>$orderID</td>";
                echo "<td><input type='checkbox' class='checkbox' name='id[]' value='$id'></td>";
                echo "</tr>";

                // 隠しフィールドをループ内に移動
                echo "<input type='hidden' name='bookname[]' value='$bookname'>";
                echo "<input type='hidden' name='orderID[]' value='$orderID'>";
            }
            echo "</table></div><p>";
            echo "<div class='toppage'>";
            echo "<span><input type='submit' formaction='staff.php' class='god' name='staff' value='戻る'></span>";
            echo "<span><input type='submit' class='god' name='rec_order' value='受け取り完了'></span>";
            echo "</div>";
        }
    } catch (Exception $e) {
        echo "エラーが発生しました。内容: " . h($e->getMessage());
        exit();
    }
    echo "</form>";

    ?>
</body>

</html>