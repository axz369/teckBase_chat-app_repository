<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.CSS">
    <title>掲示板</title>
</head>
<body>
    
<?php
$name = @$_POST["name"];
$comment = @$_POST["comment"];
$date = date("Y/m/d H:i:s");
$postPass=@$_POST['password'];
$deleteFlag=0;
$editNumber=@$_POST['editNumber'];
 
// db接続
#DB接続に必要な情報($dsn:Data Source Name)を定義
$dsn = 'mysql:dbname=データベース名;host=localhost';
$user = 'ユーザー名';
$password = 'パスワード';
$pdo = new PDO($dsn, $user, $password);
 
//接続状況の確認
if (mysqli_connect_errno()) {
    echo "データベース接続失敗" . PHP_EOL;
    echo "errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "error: " . mysqli_connect_error() . PHP_EOL;
    exit();
}

//編集、フォームに値をセットする処理
if (isset($_POST["edit"])){
    $id = $editNumber;
    $stmt = $pdo->prepare('SELECT * FROM chattable WHERE id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    foreach ($stmt as $row) {
        if($row[4]!=$_POST["editPassword"]){
            $editNumber=0;
        }
        if(($row[0]==$editNumber)&&($editNumber>=1)){
            $editName=$row[1];
            $editComment=$row[2];
        }
    }
}

//新規追加と編集送信
if (isset($_POST["submit"])){
    //編集
    if(($_POST["editPostNumber"]>=1)){
        $id = $_POST["editPostNumber"]; //変更する投稿番号
        $sql = 'UPDATE chattable SET name=:name,comment=:comment WHERE id=:id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name', $_POST["name"], PDO::PARAM_STR);
        $stmt->bindParam(':comment', $_POST["comment"], PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
    //新規追加
    else{
        $stmt = $pdo->prepare("INSERT INTO chattable(name,comment,date,password) VALUE (:name,:comment,:date,:password)"); //SQL文の骨子を準備
        $stmt->bindvalue(":name", $name); //:titleをpost送信されたtitleの内容に置換
        $stmt->bindvalue(":comment", $comment); //:contentsをpost送信されたcontentsの内容に置換
        $stmt->bindvalue(":date", $date);
        $stmt->bindvalue(":password", $postPass);
        $stmt->execute(); //SQL文を実行
    }
    //リダイレクト、データ表示
    header('Location: 指定URL');
}

//削除
if (isset($_POST["delete"])){
    $id = $_POST["deleteNumber"];
    $stmt = $pdo->prepare('SELECT * FROM chattable WHERE id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    foreach ($stmt as $row) {
        if($row[4]==$_POST["deletePassword"])$deleteFlag=1;
    }

    if($deleteFlag===1){
        $stmt = $pdo->prepare("DELETE FROM chattable WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
    $deleteFlag=0;

    //リダイレクト、データ表示
    header('Location: 指定URL');
}
?>

<table class="table" cellpadding="10">
<?php foreach($pdo->query('select * from chattable')as $row) : ?>
    <tr class="tableItem">
        <td><?php echo $row['id']; ?></td>
        <td><?php echo $row['name']; ?></td>
        <td><?php echo $row['comment']; ?></td>
        <td><?php echo $row['date']; ?></td>
    </tr>
<?php endforeach ?>
</table>


<form action="" method="post" class="form">
    <p>投稿</p>
    <input type="text" name="name" placeholder="名前" value="<?php if(isset($editName)){echo $editName;} ?>"> 
    <input type="text" name="comment" placeholder="コメント" value="<?php if(isset($editComment)){echo $editComment;} ?>">
    <input type="text" name="password" placeholder="パスワード">
    <input type="submit" name="submit">
    
    <input type="hidden" name="editPostNumber" value="<?php if(isset($editNumber)){echo $editNumber;} ?>">

    <p>削除</p>
    <input type="number" name="deleteNumber">
    <input type="text" name="deletePassword" placeholder="パスワード">
    <input type="submit" name="delete" value="削除">

    <p>編集</p>
    <input type="number" name="editNumber">
    <input type="text" name="editPassword" placeholder="パスワード">
    <input type="submit" name="edit" value="編集">
</form>
    
</body>
</html>