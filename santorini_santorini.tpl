{OVERALL_GAME_HEADER}
<div id="playareascaler">
	<div id="playArea">
		<div id="sceneContainer"></div>
	</div>
</div>
<script type="text/javascript">
const URL = dojoConfig.packages.reduce((r,p) => p.name == "bgagame" ? p.location : r, null);
document.write('<script src="' + URL + '/scripts/board.js" type="module"><\/script>');

var jstpl_player_board = '<div class="santorini-god santorini-god-${god}"></div>';
</script>

{OVERALL_GAME_FOOTER}
