<?php
$email = array(
	'name'	=> 'email',
	'id'	=> 'email',
	'value' => set_value('email'),
	'maxlength'	=> 80,
	'class' => 'validate[required,custom[email]]'
);
?>
<h4><?php echo $page_title; ?></h4>
<?php echo isset($errors[$email['name']]) ? "<p><span style=\"color: red; font-weight: bold;\">" . $errors[$email['name']] . "</span></p>" : ''; ?>
<p>Для восстановления пароля введите, пожалуста E-mail, указанный при регистрации.</p>
<?php echo form_open($this->uri->uri_string(), array('id' => 'forgot-form')); ?>
<table class="reg">
	<tr>
		<td class="td_left"><?php echo form_label('E-mail:', $email['id']); ?></td>
		<td class="td_right">
			<?php echo form_input($email); ?>
		</td>
	</tr>
	<tr>
		<td colspan=2>
			<?php echo form_submit('save', 'Напомнить', 'class="button"'); ?>&nbsp;<?php echo form_reset('cancel', 'Отмена', 'class="button"'); ?>
		</td>
	</tr>
</table>
<?php echo form_close(); ?>
