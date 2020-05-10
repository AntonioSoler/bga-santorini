{OVERALL_GAME_HEADER}
<div id="play-area-scaler">
	<div id="power-select-container">
		<div id="grid-powers"></div>
		<div id="grid-detail"></div>
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

var jstpl_miniCard = `<div class="mini-card power-\${id}">
	<div class="power-avatar"></div>
	<div class="power-pictogram"></div>
</div>`;

var jstpl_powerDetail = `<div class="power-detail">
	<div class="power-card power-\${id}">
		<div class="power-name">\${name}</div>
		<div class="power-pictogram"></div>
	</div>
	<div class="power-info">
		<div class="power-name">\${name}</div>
		<div class="power-title">\${title}</div>
		<ul class="power-text"><li>\${textList}</li></ul>
	</div>
</div>`;

</script>

{OVERALL_GAME_FOOTER}
