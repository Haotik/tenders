<?php 
/*
echo "<pre>";
var_dump($user_profile);
echo "</pre>";
*/

if ((int)$current_groupe_id == 3) {
    $group = array(1 => 'Участник торгов', 2 => 'Администратор торгов', 3 => 'Главный администратор', 4 => 'Менеджер пользователей', 5 => "Администратор торгов ИТ", 6 => "Участник торгов ИТ", 7 => "Аудитор торгов ИТ");
}
if (!empty($user_profile) && ((int)$group_id == 3 || (int)$group_id == 4)) {
    $activated = array(
        'name' => 'activated',
        'id' => 'activated',
        'value' => 1,
        'checked' => ((!empty($user_profile['activated']) && $user_profile['activated'] == 1) ? "checked" : set_value('activated'))
    );
    $banned = array(
        'name' => 'banned',
        'id' => 'banned',
        'value' => 1,
        'checked' => ((!empty($user_profile['banned']) && $user_profile['banned'] == 1) ? "checked" : set_value('banned'))
    );
    $ban_reason = array(
        'name' => 'ban_reason',
        'id' => 'ban_reason',
        'value' => (!empty($user_profile['ban_reason']) ? $user_profile['ban_reason'] : set_value('ban_reason')),
        'rows' => 5
    );
}
$name = array(
    'name' => 'name',
    'id' => 'name',
    'value' => (!empty($user_profile['name']) ? $user_profile['name'] : set_value('name')),
    'maxlength' => 255,
    'class' => 'validate[required]'
);
$type_data = array('Юридическое лицо' => 'Юридическое лицо', 'Физическое лицо' => 'Физическое лицо');
$legal_form = array(
    'name' => 'legal_form',
    'id' => 'legal_form',
    'value' => (!empty($user_profile['legal_form']) ? $user_profile['legal_form'] : set_value('legal_form')),
    'maxlength' => 255,
    'class' => 'validate[required]'
);
$small_business = array(
    'name' => 'small_business',
    'id' => 'small_business',
    'value' => 1,
    'checked' => ((!empty($user_profile['small_business']) && $user_profile['small_business'] == 1) ? "checked" : set_value('small_business'))
);
$region = array(
    'name' => 'region',
    'id' => 'region',
    'value' => (!empty($user_profile['region']) ? $user_profile['region'] : set_value('region')),
    'maxlength' => 255,
    'class' => 'validate[required]'
);
$city = array(
    'name' => 'city',
    'id' => 'city',
    'value' => (!empty($user_profile['city']) ? $user_profile['city'] : set_value('city')),
    'maxlength' => 255,
    'class' => 'validate[required]'
);
$address = array(
    'name' => 'address',
    'id' => 'address',
    'value' => (!empty($user_profile['address']) ? $user_profile['address'] : set_value('address')),
    'rows' => 5
);
$fact_address = array(
    'name' => 'fact_address',
    'id' => 'fact_address',
    'value' => (!empty($user_profile['fact_address']) ? $user_profile['fact_address'] : set_value('fact_address')),
    'rows' => 5
);
$phone = array(
    'name' => 'phone',
    'id' => 'phone',
    'value' => (!empty($user_profile['phone']) ? $user_profile['phone'] : set_value('phone')),
    'maxlength' => 255
);
$fax = array(
    'name' => 'fax',
    'id' => 'fax',
    'value' => (!empty($user_profile['fax']) ? $user_profile['fax'] : set_value('fax')),
    'maxlength' => 255
);
$email = array(
    'name' => 'email',
    'id' => 'email',
    'value' => (!empty($user_profile['email']) ? $user_profile['email'] : set_value('email')),
    'maxlength' => 80,
    'class' => 'validate[required,custom[email]]'
);
$add_email = array(
    'name' => 'add_email',
    'id' => 'add_email',
    'value' => (!empty($user_profile['add_email']) ? $user_profile['add_email'] : set_value('add_email')),
    'maxlength' => 160,
);
$director_name = array(
    'name' => 'director_name',
    'id' => 'director_name',
    'value' => (!empty($user_profile['director_name']) ? $user_profile['director_name'] : set_value('director_name')),
    'maxlength' => 100,
    'class' => 'validate[required]'
);
$contacts = array(
    'name' => 'contacts',
    'id' => 'contacts',
    'value' => (!empty($user_profile['contacts']) ? $user_profile['contacts'] : set_value('contacts')),
    'rows' => 5
);
$certifikates = array(
    'name' => 'certifikates',
    'id' => 'certifikates',
    'value' => (!empty($user_profile['certifikates']) ? $user_profile['certifikates'] : set_value('certifikates')),
    'rows' => 5
);
$organization_date = array(
    'name' => 'organization_date',
    'id' => 'organization_date',
    'value' => (!empty($user_profile['organization_date']) ? $user_profile['organization_date'] : set_value('organization_date')),
    'maxlength' => 10,
    'class' => 'validate[custom[date]]'
);
$inn = array(
    'name' => 'inn',
    'id' => 'inn',
    'value' => (!empty($user_profile['inn']) ? $user_profile['inn'] : set_value('inn')),
    'maxlength' => 12,
    'class' => 'validate[required,custom[integer]]'
);
$kpp = array(
    'name' => 'kpp',
    'id' => 'kpp',
    'value' => (!empty($user_profile['kpp']) ? $user_profile['kpp'] : set_value('kpp')),
    'maxlength' => 12,
    'class' => 'validate[required,custom[integer]]'
);
$employes_count = array(
    'name' => 'employes_count',
    'id' => 'employes_count',
    'value' => (!empty($user_profile['employes_count']) ? $user_profile['employes_count'] : set_value('employes_count')),
    'maxlength' => 4,
    'class' => 'validate[custom[integer]]'
);
$okved = array(
    'name' => 'okved',
    'id' => 'okved',
    'value' => (!empty($user_profile['okved']) ? $user_profile['okved'] : set_value('okved')),
    'maxlength' => 8
);
$services = array(
    'name' => 'services',
    'id' => 'services',
    'value' => (!empty($user_profile['services']) ? $user_profile['services'] : set_value('services')),
    'rows' => 5
);
$requisites = array(
    'name' => 'requisites',
    'id' => 'requisites',
    'value' => (!empty($user_profile['requisites']) ? $user_profile['requisites'] : set_value('requisites')),
    'rows' => 5
);
$notice_other_members = array(
    'name' => 'notice_other_members',
    'id' => 'notice_other_members',
    'value' => 1,
    'checked' => ((!empty($user_profile['notice_other_members']) && $user_profile['notice_other_members'] == 1) ? "checked" : set_value('notice_other_members'))
);
$notice_new_auctions = array(
    'name' => 'notice_new_auctions',
    'id' => 'notice_new_auctions',
    'value' => 1,
    'checked' => ((!empty($user_profile['notice_new_auctions']) && $user_profile['notice_new_auctions'] == 1) ? "checked" : set_value('notice_new_auctions'))
);
$password = array(
    'name' => 'register_password',
    'id' => 'register_password',
    'value' => set_value('register_password'),
    'maxlength' => $this->config->item('password_max_length', 'tank_auth'),
    'class' => (empty($user_profile) ? 'validate[required]' : '')
);
$confirm_password = array(
    'name' => 'confirm_password',
    'id' => 'confirm_password',
    'value' => set_value('confirm_password'),
    'maxlength' => $this->config->item('password_max_length', 'tank_auth'),
    'class' => (empty($user_profile) ? 'validate[required,equals[register_password]]' : '')
);
/*new notifications*/
$notice_disable = array(
    'name' => 'notice_disable',
    'id' => 'notice_disable',
    'value' => 1,
    'checked' => ((!empty($user_profile['notice_disable']) && $user_profile['notice_disable'] == 1) ? "checked" : set_value('notice_disable'))
);
$notice_day_before_start = array(
    'name' => 'notice_day_before_start',
    'id' => 'notice_day_before_start',
    'value' => 1,
    'checked' => ((!empty($user_profile['notice_day_before_start']) && $user_profile['notice_day_before_start'] == 1) ? "checked" : set_value('notice_day_before_start'))
);
$notice_hour_before_start = array(
    'name' => 'notice_hour_before_start',
    'id' => 'notice_hour_before_start',
    'value' => 1,
    'checked' => ((!empty($user_profile['notice_hour_before_start']) && $user_profile['notice_hour_before_start'] == 1) ? "checked" : set_value('notice_hour_before_start'))
);
$notice_day_before_end = array(
    'name' => 'notice_day_before_end',
    'id' => 'notice_day_before_end',
    'value' => 1,
    'checked' => ((!empty($user_profile['notice_day_before_end']) && $user_profile['notice_day_before_end'] == 1) ? "checked" : set_value('notice_day_before_end'))
);
$notice_hour_before_end = array(
    'name' => 'notice_hour_before_end',
    'id' => 'notice_hour_before_end',
    'value' => 1,
    'checked' => ((!empty($user_profile['notice_hour_before_end']) && $user_profile['notice_hour_before_end'] == 1) ? "checked" : set_value('notice_hour_before_end'))
);

$select_all_tags = array(
    'name' => 'select_all_tags',
    'id' => 'select_all_tags',
    'value' => 1,
    'checked' => ((!empty($user_profile['select_all_tags']) && $user_profile['select_all_tags'] == 1) ? "checked" : set_value('select_all_tags'))
);
$notice_new_purchases = array(
    'name' => 'notice_new_purchases',
    'id' => 'notice_new_purchases',
    'value' => 1,
    'checked' => ((!empty($user_profile['notice_new_purchases']) && $user_profile['notice_new_purchases'] == 1) ? "checked" : set_value('notice_new_purchases'))
);
$notice_purchases_day_before_start = array(
    'name' => 'notice_purchases_day_before_start',
    'id' => 'notice_purchases_day_before_start',
    'value' => 1,
    'checked' => ((!empty($user_profile['notice_purchases_day_before_start']) && $user_profile['notice_purchases_day_before_start'] == 1) ? "checked" : set_value('notice_purchases_day_before_start'))
);
$notice_purchases_day_before_end = array(
    'name' => 'notice_purchases_day_before_end',
    'id' => 'notice_purchases_day_before_end',
    'value' => 1,
    'checked' => ((!empty($user_profile['notice_purchases_day_before_end']) && $user_profile['notice_purchases_day_before_end'] == 1) ? "checked" : set_value('notice_purchases_day_before_end'))
);

?>
<h4><?php echo $page_title;?></h4>

<?php
if (!empty($notice) && $group_id == 3) {
    echo "<p>" . $notice . "</p>";
}
?>
<?php echo form_open($this->uri->uri_string(), array('id' => 'register-form')); ?>
<table class="reg">
    <?php
    if ($current_groupe_id == 3) {
            ?>
            <tr>
                <td class="td_left"><?php echo form_label('Роль пользователя', 'group'); ?>:</td>
                <td>
                    <?php echo form_dropdown('group', $group, (!empty($user_profile['group_id']) ? $user_profile['group_id'] : 1)); ?>
                </td>
            </tr>
        <?php
    }
    ?>
    <tr>
        <td class="td_left"><?php echo form_label('Наименование организации (ФИО)', $name['id']); ?><span
                    class="red">*</span>:
        </td>
        <td>
            <?php echo form_input($name); ?>
            <?php echo form_error($name['name']); ?><?php echo isset($errors[$name['name']]) ? $errors[$name['name']] : ''; ?>
        </td>
    </tr>
    <tr>
        <td class="td_left"><?php echo form_label('Лицо', 'type'); ?>:</td>
        <td>
            <?php echo form_dropdown('type', $type_data, (!empty($user_profile['type_data']) ? $user_profile['type_data'] : 'Юридическое лицо')); ?>
        </td>
    </tr>
    <tr>
        <td class="td_left"><?php echo form_label('Организационно-правовая форма', $legal_form['id']); ?><span
                    class="red">*</span>:
        </td>
        <td>
            <?php echo form_input($legal_form); ?>
            <?php echo form_error($legal_form['name']); ?><?php echo isset($errors[$legal_form['name']]) ? $errors[$legal_form['name']] : ''; ?>
        </td>
    </tr>
    <tr>
        <td class="td_left"><?php echo form_label('Субъект малого предпринимательства', $small_business['id']); ?>:</td>
        <td>
            <?php echo form_checkbox($small_business); ?>
        </td>
    </tr>
    <tr>
        <td class="td_left"><?php echo form_label('Регион', $region['id']); ?><span class="red">*</span>:</td>
        <td>
            <?php echo form_input($region); ?>
            <?php echo form_error($region['name']); ?><?php echo isset($errors[$region['name']]) ? $errors[$region['name']] : ''; ?>
        </td>
    </tr>
    <tr>
        <td class="td_left"><?php echo form_label('Населенный пункт', $city['id']); ?><span class="red">*</span>:</td>
        <td>
            <?php echo form_input($city); ?>
            <?php echo form_error($city['name']); ?><?php echo isset($errors[$city['name']]) ? $errors[$city['name']] : ''; ?>
        </td>
    </tr>
    <tr>
        <td class="td_left"><?php echo form_label('Адрес', $address['id']); ?><span class="red">*</span>:</td>
        <td>
            <?php echo form_textarea($address); ?>
        </td>
    </tr>
    <tr>
        <td class="td_left"><?php echo form_label('Фактический адрес поставщика', $fact_address['id']); ?><span
                    class="red">*</span>:
        </td>
        <td>
            <?php echo form_textarea($fact_address); ?>
        </td>
    </tr>
    <tr>
        <td class="td_left"><?php echo form_label('Телефон', $phone['id']); ?><span class="red">*</span>:</td>
        <td>
            <?php echo form_input($phone); ?>
        </td>
    </tr>
    <tr>
        <td class="td_left"><?php echo form_label('Факс', $fax['id']); ?>:</td>
        <td>
            <?php echo form_input($fax); ?>
        </td>
    </tr>
    <tr>
        <td class="td_left"><?php echo form_label('E-mail', $email['id']); ?><span class="red">*</span>:</td>
        <td>
            <?php
            if (empty($user_profile)) {
                echo form_input($email);
                echo form_error($email['name']); ?><?php echo isset($errors[$email['name']]) ? $errors[$email['name']] : '';
            } else
                echo $user_profile['email'];
            ?>
        </td>
    </tr>
    <tr>
        <td class="td_left"><?php echo form_label('Доплнительный E-mail', $add_email['id']); ?>:</td>
        <td>
            <?php
            if (empty($user_profile)) {
                echo form_textarea($add_email);
                echo form_error($add_email['name']); ?><?php echo isset($errors[$add_email['name']]) ? $errors[$add_email['name']] : '';
            } else
                echo form_textarea($add_email);
            ?>
        </td>
    </tr>
    <tr>
        <td class="td_left"><?php echo form_label('Пароль', $password['id']); ?><?php echo(empty($user_profile) ? '<span class="red">*</span>' : ""); ?>
            :
        </td>
        <td>
            <?php echo form_password($password); ?>
            <?php echo form_error($password['name']); ?><?php echo isset($errors[$password['name']]) ? $errors[$password['name']] : ''; ?>
        </td>
    </tr>
    <tr>
        <td class="td_left"><?php echo form_label('Еще раз Пароль', $confirm_password['id']); ?><?php echo(empty($user_profile) ? '<span class="red">*</span>' : ""); ?>
            :
        </td>
        <td>
            <?php echo form_password($confirm_password); ?>
            <?php echo form_error($confirm_password['name']); ?><?php echo isset($errors[$confirm_password['name']]) ? $errors[$confirm_password['name']] : ''; ?>
        </td>
    </tr>
    <tr>
        <td class="td_left"><?php echo form_label('ФИО руководителя предприятия', $director_name['id']); ?><span
                    class="red">*</span>:
        </td>
        <td>
            <?php echo form_input($director_name); ?>
            <?php echo form_error($director_name['name']); ?><?php echo isset($errors[$director_name['name']]) ? $errors[$director_name['name']] : ''; ?>
        </td>
    </tr>
    <tr>
        <td class="td_left"><?php echo form_label('Контактные лица', $contacts['id']); ?><span class="red">*</span>:
        </td>
        <td>
            <?php echo form_textarea($contacts); ?>
        </td>
    </tr>
    <tr>
        <td class="td_left"><?php echo form_label('Лицензии и сертификаты', $certifikates['id']); ?>:</td>
        <td>
            <?php echo form_textarea($certifikates); ?>
        </td>
    </tr>
    <tr>
        <td class="td_left"><?php echo form_label('Дата основания организации', $organization_date['id']); ?>:</td>
        <td>
            <?php echo form_input($organization_date); ?>
        </td>
    </tr>
    <tr>
        <td class="td_left"><?php echo form_label('ИНН', $inn['id']); ?><span class="red">*</span>:</td>
        <td>
            <?php echo form_input($inn); ?>
            <?php echo form_error($inn['name']); ?><?php echo isset($errors[$inn['name']]) ? $errors[$inn['name']] : ''; ?>
        </td>
    </tr>
    <tr>
        <td class="td_left"><?php echo form_label('КПП', $kpp['id']); ?><span class="red">*</span>:</td>
        <td>
            <?php echo form_input($kpp); ?>
            <?php echo form_error($kpp['name']); ?><?php echo isset($errors[$kpp['name']]) ? $errors[$kpp['name']] : ''; ?>
        </td>
    </tr>
    <tr>
        <td class="td_left"><?php echo form_label('Количество работников', $employes_count['id']); ?>:</td>
        <td>
            <?php echo form_input($employes_count); ?>
        </td>
    </tr>
    <tr>
        <td class="td_left"><?php echo form_label('ОКВЭД', $okved['id']); ?>:</td>
        <td>
            <?php echo form_input($okved); ?>
        </td>
    </tr>
    <tr>
        <td class="td_left"><?php echo form_label('Виды предлагаемой продукции, работ, услуг', $services['id']); ?>:
        </td>
        <td>
            <?php echo form_textarea($services); ?>
        </td>
    </tr>
    <tr>
        <td class="td_left"><?php echo form_label('Реквизиты для включения в контракт', $requisites['id']); ?><span
                    class="red">*</span>:
        </td>
        <td>
            <?php echo form_textarea($requisites); ?>
        </td>
    </tr>
    <?php
    if (!empty($user_profile) && ((int)$group_id == 3 || (int)$group_id == 4)) {
        ?>
        <tr style="border-top: 2px solid #dadada;">
            <td class="td_left"><?php echo form_label('Пользователь заблокирован?', $banned['id']); ?>:</td>
            <td>
                <?php echo form_checkbox($banned); ?>
            </td>
        </tr>
        <tr style="border-bottom: 2px solid #dadada;">
            <td class="td_left"><?php echo form_label('Причина блокировки', $ban_reason['id']); ?>:</td>
            <td>
                <?php echo form_textarea($ban_reason); ?>
            </td>
        </tr>
        <?php
    }
    ?>
    <tr>
        <td></td>
        <td>
            <span class="red">* обязательные поля</span>
        </td>
    </tr>
    <tr>
        <td>
            <label>Даю согласие на передачу и использование моих персональных данных и согласен с
                <a href="/pol.html" target="_blank">Политикой конфиденциальности</a>
            </label>
        </td>
        <td>
            <input name="PERSONAL" type="checkbox" required>
        </td>
    </tr>
</table>
<h4>Управление оповещениями</h4>
<table class="reg">
    <tr>
        <td class="td_left"
            style="color: red;"><?php echo form_label('Отключить все оповещения:', $notice_disable['id']); ?></td>
        <td><?php echo form_checkbox($notice_disable); ?></td>
    </tr>
</table>
<?php
    $show_notice_block = 1;
    if(isset($user_profile['notice_disable']) &&($user_profile['notice_disable'] == 1)){
        $show_notice_block = 0;
    }
?>
<div id="notice_block" style="display: <?php echo ($show_notice_block == 1)?'block':'none'?>">
    <div style="margin:7px 0 15px 10px;">
        <label for="reg_tags" style="margin-bottom:15px;display: block">Выберите интересующие вас категории:</label>
        <div class="select_all_tags">
            <?php echo form_label('Выбрать все категории:', $select_all_tags['id']); ?>
            <?php echo form_checkbox($select_all_tags); ?>
        </div>
        <select id="reg_tags" class="select2" multiple="multiple" name="user_tags[]" style="width:100%;">
            <?php if ($all_tags != null): ?>
                <?php foreach ($all_tags as $tag): ?>
                    <option <?php echo ((!empty($user_tags) && in_array($tag['id'], $user_tags)) || (isset($user_profile['select_all_tags']) && $user_profile['select_all_tags'] == 1 )) ? "selected" : ""; ?>
                            value="<?php echo $tag['id']; ?>"><?php echo $tag['caption']; ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
        <span style="display: block;margin-top:10px;">*Оповещения будут приходить только об аукционах из выбраных категорий</span>
    </div>
    <table class="reg">
        <tr>
            <td><strong>Оповещать:</strong></td>
            <td></td>
        </tr>
        <tr>
            <td class="td_left"><?php echo form_label('о новых аукционах:', $notice_new_auctions['id']); ?></td>
            <td><?php echo form_checkbox($notice_new_auctions); ?></td>
        </tr>
        <tr>
            <td class="td_left"><?php echo form_label('о действиях других участников:', $notice_other_members['id']); ?></td>
            <td><?php echo form_checkbox($notice_other_members); ?></td>
        </tr>
        <tr>
            <td class="td_left"><?php echo form_label('за день до начала аукциона:', $notice_day_before_start['id']); ?></td>
            <td><?php echo form_checkbox($notice_day_before_start); ?></td>
        </tr>
        <tr>
            <td class="td_left"><?php echo form_label('за час до начала аукциона:', $notice_hour_before_start['id']); ?></td>
            <td><?php echo form_checkbox($notice_hour_before_start); ?></td>
        </tr>
        <tr>
            <td class="td_left"><?php echo form_label('за день до окончания аукциона:', $notice_day_before_end['id']); ?></td>
            <td><?php echo form_checkbox($notice_day_before_end); ?></td>
        </tr>
        <tr>
            <td class="td_left"><?php echo form_label('за час до окончания аукциона:', $notice_hour_before_end['id']); ?></td>
            <td><?php echo form_checkbox($notice_hour_before_end); ?></td>
        </tr>
        <tr>
            <td class="td_left"><?php echo form_label('о новых закупках на сайте fpkinvest.ru:', $notice_new_purchases['id']); ?></td>
            <td><?php echo form_checkbox($notice_new_purchases); ?></td>
        </tr>
        <tr>
            <td class="td_left"><?php echo form_label('за день до начала принятия заявок на сайте fpkinvest.ru:', $notice_purchases_day_before_start['id']); ?></td>
            <td><?php echo form_checkbox($notice_purchases_day_before_start); ?></td>
        </tr>
        <tr>
            <td class="td_left"><?php echo form_label('за день до окончания принятия заявок на сайте fpkinvest.ru:', $notice_purchases_day_before_end['id']); ?></td>
            <td><?php echo form_checkbox($notice_purchases_day_before_end); ?></td>
        </tr>
    </table>
</div>
<div class="reg_controls">
    <?php echo form_submit('save', 'Сохранить', 'class="button"'); ?>
    <?php echo form_reset('cancel', 'Отмена', 'class="button"'); ?>
</div>

<?php echo form_close(); ?>
<script>
    $('select[name=group]').on('change',function(){
    var $value = $(this).val();
    var $target = $('#reg_tags');
    if ($value == 6 || $value == 5){
        $target.find('option').each(function(){
            var $opt_val = $(this).val();
            if ($opt_val != 27){
                $(this).disabled = true; 
            }else{
                $(this).prop("selected", true);
                $target.trigger('change');
            }
        });
    }
});
</script>