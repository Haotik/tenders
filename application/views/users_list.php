	<h4><?php echo $page_title; ?></h4>
<!-- <?var_dump($data)?> -->
	<table class="reg reg-options">
		<tr bgcolor='#FF5E5E'>
			<th>Имя</th>
			<th>E-mail</th>
			<th>Роль</th>
            <th>Статус</th>
			<th>Действия</th>
		</tr>
<?php
	if (!empty($users_list))
	{
		foreach ($users_list as $key => $value) {
			echo "		<tr bgcolor='" . (($key % 2) ? '#E2DFDF' : '#F1EBEB') . "' id=\"row" . $value['user_id'] . "\">
			<td>" . anchor('/auth/user_edit/' . $value['user_id'] . '/', $value['name']) . "</td>
			<td>" . $value['email'] . "</td>
			<td>" . $value['group_title'] . "</td>
			<td>" . ($value['banned'] == 1 ? 'Заблокирован' : ($value['activated'] == 1 ? 'Активный' : 'Неактивный')) . "</td>
			<td><a href=\"/auth/user_edit/" . $value['user_id'] . "/\" class=\"button-edit\" title=\"Редактировать профиль\"></a><a href=\"\" class=\"button-delete\" title=\"Удалить профиль\" onclick=\"noty({ animateOpen: {opacity: 'show'}, animateClose: {opacity: 'hide'}, layout: 'center', text: 'Вы действительно хотите удалить?', buttons: [ {type: 'btn btn-mini btn-primary', text: 'Удалить', click: function(\$noty) { \$noty.close(); $.DeleteItem(" . $value['user_id'] . "); } }, {type: 'btn btn-mini btn-danger', text: 'Отмена', click: function(\$noty) { \$noty.close(); } } ], closable: false, timeout: false }); return false;\"></a></td>
		</tr>\n";
		}
	}
?>
	</table>
