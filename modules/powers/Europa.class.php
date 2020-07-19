<?php

class Europa extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = EUROPA;
    $this->name  = clienttranslate('Europa & Talus');
    $this->title = clienttranslate('Queen & Guardian Automaton');
    $this->text  = [
      clienttranslate("[Setup:] Place the Talus Token on your God Power card."),
      clienttranslate("[End of Your Turn:] You may relocate your Talus Token to an unoccupied space neighboring the Worker that moved."),
      clienttranslate("[Any Time:] All players treat the space containing the Talus Token as if it contains only a dome."),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = false;
    $this->orderAid = 9;

    $this->implemented = true;
  }

  /* * */
  public function getToken()
  {
    $action = $this->game->log->getLastAction('addToken', $this->playerId, 'all');
    return $this->game->board->getPiece($action['id']);
  }

  public function getUIData()
  {
    $data = parent::getUIData();
    $data['counter'] = ($this->playerId != null && $this->getToken()['location'] == 'board') ? 0 : 1;
    return $data;
  }

  public function setup()
  {
    $wId = $this->getPlayer()->addToken('tokenTalus', N);
    $this->game->log->addAction('addToken', [], ['id' => $wId]);
    $this->updateUI();
  }

  public function stateAfterBuild()
  {
    return 'power';
  }

  public function argUsePower(&$arg)
  {
    $arg = $this->game->argPlayerBuild();
    $arg['power'] = $this->id;
    $arg['power_name'] = $this->name;
    $arg['skippable'] = true;
  }

  public function usePower($action)
  {
    $token = $this->getToken();
    $space = $action[1];
    if($token['location'] == 'hand'){
      $this->placeToken($token, $space);
    } else {
      $this->replaceToken($token, $space);
    }
  }

  public function stateAfterSkipPower()
  {
    return 'endturn';
  }

  public function stateAfterUsePower()
  {
    return 'endturn';
  }
}
