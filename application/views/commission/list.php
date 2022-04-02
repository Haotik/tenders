	<h4><?php echo $page_title; ?></h4>

	<table class="reg reg-options">
		<tr bgcolor = '#FF5E5E'>
			<th>Номер</th>
			<th>Администратор</th>
			<th>ФИО</th>
			<th>Должность</th>
			<th>Ранг в комиссии</th>
			<th>Действия</th>
		</tr>
<?php
	if (!empty($commission_list))
	{
		foreach ($commission_list as $key => $value) {
			echo "		<tr bgcolor='" . (($key % 2) ? '#E2DFDF' : '#F1EBEB') . "' id=\"user_" . $value['id'] . "\">
			<td>" . $value['id'] . "</td>
			<td>" . $value['author_name'] . "</td>
			<td>" . anchor('/commission/edit_user/' . $value['id'] . '/', $value['fio']) . "</td>
			<td>" . $value['post'] . "</td>
			<td>" . ($value['rank'] == 1 ? "Председатель" : ($value['rank'] == 2 ? "Член комиссии" : "Секретарь") ) . "</td>\n";

			echo "			<td>\n";
			echo anchor('/commission/edit_user/' . $value['id'] . '/', " ", "class=\"button-edit\" title=\"Редактировать личность\"");
			echo "<a href=\"\" class=\"button-delete\" title=\"Удалить личность\" onclick=\"noty({ animateOpen: {opacity: 'show'}, animateClose: {opacity: 'hide'}, layout: 'center', text: 'Вы действительно хотите удалить личность из комиссии?', buttons: [ {type: 'btn btn-mini btn-primary', text: 'Удалить', click: function(\$noty) { \$noty.close(); $.DeleteUser(" . $value['id'] . "); } }, {type: 'btn btn-mini btn-danger', text: 'Отмена', click: function(\$noty) { \$noty.close(); } } ], closable: false, timeout: false }); return false;\"></a>";
			echo "			</td>\n";
			echo "		</tr>\n";
		}
	}
	else
		echo "		<tr bgcolor='#F1EBEB'>
			<td colspan=\"6\">По заданным условиям соответствующих записей нет.</td>
		</tr>\n";
?>
	</table>
