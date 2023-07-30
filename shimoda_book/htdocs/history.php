<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=yes">
    <meta charset="UTF-8">
    <title>下田篤書店-予約履歴確認画面</title>
    <!-- CSSのリンク先 -->
    <link rel="stylesheet" href="index_book.css" type="text/css">
    <link rel="stylesheet" href="lightbox.css" type="text/css">
    <script src="lightbox-plus-jquery.js" type="text/javascript"></script>
    <style>
        /* スクロールバーのスタイルを定義 */
        .scrollbar-container {
            height: 580px;
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

    header("Cache-Control: private");
    session_cache_limiter("none");

    session_start(); // セッションを開始
    // データベース接続情報
    require_once '_database_conf.php';
    require_once '_h.php';

    // 使用する変数を空文字で初期化
    $user_device = '';

    // HTTP リクエストヘッダーが持っているユーザーエージェントの文字列を取得
    $useragent = $_SERVER['HTTP_USER_AGENT'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // フォームからのデータをセッション変数に格納
        $_SESSION['yourname'] = $_POST['yourname'];
        $_SESSION['youremail'] = $_POST['youremail'];
        $_SESSION['yourtel'] = $_POST['yourtel'];

        $yourname = $_SESSION['yourname'];
        $youremail = $_SESSION['youremail'];
        $yourtel = $_SESSION['yourtel'];
    }
    try {
        // 名前、メールアドレス、電話番号どれか一つでも入力されていなければ、エラーが表示される
        if (empty($yourname) or empty($youremail) or empty($yourtel)) {
            echo '<h5><p style="color: red;">個人情報の入力が完了していません。</p></h5>';
            echo "<br>";
            // 個人情報照会画面に戻るボタン
            echo "<div class='toppage'>
            <span><button class='god' onclick=history.back()>戻る</button></span>
            </div>";
        } else {
            $errors = array(); // エラーメッセージを格納する配列

            // 氏名の検証
            if (strlen($yourname) > 256) {
                $errors[] = '氏名は256文字以下で入力してください。';
            }

            // メールアドレスの検証
            if (strlen($youremail) > 256 or !filter_var($youremail, FILTER_VALIDATE_EMAIL)) {
                $errors[] = '正しい形式のメールアドレスを入力してください。';
            }

            // 電話番号の検証
            if (strlen($yourtel) > 13 or !preg_match('/^[0-9]+$/', $yourtel)) {
                $errors[] = '正しい形式の電話番号を入力してください。';
            }

            if (!empty($errors)) {
                // エラーメッセージを表示
                foreach ($errors as $error) {
                    echo '<h5><p style="color: red;">' . $error . '</p></h5>';
                    // 個人情報照会画面に戻るボタン
                    echo "<div class='toppage'>
                    <span><button class='god' onclick=history.back()>戻る</button></span>
                    </div>";
                }
            }
        }

        if (empty($yourname) or empty($youremail) or empty($yourtel)) {
            // 氏名、メールアドレス、電話番号のどれかが１つでもない場合
            echo "";
        } else if (!empty($errors)) {
            echo "";
        } else if (empty($errors)) {
            echo "<h4>お客様の予約履歴</h4>";

            // 検索フォーム
            echo "<form method='post' action=''>";
            echo "<label>注文番号:</label>";
            echo "<input type='text' name='orderID' style='width:auto;'>";
            echo " ";
            echo "<label>書籍の状態:</label>";
            echo "<select name='take' style='width:auto;'>";
            echo "<option value=''></option>";
            echo "<option value='準備中'>準備中</option>";
            echo "<option value='受け取り可能'>受け取り可能</option>";
            echo "<option value='受け取り完了'>受け取り完了</option>";
            echo "</select>";
            echo " ";
            echo "<input type='hidden' name='yourname' value='" . $_SESSION['yourname'] . "'>";
            echo "<input type='hidden' name='youremail' value='" . $_SESSION['youremail'] . "'>";
            echo "<input type='hidden' name='yourtel' value='" . $_SESSION['yourtel'] . "'>";
            echo "<input type='submit' value='検索' style='width:auto; background: black; color:white;'>";
            echo "</form>";
            echo "<p>";

            $db = new PDO($dsn, $dbUser, $dbPass);
            $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $orderID = $_POST['orderID'] ?? '';
            $take = $_POST['take'] ?? '';

            $_POST['yourname'] = $_SESSION['yourname'];
            $_POST['youremail'] = $_SESSION['youremail'];
            $_POST['yourtel'] = $_SESSION['yourtel'];

            if (!empty($take) or !empty($orderID)) {
                $sql = 'SELECT * FROM orderlist WHERE name = :name AND email = :email AND tel = :tel AND erase ="表示"';
                $sql .= !empty($orderID) ? " AND orderID = :orderID" : "";

                if ($take == "準備中") {
                    $sql .= !empty($take) ? " AND take = '準備中' " : "";
                } else if ($take == "受け取り可能") {
                    $sql .= !empty($take) ? " AND take = '取り置き済' OR take = '取り寄せ済' " : "";
                } else if ($take == "受け取り完了") {
                    $sql .= !empty($take) ? " AND take = '受け取り完了' " : "";
                }

                $stmt = $db->prepare($sql);
                if (!empty($orderID)) {
                    $stmt->bindValue(':orderID', $orderID);
                }
                $stmt->bindValue(':name', $yourname, PDO::PARAM_STR);
                $stmt->bindValue(':email', $youremail, PDO::PARAM_STR);
                $stmt->bindValue(':tel', $yourtel, PDO::PARAM_STR);
                $stmt->execute();
                $rec = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {

                // 入力された情報と予約完了時の情報が一致するか確認
                $sql = 'SELECT * FROM orderlist WHERE name = :name AND email = :email AND tel = :tel AND erase ="表示"';
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':name', $yourname, PDO::PARAM_STR);
                $stmt->bindValue(':email', $youremail, PDO::PARAM_STR);
                $stmt->bindValue(':tel', $yourtel, PDO::PARAM_STR);
                $stmt->execute();

                $rec = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            if (!empty($rec)) {
                // $recがある場合　※本来はelseだけで十分
                echo "<form method='post' action='history_delete.php'>";

                if (strpos($useragent, 'Android') or strpos($useragent, 'Mobile') or strpos($useragent, 'iPhone') or strpos($useragent, 'iPad')) {
                    echo '<input type="button" class="god2" onclick="toggleCheckAll()" value="一括チェック">';
                    // スクロールバー
                    echo "<div class='scrollbar-container'>";
                    echo "<table border='1' class='tabledesign_01'>";
                    // テーブルの属性（カラム）
                    echo "<tr>
                    <th class='fixed01' style='width: 150px'> </th>
                    <th class='fixed01'style='width: 250px'>書籍情報</th></tr>";

                    $count = 0;

                    foreach ($rec as $index => $row) {
                        $count++;
                        $id = $row["id"];
                        $img = $row['img'];
                        $bookname = $row['bookname'];
                        // 申込日
                        $orderdate = $row['orderdate'];
                        // 入荷日　※取り寄せのみ
                        $receive = $row['receive'];
                        // 期限日
                        $period = $row['period'];
                        $price = $row['price'];
                        $number = $row['number'];
                        $stock = $row['stock'];
                        $orderID = $row['orderID'];
                        $take = $row['take'];

                        // 状態の判定-----------------------------------------
                        // 今日の日付
                        $date = date("Y/m/d");

                        if ($stock === '取り置き' and $date > date($period)) {
                            $take = '期限切れ';
                        } else if ($stock === '取り寄せ' and $date > date($period)) {
                            $take = '期限切れ';
                        } else if ($take === '取り置き済' or $take === '取り寄せ済') {
                            // 受け取り可能
                            $take = '受け取り可能';
                        } else if ($take === '受け取り完了') {
                            $take = '受け取り完了';
                        }

                        // テーブルの行（タプル）
                        echo "<tr>";
                        echo "<td><a href='$img' data-lightbox='sample' data-title='$bookname'>
                 <img src=$img alt=Book Image></a></td>";
                        echo "<td><div class='No'>No.{$count}
                 <br><a style='color:red;'>{$bookname}</a>";
                        echo "<br><a style='font-size:18px;'>申込日：{$orderdate}";
                        if ($stock == '取り寄せ') {
                            echo "<br>入荷日：{$receive}";
                        }
                        echo "<br>期限日：{$period}";
                        echo "<br>価格：" . number_format($price) . "円";
                        echo "<br>冊数：{$number}";

                        if ($stock == "取り置き") {
                            echo "<br><a style='color:blue'>予約方法：{$stock}</a>";
                        } else {
                            echo "<br><a style='color:red'>予約方法：{$stock}</a>";
                        }

                        if ($take == "準備中") {
                            echo "<br>状態：{$take}"; // 状態を表示
                        } else if ($take == "受け取り可能") {
                            echo "<br>状態：{$take}"; // 状態を表示
                        } else if ($take == "受け取り完了") {
                            echo "<br>状態：{$take}"; // 状態を表示
                        }

                        echo "<br>注文番号：{orderID}</br>";
                        echo "<br><input type='checkbox' class='checkbox' name='id[]' value='$id'></div></td>";
                        echo "</tr>";

                        // 隠しフィールドをループ内に移動
                        echo "<input type='hidden' name='bookname[]' value='$bookname'>";
                        echo "<input type='hidden' name='orderID[]' value='$orderID'>";
                    }
                    echo '</table>';
                } else {
                    echo "<div class='scrollbar-container'>";
                    echo "<table border='1' class='tabledesign'>";
                    // テーブルの属性（カラム）
                    echo "<tr>
                <th  class='fixed01' style='width: 20px'>No.</th>
            <th class='fixed01' style='width: 100px'>画像</th>
            <th class='fixed01'>書籍名</th>
            <th  class='fixed01' style='width: 220px'>日付</th>
            <th  class='fixed01' style='width: 90px'>価格</th>
            <th  class='fixed01' style='width: 90px'>冊数</th>
            <th  class='fixed01' style='width: 90px'>予約方法</th>
            <th class='fixed01' style='width: 90px'>状態</th>
            <th class='fixed01' style='width: 150px'>注文番号</th>
            <th  class='fixed01' style='width: 150px'>";
                    echo '<input type="button" class="god2" onclick="toggleCheckAll()" value="一括チェック">';
                    echo "</th></tr>";

                    $count = 0;

                    foreach ($rec as $index => $row) {
                        $count++;
                        $id = $row["id"];
                        $img = $row['img'];
                        $bookname = $row['bookname'];
                        // 申込日
                        $orderdate = $row['orderdate'];
                        // 入荷日　※取り寄せのみ
                        $receive = $row['receive'];
                        // 期限日
                        $period = $row['period'];
                        $price = $row['price'];
                        $number = $row['number'];
                        $stock = $row['stock'];
                        $orderID = $row['orderID'];
                        $take = $row['take'];

                        // 状態の判定-----------------------------------------
                        // 今日の日付
                        $date = date("Y/m/d");

                        if ($stock === '取り置き' and $date > date($period)) {
                            $take = '期限切れ';
                        } else if ($stock === '取り寄せ' and $date > date($period)) {
                            $take = '期限切れ';
                        } else if ($take === '取り置き済' or $take === '取り寄せ済') {
                            // 受け取り可能
                            $take = '受け取り可能';
                        } else if ($take === '受け取り完了') {
                            $take = '受け取り完了';
                        }

                        // テーブルの行（タプル）
                        echo "<tr>";
                        echo "<td>$count</td>"; // 番号を表示
                        echo "<td><a href='$img' data-lightbox='sample' data-title='$bookname'>
                    <img src=$img alt=Book Image></a></td>";
                        echo "<td>$bookname</td>";
                        echo "<td style='font-size:18px;'>申込日:{$orderdate}<br>";
                        if ($stock == '取り寄せ') {
                            echo "入荷日:{$receive}";
                        }
                        echo "<br>期限日:{$period}</td>";
                        echo "<td>" . number_format($price) . "円</td>";
                        echo "<td>$number</td>";

                        if ($stock == "取り置き") {
                            echo "<td style='color:blue'>$stock</td>";
                        } else {
                            echo "<td style='color:red'>$stock</td>";
                        }

                        if ($take == "準備中") {
                            echo "<td>$take</td>"; // 状態を表示
                        } else if ($take == "受け取り可能") {
                            echo "<td>$take</td>"; // 状態を表示
                        } else if ($take == "受け取り完了") {
                            echo "<td>$take</td>"; // 状態を表示
                        }

                        echo "<td>$orderID</td>";
                        echo "<td><input type='checkbox' class='checkbox' name='id[]' value='$id'></td>";
                        echo "</tr>";

                        // 隠しフィールドをループ内に移動
                        echo "<input type='hidden' name='bookname[]' value='$bookname'>";
                        echo "<input type='hidden' name='orderID[]' value='$orderID'>";
                    }
                    echo '</table>';
                }
                echo '</div><p>';
                echo "<div class='toppage'>";
                echo "<form>";
                echo "<span><input type='submit' formaction='index.php' class='god' name='index' value='TOPへ戻る'></span>";
                echo "</form>";
                echo "<span><input type='submit' class='god' name='history_delete' value='削除'></span>";
                echo "</div>";
                echo "</form>";
            } else {
                echo "<div><h5>予約履歴がありません</h5></div>";
                echo "<div class='toppage'>
                <span><button class='god' onclick=history.back()>戻る</button></span>
                </div>";
            }
        }
    } catch (PDOException $e) {
        die("エラー：データベースに接続できませんでした");
    }
    session_destroy();
    ?>
</body>

</html>