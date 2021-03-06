{OVERALL_GAME_HEADER}

<div id="play-area-scaler">
	<a id="browser-error" href="#" target="_blank"></a>
	<div id="play-area">
		<div id="prompt-container"></div>
		<div id="token-container"></div>
		<div id="scene-container"></div>
	</div>
	<div id="power-offer-container">
		<div id="grid-powers">
			<div id="section-offer" class="power-section">
				<p id="title-offer"></p>
				<div id="cards-offer" class="card-container"></div>
			</div>
			<div id="section-deck" class="power-section">
				<p id="title-deck"></p>
				<div id="cards-deck" class="card-container"></div>
			</div>
		</div>
		<div id="grid-detail"></div>
	</div>
	<div id="power-choose-container"></div>
	<div id="left-cloud" class="zoomed"></div>
	<div id="right-cloud" class="zoomed"></div>
</div>

<script type="text/javascript">
const URL = dojoConfig.packages.reduce((r,p) => p.name == "bgagame" ? p.location : r, null);
document.write('<script src="' + URL + '/modules/scripts/board.js" type="module"><\/script>');

var jstpl_resetCamera = `<div class="preference_choice">
	<div class="row-data row-data-large">
		<div class="row-label">\${camera}</div>
		<div class="row-value"><a href="#" class="bgabutton bgabutton_blue buttonResetCamera" onclick="return false;">\${reset}</a></div>
	</div>
</div>`;

var jstpl_powerSmall = `<div id="power-small-\${id}" class="power-card power-\${id} \${type} small" data-power="\${id}" data-sort="\${sort}">
	<div class="power-name">\${name}</div>
</div>`;

var jstpl_powerContainer = '<div id="power_container_${id}" class="power-container"></div>';

var jstpl_miniCard = `<div class="mini-card power-\${id} \${type}" data-power="\${id}">
	<div class="power-name">\${name}</div>
	<div class="power-avatar"></div>
	<div class="power-pictogram"></div>
	<div class="power-counter">\${counter}</div>
</div>`;

var jstpl_powerDetail = `<div class="power-detail">
	<div class="power-card power-\${id} \${type}">
		<div class="power-playerCount" title="\${tooltipPlayerCount}">\${playerCount}</div>
		<div class="power-golden-\${golden}" title="\${tooltipGolden}"></div>
		<div class="power-name">\${name}</div>
		<div class="power-title">\${title}</div>
		<div class="power-pictogram"></div>
	</div>
	<div class="power-ext \${type}">\${text}</div>
</div>`;


var jstpl_token = '<div id="token-\${token}" class="token token-\${token}"></div>';
var jstpl_tokenPrompt = `<table class="token-prompt">
	<tr>
		<td><div class="token token-\${token} token-choose-rotation rotate-8" data-rotation="8"></div></td>
		<td><div class="token token-\${token} token-choose-rotation rotate-1"  data-rotation="1"></div></td>
		<td><div class="token token-\${token} token-choose-rotation rotate-2"  data-rotation="2"></div></td>
	</tr>
	<tr>
		<td><div class="token token-\${token} token-choose-rotation rotate-7" data-rotation="7"></div></td>
		<td>&nbsp;</td>
		<td><div class="token token-\${token} token-choose-rotation rotate-3" data-rotation="3"></div></td>
	</tr>
	<tr>
		<td><div class="token token-\${token} token-choose-rotation rotate-6" data-rotation="6"></div></td>
		<td><div class="token token-\${token} token-choose-rotation rotate-5" data-rotation="5"></div></td>
		<td><div class="token token-\${token} token-choose-rotation rotate-4" data-rotation="4"></div></td>
	</tr>
</table>`;


var jstpl_argPrompt = `<div class="arg-prompt arg-\${arg}"></div>`;

</script>

{OVERALL_GAME_FOOTER}
