<?php

class Urania extends Power
{
  public static function getId() {
    return URANIA;
  }

  public static function getName() {
    return clienttranslate('Urania');
  }

  public static function getTitle() {
    return clienttranslate('Muse of Astronomy');
  }

  public static function getText() {
    return [
      clienttranslate("Your Turn: When your Worker moves or builds, treat opposite edges and corners as if they are adjacent so that every space has 8 neighbors.")
    ];
  }

  public static function getPlayers() {
    return [2, 3, 4];
  }

  public static function getBannedIds() {
    return [APHRODITE];
  }

  public static function isGoldenFleece() {
    return true; 
  }

  /* * */
  
  public function argPlayerWork(&$arg, $action)
  {   
    foreach($arg['workers'] as &$worker){
     
     $arg['workers'] = [];
     $space = $this->game->board->getCoords($worker);
    
     $x = [$space['x']];
     $y = [$space['y']];
    
     if ($worker['x'] == 0)
      $x[] = 5;
     if ($worker['x'] == 4)
      $x[] = -1;
     if ($worker['y'] == 0)
      $y[] = 5;
     if ($worker['y'] == 4)
      $y[] = -1;
      
     // add neighbouring spaces from places outside of the board
     foreach($x as $xx)
     {
      foreach($y as $yy)
      {
        $space['x'] = $xx;
        $space['y'] = $yy;
        $worker['works'] = array_merge( $worker['works'],  $this->game->board->getNeighbouringSpaces($space, $action));
      }
     }
    }
  }
  
  public function argPlayerMove(&$arg)
  {
    return argPlayerWork($arg, 'move');
  }
  
  public function argPlayerBuild(&$arg)
  {
    return argPlayerWork($arg, 'build');
  }
  
  

}
  
