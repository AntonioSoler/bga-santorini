{OVERALL_GAME_HEADER}
<div id="play-area-scaler">
	<div id="play-area">
		<div id="scene-container"></div>
		<div id="powers-container">
			<!-- BEGIN card -->
				<div class="power-card power-{GOD_ID}">
					<div class="power-card-background"></div>
					<div class="power-card-overlay"></div>
					<div class="power-card-name">{GOD_NAME}</div>
					<div class="power-card-power"></div>
				</div>
			<!-- END card -->
		</div>
	</div>
</div>
<script type="text/javascript">
const URL = dojoConfig.packages.reduce((r,p) => p.name == "bgagame" ? p.location : r, null);
document.write('<script src="' + URL + '/scripts/board.js" type="module"><\/script>');

var jstpl_player_board = '<div class="santorini-god santorini-god-${god}"></div>';
</script>

{OVERALL_GAME_FOOTER}
