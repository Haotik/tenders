<?php
if ($no_tender == TRUE) {
    echo "<h4>" . $page_title . "</h4>";
    echo "<p>Редактирование аукциона невозможно. <a href=\"javascript:history.go(-1)\">Вернуться назад</a></p>";
} else {

    $title = array(
        'name' => 'title',
        'id' => 'title',
        'value' => (!empty($tender_detail['title']) ? $tender_detail['title'] : set_value('title')),
        'maxlength' => 255,
        'class' => 'validate[required]'
    );
    $begin_date = array(
        'name' => 'begin_date',
        'id' => 'begin_date',
        'value' => (!empty($tender_detail['begin_date']) ? date("d.m.Y", strtotime($tender_detail['begin_date'])) : date("d.m.Y", strtotime("+2 days"))),
        'maxlength' => 10,
        'readonly' => 'readonly',
        'class' => 'validate[required,custom[date]]'
    );
    $end_date = array(
        'name' => 'end_date',
        'id' => 'end_date',
        'value' => (!empty($tender_detail['end_date']) ? date("d.m.Y", strtotime($tender_detail['end_date'])) : date("d.m.Y", strtotime("+20 days"))),
        'maxlength' => 10,
        'readonly' => 'readonly',
        'class' => 'validate[required,custom[date]]'
    );
    $time_str = "";
    $time_date = (int)date("i");
    if ($time_date >= 0 && $time_date <= 14)
        $time_str = date("H", time()) . ":15";
    elseif ($time_date >= 15 && $time_date <= 29)
        $time_str = date("H", time()) . ":30";
    elseif ($time_date >= 30 && $time_date <= 44)
        $time_str = date("H", time()) . ":45";
    else
        $time_str = date("H", time() + 3600) . ":00";

    $begin_time = array(
        'name' => 'begin_time',
        'id' => 'begin_time',
        'value' => (!empty($tender_detail['begin_date']) ? date("H:i", strtotime($tender_detail['begin_date'])) : $time_str),
        'maxlength' => 5,
        'readonly' => 'readonly',
        'class' => 'validate[required]',
        'style' => 'width: 50px;'
    );
    $end_time = array(
        'name' => 'end_time',
        'id' => 'end_time',
        'value' => (!empty($tender_detail['end_date']) ? date("H:i", strtotime($tender_detail['end_date'])) : $time_str),
        'maxlength' => 5,
        'readonly' => 'readonly',
        'class' => 'validate[required]',
        'style' => 'width: 50px;'
    );
    $description = array(
        'name' => 'description',
        'id' => 'description',
        'value' => (!empty($tender_detail['description']) ? $tender_detail['description'] : set_value('description')),
        'rows' => 10,
        'class' => 'validate[required]'
    );
    $type_rate_standard = array(
        'name' => 'type_rate',
        'id' => 'type_rate_standard',
        'value' => 1,
        'checked' => ((!empty($tender_detail['type_rate']) && $tender_detail['type_rate'] == 1) ? "checked" : set_value('type_rate')),
        'class' => 'validate[required]'
    );
    $type_rate_step = array(
        'name' => 'type_rate',
        'id' => 'type_rate_step',
        'value' => 2,
        'checked' => ((!empty($tender_detail['type_rate']) && $tender_detail['type_rate'] == 2) ? "checked" : set_value('type_rate')),
        'class' => 'validate[required]'
    );
    $type_auction_open = array(
        'name' => 'type_auction',
        'id' => 'type_auction_open',
        'value' => 1,
        'checked' => ((!empty($tender_detail['type_auction']) && $tender_detail['type_auction'] == 1) ? "checked" : set_value('type_auction')),
        'class' => 'validate[required]'
    );
    $type_auction_ebay = array(
        'name' => 'type_auction',
        'id' => 'type_auction_ebay',
        'value' => 2,
        'checked' => ((!empty($tender_detail['type_auction']) && $tender_detail['type_auction'] == 2) ? "checked" : set_value('type_auction')),
        'class' => 'validate[required]'
    );
    $type_rate_it = array(
        'name' => 'type_auction',
        'id' => 'type_rate_it',
        'value' => 3,
        'checked' => ((!empty($tender_detail['type_auction']) && $tender_detail['type_auction'] == 3 || $user_group_id == 5) ? "checked" : set_value('type_auction')),
        'class' => 'validate[required]'
    );
    $type_auction_scandinavia = array(
        'name' => 'type_auction_scandinavia',
        'id' => 'type_auction_scandinavia',
        'value' => 1,
        'checked' => ((!empty($tender_detail['type_auction_scandinavia']) && $tender_detail['type_auction_scandinavia'] == 1) ? "checked" : set_value('type_auction_scandinavia'))
    );
    $type_auction_plus = array(
        'name' => 'type_auction_plus',
        'id' => 'type_auction_plus',
        'value' => 1,
        'checked' => ((!empty($tender_detail['type_auction_plus']) && $tender_detail['type_auction_plus'] == 1) ? "checked" : set_value('type_auction_plus'))
    );
    $tender_minute_end = array(
        'name' => 'tender_minute_end',
        'id' => 'tender_minute_end',
        'value' => (!empty($tender_detail['tender_minute_end']) ? $tender_detail['tender_minute_end'] : set_value('tender_minute_end')),
        'maxlength' => 6,
        'style' => 'width: 50px;'
    );
    $scan_minute = array(
        'name' => 'scan_minute',
        'id' => 'scan_minute',
        'value' => (!empty($tender_detail['scan_minute']) ? $tender_detail['scan_minute'] : set_value('scan_minute')),
        'maxlength' => 4,
        'style' => 'width: 50px;'
    );
    $users_visible = array(
        'name' => 'users_visible',
        'id' => 'users_visible',
        'value' => 1,
        'checked' => ((!empty($tender_detail['users_visible']) && $tender_detail['users_visible'] == 1) ? "checked" : set_value('users_visible'))
    );
    ?>
    <h4><?php echo $page_title; ?></h4>

    <?php
    if (!empty($notice)) {
        echo "<p>" . $notice . "</p>";
    }
    ?>
    <?php echo form_open("", array('id' => 'addtender-form'), array('tender_id' => $tender_id, 'is_add' => (!empty($tender_detail['id']) ? 'false' : 'true'))); ?>
    <table class="reg">
        <tr>
            <td></td>
            <td>
                <span class="red">* обязательные поля</span>
            </td>
        </tr>
        <?php
        if (!empty($tender_detail['tender_id'])) :
            ?>
            <tr>
                <td class="td_left"><?php echo form_label('Номер аукциона'); ?>:</td>
                <td>
                    <?php echo $tender_detail['tender_id']; ?>
                </td>
            </tr>
        <?php endif; ?>
        <tr>
            <td class="td_left"><?php echo form_label('Наименование', $title['id']); ?><span class="red">*</span>:</td>
            <td>
                <?php echo form_input($title); ?>
                <?php echo form_error($title['name']); ?><?php echo isset($errors[$title['name']]) ? $errors[$title['name']] : ''; ?>
            </td>
        </tr>
        <tr>
            <td class="td_left"><?php echo form_label('Дата начала', $begin_date['id']); ?><span class="red">*</span>:
            </td>
            <td>
                <?php echo form_input($begin_date); ?><?php echo form_input($begin_time); ?>
                <?php echo form_error($begin_date['name']); ?><?php echo isset($errors[$begin_date['name']]) ? $errors[$begin_date['name']] : ''; ?>
            </td>
        </tr>
        <tr>
            <td class="td_left"><?php echo form_label('Дата окончания', $end_date['id']); ?><span class="red">*</span>:
            </td>
            <td>
                <?php echo form_input($end_date); ?><?php echo form_input($end_time); ?>
                <?php echo form_error($end_date['name']); ?><?php echo isset($errors[$end_date['name']]) ? $errors[$end_date['name']] : ''; ?>
            </td>
        </tr>
        <tr>
            <td class="td_left"><?php echo form_label('Описание', $description['id']); ?><span class="red">*</span>:
            </td>
            <td>
                <?php echo form_textarea($description); ?>
                <?php echo form_error($description['name']); ?><?php echo isset($errors[$description['name']]) ? $errors[$description['name']] : ''; ?>
            </td>
        </tr>
        <tr>
            <td class="td_left">Опции ставки<span class="red">*</span>:</td>
            <td>
                <?php echo form_radio($type_rate_standard); ?> <?php echo form_label('Стандартная ставка', 'type_rate_standard'); ?>
                <br/>
                <?php echo form_radio($type_rate_step); ?> <?php echo form_label('Ставка не меньше шага', 'type_rate_step'); ?>
            </td>
        </tr>
        <tr>
            <td class="td_left">Опции торгов<span class="red">*</span>:</td>
            <td>
                <?php echo form_radio($type_auction_open); ?> <?php echo form_label('Открытые торги (стандартный механизм)', 'type_auction_open'); ?>
                <br/>
                <?php echo form_radio($type_auction_ebay); ?> <?php echo form_label('Полузакрытые торги (механизм «eBay»)', 'type_auction_ebay'); ?>
                <br/>
                <?php echo form_radio($type_rate_it); ?> <?php echo form_label('Ставка ИТ', 'type_rate_it'); ?>
                <br/>
                <?php echo form_checkbox($type_auction_scandinavia); ?> <?php echo form_label('Скандинавский аукцион', 'type_auction_scandinavia'); ?>
                <br/>
                <?php echo form_checkbox($type_auction_plus); ?> <?php echo form_label('Аукцион в «плюс»', 'type_auction_plus'); ?>
            </td>
        </tr>
        <tr id="row_tender_minute_end">
            <td class="td_left"><?php echo form_label('До окончания торгов', $tender_minute_end['id']); ?>:</td>
            <td>
                <?php echo form_input($tender_minute_end); ?> минут
            </td>
        </tr>
        <tr id="row_scan_minute">
            <td class="td_left"><?php echo form_label('Увеличить время торгов на', $scan_minute['id']); ?>:</td>
            <td>
                <?php echo form_input($scan_minute); ?> минут
            </td>
        </tr>
        <tr>
            <td class="td_left"><?php echo form_label('Участники могут видеть друг-друга', $users_visible['id']); ?>:
            </td>
            <td>
                <?php echo form_checkbox($users_visible); ?>
            </td>
        </tr>
    </table>

    <h4>Дополнительные условия к аукциону</h4>
    <table class="reg tablesorter" id="options">
        <thead>
        <tr>
            <th>Наименование</th>
            <th>Тип</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (!empty($tender_options)) {
            foreach ($tender_options as $key => $value) {
                ?>
                <tr id="options_<?php echo $value['id']; ?>">
                    <td><?php echo $value['name_field']; ?></td>
                    <td><?php echo($value['type_field'] == 0 ? "Строка" : ($value['type_field'] == 1 ? "Число" : "Чекбокс")); ?></td>
                    <td><a href="" class="button-delete" title="Удалить"
                           onclick="noty({ animateOpen: {opacity: 'show'}, animateClose: {opacity: 'hide'}, layout: 'center', text: 'Вы уверены, что хотите удалить условие из аукциона?', buttons: [ {type: 'btn btn-mini btn-primary', text: 'Удалить', click: function($noty) { $noty.close(); $.DeleteOptions(<?php echo $value['id']; ?>); } }, {type: 'btn btn-mini btn-danger', text: 'Отмена', click: function($noty) { $noty.close(); } } ], closable: false, timeout: false }); return false;"></a>
                    </td>
                </tr>
                <?php
            } // foreach ($tender_options as $key => $value) {
        } else {
            ?>
            <tr id="options_1">
                <td>Макс. срок поставки (дней)</td>
                <td>Строка</td>
                <td><a href="" class="button-delete" title="Удалить"
                       onclick="noty({ animateOpen: {opacity: 'show'}, animateClose: {opacity: 'hide'}, layout: 'center', text: 'Вы уверены, что хотите удалить условие из аукциона?', buttons: [ {type: 'btn btn-mini btn-primary', text: 'Удалить', click: function($noty) { $noty.close(); $.DeleteOptions(1); } }, {type: 'btn btn-mini btn-danger', text: 'Отмена', click: function($noty) { $noty.close(); } } ], closable: false, timeout: false }); return false;"></a>
                </td>
            </tr>
            <tr id="options_2">
                <td>Отсрочка платежа (дней)</td>
                <td>Строка</td>
                <td><a href="" class="button-delete" title="Удалить"
                       onclick="noty({ animateOpen: {opacity: 'show'}, animateClose: {opacity: 'hide'}, layout: 'center', text: 'Вы уверены, что хотите удалить условие из аукциона?', buttons: [ {type: 'btn btn-mini btn-primary', text: 'Удалить', click: function($noty) { $noty.close(); $.DeleteOptions(2); } }, {type: 'btn btn-mini btn-danger', text: 'Отмена', click: function($noty) { $noty.close(); } } ], closable: false, timeout: false }); return false;"></a>
                </td>
            </tr>
            <tr id="options_3">
                <td>Предоставление бесплатной доставки</td>
                <td>Чекбокс</td>
                <td><a href="" class="button-delete" title="Удалить"
                       onclick="noty({ animateOpen: {opacity: 'show'}, animateClose: {opacity: 'hide'}, layout: 'center', text: 'Вы уверены, что хотите удалить условие из аукциона?', buttons: [ {type: 'btn btn-mini btn-primary', text: 'Удалить', click: function($noty) { $noty.close(); $.DeleteOptions(3); } }, {type: 'btn btn-mini btn-danger', text: 'Отмена', click: function($noty) { $noty.close(); } } ], closable: false, timeout: false }); return false;"></a>
                </td>
            </tr>
            <?php
        } // if (!empty($tender_options))
        ?>
        </tbody>
        <tfoot>
        <tr>
            <td><input type="text" id="new_term_name" name="new_term_name" style="width: 95%;"/></td>
            <td>
                <select id="new_term_type" name="new_term_type">
                    <option value="Строка">Строка</option>
                    <option value="Число">Число</option>
                    <option value="Чекбокс">Чекбокс</option>
                </select>
            </td>
            <td><a href="" class="button-add" title="Добавить" onclick="$.AddOptions(); return false;"></a></td>
        </tr>
        </tfoot>
    </table>

    <h4>Лоты аукциона</h4>
    <button class="btn btn-primary lots-file-import fileinput-button">
        <i class="icon-upload icon-white"></i>
        <span>Загрузить и заполнить</span>
        <input type="file" name="lots_file" accept=".xls,.xlsx">
    </button>
    <table class="reg tablesorter lots-table" id="lots">
        <thead>
        <tr>
            <th>Наименование</th>
            <th>Ед. изм.</th>
            <th>Потребность</th>
            <th class="col_start_sum">Начальная цена, руб.</th>
            <th class="col_product_link">Ссылка на товар</th>
            <th class="col_rate_step">Шаг ставки, руб.</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (!empty($tender_lotes)) {
            foreach ($tender_lotes as $key => $value) {
                echo "<tr id=\"lots_" . $value['id'] . "\"><td>" . $value['name'] . "</td><td>" . $value['unit'] . "</td><td>" . $value['need'] . "</td><td>" . $value['start_sum'] . "</td><td class=\"col_rate_step\">" . $value['step_lot'] . "</td><td><a href=\"\" class=\"button-delete\" title=\"Удалить\" onclick=\"noty({ animateOpen: {opacity: 'show'}, animateClose: {opacity: 'hide'}, layout: 'center', text: 'Вы уверены, что хотите удалить лот из аукциона?', buttons: [ {type: 'btn btn-mini btn-primary', text: 'Удалить', click: function(\$noty) { \$noty.close(); $.DeleteLot(" . $value['id'] . "); } }, {type: 'btn btn-mini btn-danger', text: 'Отмена', click: function(\$noty) { \$noty.close(); } } ], closable: false, timeout: false }); return false;\"></a></td></tr>\n";
            }
        }
        ?>
        </tbody>
        <tfoot>
        <tr>
            <td><input type="text" id="new_lot_name" name="new_lot_name" style="width: 95%;"/></td>
            <td><input type="text" id="new_lot_unit" name="new_lot_unit" style="width: 95%;"/></td>
            <td><input type="text" id="new_lot_need" name="new_lot_need" style="width: 95%;"
                       class="validate[custom[integer]]"/></td>
            <td class="col_start_sum"><input type="text" id="new_lot_start_sum" name="new_lot_start_sum" style="width: 95%;"
                       class="validate[custom[integer]]"/></td>
            <td class="col_product_link"><input type="text" id="new_lot_product_link" name="new_lot_product_link" style="width: 95%;"/></td>
            <td class="col_rate_step"><input type="text" id="new_lot_step_lot" name="new_step_lot" style="width: 95%;"
                                             class="validate[custom[number]]"/></td>
            <td><a href="" class="button-add" title="Добавить" onclick="$.AddLot(); return false;"></a></td>
        </tr>
        </tfoot>
    </table>
    <h4>Категории аукциона</h4>
    <select id="add_form_tags" class="select2" multiple="multiple" name="tender_tags" style="width:100%;">
        <?php if ($all_tags != null): ?>
            <?php foreach($all_tags as $tag):?>
                <option <?php echo (!empty($tender_tags) && in_array($tag['id'],$tender_tags) )?"selected":""; ?> value="<?php echo $tag['id'];?>"><?php echo $tag['caption'];?></option>
            <?php endforeach;?>
        <?php endif;?>
    </select>

    <h4 style="margin-top:20px;">Указать пользователей для аукциона</h4>
    <select id="add_tender_users" class="select2" multiple="multiple" name="tender_users"
            style="width:100%">
        <?php if ($all_users != null): ?>
            <?php foreach ($all_users as $user): ?>
                <option <?php echo (!empty($tender_users) && in_array($user['user_id'], $tender_users)) ? "selected" : ""; ?> value="<?php echo $user['user_id']; ?>"><?php echo $user['name']; ?></option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select>

    <p style="margin: 0; padding: 20px 0 0 0;"><?php echo form_submit('save', 'Сохранить', 'class="button", id="addtender_submit_btn"'); ?>
        &nbsp;<?php echo form_reset('cancel', 'Отмена', 'class="button"'); ?></p>
    <?php echo form_close(); ?>

    <h4>Приложенные файлы к аукциону</h4>
    <div class="alert">
        <p><strong>Обратите внимание!</strong></p>
        <p>Во избежании проблем с закачиванием файлов на сервер, рекомендуется в имени файла использовать:<br/>a-z —
            латинские символы (в нижнем регистре);<br/>. — точку;<br/>- — знак тире;<br/>_ — знак подчеркивания
            (использовать вместо пробела).<br/>
            Длина имени файла должна быть не более 100 символов.</p>
        <p><strong>Пример:</strong> nazvanie_documenta.doc</p>
    </div>
    <form id="fileupload" action="/upload_tender/" method="POST" enctype="multipart/form-data">
        <input type="hidden" id="uptender_id" name="uptender_id" value="<?php echo $tender_id; ?>"/>
        <div class="fileupload-buttonbar">
		<span class="btn btn-success fileinput-button">
			<i class="icon-plus icon-white"></i>
			<span>Добавить файлы...</span>
			<input type="file" name="files[]" multiple>
		</span>
            <button type="submit" class="btn btn-primary start">
                <i class="icon-upload icon-white"></i>
                <span>Начать загрузку</span>
            </button>
            <button type="reset" class="btn btn-warning cancel">
                <i class="icon-ban-circle icon-white"></i>
                <span>Отменить загрузку</span>
            </button>
            <button type="button" class="btn btn-danger delete">
                <i class="icon-trash icon-white"></i>
                <span>Удалить</span>
            </button>
            <input type="checkbox" class="toggle" title="Выбрать все для удаления"/>
            <div class="fileupload-progress fade">
                <div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0"
                     aria-valuemax="100">
                    <div class="bar" style="width:0%;"></div>
                </div>
                <div class="progress-extended">&nbsp;</div>
            </div>
        </div>
        <div class="fileupload-loading"></div>
        <br/>
        <table role="presentation" class="table table-striped">
            <tbody class="files" data-toggle="modal-gallery" data-target="#modal-gallery"></tbody>
        </table>
    </form>

    <div id="modal-gallery" class="modal modal-gallery hide fade" data-filter=":odd">
        <div class="modal-header">
            <a class="close" data-dismiss="modal">&times;</a>
            <h3 class="modal-title"></h3>
        </div>
        <div class="modal-body">
            <div class="modal-image"></div>
        </div>
        <div class="modal-footer">
            <a class="btn modal-download" target="_blank">
                <i class="icon-download"></i>
                <span>Download</span>
            </a>
            <a class="btn btn-success modal-play modal-slideshow" data-slideshow="5000">
                <i class="icon-play icon-white"></i>
                <span>Slideshow</span>
            </a>
            <a class="btn btn-info modal-prev">
                <i class="icon-arrow-left icon-white"></i>
                <span>Previous</span>
            </a>
            <a class="btn btn-primary modal-next">
                <span>Next</span>
                <i class="icon-arrow-right icon-white"></i>
            </a>
        </div>
    </div>

    <script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-upload fade">
        <td class="preview"><span class="fade"></span></td>
        <td class="name"><span>{%=file.name%}</span></td>
        <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
        {% if (file.error) { %}
            <td class="error" colspan="2"><span class="label label-important">{%=locale.fileupload.error%}</span> {%=locale.fileupload.errors[file.error] || file.error%}</td>
        {% } else if (o.files.valid && !i) { %}
            <td>
                <div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="bar" style="width:0%;"></div></div>
            </td>
            <td class="start">{% if (!o.options.autoUpload) { %}
                <button class="btn btn-primary">
                    <i class="icon-upload icon-white"></i>
                    <span>{%=locale.fileupload.start%}</span>
                </button>
            {% } %}</td>
        {% } else { %}
            <td colspan="2"></td>
        {% } %}
        <td class="cancel">{% if (!i) { %}
            <button class="btn btn-warning">
                <i class="icon-ban-circle icon-white"></i>
                <span>{%=locale.fileupload.cancel%}</span>
            </button>
        {% } %}</td>
    </tr>
{% } %}

    </script>
    <script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-download fade">
        {% if (file.error) { %}
            <td></td>
            <td class="name"><span>{%=file.name%}</span></td>
            <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
            <td class="error" colspan="2"><span class="label label-important">{%=locale.fileupload.error%}</span> {%=locale.fileupload.errors[file.error] || file.error%}</td>
        {% } else { %}
            <td class="preview">{% if (file.thumbnail_url) { %}
                <a href="{%=file.url%}" title="{%=file.name%}" rel="gallery" download="{%=file.name%}"><img src="{%=file.thumbnail_url%}"></a>
            {% } %}</td>
            <td class="name">
                <a href="{%=file.url%}" title="{%=file.name%}" rel="{%=file.thumbnail_url&&'gallery'%}" download="{%=file.name%}">{%=file.name%}</a>
            </td>
            <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
            <td colspan="2"></td>
        {% } %}
        <td class="delete">
            <button class="btn btn-danger" data-type="{%=file.delete_type%}" data-url="{%=file.delete_url%}&tender_id=<?php echo $tender_id; ?>">
                <i class="icon-trash icon-white"></i>
                <span>{%=locale.fileupload.destroy%}</span>
            </button>
            <input type="checkbox" name="delete" value="1">
        </td>
    </tr>
{% } %}

    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js" integrity="sha512-r22gChDnGvBylk90+2e/ycr3RVrDi8DIOkIGNhJlKfuyQM4tIRAI062MaV8sfjQKYVGjOBaZBOA87z+IhZE9DA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="/js/upload/jquery.ui.widget.js"></script>
    <script src="http://blueimp.github.io/JavaScript-Templates/js/tmpl.min.js"></script>
    <script src="/js/upload/load-image.js"></script>
    <script src="http://blueimp.github.io/JavaScript-Canvas-to-Blob/js/canvas-to-blob.min.js"></script>
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
    <script src="http://blueimp.github.io/Gallery/js/jquery.blueimp-gallery.min.js"></script>
    <script src="/js/upload/jquery.iframe-transport.js"></script>
    <script src="/js/upload/jquery.fileupload.js"></script>
    <script src="/js/upload/jquery.fileupload-fp.js"></script>
    <script src="/js/upload/jquery.fileupload-ui.js"></script>
    <script src="/js/upload/locale.js"></script>
    <script src="/js/upload/main.js"></script>
    <!--[if gte IE 8]>
    <script src="/js/upload/jquery.xdr-transport.js"></script><![endif]-->

    <?php
} // if ($no_tender == TRUE)
?>