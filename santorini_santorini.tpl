{OVERALL_GAME_HEADER}

<div id="play-area-scaler">
	<div id="power-select-container">
		<div id="grid-powers">
			<div class="power-section">
				<div class="power-section-title">Selected Powers:</div>
				<div id="power-select-selected" class="power-section-cards"></div>
			</div>
			<div class="power-section">
				<div class="power-section-title">Available Powers:</div>
				<div id="power-select-available" class="power-section-cards"></div>
			</div>
		</div>
		<div id="grid-detail"></div>
	</div>
	<div id="power-choose-container"></div>
	<div id="play-area">
		<div id="prompt-container"></div>
		<div id="token-container"></div>
		<div id="scene-container"></div>
	</div>
</div>

<script type="text/javascript">
const URL = dojoConfig.packages.reduce((r,p) => p.name == "bgagame" ? p.location : r, null);
document.write('<script src="' + URL + '/scripts/board.js" type="module"><\/script>');

var jstpl_powerSelect = `<div id="power-select-\${id}" class="power-card power-\${id} \${type} small" data-power="\${id}">
	<div class="power-name">\${name}</div>
</div>`;

var jstpl_powerContainer = '<div id="power_container_${id}" class="power-container"></div>';

var jstpl_miniCard = `<div class="mini-card power-\${id} \${type}" data-power="\${id}">
	<div class="power-avatar"></div>
	<div class="power-pictogram"></div>
</div>`;

var jstpl_powerDetail = `<div class="power-detail">
	<div class="power-card power-\${id} \${type}">
		<div class="power-name">\${name}</div>
		<div class="power-pictogram"></div>
	</div>
	<div class="power-info \${type}">
		<div class="power-name">\${name}</div>
		<div class="power-title">\${title}</div>
		<ul class="power-text"><li>\${textList}</li></ul>
	</div>
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
