{OVERALL_GAME_HEADER}
<div id="play-area-scaler">
	<div id="power-select-container">
		<div id="grid-powers"></div>
		<div id="power-detail" class="power-card power-0">
			<div class="power-card-overlay"></div>
			<div id="power-detail-name" class="power-card-name"></div>
			<div class="power-card-power"></div>
		</div>
	</div>
	<div id="power-choose-container"></div>
	<div id="play-area">
		<div id="scene-container"></div>
	</div>
</div>
<script type="text/javascript">
const URL = dojoConfig.packages.reduce((r,p) => p.name == "bgagame" ? p.location : r, null);
document.write('<script src="' + URL + '/scripts/board.js" type="module"><\/script>');

var jstpl_powerSelect = '<div id="power-select-${id}" class="power-select"><div class="power-select-background power-${id}"></div><div class="power-select-overlay"></div></div>';

var jstpl_powerContainer = '<div id="power_container_${id}" class="power-container"></div>';

var jstpl_powerCard = `<div class="power-card power-\${id}">
	<div class="power-card-overlay"></div>
	<div class="power-card-name">\${name}</div>
	<div class="power-card-power"></div>
</div>`;

</script>

{OVERALL_GAME_FOOTER}
