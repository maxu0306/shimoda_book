<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset='utf-8' />
    <title>管理者在庫確認画面</title>
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
</head>

<body>
    <?php

    require_once '_database_conf.php';
    require_once '_h.php';

    echo "<h4>在庫</h4>";

    try {
        // 検索フォーム
        echo "<form method='post' action=''>";
        echo "<label>書籍名:</label>";
        echo "<input type='text' name='bookname' style='width:auto;'>";
        echo " ";
        echo "<label>ジャンル:</label>";
        echo "<select name='genre' style='width:auto;'>";
        echo "<option value=''></option>";
        echo '<option value="小説">小説</option>
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
        <option value="政治">政治</option>';
        echo "</select>";
        echo " ";
        echo "<label>在庫:</label>";
        echo "<select name='stock' style='width:auto;'>";
        echo "<option value=''></option>";
        echo "<option value='在庫あり'>在庫あり</option>";
        echo "<option value='在庫なし'>在庫なし</option>";
        echo "</select>";
        echo " ";
        echo "<input type='submit' value='検索' style='width:auto; background: black; color:white;'>";
        echo "</form>";
        echo "<p>";

        // index.phpで検索された書籍を$booknameに格納
        $bookname = $_POST['bookname'] ?? '';

        // ジャンルを指定する場合は$genreという変数に格納
        $genre = $_POST['genre'] ?? '';

        // 在庫あり、在庫なしが格納
        $stock = $_POST['stock'] ?? '';

        $db = new PDO($dsn, $dbUser, $dbPass);
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 書籍名を入力し、ジャンルの指定して検索する場合 
        if (!empty($bookname) or !empty($genre) or !empty($stock)) {
            // 書籍名を入力し、ジャンルの指定して検索する場合
            $sql = 'SELECT * FROM booklist WHERE 1=1';
            $sql .= !empty($bookname) ? " AND bookname LIKE :bookname" : "";
            $sql .= !empty($genre) ? " AND genre = :genre" : "";
            if ($stock == "在庫あり") {
                $sql .= !empty($stock) ? " AND stock > 0 " : "";
            } else if ($stock == "在庫なし") {
                $sql .= !empty($stock) ? " AND stock = 0 " : "";
            }

            $stmt = $db->prepare($sql);
            if (!empty($bookname)) {
                $stmt->bindValue(':bookname', '%' . $bookname . '%');
            }
            if (!empty($genre)) {
                $stmt->bindValue(':genre', $genre);
            }

            $stmt->execute();
            $rec = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $sql01 = "SELECT * FROM booklist";
            $stmt = $db->prepare($sql01);
            $stmt->execute();
            $rec = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $db = null;

        if (empty($rec)) {
            echo "該当する書籍がありません。";
        } else {
            // 在庫登録用のフォーム
            echo "<form method='post' action='staff_bookinfo_done.php'>";

            // スクロールバーの設定
            echo "<div class='scrollbar-container'>";
            // テーブルの属性(カラム)
            echo "<table border='1' class='tabledesign'><tr>";
            echo "<th class='fixed01' style='width: 20px'>No.</th>
                <th class='fixed01' style='width: 100px'>画像</th>
                <th class='fixed01'>書籍名</th>
                <th class='fixed01' style='width: 150px'>ジャンル</th>
                <th class='fixed01' style='width: 150px'>出版社</th>
                <th class='fixed01' style='width: 150px'>著者</th>
                <th class='fixed01' style='width: 120px'>発売日</th>
                <th class='fixed01' style='width: 100px'>価格</th>
                <th class='fixed01' style='width: 100px'>在庫数</th>
                <th class='fixed01' style='width: 100px'>在庫増減</th>
                </tr>";

            // 番号の初期値
            $count = 0;
            // 冊数の入力フォームを$buynumberに代入

            foreach ($rec as $row) {
                $count++;
                $img = ($row['img']);
                $bookname = ($row['bookname']);
                $genre = ($row['genre']);
                $company = ($row['company']);
                $author = ($row['author']);
                $sellday = ($row['sellday']);
                $price = ($row['price']);
                $stock = ($row['stock']);

                // テーブルの行（タプル）
                echo "<tr><td>$count</td>";
                echo "<td><a href='$img' data-lightbox='sample' data-title='$bookname'>
                <img src=$img alt=Book Image></a></td>";
                echo "<td>$bookname</td>
                    <td>$genre</td>
                    <td>$company</td>
                    <td>$author</td>
                    <td>$sellday</td>
                    <td>" . number_format($price) . "円</td>
                <td>$stock</td>";
                echo "<td><input type='number' class='quantity' min='-{$stock}' name='quantity[]' value='0' 
                style='width: 45px' onchange='updateTotalPrice()'>";

                echo "<input type='hidden' name='bookname[]' value='$bookname'></td>";
                echo "</tr>";
            }
            echo "</table></div><p>";
            echo "<div class='toppage'>";
            echo "<span><input type='submit' formaction='staff.php' class='god' name='staff' value='戻る'></span>";
            echo "<span><input type='submit' class='god' name='regist' value='在庫更新'></span>";
            echo "</div>";
            echo "</form>";
        }
    } catch (Exception $e) {
        echo "エラーが発生しました。内容: " . h($e->getMessage());
        exit();
    }

    ?>
</body>

</html>