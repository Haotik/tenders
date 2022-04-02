<?php

$autorefresh = array(
	'name'	=> 'autorefresh',
	'id'	=> 'autorefresh',
	'value'	=> (!empty($setting['autorefresh']) ? $setting['autorefresh'] : set_value('autorefresh')),
	'maxlength'	=> 4
);

$monthsarchive = array(
	'name'	=> 'monthsarchive',
	'id'	=> 'monthsarchive',
	'value'	=> (!empty($setting['monthsarchive']) ? $setting['monthsarchive'] : set_value('monthsarchive')),
	'maxlength'	=> 4
);

$email_newuser = array(
	'name'	=> 'email-newuser',
	'id'	=> 'email-newuser',
	'value'	=> (!empty($setting['email-newuser']) ? $setting['email-newuser'] : set_value('email-newuser')),
	'rows'	=> 10
);

$email_welcomeuser = array(
	'name'	=> 'email-welcomeuser',
	'id'	=> 'email-welcomeuser',
	'value'	=> (!empty($setting['email-welcomeuser']) ? $setting['email-welcomeuser'] : set_value('email-welcomeuser')),
	'rows'	=> 10
);

$email_banneduser = array(
	'name'	=> 'email-banneduser',
	'id'	=> 'email-banneduser',
	'value'	=> (!empty($setting['email-banneduser']) ? $setting['email-banneduser'] : set_value('email-banneduser')),
	'rows'	=> 10
);

$email_forgotpassword = array(
	'name'	=> 'email-forgotpassword',
	'id'	=> 'email-forgotpassword',
	'value'	=> (!empty($setting['email-forgotpassword']) ? $setting['email-forgotpassword'] : set_value('email-forgotpassword')),
	'rows'	=> 10
);

$email_newtender = array(
	'name'	=> 'email-newtender',
	'id'	=> 'email-newtender',
	'value'	=> (!empty($setting['email-newtender']) ? $setting['email-newtender'] : set_value('email-newtender')),
	'rows'	=> 10
);

$email_updatetender = array(
	'name'	=> 'email-updatetender',
	'id'	=> 'email-updatetender',
	'value'	=> (!empty($setting['email-updatetender']) ? $setting['email-updatetender'] : set_value('email-updatetender')),
	'rows'	=> 10
);

?>

<h4><?php echo $page_title; ?></h4>

<?php echo (!empty($message) ? '<p>' . $message . '</p>' : ''); ?>

<?php echo form_open("settings/save_settings", array('id' => 'settings-form')); ?>
	<table class="reg">
		<tr>
			<td class="td_left"><?php echo form_label('Период автоматического обновления страницы тендера', $autorefresh['id']); ?>:</td>
			<td>
				<?php echo form_input($autorefresh); ?> секунд
				<?php echo form_error($autorefresh['name']); ?><?php echo isset($errors[$autorefresh['name']])?$errors[$autorefresh['name']]:''; ?>
			</td>
		</tr>
		<tr>
			<td class="td_left"><?php echo form_label('Кол-во месяцев для переноса тендеров в архив', $monthsarchive['id']); ?>:</td>
			<td>
				<?php echo form_input($monthsarchive); ?>
				<?php echo form_error($monthsarchive['name']); ?><?php echo isset($errors[$monthsarchive['name']])?$errors[$monthsarchive['name']]:''; ?>
			</td>
		</tr>
		<tr>
			<td class="td_left"><?php echo form_label('Текст письма для администратора о новом пользователе', $email_newuser['id']); ?>:</td>
			<td>
				<?php echo form_textarea($email_newuser); ?>
				<?php echo form_error($email_newuser['name']); ?><?php echo isset($errors[$email_newuser['name']])?$errors[$email_newuser['name']]:''; ?>
			</td>
		</tr>
		<tr>
			<td class="td_left"><?php echo form_label('Текст письма приглашения нового пользователя', $email_welcomeuser['id']); ?>:</td>
			<td>
				<?php echo form_textarea($email_welcomeuser); ?>
				<?php echo form_error($email_welcomeuser['name']); ?><?php echo isset($errors[$email_welcomeuser['name']])?$errors[$email_welcomeuser['name']]:''; ?>
			</td>
		</tr>
		<tr>
			<td class="td_left"><?php echo form_label('Текст письма уведомления о блокировке пользователя', $email_banneduser['id']); ?>:</td>
			<td>
				<?php echo form_textarea($email_banneduser); ?>
				<?php echo form_error($email_banneduser['name']); ?><?php echo isset($errors[$email_banneduser['name']])?$errors[$email_banneduser['name']]:''; ?>
			</td>
		</tr>
		<tr>
			<td class="td_left"><?php echo form_label('Текст письма с напоминанием пароля пользователю', $email_forgotpassword['id']); ?>:</td>
			<td>
				<?php echo form_textarea($email_forgotpassword); ?>
				<?php echo form_error($email_forgotpassword['name']); ?><?php echo isset($errors[$email_forgotpassword['name']])?$errors[$email_forgotpassword['name']]:''; ?>
			</td>
		</tr>
		<tr>
			<td class="td_left"><?php echo form_label('Текст письма уведомления о новом тендере', $email_newtender['id']); ?>:</td>
			<td>
				<?php echo form_textarea($email_newtender); ?>
				<?php echo form_error($email_newtender['name']); ?><?php echo isset($errors[$email_newtender['name']])?$errors[$email_newtender['name']]:''; ?>
			</td>
		</tr>
		<tr>
			<td class="td_left"><?php echo form_label('Текст письма уведомления об изменениях ставок', $email_updatetender['id']); ?>:</td>
			<td>
				<?php echo form_textarea($email_updatetender); ?>
				<?php echo form_error($email_updatetender['name']); ?><?php echo isset($errors[$email_updatetender['name']])?$errors[$email_updatetender['name']]:''; ?>
			</td>
		</tr>
		<tr>
			<td class="td_left"><?php echo form_label('Предопреденные переменные для писем'); ?>:</td>
			<td>
				<p><strong>%user%</strong> — Название пользователя</p>
				<p><strong>%email_user%</strong> — Адрес электронной почты пользователя</p>
				<p><strong>%pass_user%</strong> — Пароль пользователя</p>
				<p><strong>%url_user%</strong> — Полный адрес страницы в личный кабинет пользователя</p>
				<p><strong>%url_site%</strong> — Полный адрес сайта</p>
				<p><strong>%tender_name%</strong> — Название тендера</p>
				<p><strong>%tender_date_start%</strong> — Дата и время начала тендера</p>
				<p><strong>%tender_date_end%</strong> — Дата и время окончания тендера</p>
				<p><strong>%url_tender%</strong> — Полный адрес страницы просмотра деталей тендера</p>
			</td>
		</tr>
	</table>

	<p style="margin: 0; padding: 10px 0 0 350px;"><?php echo form_submit('save', 'Сохранить', 'class="button"'); ?>&nbsp;<?php echo form_reset('cancel', 'Отмена', 'class="button"'); ?></p>
<?php echo form_close(); ?>