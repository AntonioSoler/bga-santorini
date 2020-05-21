<?php

class Hypnus extends Power
{
  public static function getId() {
    return HYPNUS;
  }

  public static function getName() {
    return clienttranslate('Hypnus');
  }

  public static function getTitle() {
    return clienttranslate('God of Sleep');
  }

  public static function getText() {
    return [
      clienttranslate("Start of Opponent's Turn: If one of your opponent's Workers is higher than all of their others, it cannot move.")
    ];
  }

  public static function getPlayers() {
    return [2, 3, 4];
  }

  public static function getBannedIds() {
    return [TERPSICHORE];
  }

  public static function isGoldenFleece() {
    return true; 
  }

  /* * */

  protected $blockedWorkerId;  

  protected static function cmpZ($worker1, $worker2){
    if ($worker1['z'] == $worker2['z'])
      return 0;
    return ($worker1['z'] > $worker2['z']) ? -1 : 1;
  }

  public function startOpponentTurn() // TODO: check name
  {
    $this->blockedWorkerId = null;
    
    $workers = $this->game->boarg->getActiveWorkers();
    if (count($workers) < 2)
      return;
    usort($workers, 'self::cmpZ');
    if(cmpZ($workers[0], $workers[1]) != 0)
      $this->blockedWorkerId = $workers[0]['id'];
  } 
  
  public function argOpponentMove(&$arg)
  {    
    Utils::filterWorkers($arg, function($worker) use ($this->blockedWorkerId) {
          return $this->blockedWorkerId != $worker['id'];
      });
    }
  }

}
  
