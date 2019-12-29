<?php
ini_set('log_errors','on');
ini_set('error_log','php.log');
session_start();

//関数読み込み
require('function.php');

//==================================================
//生き物クラス
//==================================================
abstract class Creature{
  protected $name;
  protected $hp;
  protected $attackMin;
  protected $attackMax;
  //名前のセットと取得
  public function setName($str){
    $this->name = $str;
  }
  public function getName(){
    return $this->name;
  }
  //HPのセットと取得
  public function setHp($num){
    $this->hp = $num;
  }
  public function getHp(){
    return $this->hp;
  }
  //攻撃
  public function attack($target){
    $attackPoint = mt_rand($this->attackMin, $this->attackMax);
    //1割の確率でクリティカル
    if(!mt_rand(0,9)){
      $attackPoint = $attackPoint * 1.5;
      $attackPoint = (int)$attackPoint;//小数にしないため
      //履歴の呼び出し
      History::set($this->getName().'のクリティカルヒット！');
    }
    //引数のHPを取得し、攻撃した分の数値を引いてセット
    $target->setHp($target->getHp()-$attackPoint);
    //履歴の呼び出し
    if($target == $_SESSION['human']){
      History::set($attackPoint.'ポイントのダメージをくらった！');
    }else{
      History::set($attackPoint.'ポイントのダメージをあたえた！');
    }
  }
}

//==================================================
//人クラス(生き物クラス継承)
//==================================================
class Human extends Creature{
  //プロパティ
  protected $magicAttack;
  protected $recover;
  protected $maxHp;
  
  //コンストラクタ
  function __construct($name, $maxHp, $hp, $maxMp, $mp, $attackMin, $attackMax, $magicAttack, $recover, $level){
    $this->name = $name;
    $this->maxHp = $maxHp;
    $this->hp = $hp;
    $this->maxMp = $maxMp;
    $this->mp = $mp;
    $this->attackMin = $attackMin;
    $this->attackMax = $attackMax;
    $this->magicAttack = $magicAttack;
    $this->recover = $recover;
    $this->level = $level;
  }
  //最大HPのセットと取得
  public function setMaxHp($num){
    $this->maxHp = $num;
  }
  public function getMaxHp(){
    return $this->maxHp;
  }
  //最大MPのセットと取得
  public function setMaxMp($num){
    $this->maxMp = $num;
  }
  public function getMaxMp(){
    return $this->maxMp;
  }
  //MPのセットと取得
  public function setMp($num){
    $this->mp = $num;
  }
  public function getMp(){
    return $this->mp;
  }
  //魔法攻撃取得とセット
  public function setMagicAttack($num){
    $this->magicAttack = $num;
  }
  public function getMagicAttack(){
    return $this->magicAttack;
  }
  //回復魔法取得とセット
  public function setRecover($num){
    $this->recover = $num;
  }
  public function getRecover(){
    return $this->recover;
  }
  //魔法攻撃処理
  public function magicAttack($target){
    History::set('ゆうしゃのまほうこうげき！');
    $attackPoint = $this->magicAttack;
    $useMp = 8;
    if($this->getMp() > 8){
      $target->setHp($target->getHp() - $attackPoint);
      $this->setMp($this->getMp() - $useMp);
      if($target == $_SESSION['human']){
        History::set($attackPoint.'ダメージあたえた！');
      }else{
        History::set($attackPoint.'くらった！');
      }
      //MPが足りない場合
    }else{
      History::set('しかし、MPがたりない！');
    }
  }
  //回復処理
  public function recover(){
    History::set('ゆうしゃはかいふくじゅもんをとなえた！');
    History::set('ゆうしゃは'.$_SESSION['human']->recover.'かいふく！');
    $useMp = 5;
    if($this->getMp() > 4){
      $recoverPoint = $this->recover;
      $this->SetHp($this->getHp() + $recoverPoint);
      $this->SetMp($this->getMp() - $useMp);
      //回復して最大HPを超えてしまう場合
      if($this->getHp() > $this->getMaxHp()){
        $this->SetHp($this->getMaxHp());
        History::set($recoverPoint.'かいふく！');
      }
      //MPが足りない場合
    }else{
      History::set('しかし、MPがたりない！');
    }
  }
  //レベルのセットと取得
  public function setLevel($num){
    $this->level = $num;
  }
  public function getLevel(){
    return $this->level;
  }
  //レベルアップ処理（上限をそれぞれHPを50、MPを5上昇。HPとMPは最大値まで回復する。）
  public function levelup(){
    $this->setLevel($this->getLevel()+1);
    $this->setMaxHp($this->getMaxHp()+50);
    $this->setHp($this->getMaxHp());
    $this->setMaxMp($this->getMaxMp()+5);
    $this->setMp($this->getMaxMp());
  }
}

//==================================================
//モンスタークラス(生き物クラス継承)
//==================================================
class Monster extends Creature{
  //プロパティ
  protected $exp;
  //コンストラクタ
  function __construct($name, $hp, $img, $attackMin, $attackMax, $exp) {
    $this->name = $name;
    $this->hp = $hp;
    $this->img = $img;
    $this->attackMin = $attackMin;
    $this->attackMax = $attackMax;
    $this->exp = $exp;
  }
  //モンスターの画像の取得
  public function getImg(){
    return $this->img;
  }
  //経験値の取得
  public function getExp(){
    return $this->exp;
  }
}

//==================================================
//履歴インターフェース（履歴クラスの型定義）
//==================================================
interface HistoryInterface {
  public static function set($str);
  public static function clear();
}

//==================================================
//履歴クラス
//==================================================
class History implements HistoryInterface {
  //セッションを格納
  public static function set($str){
    if(empty($_SESSION['history'])) $_SESSION['history'] = '';
    $_SESSION['history'] .= $str .'<br>';
  }
  //セッションを空にする
  public static function clear(){
    unset($_SESSION['history']);
  }
}

//==================================================
//実体化処理
//==================================================
$monster = array();
//実体化
$human = new Human('ゆうしゃ', '500', '500', '50', '50', '50', '150', '200', '300', '1');
$monster[] = new Monster('こうもり', '150', 'img/pipo-enemy001.png', '40', '70', '10');
$monster[] = new Monster('エリマキトカゲ', '160', 'img/pipo-enemy016.png', '50', '80', '12');
$monster[] = new Monster('へび', '170', 'img/pipo-enemy003.png', '60', '90', '13');
$monster[] = new Monster('かぶと', '180', 'img/pipo-enemy004.png', '70', '100', '16');
$monster[] = new Monster('ひのたま', '190', 'img/pipo-enemy012.png', '80', '120', '20');
$monster[] = new Monster('ビッグツリー', '220', 'img/pipo-enemy006.png', '90', '140', '25');
$monster[] = new Monster('おばけ', '250', 'img/pipo-enemy010.png', '110', '160', '30');
$monster[] = new Monster('ビッグきのこ', '300', 'img/pipo-enemy008.png', '130', '180', '35');


//==================================================
//ゲーム処理
//==================================================
//セッションがなければ初期化する
if(empty($_SESSION)) init();

//POSTされているか？（コマンドを選択したか？）
if(!empty($_POST)){
  //攻撃を選択した場合
  if(!empty($_POST['attack_flg'])){
    debug('攻撃を選択しています');
    //勇者がモンスターに攻撃
    History::set('ゆうしゃのこうげき！');
    $_SESSION['human']->attack($_SESSION['monster']);
    //モンスターが勇者に攻撃
    if ($_SESSION['monster']->getHp() > 0){
      History::set($_SESSION['monster']->getName().'のこうげき！');
      $_SESSION['monster']->attack($_SESSION['human']);
    }
  }
  //ファイアを選択した場合
  if(!empty($_POST['fire_flg'])){
    debug('ファイアを選択しています');
    $_SESSION['human']->magicAttack($_SESSION['monster']);
    //モンスターが勇者に攻撃
    if ($_SESSION['monster']->getHp() > 0){
      History::set($_SESSION['monster']->getName().'のこうげき！');
      $_SESSION['monster']->attack($_SESSION['human']);
    }
  }
  //かいふくを選択した場合
  if(!empty($_POST['recover_flg'])){
    debug('回復を選択しています');
    $_SESSION['human']->recover();
    //モンスターが勇者に攻撃
    History::set($_SESSION['monster']->getName().'のこうげき！');
    $_SESSION['monster']->attack($_SESSION['human']);
  }
  //逃げるを選択した場合
  if(!empty($_POST['escape_flg'])){
    debug('逃げるを選択しています');
    createMonster();
  }
  //リスタートを選択した場合
  if(!empty($_POST['restart_flg'])){
    debug('ゲームをリスタートします');
    $_SESSION['history'] = '';
    init();
  }
  //モンスターの体力が0以下の場合
  if($_SESSION['monster']->getHp() <= 0){
    History::Set($_SESSION['monster']->getName().'をたおした！');
    //新しいモンスターを作成
    createMonster();
    //経験値を入れる
    $_SESSION['exp'] = $_SESSION['exp'] + $_SESSION['monster']->getExp();
  }
  //一定のexpが貯まればレベルアップ
  if($_SESSION['human']->getLevel() == 1 && $_SESSION['exp'] > 25){
    $_SESSION['human']->levelup();
  }else if($_SESSION['human']->getLevel() == 2 && $_SESSION['exp'] > 75){
    $_SESSION['human']->levelup();
  }
  //勇者の体力が0以下の場合
  if($_SESSION['human']->getHp() <= 0){
    debug('勇者がやられてしまいました。ゲームをリスタートします');
    gameOver();
  }
}

?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>RPG</title>
  <link rel="stylesheet" href="reset.css">
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/earlyaccess/nicomoji.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/earlyaccess/nikukyu.css" rel="stylesheet">
</head>
<body>
  <section class="back-screen">
    <!--      <div class="game-start"></div>-->
    <div class="game-play">
      <?php if(empty($_SESSION)){ ?>
      <div class="game-start">
        <form method="post">
          <input type="submit" value="ゲームスタート">
        </form>
      </div>
      <?php } ?>
     <?php if(!empty($_SESSION)){ ?>
      <div class="human-info">
        <div class="human-name"><?php echo $_SESSION['human']->getName(); ?></div>
        <div class="human-hp">HP：<?php echo $_SESSION['human']->getHp(); ?></div>
        <div class="human-mp">MP：<?php echo $_SESSION['human']->getMp(); ?></div>
        <div class="human-lv">Lv.<?php echo $_SESSION['human']->getLevel(); ?></div>
      </div>
      <form method="post" class="human-action">
      <?php if(!empty($_POST['magic_flg'])){ ?>
        <input type="submit" class="human-magic-attack" value="ファイア" name="fire_flg">
        <input type="submit" class="human-recover" value="かいふく" name="recover_flg">
        <input type="submit" class="human-backmenu" value="もどる" name="back_flg">
      <?php }else{ ?>
        <input type="submit" class="human-attack" value="こうげき" name="attack_flg">
        <input type="submit" class="human-magic" value="まほう" name="magic_flg">
        <input type="submit" class="human-escape" value="にげる" name="escape_flg">
      <?php } ?>
      </form>
      <div class="monster-info">
        <img src="<?php echo $_SESSION['monster']->getImg(); ?>" alt="">
        <div class="monster-name"><?php echo $_SESSION['monster']->getName(); ?>があらわれた！　　　　のこりHP：<?php echo $_SESSION['monster']->getHp(); ?></div>
      </div>
      <div class="action-log">
        <div class="log-new">
          <?php if(!empty($_SESSION['history'])) echo $_SESSION['history']; ?>
        </div>
      </div>
      <form method="post">
        <input type="submit" class="restart" value="リスタート" name="restart_flg">
      </form>
      <?php } ?>
    </div>
  </section>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script>
    $(function(){
      $log_new = $(".log-new");
      $log_new.scrollTop($log_new[0].scrollHeight);  
    });
  </script>
</body>

</html>
