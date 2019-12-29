<?php
//変数宣言
$debug_flg = true;

function debug($str){
  global $debug_flg;
  if($debug_flg){
    error_log('デバッグ：'.$str);
  }
}

//セッションにインスタンス化した勇者の情報を入れる
function createHuman(){
  global $human;
  $_SESSION['human'] = $human;
}

//セッションにインスタンス化したモンスター情報を入れる
function createMonster(){
  global $monster;
  $_SESSION['monster'] = $monster[mt_rand(0, 7)];
  History::Set($_SESSION['monster']->getName().'があらわれた！');
  }

//初期化メソッド
function init(){
  createHuman();
  createMonster();
  $_SESSION['exp'] = 0;
}

//ゲームオーバー時、セッションを空にする
function gameOver(){
  $_SESSION = array();
}
