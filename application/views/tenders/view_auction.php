<?php
if ($no_tender == TRUE || (!empty($allowed_users) && !in_array($user_id, $allowed_users) && $this->tank_auth->get_group_id() == 1)) {
    echo "<h4>" . $page_title . "</h4>";
    echo "<p>Просмотр аукциона невозможен. <a href=\"javascript:history.go(-1)\">Вернуться назад</a></p>";
} else {
    $end_date = array(
        'name' => 'new_end_date',
        'id' => 'new_end_date',
        'value' => (!empty($tender_detail['end_date']) ? date("d.m.Y", strtotime($tender_detail['end_date'])) : date("d.m.Y", strtotime("+20 days"))),
        'maxlength' => 10,
        'class' => 'validate[required,custom[date]]'
    );
    $end_time = array(
        'name' => 'new_end_time',
        'id' => 'new_end_time',
        'value' => (!empty($tender_detail['end_date']) ? date("H:i", strtotime($tender_detail['end_date'])) : $time_str),
        'maxlength' => 5,
        'class' => 'validate[required]',
        'style' => 'width: 50px;'
    );
    ?>
    <h4><?php echo $page_title; ?></h4>
<?print_r2($tender_detail);?>
    <table class="reg">
        <tr>
            <td colspan="2">
                <?php echo($start_tender == FALSE ? "<span class=\"red\">Аукцион еще не начался</span>" : ""); ?>
                <?php echo($game_tender == TRUE ? "<span class=\"red\">Аукцион закончен</span>" : ""); ?>
            </td>
        </tr>

        <tr>
            <td class="td_left"><strong>Номер аукциона:</strong></td>
            <td class = ''>
                <?php echo $tender_id; ?>
            </td>
        </tr>
        <tr>
            <td class="td_left"><strong>Наименование:</strong></td>
            <td class = ''>
                <?php echo $tender_detail['title']; ?>
            </td>
        </tr>
        <tr>
            <td class="td_left"><strong>Дата начала:</strong></td>
            <td class = ''>
                <?php echo date("d.m.Y H:i", strtotime($tender_detail['begin_date'])); ?>
            </td>
        </tr>
        <tr>
            <td class="td_left"><strong>Дата окончания:</strong></td>
            <td class = ''>
                <?php echo date("d.m.Y H:i", strtotime($tender_detail['end_date'])); ?>
            </td>
        </tr>
        <?php if ($game_tender == FALSE && $start_tender == TRUE) : ?>
            <tr>
                <td class="td_left" style="color: red;"><strong>До окончания:</strong></td>
                <td class = ''>
                    <script language="javascript"
                            src="/classes/countdown.php?id=<?php echo $tender_id; ?>&do=r"></script>
                </td>
            </tr>
        <?php endif; ?>
        <tr>
            <td class="td_left"><strong>Ставка:</strong></td>
            <td class = ''>
                <?php echo($tender_detail['type_rate'] == 1 ? "Стандартная" : "Ставка не меньше шага"); ?>
            </td>
        </tr>
        <tr>
            <td class="td_left"><strong>Торги:</strong></td>
            <td class = ''>
                <?php echo ($tender_detail['type_auction'] == 1 ? "Открытые торги (стандартный механизм)" : ($tender_detail['type_auction'] == 3 ? "Ставка ИТ" : "Полузакрытые торги (механизм «eBay»)") . ($tender_detail['type_auction_scandinavia'] == 1 ? " + Скандинавский аукцион" : "") . ($tender_detail['type_auction_plus'] == 1 ? " + Аукцион в «плюс»" : "")); ?>
            </td>
        </tr>
        <tr>
            <td class="td_left"><strong>Категории:</strong></td>
            <td class = ''>
                <?php if (!empty($tender_tags)): ?>
                    <?php echo implode(', ', $tender_tags); ?>
                <?php endif; ?>
            </td>
        </tr>
        <?php if(($group_id == 2 && $tender_author == TRUE) || $group_id == 3 || $group_id == 5):?>
            <tr>
                <td class="td_left"><strong>Допущенные участники:</strong></td>
                <td class = ''>
                    <?php if (!empty($tender_users)): ?>
                        <?php echo implode(', ', $tender_users); ?>
                    <?php else:?>
                        Все
                    <?php endif; ?>
                </td>
            </tr>
        <?php endif;?>
        <tr>
            <td colspan="2">
                <?php echo nl2br($tender_detail['description']); ?>
            </td>
        </tr>
        <?php

        if (!empty($tenders_documents)) {
            ?>
            <tr>
                <td class="td_left" style="vertical-align: top;"><strong>Прилагаемые файлы:</strong></td>
                <td class = ''>
                    <ol>
                        <?php
                        foreach ($tenders_documents as $key => $value) {
                            echo "<li><a href=\"/upload_tender/files/" . $value['tender_id'] . "/" . $value['filename'] . "\">" . $value['filename'] . "</a> (" . formatsize($value['filesize']) . ")</li>";
                        }
                        ?>
                    </ol>
                </td>
            </tr>
            <?php
        } ?>
        <?php if (!empty($completed_protocol_documents)): ?>
            <tr>
                <td class="td_left" style="vertical-align: top;"><strong>Протокол о завершении аукциона:</strong></td>
                <td class = ''>
                    <ol>
                        <?php
                        foreach ($completed_protocol_documents as $key => $value) {
                            echo "<a target=\"_blank\" href=\"/upload/completed_protocol/" . $value['tender_id'] . "/" . $value['filename'] . "\">" . $value['filename'] . "</a> (" . formatsize($value['filesize']) . ")";
                        }
                        ?>
                    </ol>
                </td>
            </tr>
        <?php endif; ?>
        <?php if ((($group_id == 2 && $tender_author == TRUE) || $group_id == 3 || $group_id == 5) && $game_tender == TRUE) {
            ?>
            <tr>
                <td class="td_left" style="vertical-align: top;"><strong>Документы:</strong></td>
                <td class = ''>
                    <?php
                    if (is_file($_SERVER['DOCUMENT_ROOT'] . "/data/protocol/protocol_" . $tender_id . ".docx")) {
                        echo "<p style=\"margin: 0; padding: 0;\"><a href=\"/tenders/down/protocol/" . $tender_id . "\">Скачать Протокол комиссии (DOCX-файл)</a></p>";
                    }
                    if (is_file($_SERVER['DOCUMENT_ROOT'] . "/data/itogi/itogi_" . $tender_id . ".xlsx")) {
                        echo "<p style=\"margin: 0; padding: 0;\"><a href=\"/tenders/down/itogi/" . $tender_id . "\">Скачать Итоговую таблицу (XLSX-файл)</a></p>";
                    }
                    ?>
                </td>
            </tr>
            <?php
        }
        ?>
    </table>
    <?php
    if (($group_id == 2 && $tender_author == TRUE) || $group_id == 3 || $group_id == 5) {
        ?>
        <p style="margin: 0; padding: 10px 0 0 0;">
        <?php if ($game_tender == FALSE) : ?>
            <?php echo form_button('early_end', 'Досрочно завершить торги', "class=\"button\" onclick=\"$.show_popup_end()\""); ?>
            &nbsp;
            <?php echo form_button('cancellation', 'Аннулировать торги', "class=\"button\" onclick=\"$.show_popup_cancel()\""); ?>
            &nbsp;
            <?php echo form_button('prolongation', 'Продлить торги', "class=\"button\" onclick=\"$.show_popup_date()\""); ?>
        <?php else : ?>
            &nbsp;<?php echo form_button('generate_xls', 'Сгенерировать итоговую таблицу', "class=\"button\" onclick=\"$.GeterateXLS(" . $tender_id . ", " . $user_id . "); return false;\""); ?>

            <?php echo form_button('upload_protocol', 'Прикрепить протокол', "class=\"button\" id=\"protocol_btn\""); ?>
            <form id="upload_form" action="" method="post" enctype="multipart/form-data" style="display: none">
                <input type="file" name="protocol_file" id="protocol_file"/>
                <input type="hidden" name="sended" value="true"/>
            </form>
            <div><?php echo $success_upload; ?></div>
        <?php endif; ?>
        </p>
        <?php
    } // if (($group_id == 2 && $tender_author == TRUE) || ($group_id == 5 && $tender_author == TRUE) || $group_id == 3) {

    if (($start_tender == false && $game_tender == false) && ($group_id == 1 OR $group_id == 6)):?>
        <h4>Дополнительные условия к аукциону</h4>
        <table class="reg tablesorter" id="options_show">
            <thead>
            <tr>
                <th>Наименование</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($tender_options)) : ?>
                <?php foreach ($tender_options as $key => $value): ?>
                    <tr id="options_<?php echo $value['id']; ?>">
                        <td class = ''><?php echo $value['name_field']; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
        <h4>Лоты аукциона</h4>
        <table class="reg tablesorter" id="lots_show">
            <thead>
            <tr>
                <th>Наименование</th>
                <th>Ед. изм.</th>
                <th>Потребность</th>
                <?php if ($tender_detail['type_auction'] != 3): ?>
                    <th>Начальная цена, руб.</th>
                <?php endif; ?>
                <?php if ($tender_detail['type_auction'] == 3): ?>
                    <th>Ссылка на товар</th>
                <?php endif; ?>
                <?php if ($tender_detail['type_rate'] == 2 || $tender_detail['type_auction'] == 2): ?>
                    <th>Шаг ставки, руб.</th>
                <?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($tender_lotes)): ?>
                <?php foreach ($tender_lotes as $key => $value) : ?>
                    <tr id="lots_<?php echo $value['id']; ?>">
                        <td class = ''><?php echo $value['name']; ?></td>
                        <td class = ''><?php echo $value['unit']; ?></td>
                        <td class = ''><?php echo $value['need']; ?></td>
                        <?php if ($tender_detail['type_auction'] != 3): ?>
                            <td class = ''><?php echo $value['start_sum']; ?></td>
                        <?php endif; ?>
                        <?php if ($tender_detail['type_auction'] == 3): ?>
                            <td class = ''><?php echo $value['product_link']; ?></td>
                        <?php endif; ?>
                        <?php if ($tender_detail['type_rate'] == 2 || $tender_detail['type_auction'] == 2): ?>
                            <td class = ''><?php echo $value['step_lot']; ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    <?php endif;
    // Начался тендер?
    if ($start_tender == TRUE || (($group_id == 2 && $tender_author == TRUE) || ($group_id == 5 && $tender_author == TRUE) || $group_id == 3)) {
        if (($group_id == 2 && $tender_author == FALSE) || $group_id == 1 || $group_id == 6) {
            echo form_open("/tenders/save_terms/", array('id' => 'termtender-form'), array('term_tender_id' => $tender_id));
        }
        ?>
        <?if (($group_id == 2 && $tender_author == TRUE) || ($group_id == 5 && $tender_author == TRUE)) {
            goto skipForAutor;
        }?>
        <h4>Дополнительные условия к аукциону</h4>
        <table class="reg tablesorter" id="options_show">
            <thead>
            <tr>
                <?php
                if ($group_id == 3)
                    echo "				<th>Участник</th>";

                if (!empty($tender_results_options) && ($group_id == 2 && $tender_author == TRUE) || ($group_id == 5 && $tender_author == TRUE) || $group_id == 3) {
                    if (!empty($tender_options))
                        foreach ($tender_options as $key => $value) {
                            echo "				<th>" . $value['name_field'] . "</th>";
                        } // foreach ($tender_options as $key => $value) {
                } else {
                    echo "				<th>Наименование</th>
                    <th>Ваше условие</th>";
                }
                ?>
            </tr>
            </thead>
            <tbody>
            <?php
            if (!empty($tender_results_options) && (($group_id == 2 && $tender_author == TRUE) || ($group_id == 5 && $tender_author == TRUE) || $group_id == 3)) {
                // Показываем результаты
                foreach ($tender_results_options as $key => $value) {
                    echo "			<tr>
                    <td class = ''>";
                    foreach ($users_list as $u) {
                        if ($u['user_id'] == $key)
                            echo anchor('/auth/user_edit/' . $u['id'] . '/', $u['name']);
                    }
                    echo "				</td>";
                    if (!empty($tender_options))
                        foreach ($tender_options as $k => $v) {
                            echo "				<td class = ''>" . (!empty($value[$v['id']]) ? ($value[$v['id']] == 'Y' ? 'Да' : $value[$v['id']]) : "Нет") . "</td>";
                        }
                    echo "			</tr>";

                } // foreach ($tender_options as $key => $value) {
            } // if ( !empty($tender_results_options) && (($group_id == 2 && $tender_author == TRUE) || ($group_id == 5 && $tender_author == TRUE) || $group_id == 3) )
            else {
                if (($group_id == 2 && $tender_author == TRUE) || ($group_id == 5 && $tender_author == TRUE) || $group_id == 3) {
                    echo "<tr><td colspan=\"" . (count($tender_options) + 1) . "\" style=\"text-align: center;\">Участников в данном аукционе не было.</td></tr>";
                } else {
                    // Заполняем допусловия
                    if (!empty($tender_options)) {
                        foreach ($tender_options as $key => $value) {
                            ?>
                            <tr id="options_<?php echo $value['id']; ?>">
                                <td class = ''><?php echo $value['name_field']; ?></td>
                                <td class = ''>
                                    <?php
                                    if ($game_tender == FALSE) {
                                        ?>
                                        <input type="<?php echo($value['type_field'] == 0 || $value['type_field'] == 1 ? "text" : "checkbox"); ?>"
                                               id="tender_option_<?php echo $value['id']; ?>"
                                               name="tender_option[<?php echo $value['id']; ?>]"
                                               class="<?php echo ($value['type_field'] == 1 ? "mini validate[custom[integer]]" : "validate[custom[onlyLetterNumber]]") . ($value['type_field'] == 0 ? " middle" : ""); ?>"
                                               value="<?php echo(!empty($tender_options_user[$value['id']]) ? $tender_options_user[$value['id']] : ($value['type_field'] == 2 ? "Y" : "")); ?>"<?php echo(($value['type_field'] == 2 && !empty($tender_options_user[$value['id']]) && $tender_options_user[$value['id']] == 'Y') ? " checked=\"checked\"" : ""); ?> />
                                        <?php
                                    } else {
                                        echo(!empty($tender_options_user[$value['id']]) && $tender_options_user[$value['id']] != 'Y' ? $tender_options_user[$value['id']] : (!empty($tender_options_user[$value['id']]) && $tender_options_user[$value['id']] == 'Y' ? "Да" : "Нет"));
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php
                        } // foreach ($tender_options as $key => $value) {
                    }
                }

            }
            ?>
            </tbody>
        </table>
        <?php
        if ((($group_id == 2 && $tender_author == FALSE) || $group_id == 1 || $group_id == 6) && $game_tender == FALSE) {
            echo "	<p style=\"margin: 0; padding: 0; text-align: right\">" . form_reset('cancel', 'Отмена', 'class="button"') . "&nbsp;" . form_submit('term', 'Выполним такие условия', 'class="button"') . "</p>";
            echo form_close();
        }

        if (($group_id == 2 && $tender_author == FALSE) || $group_id == 1 || $group_id == 6) {
            echo form_open("#", array('id' => 'runtender-form'), array('tender_id' => $tender_id));
        }
        ?>

        <h4>
            Лоты
            аукциона <?php echo((($group_id == 2 && $tender_author == TRUE) || ($group_id == 5 && $tender_author == TRUE) || $group_id == 3) ? " (<a href=\"/tenders/show_history/" . $tender_id . "\" target=\"_blank\">история ставок</a>)" : ""); ?>
        </h4>
        <table class="reg tablesorter" id="lots_show">
            <thead>
            <tr>
                <th class="name">Наименование</th>
                <th class="unit">Ед. изм.</th>
                <th class="">Потребность</th>
                <?php if ($tender_detail['type_auction'] != 3): ?>
                    <th class="">Начальная цена, руб.</th>
                <?php endif; ?>
                <?php if ($tender_detail['type_auction'] == 3): ?>
                    <th class="">Ссылка на товар</th>
                <?php endif; ?>
                <?php
                if ($tender_detail['type_rate'] == 2 || $tender_detail['type_auction'] == 2)
                    echo "				<th>Шаг ставки, руб.</th>\n";

                if (!empty($tender_results_lotes) && (($group_id == 2 && $tender_author == TRUE) || ($group_id == 5 && $tender_author == TRUE) || $group_id == 3)) {
                    // Колонки для администраторов и администраторов торгов (авторов)
                    echo "				<th>Лучшая цена, руб.</th>\n";
                    echo "				<th>Предлагает</th>\n";
                } else {
                    // Колонки для участников
                    //	if ($tender_detail['type_auction'] == 2)
                    echo "				<th>Лучшая цена, руб.</th>\n";

                    if ($tender_lotes_user && $game_tender == FALSE) {
                        echo "				<th>Ваша текущая цена, руб.</th>\n";
                        echo "				<th>Ваша новая цена, руб.</th>\n";
                    } else {
                        echo "				<th>Ваша цена, руб.</th>\n";
                    }
                }
                ?>
            </tr>
            </thead>
            <tbody>
            <?php
            if (!empty($tender_results_lotes) && (($group_id == 2 && $tender_author == TRUE) || ($group_id == 5 && $tender_author == TRUE) || $group_id == 3)) {
                // Показываем результаты для администраторов и администраторов торгов (авторов)
                if (!empty($tender_lotes))
                    foreach ($tender_lotes as $key => $value) {
                        echo "<tr id=\"lots_" . $value['id'] . "\"><td class = ''>" 
                        . $value['name'] . "</td><td class = ''>" 
                        . $value['unit'] . "</td><td class = ''>" 
                        . $value['need'] . "</td><td class = ''>" 
                        . ($tender_detail['type_auction'] == 3 ? $value['product_link'] : $value['start_sum']) . "</td>" 
                        . ($tender_detail['type_rate'] == 2 || $tender_detail['type_auction'] == 2 ? "<td class = ''>" . $value['step_lot'] . "</td>" : "") . "<td class = ''>" 
                        . (!empty($tender_results_lotes[$value['id']]) ? $tender_results_lotes[$value['id']]['best_value'] : "0.00") . "</td><td class = ''>"
                        . (!empty($tender_results_lotes[$value['id']]) ? $tender_results_lotes[$value['id']]['name'] : "нет") . "</td></tr>\n";
                    } // foreach ($tender_results_lotes as $key => $value) {
            } // if ( !empty($tender_results_lotes) && ($group_id == 2 || $group_id == 3) )
            else {
                //          var_dump($tender_results_lotes);
                // Заполняем лоты аукциона (для участников)
                if (!empty($tender_lotes)) {
                    print_r2(@$tender_lotes[0]['name']);
                    foreach ($tender_lotes as $key => $value) {
                        echo "<tr id=\"lots_" . $value['id'] . "\"><td class = ''>" 
                        . $value['name'] . "</td><td class = ''>" 
                        . $value['unit'] . "</td><td class = ''>" 
                        . $value['need'] . "</td><td class = ''>" 
                        . ($tender_detail['type_auction'] == 3 ? $value['product_link'] : $value['start_sum']) . "</td>";

                        if ($tender_detail['type_rate'] == 2 || $tender_detail['type_auction'] == 2)
                            echo "<td class = ''>" . $value['step_lot'] . "</td>";

                        // Отображение лучшей цены по схеме Ebay
                        if ($tender_detail['type_auction'] == 2) {
                            if ((empty($tender_members) || count($tender_members) == 1) && $game_tender == FALSE)
                                echo "<td class = ''>" . $value['start_sum'] . "</td>";
                            elseif ($game_tender == FALSE) {
                                echo "<td class = ''>" . ((!empty($tender_results_lotes_ebay[$value['id']]) && !empty($tender_lotes_user[$value['id']]) && $tender_lotes_user[$value['id']] > $tender_results_lotes_ebay[$value['id']]['best_value']) ? ((float)$tender_lotes_user[$value['id']] - (float)$value['step_lot']) : "") . ((!empty($tender_results_lotes_ebay_expensive[$value['id']]) && !empty($tender_lotes_user[$value['id']]) && $tender_lotes_user[$value['id']] < $tender_results_lotes_ebay_expensive[$value['id']]['expensive_value']) ? ((float)$tender_results_lotes_ebay_expensive[$value['id']]['expensive_value'] - (float)$value['step_lot']) : "") . "</td>";
                            } elseif ($game_tender == TRUE) {
                                echo "<td class = ''>" . (!empty($tender_results_lotes[$value['id']]) ? $tender_results_lotes[$value['id']]['best_value'] : "0.00") . "</td>";
                            }
                        } else {
                            echo "<td class = ''>" . (!empty($tender_results_lotes[$value['id']]) ? $tender_results_lotes[$value['id']]['best_value'] : "0.00") . "</td>";
                        }
                        if ($tender_detail['type_auction_plus'] == 1) {
                            // Аукцион в плюс
                            if ($tender_lotes_user && $game_tender == FALSE) {
                                $max_price = 0;
                                if (!empty($tender_results_lotes[$value['id']])) {
                                    $max_price = (float)$tender_results_lotes[$value['id']]['best_value'] + (float)$value['step_lot'];
                                } else {
                                    $max_price = (float)$value['start_sum'] + (float)$value['step_lot'];
                                }
                                if ($tender_detail['type_rate'] == 1 && $tender_detail['type_auction'] == 1) {
                                    if (!empty($tender_results_lotes[$value['id']])) {
                                        $max_price = (float)$tender_results_lotes[$value['id']]['best_value'] + 1;
                                    } else {
                                        $max_price = (float)$value['start_sum'] + 1;
                                    }
                                }

                                echo "<td class = ''>" . (!empty($tender_lotes_user[$value['id']]) ? $tender_lotes_user[$value['id']] : "") . "</td>";
                                echo "<td class = ''><input type=\"text\" id=\"tender_lot_" . $value['id'] . "\" name=\"tender_lot[" . $value['id'] . "]\" class=\"input_lote middle validate[" . "required," . "custom[number],maxSize[20],min[" . $max_price . "],ajax[ajaxLotCall]]\" value=\"\" /></td></tr>\n";
                            } else {
                                if (!empty($tender_results_lotes[$value['id']])) {
                                    $max_price = (float)$tender_results_lotes[$value['id']]['best_value'] + $value['step_lot'];
                                } else {
                                    $max_price = (float)$value['start_sum'] + (float)$value['step_lot'];
                                }
                                if ($tender_detail['type_rate'] == 1 && $tender_detail['type_auction'] == 1) {
                                    if (!empty($tender_results_lotes[$value['id']])) {
                                        $max_price = (float)$tender_results_lotes[$value['id']]['best_value'] + 1;
                                    } else {
                                        $max_price = (float)$value['start_sum'] + 1;
                                    }
                                }
                                if ($game_tender == FALSE)
                                    echo "<td class = ''><input type=\"text\" id=\"tender_lot_" . $value['id'] . "\" name=\"tender_lot[" . $value['id'] . "]\" class=\"middle validate[required,custom[number],maxSize[20],min[" . $max_price . "],ajax[ajaxLotCall]]\" value=\"" . (!empty($tender_lotes_user[$value['id']]) ? $tender_lotes_user[$value['id']] : "") . "\" /></td></tr>\n";
                                else
                                    echo "<td class = ''>" . (!empty($tender_lotes_user[$value['id']]) ? $tender_lotes_user[$value['id']] : "") . "</td></tr>\n";
                            }
                        } else {

                            // Обычные условия
                            if ($tender_lotes_user && $game_tender == FALSE) {

                                $max_price = 0;

                                if (!empty($tender_results_lotes[$value['id']]) && (float)$tender_results_lotes[$value['id']]['best_value'] >= (float)$value['step_lot'])
                                    $max_price = (float)$tender_results_lotes[$value['id']]['best_value'] - (float)$value['step_lot'];
                                else
                                    $max_price = (float)$value['start_sum'] - (float)$value['step_lot'];

                                //если открытые торги (без шага) уменьшаем цену на 1руб.
                                if ($tender_detail['type_rate'] == 1 && $tender_detail['type_auction'] == 1) {
                                    if (!empty($tender_results_lotes[$value['id']])) {
                                        $max_price = (float)$tender_results_lotes[$value['id']]['best_value'] - 1;
                                    } else {
                                        $max_price = (float)$value['start_sum'] - 1;
                                    }
                                }
                                echo "<td class = ''>" . (!empty($tender_lotes_user[$value['id']]) ? $tender_lotes_user[$value['id']] : "") . "</td>";
                                echo "<td class = ''><input type=\"text\" id=\"tender_lot_" . $value['id'] . "\" name=\"tender_lot[" . $value['id'] . "]\" class=\"input_lote middle validate[" . "required," . "custom[number],maxSize[20],max[" . $max_price . "],ajax[ajaxLotCall]]\" value=\"\" /></td></tr>\n";
                            } else {
//Выводит только для цеы а где все остальное?
                                if ($game_tender == FALSE) {
                                    if ($tender_results_lotes == null) {
                                        $max_price = $value['start_sum'] - $value['step_lot'];
                                    } else {
                                        $max_price = (float)$tender_results_lotes[$value['id']]['best_value'] - $value['step_lot'];
                                    }
                                    if ($tender_detail['type_rate'] == 1 && $tender_detail['type_auction'] == 1) {
                                        if (!empty($tender_results_lotes[$value['id']])) {
                                            $max_price = (float)$tender_results_lotes[$value['id']]['best_value'] - 1;
                                        } else {
                                            $max_price = (float)$value['start_sum'] - 1;
                                        }
                                    }
                                    echo "<td class = ''><input type=\"text\" 
                                            id=\"tender_lot_" . $value['id'] . "\" 
                                            name=\"tender_lot[" . $value['id'] . "]\" 
                                            class=\"middle validate[required,custom[number],maxSize[20],max[" .
                                        $max_price
                                        . "],ajax[ajaxLotCall]]\" 
                                            value=\"" . (!empty($tender_lotes_user[$value['id']]) ? $tender_lotes_user[$value['id']] : "") . "\" /></td></tr>\n";
                                } else {
                                    echo "<td class = ''>" . (!empty($tender_lotes_user[$value['id']]) ? $tender_lotes_user[$value['id']] : "") . "</td></tr>\n";
                                }
                            }
                        }

                    } // foreach ($tender_lotes as $key => $value) {
                } // if (!empty($tender_lotes))

            }

            // Итого в лотах
            $total_price = 0.00;
            if (!empty($tender_results_lotes)) {
                foreach ($tender_lotes as $v) {
                    if (!empty($tender_results_lotes[$v['id']]))
                        $total_price = (float)$total_price + floatval($tender_results_lotes[$v['id']]['best_value'] * $v['need']);
                    else
                        $total_price = (float)$total_price;
                }
            }

            echo "			<tr>\n";
            if ($tender_detail['type_rate'] == 2 || $tender_detail['type_auction'] == 2)
                echo "				<td colspan=\"5\" style=\"text-align: right;\"><strong>Итого:</strong></td>\n";
            else
                echo "				<td colspan=\"4\" style=\"text-align: right;\"><strong>Итого:</strong></td>\n";

            echo "				<td class = ''>" . (float)$total_price . " руб.</td>\n";

            if (!empty($tender_results_lotes) && (($group_id == 2 && $tender_author == TRUE) || ($group_id == 5 && $tender_author == TRUE) || $group_id == 3))
                echo "				<td class = ''>&nbsp;</td>\n";
            else {
                if ($tender_lotes_user && $game_tender == FALSE)
                    echo "				<td colspan=\"2\">&nbsp;</td>\n";
                else
                    echo "				<td class = ''>&nbsp;</td>\n";
            }
            echo "			</tr>\n";
            ?>
            </tbody>
        </table>
        <?php
        if ((($group_id == 2 && $tender_author == FALSE) || $group_id == 1 || $group_id == 6) && $game_tender == FALSE) {
            echo "	<p style=\"margin: 0; padding: 0; text-align: right\">" . form_reset('cancel', 'Отмена', 'class="button"') . "&nbsp;" . form_submit('run', 'Сделать ставку', 'class="button"') . "</p>";
            echo form_close();
        }

    } // if ($start_tender == FALSE || (($group_id == 2 && $tender_author == TRUE) || ($group_id == 5 && $tender_author == TRUE) || $group_id == 3) )
    ?>

    <?php
    if ($tender_detail['users_visible'] == 1 || ($group_id == 2 && $tender_author == TRUE) || $group_id == 3 || ($game_tender == TRUE && !empty($tender_detail['winner']))) {
        if (!empty($tender_members) && ($group_id == 2 && $tender_author == TRUE) || ($group_id == 5 && $tender_author == TRUE) || $group_id == 3) {
            echo form_open("/tenders/winner/", array('id' => 'wintender-form'), array('tender_id' => $tender_id));
        }
        ?>
        <h4>Участники аукциона и цены</h4>
        <table class="reg tablesorter" id="members_show">
            <thead>
            <tr>
                <th>Номер</th>
                <th>Наименование</th>
                <th>ИНН</th>
                <?php echo ($group_id == 2 || $group_id == 3)?"<th>E-mail</th>":"";?>
                <?php echo ($group_id == 2 || $group_id == 3)?"<th>Телефон</th>":"";?>
                <?php echo ($group_id == 2 || $group_id == 3)?"<th>ФИО</th>":"";?>
                <th>Предложение</th>
                <?php
                if (($group_id == 2 && $tender_author == TRUE) || ($group_id == 5 && $tender_author == TRUE) || $group_id == 3) {
                    echo "				<th>Победитель</th>\n";
                }
                ?>
            </tr>
            </thead>
            <tbody>
            <?php
            $i = 1;
            if (!empty($tender_members)) {
                foreach ($tender_members as $key => $value) {
                    echo "<tr" . (!empty($tender_detail['winner']) && $tender_detail['winner'] == $value['user_id'] ? " class=\"winner\"" : "") . "><td style=\"text-align: center;\">" . $i . "</td><td class = ''>" . $value['res_name'] . "</td><td class = ''>" . $value['inn'];

                    echo ($group_id == 2 || $group_id == 3)?"<td class = ''>".$value['email']."</td>":"";
                    echo ($group_id == 2 || $group_id == 3)?"<td class = ''>".$value['phone']."</td>":"";
                    echo ($group_id == 2 || $group_id == 3)?"<td class = ''>".$value['director_name']."</td>":"";

                    echo "</td><td style=\"text-align: center;\">" . (!empty($value['total_sum']) ? $value['total_sum'] : "0.00") . "</td>" . ((($group_id == 2 && $tender_author == TRUE) || ($group_id == 5 && $tender_author == TRUE) || $group_id == 3) ? "<td style=\"text-align: center;\"><input type=\"radio\" name=\"victory_member\" value=\"" . $value['user_id'] . "\" " . (!empty($value['leader']) && $value['leader'] == 1 ? "checked=\"checked\"" : "") . " /></td>" : "") . "</tr>\n";
                    $i++;
                }
            } else {
                echo "<tr><td colspan=\"" . (($group_id == 2 && $tender_author == TRUE) || $group_id == 3 ? "7" : "6") . "\" style=\"text-align: center;\">Участников в данном аукционе не было.</tr>\n";
            }

            if (!empty($visited_users) && ($group_id == 2 || $group_id == 3)) {
                foreach ($visited_users as $user) {;
                    echo "<tr>
<td style=\"text-align: center;\">" . $i . "</td>
<td class = ''>" . $user->name . "</td>
<td class = ''>" . $user->inn . "</td>
<td class = ''>" . $user->email . "</td>
<td class = ''>" . $user->phone . "</td>
<td class = ''>" . $user->director_name . "</td>
<td colspan='2'>Посещал, но не делал ставок</td>
</tr>";
                    $i++;
                }
            }

            ?>
            </tbody>
        </table>
        <?php
        if (!empty($tender_members) && (($group_id == 2 && $tender_author == TRUE) || ($group_id == 5 && $tender_author == TRUE) || $group_id == 3)) {
            ?>
            <p style="margin: 0; text-align: right;"><?php echo form_submit('winner', 'Выбранный участник — победитель', "class=\"button\" onclick=\"noty({ animateOpen: {opacity: 'show'}, animateClose: {opacity: 'hide'}, layout: 'center', text: 'Вы уверены, что хотите выбрать участника победителем?', buttons: [ {type: 'btn btn-mini btn-primary', text: 'Да!', click: function(\$noty) { \$noty.close(); $.SelectWinner($('input[name=victory_member]').val(), " . $tender_id . "); } }, {type: 'btn btn-mini btn-danger', text: 'Отмена', click: function(\$noty) { \$noty.close(); } } ], closable: false, timeout: false }); return false;\""); ?></p>
            <?php
            echo form_close();
        }
        ?>
        <?if (($group_id == 2 && $tender_author == TRUE) || ($group_id == 5 && $tender_author == TRUE)) {
            skipForAutor:
        }?>
        <?php
        if ((($group_id == 2 && $tender_author == TRUE) || ($group_id == 5 && $tender_author == TRUE) || $group_id == 3) && $game_tender == TRUE) {
            echo form_open("/classes/generate_doc.php", array('id' => 'protocol-form'), array('tender_id' => $tender_id, 'user_id' => $user_id));
            ?>
            <h4>Протокол аукциона</h4>
            <?php /*
            <table class="reg tablesorter" id="members_show">
                <thead>
                <tr>
                    <th>Председатель</th>
                    <th>Член комиссии</th>
                    <th>Секретарь</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if (!empty($tender_commission)) {
                    echo "<tr>\n";
                    foreach ($tender_commission as $key => $value) {
                        echo "<td style=\"text-align: center;\">
	<select id=\"commission_" . $key . "\" name=\"commission[]\" style=\"height: 200px\" multiple=\"multiple\">\n";
                        foreach ($value as $row) {
                            echo "		<option value=\"" . $row['id'] . "\">" . $row['fio'] . "</option>\n";
                        }
                        echo "	</select>
</td>\n";
                    }
                    echo "</tr>\n";
                } else {
                    echo "<tr><td colspan=\"3\" style=\"text-align: center;\">Состав не опреден.</tr>\n";
                }
                ?>
                </tbody>
            </table>
            */ ?>
            <p style="margin: 0;"><?php echo form_submit('generate_doc', 'Сгенерировать протокол', "class=\"button\" onclick=\"$.GeterateDOC(" . $tender_id . ", " . $user_id . "); return false;\""); ?></p>
            <?php echo form_close(); ?>

            <?php
        } // if ( (($group_id == 2 && $tender_author == TRUE) || ($group_id == 5 && $tender_author == TRUE) || $group_id == 3) && $game_tender == TRUE ) {
    } // if ($tender_detail['users_visible'] == 1 || ($group_id == 2 && $tender_author == TRUE) || $group_id == 3 ) {
} // if ($no_tender == TRUE)
?>
<script type="text/javascript">
    $(document).ready(function () {
        $('#protocol_btn').click(function () {
            $('#protocol_file').click();
        });
        $('#protocol_file').on('change', function () {
            $('#upload_form').submit();
        });
    });
</script>

<div id="new_date_picker">
    <div class="date_picker_container">
        <div class="erore_place"></div>
        <h4>Введите новую дату:</h4>
        <input type="text" value="<?=$end_date["value"]?>" id="new_end_date" placeholder="Новая дата">
        <input type="text" value="<?=$end_time["value"]?>" id="new_end_time" placeholder="Новое время">
        <textarea name="comment" id="new_end_date_comment" cols="10" rows="3" require="require"></textarea>
        <div style="display: flex; justify-content: space-between; align-items: center;" placeholder="Причина изменений">
            <button id="change_date_request" value="<?=$tender_id?>">Сменить</button>
            <button id="change_date_cancel">Закрыть</button>
        </div>
    </div>
</div>
<div id="cancel_reason">
    <div class="date_picker_container">
        <div class="erore_place"></div>
        <h4>Укажите причину изменений:</h4>
        <textarea name="comment" id="cancel_comment" cols="10" rows="3" require="require"></textarea>
        <div style="display: flex; justify-content: space-between; align-items: center;" placeholder="Причина изменений">
            <button id="cancel_request" value="<?=$tender_id?>">Анулировать</button>
            <button id="cancel_cancel">Закрыть</button>
        </div>
    </div>
</div>
<div id="early_end_reason">
    <div class="date_picker_container">
        <div class="erore_place"></div>
        <h4>Укажите причину изменений:</h4>
        <textarea name="comment" id="early_end_comment" cols="10" rows="3" require="require"></textarea>
        <div style="display: flex; justify-content: space-between; align-items: center;" placeholder="Причина изменений">
            <button id="early_end_request" value="<?=$tender_id?>">Завершить</button>
            <button id="early_end_cancel">Закрыть</button>
        </div>
    </div>
</div>

<script>
     $.show_popup_date = function (){
        $("#new_date_picker").css('display', 'flex');
        $("#new_end_date").datepicker($.datepicker.regional["ru"]);
    }

    $('#change_date_cancel').on('click', function(event) {
        $("#new_date_picker").css('display', 'none');
    });

    $('#change_date_request').on('click', function(event) {
        var $date = $('#new_end_date').val();
        var $reason = $('#new_end_date_comment').val();
        if ($date.trim() == '') {
            $('.erore_place').html('Дата не может быть пустой');
        }else if ($reason.trim() == '') {
            $('.erore_place').html('Комментарий не может быть пустым');
        } else {
            var $time = $('#new_end_time').val();
            var $tender_id = $(this).val();
            var $new_date = $date + " " + $time + ":00";
            
            $.Prolongation($tender_id,$new_date,$reason);
            $("#new_date_picker").css('display', 'none');
        }
    });

    $.Prolongation = function (tender_id,new_date,reason) {
        $.post('/tenders/prolongation/', {tender_id: tender_id,new_date: new_date,reason:reason},
            function (txt) {
                get = txt.split('|');
                if (get[0] == 'success') {
                    noty({
                        animateOpen: {opacity: 'show'},
                        animateClose: {opacity: 'hide'},
                        layout: 'center',
                        text: get[1],
                        type: 'success'
                    });
                   setTimeout('window.location.reload()', 3000);
                }
                else {
                    noty({
                        animateOpen: {opacity: 'show'},
                        animateClose: {opacity: 'hide'},
                        layout: 'center',
                        text: get[1],
                        type: 'error'
                    });
                }
            }
        );
        return false;
    }


    $.show_popup_end = function (){
        $("#early_end_reason").css('display', 'flex');
    }
    $('#early_end_cancel').on('click', function(event) {
        $("#early_end_reason").css('display', 'none');
    });

    $('#early_end_request').on('click', function(event) {
      
        var $reason = $('#early_end_comment').val();
        if ($reason.trim() == '') {
            $('.erore_place').html('Комментарий не может быть пустым');
        } else {
            var $tender_id = $(this).val();
            $.EarlyEnd($tender_id,$reason);
            $("#early_end_reason").css('display', 'none');
        }
    });
    
    $.show_popup_cancel = function (){
        $("#cancel_reason").css('display', 'flex');
    }
    $('#cancel_cancel').on('click', function(event) {
        $("#cancel_reason").css('display', 'none');
    });

    $('#cancel_request').on('click', function(event) {
      
        var $reason = $('#cancel_comment').val();
        if ($reason.trim() == '') {
            $('.erore_place').html('Комментарий не может быть пустым');
        } else {
            var $tender_id = $(this).val();
            $.Cancellation($tender_id,$reason);
            $("#cancel_reason").css('display', 'none');
        }
    });

    
    
</script>