<?php
/*
    From this file, you can edit the various meta-information of your game.

    Once you modified the file, don't forget to click on "Reload game informations" from the Control Panel in order in can be taken into account.

    See documentation about this file here:
    http://en.doc.boardgamearena.com/Game_meta-information:_gameinfos.inc.php

*/

$gameinfos = array(
    // Game publisher
    'publisher' => 'Roxley',

    // Url of game publisher website
    'publisher_website' => 'https://roxley.com',

    // Board Game Geek ID of the publisher
    'publisher_bgg_id' => 21765,

    // Board game geek if of the game
    'bgg_id' => 194655,

    // Players configuration that can be played (ex: 2 to 4 players)
    'players' => array(2, 3, 4),

    // Suggest players to play with this number of players. Must be null if there is no such advice, or if there is only one possible player configuration.
    'suggest_player_number' => 2,

    // Discourage players to play with these numbers of players. Must be null if there is no such advice.
    // 'not_recommend_player_number' => array( 2, 3 ),      // <= example: this is not recommended to play this game with 2 or 3 players

    // Estimated game duration, in minutes (used only for the launch, afterward the real duration is computed)
    'estimated_duration' => 10,

    // Time in second add to a player when "giveExtraTime" is called (speed profile = fast)
    'fast_additional_time' => 20,

    // Time in second add to a player when "giveExtraTime" is called (speed profile = medium)
    'medium_additional_time' => 30,

    // Time in second add to a player when "giveExtraTime" is called (speed profile = slow)
    'slow_additional_time' => 50,

    // If you are using a tie breaker in your game (using "player_score_aux"), you must describe here
    // the formula used to compute "player_score_aux". This description will be used as a tooltip to explain
    // the tie breaker to the players.
    // Note: if you are NOT using any tie breaker, leave the empty string.
    //
    // Example: 'tie_breaker_description' => totranslate( "Number of remaining cards in hand" ),
    'tie_breaker_description' => '',

    // Is this game cooperative (all players wins together or loose together)
    'is_coop' => 0,

    // If in the game, all losers are equal (no score to rank them or explicit in the rules that losers are not ranked between them), set this to true
    // The game end result will display "Winner" for the 1st player and "Loser" for all other players
    'losers_not_ranked' => true,

    // Colors attributed to players
    'player_colors' => array("0000ff", "ffffff", "982fff"),

    // Favorite colors support : if set to "true", support attribution of favorite colors based on player's preferences (see reattributeColorsBasedOnPreferences PHP method)
    'favorite_colors_support' => false,

    // Game interface width range (pixels)
    // Note: game interface = space on the left side, without the column on the right
    'game_interface_width' => array(

        // Minimum width
        //  default: 740
        //  maximum possible value: 740 (ie: your game interface should fit with a 740px width (correspond to a 1024px screen)
        //  minimum possible value: 320 (the lowest value you specify, the better the display is on mobile)
        'min' => 550,
    ),
);
