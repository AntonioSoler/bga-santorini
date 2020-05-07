<?php

abstract class Power extends APP_GameClass {
    /* Factory function to create a power by ID */
    public static function getPower($game, $powerId) {
      if(!isset(Power::$powersClasses[$powerId])) {
        throw new BgaVisibleSystemException( "Power $powerId is not implemented" );
      }
      return new Power::$powersClasses[$powerId]($game);
    }

    protected $game;

    public function __construct($game) {
      $this->game = $game;
    }

    public function setup($player) {}
    public function beforeMove() {}
    public function argPlayerMove(&$workers) { }
    public function playerMove($wId, $x, $y, $z) { return false; }
    public function beforeBuild() {}
    public function argBuild() {}
    public function build() {}
    public function endTurn() {}
    public function winCondition() {}

    public static $powersClasses = [
      APOLLO => 'Apollo',
      ARTEMIS => 'Artemis',
      ATHENA => 'Athena',
      ATLAS => 'Atlas',
      DEMETER => 'Demeter',
      HEPHAESTUS => 'Hephaestus',
      HERMES => 'Hermes',
      MINOTAUR => 'Minotaur',
      PAN => 'Pan',
      PROMETHEUS => 'Prometheus',
      APHRODITE => 'Aphrodite',
      ARES => 'Ares',
      BIA => 'Bia',
      CHAOS => 'Chaos',
      CHARON => 'Charon',
      CHRONUS => 'Chronus',
      CIRCE => 'Circe',
      DIONYSUS => 'Dionysus',
      EROS => 'Eros',
      HERA => 'Hera',
      HESTIA => 'Hestia',
      HYPNUS => 'Hypnus',
      LIMUS => 'Limus',
      MEDUSA => 'Medusa',
      MORPHEUS => 'Morpheus',
      PERSEPHONE => 'Persephone',
      POSEIDON => 'Poseidon',
      SELENE => 'Selene',
      TRITON => 'Triton',
      ZEUS => 'Zeus',
      AEOLUS => 'Aeolus',
      CHARYBDIS => 'Charybdis',
      CLIO => 'Clio',
      EUROPA => 'Europa',
      GAEA => 'Gaea',
      GRAEAE => 'Graeae',
      HADES => 'Hades',
      HARPIES => 'Harpies',
      HECATE => 'Hecate',
      MOERAE => 'Moerae',
      NEMESIS => 'Nemesis',
      SIREN => 'Siren',
      TARTARUS => 'Tartarus',
      TERPSICHORE => 'Terpsichore',
      URANIA => 'Urania',
      ACHILLES => 'Achilles',
      ADONIS => 'Adonis',
      ATALANTA => 'Atalanta',
      BELLEROPHON => 'Bellerophon',
      HERACLES => 'Heracles',
      JASON => 'Jason',
      MEDEA => 'Medea',
      ODYSSEUS => 'Odysseus',
      POLYPHEMUS => 'Polyphemus',
      THESEUS => 'Theseus',
    ];
}
