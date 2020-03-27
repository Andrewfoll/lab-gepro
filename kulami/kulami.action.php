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
 * kulami.action.php
 *
 * Kulami main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/kulami/kulami/myAction.html", ...)
 *
 */
 
  
  class action_kulami extends APP_GameAction
  { 
    // Constructor: please do not modify
   	public function __default()
  	{
  	    if( self::isArg( 'notifwindow') )
  	    {
            $this->view = "common_notifwindow";
  	        $this->viewArgs['table'] = self::getArg( "table", AT_posint, true );
  	    }
  	    else
  	    {
            $this->view = "kulami_kulami";
            self::trace( "Complete reinitialization of board game" );
      }
  	} 
  	
  	// TODO: defines your action entry points there

	//Get data from client as pair(key-value)
    public function playMarble()
    {
        self::setAjaxMode();     
		//Get x and y coordinates
        $x = self::getArg( "x", AT_posint, true );
        $y = self::getArg( "y", AT_posint, true );
        $result = $this->game->playMarble( $x, $y );
        self::ajaxResponse( );
    }
	
  }
  

