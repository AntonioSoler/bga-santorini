<?php

class Clio extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = CLIO;
    $this->name  = clienttranslate('Clio');
    $this->title = clienttranslate('Muse of History');
    $this->text  = [
      clienttranslate("[Your Build:] Place a Coin Token on each of the first 3 blocks your Workers build."),
      clienttranslate("[Opponent's Turn:] Opponents treat spaces containing your Coin Tokens as if they contain only a dome."),
    ];
    $this->playerCount = [2, 3];
    $this->golden  = false;
    $this->orderAid = 39;

    $this->implemented = true;
  }

  // See https://boardgamegeek.com/thread/1750443/article/25434309#25434309
  // Clio's coins cannot be removed by any other power.
  // Clio's workers on a coin are invisible to opponents and cannot be targeted.
  // ( handled via SantoriniBoard->getPlacedOpponentWorkers() )
  // Banned: Circe, Nemesis
  // Affects: Ares, Apollo, Bia, Charon, Dionysus, Eris, Iris, Maenads, Medea, Medusa, Minotaur, Odysseus, Scylla, Siren, Theseus
  // No change: Aphrodite, Hades, Harpies, Hera, Hypnus, Limus, Moerae, Persephone

  /* * */

  public function getTokens()
  {
    return $this->game->board->getPiecesByType('tokenCoin', null, 'hand');
  }

  public function getUiData()
  {
    $data = parent::getUiData();
    $data['counter'] = ($this->playerId != null) ? count($this->getTokens()) : 0;
    return $data;
  }

  public function setup()
  {
    for ($i = 0; $i < 3; $i++) {
      $this->getPlayer()->addToken('tokenCoin');
    }
    $this->updateUI();
  }

/*
  public function playerBuild($worker, $work)
  {
    // Remove the coin token if Clio builds on top of it
    $pieces = $this->game->board->getPiecesAt($work);
    foreach ($pieces as $piece) {
      if ($piece['type'] == 'tokenCoin') {
        $this->removePiece($piece);
      } else {
        throw new BgaVisibleSystemException("Clio: Invalid build attempt (id: {$piece['id']}, type: {$piece['type']})");
      }
    }

    return false;
  }
*/

  public function afterPlayerBuild($worker, $work)
  {
    $tokens = $this->getTokens();
    if (!empty($tokens)) {
      $space = $work;
      $space['z'] = $space['z'] + 1;
      if ($space['z'] <= 3) {
        $this->placeToken($tokens[0], $space);
        $this->updateUI();
      }
    }
  }
}
