<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Kulami implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * kulami.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in kulami_kulami.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */
  
  require_once( APP_BASE_PATH."view/common/game.view.php" );
  
  class view_kulami_kulami extends game_view
  {
    function getGameName() {
        return "kulami";
    }    
  	function build_page( $viewArgs )
  	{		
  	    // Get players & players number
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count( $players );

        /*********** Place your code below:  ************/
		
		$result = $this->game->getBoardSchema();


		$this->page->begin_block( "kulami_kulami", "tile" );
        
        $hor_scale = 64;
        $ver_scale = 64;
		
		foreach($result as $id => $piece){
			
			foreach($piece as $tile){
				$this->page->insert_block("tile", array('PIECE' => ('tile_' . $id), "SPRITE" => ("sprite" . $tile["sprite"]),'X' => $tile["x"], 'Y' => $tile["y"], 'LEFT' => ($tile["x"] * $hor_scale), 'TOP' => ($tile["y"] * $ver_scale)));
			}
		}
		

        /*********** Do not change anything below this line  ************/
  	}
  }
  

