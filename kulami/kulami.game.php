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
    function setupNewGame( $players, $options = array() )
    {    

        $gameinfos = self::getGameinfos();
        
		//Initialize players
		$this->InsertPlayersInfoIntoDatabase($players);
		
		//Initialize pieces
		$this->InsertPiecesInfoIntoDatabase();
		
		//Initialize tiles
		$this->InsertTilesInfoIntoDatabase();
       
		//Active next player
        $this->activeNextPlayer();

    }
	
	function InsertPlayersInfoIntoDatabase($players){
	
		$values = array();
	
		$player_color = '';
		$player_canal = '';
		$player_name = '';
		$player_avatar = '';
	
		//Default colors (RED, BLACK)
		$default_colors = array( "ff0000", "000000" );
	
		$sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
	
		//Insert each player info into the database
		foreach( $players as $player_id => $player )
		{
			$player_color = "'" . array_shift( $default_colors ) . "'";
			$player_canal = "'" . $player['player_canal'] . "'";
			$player_name = "'" . addslashes($player['player_name']) . "'";
			$player_avatar = "'" . addslashes( $player['player_avatar']) . "'"; 
		
			$player_info = "('" . $player_id . "',";
			$player_info .= $player_color . ",";
			$player_info .= $player_canal . ",";
			$player_info .= $player_name . ",";
			$player_info .= $player_avatar . ")";
		
			$values[] = $player_info;
    
		}
        
		$sql .= implode( $values, ',' );
		self::DbQuery( $sql );
        
		self::reloadPlayersBasicInfos();
	
	}

	function InsertPiecesInfoIntoDatabase(){
	
		//TODO: Read this data from a file
		$sql = "INSERT INTO pieces (piece_id, n_tiles) VALUES ";
		$sql .= "(0, 4),(1,4),(2,4),(3,4),(4,4),(5,6),(6,6),(7,6),(8,6),";
		$sql .= "(9,3),(10,3),(11,3),(12,3),(13,2),(14,2),(15,2),(16,2);";
	
		self::DbQuery($sql);
	
	}

	function InsertTilesInfoIntoDatabase(){
	
		$x = 0;
		$y = 1;
	
		$tile_x = 0;
		$tile_y = 0;
		$owner = '';
		$tile_piece_id = 0;
		$last_played = '';
	
	
		$sql = "INSERT INTO tiles (x,y,player, piece_id, last_played) VALUES ";
        
		$values = array();
		
		$schema = $this->getBoardSchema();
		
		foreach($schema as $piece_id => $piece){
			
			foreach($piece as $tile){
				
				$tile_x = "'" . $tile["x"] . "'";
				$tile_y = "'" . $tile["y"] . "'";
				$owner = 'NULL'; //At the beginning of the game, all tiles are empty
				$tile_piece_id = "'" . $piece_id . "'";
				$last_played = 'NULL';
			
				$tile_info = "(" . $tile_x . ",";
				$tile_info .= $tile_y . ",";
				$tile_info .= $owner . ",";
				$tile_info .= $tile_piece_id . ",";
				$tile_info .= $last_played . ")";
			
				$values[] = $tile_info;
			}
		}

		$sql .= implode( $values, ',' );
		self::DbQuery( $sql );
	}

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    function getAllDatas(){
        $result = array();
    
        $current_player_id = self::getCurrentPlayerId();
    
        $sql = "SELECT player_id id, player_score score FROM player ";
        $result['players'] = self::getCollectionFromDb( $sql );
		
		
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
    function getGameProgression(){
		$total_marbles = 28 * 2;
		
		$counters = self::getGameCounters();
		
		$progress = 0;
		
		$remaining_marbles = 0;
		
		//Get remaing marbles
		foreach($counters as $counter){
			$remaining_marbles += $counter['counter_value'];
		}
		
		//Get marbles played
		$progress = $total_marbles - $remaining_marbles;
		
		$progress = ($progress * 100.0) / $total_marbles;

        return $progress;
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    
	
	//TODO: read schema's info from file
	function getBoardSchema(){
		$model = 0;
		$result = array();
		
		switch($model){
			case 0:
				$result = array(
		"0" => array(
					array("sprite" => 1, "x" => 4, "y" => 0),
					array("sprite" => 2, "x" => 5, "y" => 0),
					array("sprite" => 7, "x" => 4, "y" => 1),
					array("sprite" => 8, "x" => 5, "y" => 1)
				),
		"1" => array(
					array("sprite" => 1, "x" => 2, "y" => 1),
					array("sprite" => 2, "x" => 3, "y" => 1),
					array("sprite" => 7, "x" => 2, "y" => 2),
					array("sprite" => 8, "x" => 3, "y" => 2)
				),
		"2" => array(
					array("sprite" => 1, "x" => 2, "y" => 6),
					array("sprite" => 2, "x" => 3, "y" => 6),
					array("sprite" => 7, "x" => 2, "y" => 7),
					array("sprite" => 8, "x" => 3, "y" => 7)
				),
		"3" => array(
					array("sprite" => 1, "x" => 5, "y" => 6),
					array("sprite" => 2, "x" => 6, "y" => 6),
					array("sprite" => 7, "x" => 5, "y" => 7),
					array("sprite" => 8, "x" => 6, "y" => 7)
				),
		"4" => array(
					array("sprite" => 1, "x" => 5, "y" => 8),
					array("sprite" => 2, "x" => 6, "y" => 8),
					array("sprite" => 7, "x" => 5, "y" => 9),
					array("sprite" => 8, "x" => 6, "y" => 9)
				),

		"5" => array(
					array("sprite" => 1, "x" => 0, "y" => 2),
					array("sprite" => 2, "x" => 1, "y" => 2),
					array("sprite" => 4, "x" => 0, "y" => 3),
					array("sprite" => 5, "x" => 1, "y" => 3),
					array("sprite" => 7, "x" => 0, "y" => 4),
					array("sprite" => 8, "x" => 1, "y" => 4)
				),
		"6" => array(
					array("sprite" => 1, "x" => 7, "y" => 6),
					array("sprite" => 2, "x" => 8, "y" => 6),
					array("sprite" => 4, "x" => 7, "y" => 7),
					array("sprite" => 5, "x" => 8, "y" => 7),
					array("sprite" => 7, "x" => 7, "y" => 8),
					array("sprite" => 8, "x" => 8, "y" => 8)
				),
		"7" => array(
					array("sprite" => 1, "x" => 3, "y" => 3),
					array("sprite" => 12, "x" => 4, "y" => 3),
					array("sprite" => 2, "x" => 5, "y" => 3),
					array("sprite" => 7, "x" => 3, "y" => 4),
					array("sprite" => 13, "x" => 4, "y" => 4),
					array("sprite" => 8, "x" => 5, "y" => 4)
				),
		"8" => array(
					array("sprite" => 1, "x" => 6, "y" => 4),
					array("sprite" => 12, "x" => 7, "y" => 4),
					array("sprite" => 2, "x" => 8, "y" => 4),
					array("sprite" => 7, "x" => 6, "y" => 5),
					array("sprite" => 13, "x" => 7, "y" => 5),
					array("sprite" => 8, "x" => 8, "y" => 5)
				),
				
		"9" => array(
					array("sprite" => 0, "x" => 2, "y" => 3),
					array("sprite" => 3, "x" => 2, "y" => 4),
					array("sprite" => 6, "x" => 2, "y" => 5)
				),
		"10" => array(
					array("sprite" => 0, "x" => 6, "y" => 1),
					array("sprite" => 3, "x" => 6, "y" => 2),
					array("sprite" => 6, "x" => 6, "y" => 3)
				),
		"11" => array(
					array("sprite" => 9, "x" => 7, "y" => 3),
					array("sprite" => 10, "x" => 8, "y" => 3),
					array("sprite" => 11, "x" => 9, "y" => 3)
				),
		"12" => array(
					array("sprite" => 9, "x" => 3, "y" => 5),
					array("sprite" => 10, "x" => 4, "y" => 5),
					array("sprite" => 11, "x" => 5, "y" => 5)
				),
				
		"13" => array(
					array("sprite" => 9, "x" => 4, "y" =>2 ),
					array("sprite" => 11, "x" => 5, "y" =>2 )
				),
		"14" => array(
					array("sprite" => 9, "x" => 0, "y" => 5),
					array("sprite" => 11, "x" => 1, "y" => 5)
				),
		"15" => array(
					array("sprite" => 9, "x" => 3, "y" => 8),
					array("sprite" => 11, "x" => 4, "y" => 8)
				),
		"16" => array(
					array("sprite" => 0, "x" => 4, "y" => 6),
					array("sprite" => 6, "x" => 4, "y" => 7)
				)
);
				break;
				
			
		}
		
		return $result;
		
	}
	
	function getAllBoardInfoFromDatabase()
    {
        return self::getObjectListFromDB( "SELECT x, y, player, piece_id, last_played FROM tiles");
    }
	
	
	function getPossibleMoves( $board, $player_id ){
	
		$last_moves_info = self::getObjectListFromDB("SELECT * FROM tiles WHERE last_played IS NOT NULL");
	
		$last_moves_counter = count($last_moves_info);
	
		$moves = null;
	
		switch($last_moves_counter){
			case 0:
				//No marbles placed
				$moves = $this->GetPossibleMovesFirstTime();
				break;
			case 1:
				//Only one marble placed
				$moves = $this->GetPossibleMovesSecondTime($board, $player_id, $last_moves_info);
				break;
			case 2:
				//Check normally
				$moves = $this->GetPossibleMovesNormally($board, $player_id, $last_moves_info);
				break;
		}
	
		$result = array("moves" => $moves['moves']);
	
		$result['opponent_move_piece_id'] = $moves['opponent_move']['player_id'] ?? null;
	
		$result['my_last_move_piece_id'] = $moves['my_last_move']['player_id'] ?? null;
	
		$result['last_marble'] = (isset($moves['opponent_move']) ? 
								array(
									$moves['opponent_move']['x'],
									$moves['opponent_move']['y']
								) : null);
								
		return $result;
	
	}

	function GetPossibleMovesFirstTime(){
	
		$schema = $this->getBoardSchema();
	
		$result = array();

	
		foreach($schema as $piece){
			foreach($piece as $tile){
				if(!isset($result[$tile["x"]])){
					$result[$tile["x"]] = array();
				}
					
				$result[$tile["x"]][$tile["y"]] = true;

			}
		}
	
		return array("moves" => $result); 
	}

	function GetPossibleMovesSecondTime($board, $player_id, $last_moves_info){
	
		$result = array();

		$last_move = $last_moves_info[0];
	
		foreach($board as $tile){
			if($tile['player'] !== null)
				continue;
			if(($tile['x'] == $last_move['x'] || $tile['y'] == $last_move['y']) && $tile['piece_id'] != $last_move['piece_id'])
			{
				if(!isset($result[$tile['x']])){
					$result[$tile['x']] = array();
				}
					
				$result[$tile['x']][$tile['y']] = true;
			
			}
		}
	
		return array("moves" => $result, "opponent_move" => $last_move); 
	
	}

	function GetPossibleMovesNormally($board, $player_id, $last_moves_info){
	
		$result = array();
	
		$opponent_move = '';
		$my_last_move = '';
	
		if($last_moves_info[0]['player'] == $player_id){
			$opponent_move = $last_moves_info[1];
			$my_last_move = $last_moves_info[0];
		}
		else{
			$opponent_move = $last_moves_info[0];
			$my_last_move = $last_moves_info[1];
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
			}
		}
	
		return array("moves" => $result, "opponent_move" => $opponent_move,
					"my_last_move" => $my_last_move);
	
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
        $board = self::getAllBoardInfoFromDatabase();
        $moves = self::getPossibleMoves($board, $player_id)['moves'];
		
        
        if( isset($moves[$x]) && isset($moves[$x][$y]) && $moves[$x][$y] == true )
        {
            // This move is possible!
             
			$sql = "UPDATE tiles SET last_played = NULL WHERE last_played = " . $player_id . ";";
			
			self::DbQuery( $sql );
			
            $sql = "UPDATE tiles SET player='$player_id', last_played ='$player_id' WHERE x = " . $x . " AND y = " . $y . ";";
			
                       
            self::DbQuery( $sql );
            
			
			//Calculate points
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
			

			//Statistics//
			
			
			//////////////
            
            
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

	
	function argPlayerTurn()
    {

        return array(
            'possibleMoves' => self::getPossibleMoves(self::getAllBoardInfoFromDatabase(), self::getActivePlayerId())
        );
    }
                

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////
	
	function stNextPlayer()
    {
        // Activate next player
        $player_id = self::activeNextPlayer();
		
		$marbles_left = $this->getGameCounters();
		
		if($marbles_left['marblecount_p' . $player_id]['counter_value'] < 1){
			$this->gamestate->nextState('endGame');
		}
		
		if(count(self::getPossibleMoves(self::getAllBoardInfoFromDatabase(), self::getActivePlayerId())['moves']) == 0)
			$this->gamestate->nextState('endGame');
        
        //self::giveExtraTime( $player_id );
        $this->gamestate->nextState( 'nextTurn' );

    }
    
//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////


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
   
    function upgradeTableDb( $from_version )
    {
        


    }    
}
