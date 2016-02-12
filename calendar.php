<!-- カレンダー作成の計算 -->
<?php
class Calendar{
  
  private $year;
  private $month;

  public function __construct($y,$m){
    $this->year = $y;
    $this->month = $m;
  }

  public function create_rows(){
    $last_day = date("j", mktime(0,0,0,$this->month + 1, 0, $this->year));

    $rows = array();
    // 配列$rowの中身を"・"に初期化
    $row = self::init_row();

    for( $i = 1; $i <= $last_day; $i++ ) {
      
      $date = Date("w", mktime(0,0,0,$this->month,$i,$this->year));
      $row[$date] = $i;

      if($date == 6 || $i == $last_day ){
        // 配列の中に配列を入れる
        $rows[]= $row;
        $row = self::init_row();
      }
    }
    return $rows;
  }

  public function get_info(){
    return $this->year ."-" .$this->month;
  }

  private static function init_row(){
    $ary = array();
    for( $i = 0; $i <= 6; $i++ ){
      $ary[] = "・";
    }
    return $ary;
  }

}

date_default_timezone_set('Asia/Tokyo');
$diary_date = null;
$year = Date("Y"); //今年
$month = Date("n"); //今月
$day = date("j"); //今日
$diary_date=date("Y").date("n").date("j");


  // 日付の指定がある場合
  if(!empty($_GET['diary_date']))
  {
    $arr_date = explode('-', htmlspecialchars($_GET['diary_date'], ENT_QUOTES));
    
    if(count($arr_date) == 2 and is_numeric($arr_date[0]) and is_numeric($arr_date[1]))
    {
      $year = (int)$arr_date[0];
      $month = (int)$arr_date[1];

    }
    if(count($arr_date) == 3 and is_numeric($arr_date[0]) and is_numeric($arr_date[1]) and is_numeric($arr_date[2])){
      $year = (int)$arr_date[0];
      $month = (int)$arr_date[1];  
      $day = (int)$arr_date[2];
      $diary_date=$year.$month.$day;
    }
  }

$cal = new Calendar($year, $month);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>PHP Calendar</title>
  <link REL="stylesheet" href="sample.css" type="text/css">
</head>

<body>

<!-- コメント欄 -->
<div class="comment">
<?php
$isSend = false;
if(isset($_GET['isSend'])){
  $isSend = $_GET['isSend'];
}

try {
  //データベース選択　なければ作成
  $db = new SQLite3('./test');
  //テキストボックスに保存されているコメントを表示させる
  //日付をクリックした場合はisSendはfalseで元々保存されているコメント表示 
  if($isSend){
    $inputSet = $_POST['msg'];
  } else {
    $rs = $db->query("select msg from test02 where id = $diary_date");
      if($r = $rs->fetchArray(SQLITE3_ASSOC)){
        $inputSet = $r['msg'];
      }
  }
} catch (PDOException $e) {
    echo "<p>エラー：", $e->getMessage(), "</p>";
}
?>

<form action="calendar.php?diary_date=<?php echo $year.'-'.$month.'-'.$day;?>&isSend=true" method="post">
<p>Comment：<br>
<textarea name="msg" rows="10" cols="100"><?php print $inputSet;?></textarea><br>

<input type="submit" value="save">
<!-- <input type="reset" value="clear"></p> -->
</form>
  
<?php

try {
  //入力されていれば
  if (@$_POST['msg'] != '') {
    // prepareで実行コードを準備
    // REPLACE INTO でレコードがなければ追加、あれば更新
    $s = $db->prepare("REPLACE INTO test02 values($diary_date, :msg)");
    // bindParamでprepareの値を決定する
    $s->bindParam(':msg', $_POST['msg']);
    $s->execute() // 実行
      or die("<p>書き込みに失敗しました</p>");
  }
  
  //$rs = $db->query('select * from test01 order by id desc limit 20');
  // if($rs = $db->query("select * from test02 where id = $diary_date")){
  //   echo "<table border=\"1\">\n";
  // echo "<tr><th>Number</th><th>Date</th><th>Message</th></tr>\n";
  // //データベースの項目を１行ずつ出力
  //   while($r = $rs->fetchArray(SQLITE3_ASSOC)){
  //     echo "<tr><td>", $r['id'], "</td><td>", $r['t'], "</td><td>",
  //          htmlspecialchars($r['msg']), "</td></tr>\n";
  //     //var_dump($r);
  //   }
  //  echo "</table>\n";
    
  // }
  $db = null;

} catch (PDOException $e) {
  echo "<p>エラー：", $e->getMessage(), "</p>";
}

?>
</div>


<!-- カレンダー作成 -->
<div class="calendar">
<h1>
Clendar
</h1>
<table width="100%">
  <tr>
    <td><a href="./calendar.php?diary_date=<?php echo date('Y-m', strtotime($year .'-' . $month . ' -1 month')); ?>">&lt; 前の月</a></td>
    <td><?php echo $year ?>年<?php echo $month ?>月</td>
    <td><a href="./calendar.php?diary_date=<?php echo date('Y-m', strtotime($year .'-' . $month . ' +1 month')); ?>">次の月 &gt;</a></td>
  </tr>
</table>

<table width="100%" height=300>
  <tr>
    <th bgcolor="#fe0000">日</th>
    <th>月</th>
    <th>火</th>
    <th>水</th>
    <th>木</th>
    <th>金</th>
    <th>土</th>
  </tr>

<?php
foreach( $cal->create_rows() as $row ){
  echo "<tr>";

  for( $i = 0; $i <= 6; $i++ ){
    //選択した日付、または今日なら色変更
    if($year.$month.$row[$i] == $diary_date){
      $style=$style."; background:gold";
      echo "<td style=\"color:".$style."\"><a href=\"calendar.php?diary_date=$year-$month-$row[$i]\">".$row[$i]."</a></td>";
    }
    else if($year.$month.$row[$i] == date("Y").date("n").date("j")){
      $style=$style."; background:silver";
      echo "<td style=\"color:".$style."\"><a href=\"calendar.php?diary_date=$year-$month-$row[$i]\">".$row[$i]."</a></td>";
    } 
    else {
      echo "<td><a href=\"calendar.php?diary_date=$year-$month-$row[$i]\">".$row[$i]."</a></td>";
    }
  }
  echo "</tr>";
}
?>

</table>
</div>


<div class="blockc">
   <a href='./todo_main.php'>Go ToDo list</a>
</div>
</body>
</html>

