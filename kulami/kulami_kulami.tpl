{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- Kulami implementation : © <Your name here> <Your email address here>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    kulami_kulami.tpl
    
    This is the HTML template of your game.
    
    Everything you are writing in this file will be displayed in the HTML page of your game user interface,
    in the "main game zone" of the screen.
    
    You can use in this template:
    _ variables, with the format {MY_VARIABLE_ELEMENT}.
    _ HTML block, with the BEGIN/END format
    
    See your "view" PHP file to check how to set variables and control blocks
    
    Please REMOVE this comment before publishing your game on BGA
-->

<div id="board">
	<!-- BEGIN tile -->
        <div id="tile_{X}_{Y}" class="tile {PIECE} {SPRITE}" style="left: {LEFT}px; top: {TOP}px;"></div>
    <!-- END tile -->
	
	<div id="marbles">
</div>

<script type="text/javascript">

// Templates

var jstpl_marble='<div class="marble marblecolor_${color}" id="marble_${x_y}"></div>';

var jstpl_player_board = '\<div class="cp_board">\
    <div id="marbleicon_p${id}" class="marbleicon marbleicon_${color}"></div><span id="marblecount_p${id}">0</span>\
</div>';

</script>

{OVERALL_GAME_FOOTER}
