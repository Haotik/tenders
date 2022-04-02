<?php
if ($no_tender == TRUE)
{
	echo "<h4>" . $page_title . "</h4>";
	echo "<p>Просмотр истории аукциона невозможен. <a href=\"javascript:window.close();\">Закрыть окно</a></p>";
}
else
{
?>
	<h4><?php echo $page_title; ?><a href="javascript:window.close();" style="float: right;">Закрыть</a></h4>
	<div class="clear"></div>
	<?php echo ( $game_tender == TRUE ? "<p><span class=\"red\">Аукцион закончен</span></p>" : "" ); ?>
    <p class=""><a href="/tenders/export_lot_history/<?= $tender_id; ?>">Скачать отчет</a></p>
	<table class="reg tablesorter" id="options_show">
		<thead>
			<tr>
				<th>Наименование лота</th>
				<th>Время ставки</th>
				<th>Цена участника</th>
				<th>Участник</th>
                <th>Комментарий</th>
			</tr>
		</thead>
		<tbody>
<?php
if ( !empty($tender_results_lotes_history) && (($group_id == 2 && $tender_author == TRUE) || $group_id == 3) )
{
	// Показываем результаты
	foreach ($tender_results_lotes_history as $key => $value) {
		echo "			<tr class='".(($value['is_deleted'] == 1) ? "is_deleted" : "")."'>
				<td>" . $value['lote_name'] . "</td>
				<td>" . $value['created'] . "</td>
				<td>" . $value['value'] . (!empty($tender_lotes_users[$value['user_id']][$value['lote_id']]) && $tender_lotes_users[$value['user_id']][$value['lote_id']] == $value['value'] ? " (<a href=\"#\" onclick=\"$.ResetLot('" . $value['lote_id'] . "_" . $value['user_id'] . "_" . $value['tender_id'] . "'); return false;\">отменить</a>)" : "") . "</td>
				<td>" . $value['member_name'] . "</td>
				<td width=\"300\">" . (($value['is_deleted'] == 1) ? $value['comment'] : "") . "</td>
			</tr>";
	} // foreach ($tender_results_lotes_history as $key => $value) {
} // if ( !empty($tender_results_lotes_history) && (($group_id == 2 && $tender_author == TRUE) || $group_id == 3) )
else
{
	echo "<tr><td colspan=\"4\" style=\"text-align: center;\">Участники еще не поставили ни одну ставку.</tr>\n";
}
?>
		</tbody>
	</table>
<?php
} // if ($no_tender == TRUE)
