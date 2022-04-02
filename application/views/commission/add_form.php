<?php
if ($no_user == TRUE)
{
	echo "<h4>" . $page_title . "</h4>";
	echo "<p>Редактирование личности невозможно. <a href=\"javascript:history.go(-1)\">Вернуться назад</a></p>";
}
else
{

$rank = array(1 => 'Председатель', 2 => 'Член комиссии', 3 => 'Секретарь');
$fio = array(
	'name'	=> 'fio',
	'id'	=> 'fio',
	'value'	=> (!empty($person['fio']) ? $person['fio'] : set_value('fio')),
	'maxlength'	=> 255,
	'class' => 'validate[required]'
);
$post = array(
	'name'	=> 'post',
	'id'	=> 'post',
	'value'	=> (!empty($person['post']) ? $person['post'] : set_value('post')),
	'maxlength'	=> 255,
	'class' => 'validate[required]'
);
?>
<h4><?php echo $page_title; ?></h4>

<?php
if (!empty($notice))
{
	echo "<p>" . $notice . "</p>";
}
?>
<?php echo form_open("", array('id' => 'addperson-form'), array('person_id' => (!empty($person_id) ? $person_id : 0) )); ?>
	<table class="reg">
		<tr>
			<td></td>
			<td>
				<span class="red">* обязательные поля</span>
			</td>
		</tr>
		<tr>
			<td class="td_left"><?php echo form_label('Ранг', 'rank'); ?>:</td>
			<td>
				<?php echo form_dropdown('rank', $rank, (!empty($person['rank']) ? $person['rank'] : 2), "id='rank'"); ?>
			</td>
		</tr>
		<tr>
			<td class="td_left"><?php echo form_label('ФИО', $fio['id']); ?><span class="red">*</span>:</td>
			<td>
				<?php echo form_input($fio); ?>
				<?php echo form_error($fio['name']); ?><?php echo isset($errors[$fio['name']])?$errors[$fio['name']]:''; ?>
			</td>
		</tr>
		<tr>
			<td class="td_left"><?php echo form_label('Должность', $post['id']); ?><span class="red">*</span>:</td>
			<td>
				<?php echo form_input($post); ?>
				<?php echo form_error($post['name']); ?><?php echo isset($errors[$post['name']])?$errors[$post['name']]:''; ?>
			</td>
		</tr>
	</table>

	<p style="margin: 0; padding: 10px 0 0 0;"><?php echo form_submit('save', 'Сохранить', 'class="button"'); ?>&nbsp;<?php echo form_reset('cancel', 'Отмена', 'class="button"'); ?></p>
<?php echo form_close(); ?>

<?php
} // if ($no_user == TRUE)
?>