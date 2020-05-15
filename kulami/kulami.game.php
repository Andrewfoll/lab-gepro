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
  * kulami.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class Kulami extends Table
{
	function __construct( )
	{
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();
        
        self::initGameStateLabels( array() );        
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "kulami";
    }	

    /*
        setupNewGame:
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame( $players, $options = array() )
    {    

        $gameinfos = self::getGameinfos();
        
		$default_colors = array( "ff0000", "000000" );
 
        // Create players
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $values = array();
        foreach( $players as $player_id => $player )
        {
            $color = array_shift( $default_colors );
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."')";
        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );
        
        self::reloadPlayersBasicInfos();
        
        /************ Start the game initialization *****/
       
		$sql = "INSERT INTO pieces (piece_id, n_tiles) VALUES ";
		$sql .= "(0, 4),(1,4),(2,2),(3,3),(4,6),(5,3),(6,6),(7,3),(8,6),";
		$sql .= "(9,2),(10,3),(11,4),(12,2),(13,4),(14,6),(15,2),(16,4);";
	   
		self::DbQuery($sql);
		

	   
		// Init the board
        $sql = "INSERT INTO tiles (x,y,player, piece_id, last_played) VALUES ";
        $sql_values = array();
		
		
		$schema = $this->getBoardSchema();
		
		foreach($schema as $piece_id => $piece){
			
			foreach($piece as $tile){
				
				$sql_values[] = "('$tile[0]','$tile[1]',NULL,'$piece_id',NULL)";
			}
		}

        $sql .= implode( $sql_values, ',' );
        self::DbQuery( $sql );
        
        
        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();

        /************ End of the game initialization *****/
    }

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array();
    
        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!
    
        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score FROM player ";
        $result['players'] = self::getCollectionFromDb( $sql );
		
  
        // TODO: Gather all information about current game situation (visible by player $current_player_id).
		
		
        $result['tiles'] = self::getObjectListFromDB( "SELECT x, y, player FROM tiles WHERE player IS NOT NULL" );
		
		$result['counters'] = $this->getGameCounters();

 
        return $result;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {
        // TODO: compute and return the game progression
		
		

        return 0;
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

    /*
        In this space, you can put any utility methods useful for your game logic
    */
	
	function getBoardSchema(){
		$model = 0;
		$result = array();
		
		switch($model){
			case 0:
				$result = array("0" => array(array(4,0),array(5,0),array(4,1),array(5,1)), "1" => array(array(2,1),array(3,1),array(2,2),array(3,2)),
								"2" => array(array(4,2),array(5,2)), "3" => array(array(6,1),array(6,2),array(6,3)),
								"4" => array(array(0,2),array(1,2),array(0,3),array(1,3),array(0,4),array(1,4)), "5" => array(array(2,3),array(2,4),array(2,5)),
								"6" => array(array(3,3),array(4,3),array(5,3),array(3,4),array(4,4),array(5,4)), "7" => array(array(7,3),array(8,3),array(9,3)),
								"8" => array(array(6,4),array(7,4),array(8,4),array(6,5),array(7,5),array(8,5)), "9" => array(array(0,5),array(1,5)),
							   "10" => array(array(3,5),array(4,5),array(5,5)), "11" => array(array(2,6),array(3,6),array(2,7),array(3,7)),
							   "12" => array(array(4,6),array(4,7)), "13" => array(array(5,6),array(6,6),array(5,7),array(6,7)),
							   "14" => array(array(7,6),array(8,6),array(7,7),array(8,7),array(7,8),array(8,8)), "15" => array(array(3,8),array(4,8)),
							   "16" => array(array(5,8),array(6,8),array(5,9),array(6,9)));
				break;
				
			
		}
		
		return $result;
		
	}
	
	function getBoard()
    {
        return self::getObjectListFromDB( "SELECT x, y, player, piece_id, last_played FROM tiles");
    }
	
	
	function getPossibleMoves( $board, $player_id )
    {
		
        $result = array();
        
        
		$last_moves = self::getObjectListFromDB("SELECT x, y, player, piece_id FROM tiles WHERE last_played IS NOT NULL");
		
		
		$schema = $this->getBoardSchema();
		
		$count = count($last_moves);
		$found = 0;
		
		if($count == 0){
			//First move
			
			foreach($schema as $piece){
				foreach($piece as $tile){
					if(!isset($result[$tile[0]])){
						$result[$tile[0]] = array();
					}
					
					$result[$tile[0]][$tile[1]] = true;
					$found++;
				}
			}
			/*
			for( $x=1; $x<=8; $x++ )
			{
				for( $y=1; $y<=8; $y++ )
				{
					
					if( ! isset( $result[$x] ) ){
						$result[$x] = array();
                        
					}
					
					$result[$x][$y] = true;
					$found++;
				}
			}*/
			
		}
		else if($count == 1){
			//My first move
			$opponent_move = $last_moves[0];
			
			foreach($board as $tile){
				if($tile['player'] !== null)
					continue;
				if(($tile['x'] == $opponent_move['x'] || $tile['y'] == $opponent_move['y']) && $tile['piece_id'] != $opponent_move['piece_id'])
				{
					if(!isset($result[$tile['x']])){
						$result[$tile['x']] = array();
					}
					
					$result[$tile['x']][$tile['y']] = true;
					$found++;
				}
			}
		}
		else{
			
			if($last_moves[0]['player'] == $player_id){
				$opponent_move = $last_moves[1];
				$my_last_move = $last_moves[0];
			}
			else{
				$opponent_move = $last_moves[0];
				$my_last_move = $last_moves[1];
			}
			
			foreach($board as $tile){
				if($tile['player'] !== null)
					continue;
				if(($tile['x'] == $opponent_move['x'] || $tile['y'] == $opponent_move['y']) && $tile['piece_id'] != $opponent_move['piece_id'] && $tile['piece_id'] != $my_last_move['piece_id'])
				{
					if(!isset($result[$tile['x']])){
						$result[$tile['x']] = array();
					}
					
					$result[$tile['x']][$tile['y']] = true;
					$found++;
				}
			}
		}
		
		
		$result = array('moves' => $result);
		$result['opponent_move_piece_id'] = $opponent_move['piece_id'] ?? null;
		$result['my_last_move_piece_id'] = $my_last_move['piece_id'] ?? null;
		
		$result['last_marble'] = (isset($opponent_move)? array($opponent_move['x'], $opponent_move['y']) : null);
                
			
        return $result;
    }
	
	
	function getGameCounters() {
    	
		$sql = "SELECT player, count(*) AS n FROM tiles WHERE player IS NOT NULL GROUP BY player";

		$marbles = self::getObjectListFromDB($sql);
		

		
		if(count($marbles) < 2){
			if(count($marbles) == 1){
				$sql = "SELECT player_id FROM player WHERE player_id != " . $marbles[0]['player'];
				$temp = self::getObjectListFromDB($sql);
				$marbles[1] = array("player" => $temp[0]['player_id'], "n" => 0);
				
			}
			else{
				//No one played
				$sql = "SELECT player_id FROM player";
				$temp = self::getObjectListFromDB($sql);
				$counter = 0;
				foreach($temp as $p){
					$marbles[$counter] = array("player" => $p['player_id'], "n" => 0);
					$counter++;
				}
			
			}
		}

		$result = array();
		
		foreach($marbles as $row){
			$player = 'marblecount_p' . $row['player'];/////////////////////////
			$result[$player] = array('counter_name' => $player, 'counter_value' => (28 - $row['n']));
			
		}


		
		return $result;
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in kulami.action.php)
    */
	function playMarble( $x, $y )
    {
        // Check that this player is active and that this action is possible at this moment
        self::checkAction( 'playMarble' );  
        
        $player_id = self::getActivePlayerId(); 
        
        // Now, check if this is a possible move
        $board = self::getBoard();
        $moves = self::getPossibleMoves($board, $player_id)['moves'];
		
        
        if( isset($moves[$x]) && isset($moves[$x][$y]) && $moves[$x][$y] == true )
        {
            // This move is possible!
             
			$sql = "UPDATE tiles SET last_played = NULL WHERE last_played = " . $player_id . ";";
			
			self::DbQuery( $sql );
			
            $sql = "UPDATE tiles SET player='$player_id', last_played ='$player_id' WHERE x = " . $x . " AND y = " . $y . ";";
			
                       
            self::DbQuery( $sql );
            
			
			//calc points
			$sql = "SELECT count(*) AS n, tiles.piece_id AS id, tiles.player AS player, pieces.n_tiles AS n_tiles FROM tiles INNER JOIN pieces ON tiles.piece_id = pieces.piece_id WHERE tiles.player IS NOT NULL GROUP BY tiles.piece_id , tiles.player";
			
			
			$result = self::getObjectListFromDB( $sql );
			
			$tile_dominated = array();
			
			foreach($result as $row){
				$piece_id = $row['id'];
				if(isset($tile_dominated[$piece_id])){
					if($row['n'] > $tile_dominated[$piece_id]["count"]){
						$tile_dominated[$piece_id] = array("player" => $row['player'], "count" => $row['n'], "tiles" => $row['n_tiles']);
					}
					else if($row['n'] == $tile_dominated[$piece_id]["count"]){
						unset($tile_dominated[$piece_id]);
					}
				}
				else{
					$tile_dominated[$piece_id] = array("player" => $row['player'], "count" => $row['n'], "tiles" => $row['n_tiles']);
				}
			}
			
			$score = array();
			
			foreach($tile_dominated as $tile){
				if(! isset($score[$tile['player']])){
					$score[$tile['player']] = $tile['tiles'];
				}
				else{
					$score[$tile['player']] += $tile['tiles'];
				}
			}
			
			
			//Update player score table
			foreach($score as $key => $value){
				$sql = "UPDATE player SET player_score = " . $value . " WHERE player_id = " . $key; 
				self::DbQuery( $sql );
			}
			

			
            
            /*
            // Statistics
            self::incStat( count( $turnedOverDiscs ), "turnedOver", $player_id );
            if( ($x==1 && $y==1) || ($x==8 && $y==1) || ($x==1 && $y==8) || ($x==8 && $y==8) )
                self::incStat( 1, 'discPlayedOnCorner', $player_id );
            else if( $x==1 || $x==8 || $y==1 || $y==8 )
                self::incStat( 1, 'discPlayedOnBorder', $player_id );
            else if( $x>=3 && $x<=6 && $y>=3 && $y<=6 )
                self::incStat( 1, 'discPlayedOnCenter', $player_id );
            */
            // Notify
            self::notifyAllPlayers( "playMarble", clienttranslate( '${player_name} plays a marble' ), array(
                'player_id' => $player_id,
                'player_name' => self::getActivePlayerName(),
                'x' => $x,
                'y' => $y, 
				'counters' => $this->getGameCounters()
            ) );

            self::notifyAllPlayers( "placeAMarble", '', array(
                'player_id' => $player_id
            ) );
            
            $newScores = self::getCollectionFromDb( "SELECT player_id, player_score FROM player", true );
            self::notifyAllPlayers( "newScores", "", array(
                "scores" => $newScores
            ) );
            
            // Then, go to the next state
            $this->gamestate->nextState( 'playMarble' );
        }
        else 
            throw new feException( "Impossible move" );
    }
	
    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */
	
	function argPlayerTurn()
    {

		
        return array(
            'possibleMoves' => self::getPossibleMoves(self::getBoard(), self::getActivePlayerId())
        );
    }
                

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */
	
	function stNextPlayer()
    {
        // Activate next player
        $player_id = self::activeNextPlayer();
		
		$marbles_left = $this->getGameCounters();
		
		if($marbles_left['marblecount_p' . $player_id]['counter_value'] < 1){
			$this->gamestate->nextState('endGame');
		}
		
		if(count(self::getPossibleMoves(self::getBoard(), self::getActivePlayerId())['moves']) == 0)
			$this->gamestate->nextState('endGame');
        
        self::giveExtraTime( $player_id );
        $this->gamestate->nextState( 'nextTurn' );

    }
    
//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
    */

    function zombieTurn( $state, $active_player )
    {
        if( $state['name'] == 'playerTurn' )
        {
            $this->gamestate->nextState( "zombiePass" );//The game can't go forward with only 1 player so end the game
        }
        else
            throw new feException( "Zombie mode not supported at this game state:".$state['name'] );
    }
    
///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */
   
    function upgradeTableDb( $from_version )
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345
       
        // Example:
//        if( $from_version <= 1404301345 )
//        {
//           // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        // Please add your future database scheme changes here
//
//


    }    
}
