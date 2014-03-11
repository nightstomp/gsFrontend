{*
 * gsFrontend
 * Copyright (C) 2011 Gedankengut GbR Häuser & Sirin <support@gsales.de>
 * 
 * This file is part of gsFrontend.
 * 
 * gsFrontend is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * gsFrontend is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with gsFrontend. If not, see <http://www.gnu.org/licenses/>.
 *}
<h1>Angebot <a href="{felink controller="offer" action="pdf"}/{$offer->getId()}" class="">{$offer->getOfferNo()}</a> annehmen</h1>

<table class="half">
	<tr>
		<td><strong>Angebotsdatum:</strong></td>
		<td> {$offer->getCreated()|date_format:"%d.%m.%Y"}</td>
	</tr>
	<tr class="altRow">
		<td><strong>Angebot noch gültig bis:</strong></td>
		<td>{$offer->getValidUntil()|date_format:"%d.%m.%Y"}</td>
	</tr>
	<tr>
		<td><strong>Angebotsnummer:</strong></td>
		<td>{$offer->getOfferNo()}</td>
	</tr>
	{if $offer->getDunningFee()}
	<tr class="altRow">
		<td><strong>Rechnungsbetrag:</strong></td>
		<td>{$offer->getFormatedRoundedAmountWithSymbol()}</td>
	</tr>
	<tr>
		<td><strong>zzgl. Mahngebühren:</strong></td>
		<td>{$offer->getFormatedDunningFeeWithSymbol()}</td>
	</tr>
	{/if}
</table>

<h2>Angebotsbetrag: {$offer->getFormatedRoundOpenAmountWithSymbol()}</h2>

{if strtotime($offer->getValidUntil()) > time()}
	<p><br /><strong>Bestätigung</strong></p>
	<p>
		Mit der Angebotsannahme akzeptieren Sie automatisch unsere <a href="{$smarty.const.LINK_AGB}" target="_blank">AGB.</a>
		<br/>
		Bitte beachten Sie, dass ein Widerruf ausgeschlossen ist.<br/><br/>

		<a href="{felink controller="offer" action="acceptnow"}/{$offer->getId()}" class="button big">Dieses Angebot jetzt verbindlich annehmen.</a>
	</p>

{else}
	<p>Das Angebot ist leider nicht mehr gültig und kann online nicht angenommen werden.<br>Bitte setzen Sie sich mit uns in Verbindung.</p>
{/if}

<p><br /><br /><a href="{felink controller="offer" action="index"}" class="button">zurück zur Übersicht</a></p>