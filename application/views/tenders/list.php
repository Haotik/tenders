<?php
$is_admin = (($this->tank_auth->is_logged_in() && $this->tank_auth->get_group_id() != 1) &&
    (!empty($this->uri->segments[2]) && ($this->uri->segments[2] == 'finished' || $this->uri->segments[2] == 'archive')));
?>
<script>
    $(document).ready(function () {
        $.datepicker.setDefaults($.datepicker.regional[""]);
        $("#from, #to").datepicker($.datepicker.regional["ru"]);
        <?php if($is_admin):?>
        $("#select_all").on('change', function (e) {
            $(".select_tender").prop('checked', $("#select_all").prop('checked'));
        });
        $("#export_tenders").on('click', function (e) {
            var checked = [];
            $('input:checkbox.select_tender').each(function () {
                if (this.checked) {
                    checked.push($(this).data('id'));
                }
            });
            $.post("/tenders/export_tenders", {ids: checked}, function (data) {
                var response = JSON.parse(data);
                if (response.success === true) {
                    window.location = response.link;
                } else {
                    noty({
                        animateOpen: {opacity: 'show'},
                        animateClose: {opacity: 'hide'},
                        layout: 'center',
                        text: response.error,
                        type: 'error'
                    });
                }
            });
        });
        <?php endif;?>
    })
</script>
<h4>Фильтр:</h4>
<form method="get" id="filter_form" style="margin-bottom:20px;">
    <label for="filter_tags" style="display: block;margin-bottom:10px;">Выберите категорию:</label>
    <select name="tag" id="filter_tags" style="width:40%;">
        <option></option>
        <?php if ($all_tags != null): ?>
            <?php foreach ($all_tags as $tag): ?>
                <option <?php echo ($tag['id'] == $selected_tag) ? 'selected' : ''; ?>
                        value="<?php echo $tag['id']; ?>"><?php echo $tag['caption']; ?></option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select>

    <div class="select_date">
        <label>Выберите период окончания аукциона:</label>
        <br>
        <?php
        $start_date = '';
        if (!empty($_GET['from'])) {
            $start_date = $_GET['from'];
        }
        $end_date = '';
        if (!empty($_GET['to'])) {
            $end_date = $_GET['to'];
        }
        $begin_date = array(
            'name' => 'from',
            'id' => 'from',
            'value' => $start_date,
            'maxlength' => 10,
            'autocomplete' => "off",
        );
        $end_date = array(
            'name' => 'to',
            'id' => 'to',
            'value' => $end_date,
            'maxlength' => 10,
            'autocomplete' => "off",
        );
        ?>

        <?php echo form_label('от', $begin_date['id']); ?>
        <?php echo form_input($begin_date); ?>

        <?php echo form_label('до', $end_date['id']); ?>
        <?php echo form_input($end_date); ?>
        <button type="submit">Показать</button>
    </div>
</form>
<h4><?php echo $page_title; ?></h4>

<table class="reg reg-options">
    <tr bgcolor='#FF5E5E'>
        <?php if ($is_admin): ?>
            <th title="Выбрать все"><input type="checkbox" id="select_all" title="Выбрать все"/></th>
        <?php endif; ?>
        <th>Номер</th>
        <th>Наименование</th>
        <th>Дата начала</th>
        <th>Дата окончания</th>
        <th>Заказчик</th>
        <?php
        if ($this->uri->segments[2] == 'finished') {
            ?>
            <th>Количество лотов</th>
            <th>Лучшая цена</th>
            <th>Количество участников</th>
            <?php
        } ?>
        <?php if ($this->uri->segments[2] == 'current' || $this->uri->segments[2] == 'previous'): ?>
            <th>Количество лотов</th>
            <th>Начальная цена</th>
            <th>Шаг ставки</th>
        <?php endif; ?>
        <?php if ($group_id == 2 || $group_id == 3) {
            ?>
            <th>Действия</th>
            <?php
        }
        ?>
    </tr>
    <?php
    $date_between = mktime(0, 0, 0, date("n"), date("d"), date("Y")) - 15 * 2678400;
    if (!empty($tenders_list)) {
        foreach ($tenders_list as $key => $value) {
            $tender_info = $this->tenders->get_tenders_lotes((int)$value['id']);
            echo "		<tr bgcolor='" . (($key % 2) ? '#E2DFDF' : '#F1EBEB') . "' id=\"tender_" . $value['id'] . "\">";
            if ($is_admin) {
                echo "<td><input type='checkbox' class='select_tender' data-id='" . $value['id'] . "'></td>";
            }
            echo "<td>" . anchor('/tenders/show/' . $value['id'] . '/', $value['id']) . "</td>
			<td>" . anchor('/tenders/show/' . $value['id'] . '/', $value['title']) . "</td>
			<td>" . $value['begin_date'] . "</td>
			<td>" . $value['end_date'] . "</td>
			<td>" . $value['author_name'] . "</td>\n";

            if ($this->uri->segments[2] == 'finished') {
                echo "			<td>" . (!empty($cnt_lotes[$value['id']]) ? $cnt_lotes[$value['id']] : 0) . "</td>
			<td>" . (!empty($best_summa[$value['id']]) ? $best_summa[$value['id']] : "0.00") . "</td>
			<td>" . (!empty($cnt_results[$value['id']]) ? $cnt_results[$value['id']] : 0) . "</td>\n";
            } ?>

            <?php if ($this->uri->segments[2] == 'current' || $this->uri->segments[2] == 'previous'): ?>
                <td><?php echo !empty($cnt_lotes[$value['id']]) ? $cnt_lotes[$value['id']] : 0; ?></td>
                <td><?php echo isset($tender_info[0]['start_sum']) ? $tender_info[0]['start_sum'] : '' ?></td>
                <td><?php echo isset($tender_info[0]['step_lot']) ? $tender_info[0]['step_lot'] : '' ?></td>
            <?php endif; ?>

            <?php if ($group_id == 2 || $group_id == 3) {
                echo "			<td>\n";
                if (($this->uri->segments[2] == 'previous' && $group_id == 2) || (($this->uri->segments[2] == 'previous' || $this->uri->segments[2] == 'current') && $group_id == 3)) {
                    echo anchor('/tenders/edit/' . $value['id'] . '/', " ", "class=\"button-edit\" title=\"Редактировать тендер\"");
                }

                if ($this->uri->segments[2] == 'finished') {
                    echo "<a href=\"\" class=\"button-archive\" title=\"Переместить в архив\" onclick=\"noty({ animateOpen: {opacity: 'show'}, animateClose: {opacity: 'hide'}, layout: 'center', text: 'Вы действительно хотите переместить тендер в архив?', buttons: [ {type: 'btn btn-mini btn-primary', text: 'Удалить', click: function(\$noty) { \$noty.close(); $.ArchiveTender(" . $value['id'] . "); } }, {type: 'btn btn-mini btn-danger', text: 'Отмена', click: function(\$noty) { \$noty.close(); } } ], closable: false, timeout: false }); return false;\"></a>";
                } else {
                    if ($group_id == 3) {
                        if ($this->uri->segments[2] == 'previous' || $this->uri->segments[2] == 'current' || ($this->uri->segments[2] == 'archive' && strtotime($value['end_date']) >= $date_between))
                            echo "<a href=\"\" class=\"button-delete\" title=\"Удалить тендер\" onclick=\"noty({ animateOpen: {opacity: 'show'}, animateClose: {opacity: 'hide'}, layout: 'center', text: 'Вы действительно хотите удалить тендер?', buttons: [ {type: 'btn btn-mini btn-primary', text: 'Удалить', click: function(\$noty) { \$noty.close(); $.DeleteTender(" . $value['id'] . "); } }, {type: 'btn btn-mini btn-danger', text: 'Отмена', click: function(\$noty) { \$noty.close(); } } ], closable: false, timeout: false }); return false;\"></a>";
                    }
                }
                echo "			</td>\n";
            }
            echo "		</tr>\n";
        }
    } else
        echo "		<tr bgcolor='#F1EBEB'>
			<td colspan=\"" . ($group_id == 2 || $group_id == 3 ? ($this->uri->segments[2] == "finished" ? "9" : "6") : ($this->uri->segments[2] == "finished" ? "8" : "5")) . "\">По заданным условиям соответствующих тендеров нет.</td>
		</tr>\n";
    ?>
</table>

<?php if ($is_admin): ?>
    <button id="export_tenders">Выгрузить</button>
<?php endif; ?>


