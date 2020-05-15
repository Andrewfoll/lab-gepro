/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Kulami implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * kulami.js
 *
 * Kulami user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter"
],
function (dojo, declare) {
    return declare("bgagame.kulami", ebg.core.gamegui, {
        constructor: function(){
            console.log('kulami constructor');
              
            // Here, you can init the global variables of your user interface
            // Example:
            // this.myGlobalValue = 0;

        },
        
        /*
            setup:
            
            This method must set up the game user interface according to current game situation specified
            in parameters.
            
            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)
            
            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */
         
        setup: function( gamedatas )
        {
            console.log( "Starting game setup" );
            
            // Setting up player boards
            for( var player_id in gamedatas.players )
            {
                var player = gamedatas.players[player_id];
                         
                // TODO: Setting up players boards if needed
				var player_board_div = $('player_board_'+player_id);
                dojo.place( this.format_block('jstpl_player_board', player ), player_board_div );
            }
            
            // TODO: Set up your game interface here, according to "gamedatas"
			for( var i in gamedatas.tiles )
            {
                var tile = gamedatas.tiles[i];
                
                if( tile.player !== null )
                {
                    this.addMarbleOnBoard( tile.x, tile.y, tile.player );
                }
            }
			
			dojo.query( '.tile' ).connect( 'onclick', this, 'onPlayMarble' );
 
			this.updateCounters(gamedatas.counters);
 
 
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            console.log( "Ending game setup" );
        },
		
		
		addMarbleOnBoard: function( x, y, player )
        {
            dojo.place( this.format_block( 'jstpl_marble', {
                x_y: x+'_'+y,
                color: this.gamedatas.players[ player ].color
            } ) , 'marbles' );
            
            this.placeOnObject( 'marble_'+x+'_'+y, 'overall_player_board_'+player );
            this.slideToObject( 'marble_'+x+'_'+y, 'tile_'+x+'_'+y ).play();
        },
       

        ///////////////////////////////////////////////////
        //// Game & client states
        
        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
           console.log( 'Entering state: '+stateName );
            
            switch( stateName )
            {
            case 'playerTurn':
				
                this.updatePossibleMoves( args.args.possibleMoves );
                break;
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {
            console.log( 'Leaving state: '+stateName );
            
            switch( stateName )
            {
            
            /* Example:
            
            case 'myGameState':
            
                // Hide the HTML block we are displaying only during this game state
                dojo.style( 'my_html_block_id', 'display', 'none' );
                
                break;
           */
           
           
            case 'dummmy':
                break;
            }               
        }, 

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        onUpdateActionButtons: function( stateName, args )
        {
            console.log( 'onUpdateActionButtons: '+stateName );
                      
            if( this.isCurrentPlayerActive() )
            {            
                switch( stateName )
                {
/*               
                 Example:
 
                 case 'myGameState':
                    
                    // Add 3 action buttons in the action status bar:
                    
                    this.addActionButton( 'button_1_id', _('Button 1 label'), 'onMyMethodToCall1' ); 
                    this.addActionButton( 'button_2_id', _('Button 2 label'), 'onMyMethodToCall2' ); 
                    this.addActionButton( 'button_3_id', _('Button 3 label'), 'onMyMethodToCall3' ); 
                    break;
*/
                }
            }
        },        

        ///////////////////////////////////////////////////
        //// Utility methods
        
        /*
        
            Here, you can defines some utility methods that you can use everywhere in your javascript
            script.
        
        */
		
		updatePossibleMoves: function( possibleMoves )
        {
			
			var last_marble = possibleMoves.last_marble;
			var opponent_move_piece_id = possibleMoves.opponent_move_piece_id;
			var my_last_move_piece_id = possibleMoves.my_last_move_piece_id;
			
			possibleMoves = possibleMoves.moves;
			
			
			
            // Remove current possible moves
            dojo.query( '.possibleMove' ).removeClass( 'possibleMove' );
			dojo.query( '.no_play' ).removeClass( 'no_play' );
			dojo.query( '.last_marble').removeClass('last_marble');
			
			if(opponent_move_piece_id != null){
				
				dojo.query('.tile_' + opponent_move_piece_id).addClass('no_play');
				
			}
			
			if(my_last_move_piece_id != null){
				
				dojo.query('.tile_' + my_last_move_piece_id).addClass('no_play');
				
			}
			
			if(last_marble != null){
				dojo.addClass('marble_'+last_marble[0]+'_'+last_marble[1], 'last_marble');
			}

            for( var x in possibleMoves )
            {
                for( var y in possibleMoves[ x ] )
                {
                    // x,y is a possible move
                    dojo.addClass( 'tile_'+x+'_'+y, 'possibleMove' );
                }            
            }
                        
            this.addTooltipToClass( 'possibleMove', '', _('Place a marble here') );
        },


        ///////////////////////////////////////////////////
        //// Player's action
        
        /*
        
            Here, you are defining methods to handle player's action (ex: results of mouse click on 
            game objects).
            
            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server
        
        */
        
		
		onPlayMarble: function( evt )
        {
            // Stop this event propagation
            dojo.stopEvent( evt );

            // Get the cliqued tile x and y
            // Note: tile id format is "tile_X_Y"
            var coords = evt.currentTarget.id.split('_');
            var x = coords[1];
            var y = coords[2];

            if( ! dojo.hasClass( 'tile_'+x+'_'+y, 'possibleMove' ) )
            {
                // This is not a possible move => the click does nothing
                return ;
            }
            
            if( this.checkAction( 'playMarble' ) )    // Check that this action is possible at this moment
            {            
                this.ajaxcall( "/kulami/kulami/playMarble.html", {
                    x:x,
                    y:y
                }, this, function( result ) {} );
            }            
        },
        

        
        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your kulami.game.php file.
        
        */
        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );
			
			dojo.subscribe( 'playMarble', this, "notif_playMarble" );
            this.notifqueue.setSynchronous( 'playMarble', 500 );
            dojo.subscribe( 'placeAMarble', this, "notif_placeAMarble" );
            this.notifqueue.setSynchronous( 'placeAMarble', 1000 );
            dojo.subscribe( 'newScores', this, "notif_newScores" );
            this.notifqueue.setSynchronous( 'newScores', 500 );
            
            // TODO: here, associate your game notifications with local methods
            
            // Example 1: standard notification handling
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            
            // Example 2: standard notification handling + tell the user interface to wait
            //            during 3 seconds after calling the method in order to let the players
            //            see what is happening in the game.
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
            // 
        },  
        
        // TODO: from this point and below, you can write your game notifications handling methods
        
		notif_playMarble: function( notif )
        {
            // Remove current possible moves (makes the board more clear)
            dojo.query( '.possibleMove' ).removeClass( 'possibleMove' );        
        
            this.addMarbleOnBoard( notif.args.x, notif.args.y, notif.args.player_id );
			
			this.updateCounters(notif.args.counters);
        },
		
		
		notif_placeAMarble: function( notif )
        {
            
        },
		
		notif_newScores: function( notif )
        {
            for( var player_id in notif.args.scores )
            {
                var newScore = notif.args.scores[ player_id ];
                this.scoreCtrl[ player_id ].toValue( newScore );
            }
        },
		
        /*
        Example:
        
        notif_cardPlayed: function( notif )
        {
            console.log( 'notif_cardPlayed' );
            console.log( notif );
            
            // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call
            
            // TODO: play the card in the user interface.
        },    
        
        */
   });             
});
