{OVERALL_GAME_HEADER}
<div id="play-area-scaler">
	<div id="power-select-container">
		<div id="grid-powers"></div>
		<div id="power-detail" class="power-card power-0">
			<div class="power-card-background"></div>
			<div class="power-card-overlay"></div>
			<div id="power-detail-name" class="power-card-name"></div>
			<div class="power-card-power"></div>
		</div>
	</div>
	<div id="play-area">
		<div id="scene-container"></div>
		<div id="powers-container">
			<!-- BEGIN card -->
				<div class="power-card power-{POWER_ID}">
					<div class="power-card-background"></div>
					<div class="power-card-overlay"></div>
					<div class="power-card-name">{POWER_NAME}</div>
					<div class="power-card-power"></div>
				</div>
			<!-- END card -->
		</div>
	</div>
</div>
<script type="text/javascript">
const URL = dojoConfig.packages.reduce((r,p) => p.name == "bgagame" ? p.location : r, null);
document.write('<script src="' + URL + '/scripts/board.js" type="module"><\/script>');

var jstpl_powerSelect = '<div class="power-select power-${id}"><div class="power-select-background"></div><div class="power-select-overlay"></div></div>';
</script>

{OVERALL_GAME_FOOTER}
