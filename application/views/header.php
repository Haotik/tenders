<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="author" content="Voshod Media - www.voshod-media.ru"/>
    <link rel="icon" href="/favicon.ico" type="image/x-icon"/>
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon"/>
    <link rel="stylesheet" type="text/css" href="/styles/reset.css" media="screen,projection"/>
    <link rel="stylesheet" type="text/css" href="/styles/style.css" media="screen,projection"/>
    <!--[if IE 6]>
    <link rel="stylesheet" type="text/css" href="/styles/ie6.css" media="screen,projection"/>
    <![endif]-->

    <title><?php echo(!empty($page_title) ? $page_title . " — " : ""); ?><?php echo $config['engine_title']; ?></title>

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"
            charset="utf-8"></script>

    <link rel="stylesheet" type="text/css" href="/styles/jquery.noty.css"/>
    <link rel="stylesheet" type="text/css" href="/styles/noty_theme_twitter.css"/>
    <link rel="stylesheet" type="text/css" href="/styles/noty_theme_twitter_buttons.css"/>
    <script src="/js/jquery.noty.js" type="text/javascript" charset="utf-8"></script>
    <link href="/styles/select2.css" rel="stylesheet"/>
    <script src="/js/select2.js" type="text/javascript"></script>
    <script src="/js/main.js" type="text/javascript"></script>


    <?php
    if (!empty($this->uri->segments[2]) && ($this->uri->segments[2] == 'user_edit' ||
            $this->uri->segments[2] == 'register' ||
            $this->uri->segments[2] == 'forgot_password' ||
            $this->uri->segments[2] == 'add' ||
            $this->uri->segments[2] == 'edit' ||
            $this->uri->segments[2] == 'show' ||
            $this->uri->segments[2] == 'show_history' ||
            $this->uri->segments[2] == 'add_user' ||
            $this->uri->segments[2] == 'edit_user' ||
            $this->uri->segments[2] == 'finished' ||
            $this->uri->segments[2] == 'archive' ||
            $this->uri->segments[2] == 'previous' ||
            $this->uri->segments[2] == 'current'
        )) {
        ?>
        <link rel="stylesheet" href="/styles/jquery-ui-1.8.14.custom.css" type="text/css" media="screen"
              charset="utf-8"/>
        <link type="text/css" rel="stylesheet" href="/styles/validation.css" charset="utf-8"/>

        <script src="/js/validate/jquery.validationEngine-ru.js" type="text/javascript" charset="utf-8"></script>
        <script src="/js/validate/jquery.validationEngine.js" type="text/javascript" charset="utf-8"></script>
        <script src="/js/jquery-ui-1.8.14.custom.min.js" type="text/javascript" charset="utf-8"></script>
        <script src="/js/jquery.ui.datepicker-ru.js" type="text/javascript" charset="utf-8"></script>
    <?php
    if ($this->uri->segments[2] == 'add' || $this->uri->segments[2] == 'edit' || $this->uri->segments[2] == 'show' || $this->uri->segments[2] == 'show_history') {
    ?>
    <link rel="stylesheet" href="/styles/tables/style.css" type="text/css" media="print, projection, screen"/>
        <script src="/js/tables/jquery.tablesorter.min.js" type="text/javascript"></script>
        <script src="/js/jquery.countdown.js" type="text/javascript"></script>
    <?php
    if ($this->uri->segments[2] == 'add' || $this->uri->segments[2] == 'edit') {
    ?>
    <link rel="stylesheet" href="/styles/fileupload/bootstrap-image-gallery.min.css">
    <link rel="stylesheet" href="/styles/fileupload/jquery.fileupload-ui.css">
        <!--[if lt IE 9]>
        <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->

        <script src="/js/jquery-ui-timepicker-addon.js" type="text/javascript" charset="utf-8"></script>
    <?php
    } // if ($this->uri->segments[2] == 'add' || $this->uri->segments[2] == 'edit') {
    } // if ($this->uri->segments[2] == 'add' || $this->uri->segments[2] == 'edit' || $this->uri->segments[2] == 'show') {
    ?>
        <script language="JavaScript" type="text/javascript">
            $.post('/classes/change_status.php'); //старт на хитах
            <?php
            if ($this->uri->segments[2] == 'add' || $this->uri->segments[2] == 'edit') {
            ?>
            function redirect_previous() {
                window.location = "<?php echo isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:"/tenders/previous/"; ?>"
            }
            <?php
            } elseif ($this->uri->segments[2] == 'add_user' || $this->uri->segments[2] == 'edit_user') {
            ?>
            function redirect_commission() {
                window.location = "/commission/"
            }
            <?php
            }
            if ($this->uri->segments[2] == 'show' && $game_tender == FALSE) {
            ?>
            function ajaxValidationCallback(status, form, json, options) {
                if (status === true) {
                    $.post('/tenders/run/', $("#runtender-form").serialize(),
                        function (txt) {
                            console.log(txt);
                            get = txt.split('|');

                            if (get[0] == 'success') {
                                noty({
                                    animateOpen: {opacity: 'show'},
                                    animateClose: {opacity: 'hide'},
                                    layout: 'center',
                                    text: get[1],
                                    type: 'success'
                                });
                                $("input.input_lote").val("");
                                setTimeout('window.location.reload()', 1000);
                            }
                            else (get[0] == 'error')
                            {
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
                    return true;
                }
                else {
                    return false;
                }
            }

            var sURL = unescape(window.location.pathname);

            function refresh() {
                window.location.href = sURL;
            }

            setTimeout("refresh()", <?php echo $autorefresh; ?>000);
            <?php
            }
            ?>
            $(document).ready(function () {
                <?php
                if ($this->uri->segments[2] == 'user_edit' || $this->uri->segments[2] == 'register') {
                ?>
                $.datepicker.setDefaults($.datepicker.regional[""]);
                $("#organization_date").datepicker($.datepicker.regional["ru"]);
                $("#register-form").validationEngine();
                <?php
                } elseif ($this->uri->segments[2] == 'forgot_password') {
                ?>
                $("#forgot-form").validationEngine();
                <?php
                } elseif ($this->uri->segments[2] == 'add' || $this->uri->segments[2] == 'edit') {
                ?>
                $.datepicker.setDefaults($.datepicker.regional[""]);
                $("#begin_date, #end_date, #new_end_datetime").datepicker($.datepicker.regional["ru"]);
                $('#begin_time, #end_time').timepicker({});
                $("#row_tender_minute_end, #row_scan_minute, .col_rate_step").hide();

                function scanMinute(element) {
                    var n = $("#type_auction_scandinavia:checked").length;
                    if (n == 0) {
                        $("#row_tender_minute_end, #row_scan_minute").hide();
                        $("#tender_minute_end, #scan_minute").removeClass("validate[required,custom[integer]]");
                    } else {
                        $("#row_tender_minute_end, #row_scan_minute").fadeIn();
                        $("#tender_minute_end, #scan_minute").addClass("validate[required,custom[integer]]");
                    }
                }

                function rateStep(element) {
                    if ($(element).attr("id") != "type_rate_step") {
                        $(".col_rate_step").hide();
                        //$("#lots tbody").html('');
                        $("#new_lot_name").val("");
                        $("#new_lot_unit").val("");
                        $("#new_lot_need").val("");
                        $("#new_lot_start_sum").val("");
                        $("#new_lot_step_lot").val("");
                    } else {
                        $(".col_rate_step").fadeIn();
                        //$("#lots tbody").html('');
                        $("#new_lot_name").val("");
                        $("#new_lot_unit").val("");
                        $("#new_lot_need").val("");
                        $("#new_lot_start_sum").val("");
                        $("#new_lot_step_lot").val("");
                    }
                }

                function rateEbay(element) {
                    if ($(element).attr("id") != "type_auction_ebay") {
                        if ($("input[name=type_rate]:checked").attr("id") != "type_rate_step")
                            $(".col_rate_step").hide();
                        $("#type_rate_step").removeAttr("disabled");
                    } else {
                        $(".col_rate_step").fadeIn();
                        $("#type_rate_step").attr("disabled", "disabled");
                        $("#type_rate_standard").attr("checked", "checked");
                    }
                }
                function parseDateToStr(date) {
                    let day = date.getDate(),
                        month = date.getMonth() + 1,
                        year = date.getFullYear();
                    day = day < 10 ? '0' + day : day;
                    month = month < 10 ? '0' + month : month;
                    return `${day}.${month}.${year}`;
                }
                function changeRateIT(e) {
                    const input = $("#type_rate_it");
                    if (!input.is(":checked")) {
                        $("#type_auction_scandinavia, #type_auction_plus").fadeIn();
                        $('label[for="type_auction_scandinavia"], label[for="type_auction_plus"]').fadeIn();
                        $("#type_auction_scandinavia, #type_auction_plus").addClass("validate[required,custom[integer]]");
                        $('.col_product_link').hide();
                        $('.lots-file-import').hide();
                        $('.col_start_sum').fadeIn();
                        $('#options #options_2, #options #options_3').fadeIn();

                    } else {
                        $("#type_auction_scandinavia, #type_auction_plus").hide();
                        $('label[for="type_auction_scandinavia"], label[for="type_auction_plus"]').hide();
                        $("#type_auction_scandinavia, #type_auction_plus").removeClass("validate[required,custom[integer]]");
                        $('.col_product_link').fadeIn();
                        $('.lots-file-import').fadeIn();
                        $('.col_start_sum').hide();
                        $('#options #options_2, #options #options_3').hide();
                        const beginDate = parseDateToStr(new Date());
                        const endDate = parseDateToStr(new Date(Date.now() + 48 * 3600 * 1000));
                        $("#begin_date").val(beginDate);
                        $("#end_date").val(endDate);
                    }
                }

                $('[name="type_auction"]').on('input', changeRateIT);
                changeRateIT();

                $("#type_auction_scandinavia").click(function () {
                    scanMinute(this);
                });
                scanMinute($("#type_auction_scandinavia:checked"));

                $("input[name=type_rate]").click(function () {
                    rateStep(this);
                });
                rateStep($("input[name=type_rate]:checked"));

                $("input[name=type_auction]").click(function () {
                    rateEbay(this);
                });
                rateEbay($("input[name=type_auction]:checked"));

                // Дополнительные условия
                $("#options").tablesorter({
                    headers: {
                        0: {sorter: false},
                        1: {sorter: false},
                        2: {sorter: false}
                    }
                });

                $.AddOptions = function () {
                    var new_term_name = $("#new_term_name").val();
                    var new_term_type = $("#new_term_type").val();
                    var tr_options = $("#options tbody tr").length;
                    if (new_term_name.length > 0) {
                        html = "<tr id=\"options_" + (tr_options + 1) + "\"><td>" + new_term_name + "</td><td>" + new_term_type + "</td><td><a href=\"\" class=\"button-delete\" title=\"Удалить\" onclick=\"noty({ animateOpen: {opacity: 'show'}, animateClose: {opacity: 'hide'}, layout: 'center', text: 'Вы уверены, что хотите удалить условие из аукциона?', buttons: [ {type: 'btn btn-mini btn-primary', text: 'Удалить', click: function(\$noty) { \$noty.close(); $.DeleteOptions(" + (tr_options + 1) + "); } }, {type: 'btn btn-mini btn-danger', text: 'Отмена', click: function(\$noty) { \$noty.close(); } } ], closable: false, timeout: false }); return false;\"></a></td></tr>";
                        $("#options tbody").append(html);
                        $("#options").trigger("update");
                        $("#new_term_name").val("");
                    }
                };

                $.DeleteOptions = function (row_id) {
                    <?php
                    if ($this->uri->segments[2] == 'edit') {
                    ?>
                    $.post('/tenders/deleteoption/' + row_id,
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
                                $("#options_" + row_id).fadeOut().remove();
                                $("#options").trigger("update");
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
                    <?php
                    } else {
                    ?>
                    $("#options_" + row_id).fadeOut().remove();
                    $("#options").trigger("update");
                    <?php
                    }
                    ?>
                    return false;
                }

                // Лоты аукциона
                $("#lots").tablesorter({
                    headers: {
                        0: {sorter: false},
                        1: {sorter: false},
                        2: {sorter: false},
                        3: {sorter: false},
                        4: {sorter: false},
                        5: {sorter: false}
                    }
                });

                $.AddLot = function () {
                    var new_lot_name = $("#new_lot_name").val();
                    var new_lot_unit = $("#new_lot_unit").val();
                    var new_lot_need = $("#new_lot_need").val();
                    var new_lot_start_sum = $("#new_lot_start_sum").val();
                    var new_lot_product_link = $("#new_lot_product_link").val();
                    var new_lot_step_lot = $("#new_lot_step_lot").val();
                    var tr_lots = $("#lots tbody tr").length;
                    if (new_lot_product_link.indexOf('://') == -1) {
                        var a = "http://" + new_lot_product_link;
                        new_lot_product_link = a;
                    }
                    console.log(new_lot_product_link);
                    console.log($('.col_product_link').is(":visible"));
                    if (( 
                            new_lot_name.length > 0 
                            && new_lot_unit.length > 0 
                            && new_lot_need.length > 0 
                        ) 
                        && ( 
                            ( $("#lots th.col_rate_step").is(":visible") == false 
                            && new_lot_step_lot.length == 0) 
                            || ($("#lots th.col_rate_step").is(":visible") == true
                            && new_lot_step_lot.length > 0) 
                            )
                        && ( 
                            ( $("#lots th.col_start_sum").is(":visible") == false 
                            && new_lot_start_sum.length == 0) 
                            || ($("#lots th.col_start_sum").is(":visible") == true
                            && new_lot_start_sum.length > 0) 
                            )
                        && ( 
                            ( $("#lots th.col_product_link").is(":visible") == false 
                            && new_lot_product_link.length == 0) 
                            || ($("#lots th.col_product_link").is(":visible") == true
                            && new_lot_product_link.length > 0) 
                            )
                        ) {
                        html = "<tr id=\"lots_" + (tr_lots + 1) + "\"><td>" + new_lot_name + "</td><td>" + new_lot_unit + "</td><td>" + new_lot_need + '</td><td class="col_start_sum">' + new_lot_start_sum + '</td><td class="col_product_link"><a href="' + new_lot_product_link + '" target="_blank">Ссылка</a></td><td class=\"col_rate_step\">' + new_lot_step_lot + "</td><td><a href=\"\" class=\"button-delete\" title=\"Удалить\" onclick=\"noty({ animateOpen: {opacity: 'show'}, animateClose: {opacity: 'hide'}, layout: 'center', text: 'Вы уверены, что хотите удалить лот из аукциона?', buttons: [ {type: 'btn btn-mini btn-primary', text: 'Удалить', click: function(\$noty) { \$noty.close(); $.DeleteLot(" + (tr_lots + 1) + "); } }, {type: 'btn btn-mini btn-danger', text: 'Отмена', click: function(\$noty) { \$noty.close(); } } ], closable: false, timeout: false }); return false;\"></a></td></tr>";
                        $("#lots tbody").append(html);
                        $("#lots").trigger("update");
                        $("#new_lot_name").val("");
                        $("#new_lot_unit").val("");
                        $("#new_lot_need").val("");
                        $("#new_lot_start_sum").val("");
                        $("#new_lot_product_link").val("");
                        $("#new_lot_step_lot").val("");
                        //rateStep($("input[name=type_rate]:checked"));
                        rateEbay($("input[name=type_auction]:checked"));
                        changeRateIT();
                    }
                }

                $.DeleteLot = function (row_id) {
                    <?php
                    if ($this->uri->segments[2] == 'edit') {
                    ?>
                    $.post('/tenders/deletelot/' + row_id,
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
                                $("#lots_" + row_id).fadeOut().remove();
                                $("#lots").trigger("update");
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
                    <?php
                    } else {
                    ?>
                    $("#lots_" + row_id).fadeOut().remove();
                    $("#lots").trigger("update");
                    <?php
                    }
                    ?>
                    return false;
                }
                //с загрузкой на сервер
                $('input[name="lots_file_base_add_x"]').change(function(e) {
                    e.preventDefault();
                        // Получить загруженный объект файла
                    const { files } = e.target;
                        // Чтение файла через объект FileReader
                    const fileReader = new FileReader();

                    fileReader.onload = event => {
                        try {
                            const { result } = event.target;
                                // Читаем весь объект таблицы Excel в двоичном потоке
                            const workbook = XLSX.read(result, { type: 'binary' });
                                let data = []; // сохранить полученные данные
                                // проходим каждый лист для чтения (здесь по умолчанию читается только первый лист)
                            for (const sheet in workbook.Sheets) {
                                if (workbook.Sheets.hasOwnProperty(sheet)) {
                                        // Используем метод sheet_to_json для преобразования Excel в данные JSON
                                    
                                    data = data.concat(XLSX.utils.sheet_to_json(workbook.Sheets[sheet], {header: 1}));
                                        // break; // Если берется только первая таблица, раскомментируйте эту строку
                                }
                            }
                            console.log(data);

                            let html = data.map((item, i) => {
                                const [ new_lot_name, new_lot_unit, new_lot_need,new_lot_model, new_lot_product_link ] = item;
                                if (new_lot_name != "Наименование") {
                                    var tr_lots = $("#lots tbody tr").length + 1;
                                    return "<tr id=\"lots_" + (tr_lots + i) + "\"><td>" + new_lot_name + "</td><td>" + new_lot_unit + "</td><td>" + new_lot_need + '</td><td class="col_start_sum"></td><td class="col_product_link"><a href="' + new_lot_product_link + '" target="_blank">Ссылка</a></td><td class=\"col_rate_step\">' + '' + "</td><td><a href=\"\" class=\"button-delete\" title=\"Удалить\" onclick=\"noty({ animateOpen: {opacity: 'show'}, animateClose: {opacity: 'hide'}, layout: 'center', text: 'Вы уверены, что хотите удалить лот из аукциона?', buttons: [ {type: 'btn btn-mini btn-primary', text: 'Удалить', click: function(\$noty) { \$noty.close(); $.DeleteLot(" + (tr_lots + i) + "); } }, {type: 'btn btn-mini btn-danger', text: 'Отмена', click: function(\$noty) { \$noty.close(); } } ], closable: false, timeout: false }); return false;\"></a></td></tr>";
                                } else {
                                    return false;
                                }
                            }).join("\n");

                            $('.lots-table tbody').prepend(html);
                            rateEbay($("input[name=type_auction]:checked"));
                            changeRateIT();
                            e.target.closest(".lots-file-import").innerHTML = e.target.closest(".lots-file-import").innerHTML;
                            Send_base_file(files[0]);
                        } catch (err) {
                            // Соответствующие запросы о неправильном типе ошибки файла могут быть брошены сюда
                            console.log(err);
                            console.log ('Неверный тип файла');
                            return;
                        }
                    };
                        // Открыть файл в двоичном режиме
                    fileReader.readAsBinaryString(files[0]);
                });
               function Send_base_file($file){
                    //грузим файл на сервер
                    var tender_id = $('#tender_id_rand').text();
                    var formData = new FormData();
                    formData.append("file", $file, 'temp.xlsx');
                    formData.append("tender_id", tender_id);
                    console.log(formData);
                    var xhr = new XMLHttpRequest();
                    xhr.open("POST", "/upload_tender/it_tenders_upload.php");
                    xhr.send(formData);

                    return false;
                }

                //без загрузки на сервер
                $('input[name="lots_file"]').change(function(e) {
                     e.preventDefault();
                        // Получить загруженный объект файла
                    const { files } = e.target;
                        // Чтение файла через объект FileReader
                    const fileReader = new FileReader();

                    fileReader.onload = event => {
                    try {
                        const { result } = event.target;
                            // Читаем весь объект таблицы Excel в двоичном потоке
                        const workbook = XLSX.read(result, { type: 'binary' });
                            let data = []; // сохранить полученные данные
                            // проходим каждый лист для чтения (здесь по умолчанию читается только первый лист)
                        for (const sheet in workbook.Sheets) {
                            if (workbook.Sheets.hasOwnProperty(sheet)) {
                                    // Используем метод sheet_to_json для преобразования Excel в данные JSON
                                
                                data = data.concat(XLSX.utils.sheet_to_json(workbook.Sheets[sheet], {header: 1}));
                                    // break; // Если берется только первая таблица, раскомментируйте эту строку
                            }
                        }
                        console.log(data);

                        let html = data.map((item, i) => {
                            const [ new_lot_name, new_lot_unit, new_lot_need,new_lot_model, new_lot_product_link ] = item;
                            if (new_lot_name != "Наименование") {
                                var tr_lots = $("#lots tbody tr").length + 1;
                                return "<tr id=\"lots_" + (tr_lots + i) + "\"><td>" + new_lot_name + "</td><td>" + new_lot_unit + "</td><td>" + new_lot_need + '</td><td class="col_start_sum"></td><td class="col_product_link"><a href="' + new_lot_product_link + '" target="_blank">Ссылка</a></td><td class=\"col_rate_step\">' + '' + "</td><td><a href=\"\" class=\"button-delete\" title=\"Удалить\" onclick=\"noty({ animateOpen: {opacity: 'show'}, animateClose: {opacity: 'hide'}, layout: 'center', text: 'Вы уверены, что хотите удалить лот из аукциона?', buttons: [ {type: 'btn btn-mini btn-primary', text: 'Удалить', click: function(\$noty) { \$noty.close(); $.DeleteLot(" + (tr_lots + i) + "); } }, {type: 'btn btn-mini btn-danger', text: 'Отмена', click: function(\$noty) { \$noty.close(); } } ], closable: false, timeout: false }); return false;\"></a></td></tr>";
                            } else {
                                return false;
                            }
                        }).join("\n");

                        $('.lots-table tbody').prepend(html);
                        rateEbay($("input[name=type_auction]:checked"));
                        changeRateIT();
                        e.target.closest(".lots-file-import").innerHTML = e.target.closest(".lots-file-import").innerHTML;
                    } catch (err) {
                        // Соответствующие запросы о неправильном типе ошибки файла могут быть брошены сюда
                        console.log ('Неверный тип файла');
                        return;
                    }
                    };
                        // Открыть файл в двоичном режиме
                    fileReader.readAsBinaryString(files[0]);
                })

                // Проверяем форму
                $("#addtender-form").submit(function () {
                    if ($("#addtender-form").validationEngine('validate') == true) {
                        //выключаем кнопку чтобы не создавались дубликаты при долгой рассылке писем
                        //$("#addtender_submit_btn").prop('disabled', true);
                        $("#addtender_submit_btn").val("Обработка");
                        $("#addtender_submit_btn").css( "width", '111');
                        var value = $("#addtender_submit_btn").val();
                        var sec = 0;
                        setInterval(function(){
                            if(sec == 3){
                                value ='Обработка';
                                sec = 0;
                            }else{
                                value = value +'.';
                                sec++;
                            }
                            $("#addtender_submit_btn").val(value);
                        }, 500);
                        var arr_options = new Array();
                        var k = 0;
                        $("#options tbody tr").find('td').each(function (i) {
                            if ($(this).text() != "" && $(this).is(":visible")) {
                                arr_options[k] = $(this).text();
                                k++;
                            }
                        });

                        var arr_lots = new Array();
                        var k = 0;
                        $("#lots tbody tr").find('td').each(function (i) {
                            // if ($(this).text() != "") {
                            if ($(this).find(".button-delete").length === 0) {
                                if ($(this).text() == "Ссылка") {
                                    var href = $(this).find('a').attr('href');
                                    arr_lots[k] = href;
                                } else{
                                    arr_lots[k] = $(this).text();
                                }
                                k++;
                            }
                            // }
                        });
                        console.log($("input[name=is_add]").val());
                        $.post('/tenders/save/', {
                                is_add: $("input[name=is_add]").val(),
                                tender_id: $("input[name=tender_id]").val(),
                                title: $("#title").val(),
                                begin_date: $("#begin_date").val(),
                                begin_time: $("#begin_time").val(),
                                end_date: $("#end_date").val(),
                                end_time: $("#end_time").val(),
                                description: $("#description").val(),
                                type_rate: $("input[name=type_rate]:checked").val(),
                                type_auction: $("input[name=type_auction]:checked").val(),
                                type_auction_scandinavia: $("#type_auction_scandinavia:checked").val(),
                                type_rate_it: $("#type_rate_it:checked").val(),
                                type_auction_plus: $("#type_auction_plus:checked").val(),
                                tender_minute_end: $("#tender_minute_end").val(),
                                scan_minute: $("#scan_minute").val(),
                                users_visible: $("#users_visible:checked").val(),
                                options: arr_options,
                                lots: arr_lots,
                                new_term_name: $("#new_term_name").val(),
                                new_term_type: $("#new_term_type").val(),
                                new_lot_name: $("#new_lot_name").val(),
                                new_lot_unit: $("#new_lot_unit").val(),
                                new_lot_need: $("#new_lot_need").val(),
                                new_lot_start_sum: $("#new_lot_start_sum").val(),
                                new_lot_product_link: $("#new_lot_product_link").val(),
                                new_lot_step_lot: $("#new_lot_step_lot").val(),
                                tags: $("#add_form_tags").val(),
                                users: $("#add_tender_users").val(),
                            },

                            function (txt) {
                                console.log(txt);
                                get = txt.split('|');
                                if (get[0] == 'success') {
                                    noty({
                                        animateOpen: {opacity: 'show'},
                                        animateClose: {opacity: 'hide'},
                                        layout: 'center',
                                        text: get[1],
                                        type: 'success'
                                    });
                                    setTimeout('redirect_previous()', 3000);
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

                    }
                    return false;
                });
                <?php
                } elseif ($this->uri->segments[2] == 'show') {
                if ($game_tender == FALSE) {
                ?>
                // Проверяем форму
                $("#runtender-form").validationEngine({
                    ajaxFormValidation: true,
                    onAjaxFormComplete: ajaxValidationCallback
                });
                $("#runtender-form").submit(function () {
                    if ($("#runtender-form").validationEngine('validate') == true) {
                        $.post('/tenders/run/', $("#runtender-form").serialize(),
                            function (txt) {
                                console.log(txt);
                                get = txt.split('|');
                                if (get[0] == 'success') {
                                    noty({
                                        animateOpen: {opacity: 'show'},
                                        animateClose: {opacity: 'hide'},
                                        layout: 'center',
                                        text: get[1],
                                        type: 'success'
                                    });
                                    setTimeout('window.location.reload()', 1000);
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
                    }
                    return false;
                });

                $("#termtender-form").submit(function () {
                    if ($("#termtender-form").validationEngine('validate') == true) {
                        $.post('/tenders/save_terms/', $("#termtender-form").serialize(),
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
                    }
                    return false;
                });
                <?php
                }
                if (($group_id == 2 && $tender_author == TRUE) || ($group_id == 5 && $tender_author == TRUE) || $group_id == 3) {
                ?>
                $.SelectWinner = function (user_id, tender_id){
                    $('#winner_reason').css('display', 'flex');
                }
                $._SelectWinner = function (user_id, tender_id, comment = '') {
                    $.post('/tenders/winner/', {user_id: user_id, tender_id: tender_id, comment: comment},
                        function (txt) {
                            console.log(txt);
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

                $.EarlyEnd = function (tender_id, reason) {
                    $.post('/tenders/earlyend/', {tender_id: tender_id, auto_end: 0, reason:reason},
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

                $.Cancellation = function (tender_id, reason) {
                    $.post('/tenders/cancellation/', {tender_id: tender_id, reason: reason},
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
               

                $.GeterateDOC = function (tender_id, user_id) {
                    var fields = $("#protocol-form select[name=commission[]]:input").serializeArray();
                    $.post('/classes/generate_doc.php', {user_id: user_id, tender_id: tender_id, commission: fields},
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
                                setTimeout("location.reload(true);", 2000);
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

                $.GeterateXLS = function (tender_id, user_id) {
                    $.post('/classes/generate_xls.php', {user_id: user_id, tender_id: tender_id},
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
                                setTimeout("location.reload(true);", 2000);
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
                <?php
                }
                } elseif ($this->uri->segments[2] == 'show_history') {
                ?>
                $.ResetLot = function (row_id) {
                    $.post('/tenders/resetlot/' + row_id,
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
                                setTimeout("location.reload(true);", 2000);
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
                <?php
                } elseif ($this->uri->segments[2] == 'add_user' || $this->uri->segments[2] == 'edit_user') {
                ?>
                // Проверяем форму
                $("#addperson-form").submit(function () {
                    if ($("#addperson-form").validationEngine('validate') == true) {
                        $.post('/commission/save/', {
                                person_id: $("input[name=person_id]").val(),
                                rank: $("#rank").val(),
                                fio: $("#fio").val(),
                                post: $("#post").val()
                            },
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
                                    setTimeout('redirect_commission()', 3000);
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

                    }
                    return false;
                });
                <?php
                }
                ?>

            });
        </script>
        <?php
    }

    if (!empty($this->uri->segments[2]) && $this->uri->segments[2] == 'edit' && $this->uri->segments[1] == 'instructions') {
        ?>
        <script src="/editor/ckeditor.js" type="text/javascript" charset="utf-8"></script>
        <script src="/editor/filemanager/ajex.js" type="text/javascript" charset="utf-8"></script>
        <?php
    }

    if ((!empty($this->uri->segments[2]) && ($this->uri->segments[2] == 'users' || $this->uri->segments[2] == 'users_confirm')) || (!empty($this->uri->segments[1]) && $this->uri->segments[1] == 'tenders') || (!empty($this->uri->segments[1]) && $this->uri->segments[1] == 'commission') || (!empty($this->uri->segments[1]) && $this->uri->segments[1] == 'responses')) {
        ?>
        <script>
            $(document).ready(function () {
                <?php
                if ( !empty($this->uri->segments[2]) && ($this->uri->segments[2] == 'users' || $this->uri->segments[2] == 'users_confirm') ) {
                ?>
                // Удаление записи
                $.DeleteItem = function (id) {
                    $.post('/auth/unregister/' + id + '/',
                        function (txt) {
                            get = txt.split('|');
                            if (get[0] == 'success') {
                                $('#row' + id).fadeOut('slow');
                                noty({
                                    animateOpen: {opacity: 'show'},
                                    animateClose: {opacity: 'hide'},
                                    layout: 'center',
                                    text: get[1],
                                    type: 'success'
                                });
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
                }
                <?php
                }
                if ( !empty($this->uri->segments[2]) && ($this->uri->segments[2] == 'previous' || $this->uri->segments[2] == 'current' || $this->uri->segments[2] == 'archive') ) {
                ?>
                // Удаление тендера
                $.DeleteTender = function (id) {
                    $.post('/tenders/delete/' + id + '/',
                        function (txt) {
                            get = txt.split('|');
                            if (get[0] == 'success') {
                                $('#tender_' + id).fadeOut('slow');
                                noty({
                                    animateOpen: {opacity: 'show'},
                                    animateClose: {opacity: 'hide'},
                                    layout: 'center',
                                    text: get[1],
                                    type: 'success'
                                });
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
                }
                <?php
                }
                if ( !empty($this->uri->segments[2]) && $this->uri->segments[2] == 'finished') {
                ?>
                // Перемещение тендера в архив
                $.ArchiveTender = function (id) {
                    $.post('/tenders/inarchive/' + id + '/',
                        function (txt) {
                            get = txt.split('|');
                            if (get[0] == 'success') {
                                $('#tender_' + id).fadeOut('slow');
                                noty({
                                    animateOpen: {opacity: 'show'},
                                    animateClose: {opacity: 'hide'},
                                    layout: 'center',
                                    text: get[1],
                                    type: 'success'
                                });
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
                }
                <?php
                }
                if ( !empty($this->uri->segments[1]) && $this->uri->segments[1] == 'commission') {
                ?>
                // Удаление персоны
                $.DeleteUser = function (id) {
                    $.post('/commission/delete/' + id + '/',
                        function (txt) {
                            get = txt.split('|');
                            if (get[0] == 'success') {
                                $('#user_' + id).fadeOut('slow');
                                noty({
                                    animateOpen: {opacity: 'show'},
                                    animateClose: {opacity: 'hide'},
                                    layout: 'center',
                                    text: get[1],
                                    type: 'success'
                                });
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
                }
                <?php
                }

                if ( !empty($this->uri->segments[1]) && $this->uri->segments[1] == 'responses' ) {
                ?>
                // Проверяем форму отзывов
                $("#settings-form").submit(function () {
                    $.post('/responses/save/', {responses: $("#responses").val()},
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
                                setTimeout("location.reload(true);", 2000);
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
                });

                // Проверяем форму отзывов
                $("#answer-form").submit(function () {
                    $.post('/responses/saveanswer/' + $("input[name=comment_id]").val(), {answer: $("#answer").val()},
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
                                setTimeout('window.location = "/responses/"', 2000);
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
                });

                // Удаление тендера
                $.DeleteComment = function (id) {
                    $.post('/responses/delete/' + id + '/',
                        function (txt) {
                            get = txt.split('|');
                            if (get[0] == 'success') {
                                $('#comment_' + id).fadeOut('slow');
                                noty({
                                    animateOpen: {opacity: 'show'},
                                    animateClose: {opacity: 'hide'},
                                    layout: 'center',
                                    text: get[1],
                                    type: 'success'
                                });
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
                }
                <?php
                } // if ( !empty($this->uri->segments[1]) && $this->uri->segments[1] == 'responses' ) {
                ?>
            });
        </script>
        <?php
    }
    ?>
</head>
<body>

<div id="wrap">
    <div id="header">
        <table>
            <tr>
                <td><a href="/"><img src="/i/b.gif" border="0"/></a></td>
                <td style="vertical-align: middle; padding-left: 10px">
                    <strong>ФПК Инвест</strong>
                    &nbsp;; Тел.: (4912) 306-506; Факс.: (4912) 348-001<br/>
                    WEB: <?php echo $config['engine_url']; ?>; E-mail: <a
                            href="mailto:<?php echo $config['engine_admin_email']; ?>"><?php echo $config['engine_admin_email']; ?></a>;
                    <h1 class="logo"><a href="/"><?php echo $config['engine_title']; ?></a></h1>
                </td>
            </tr>
        </table>
    </div>
    <div id="leftside">
        <div id="menu3">
            <?php
            if (empty($user_id)) {
                $login = array(
                    'name' => 'login',
                    'id' => 'login',
                    'value' => set_value('login'),
                    'maxlength' => 80
                );
                $password = array(
                    'name' => 'password',
                    'id' => 'password'
                );

                if ($this->config->item('allow_registration', 'tank_auth'))
                    echo "			<ul>
				<li>" . anchor('/auth/register/', 'Регистрация') . "</li>
			</ul>";
                ?>
                <?php echo form_open('auth/login'); ?>
                <strong><?php echo form_label('Логин', $login['id']); ?></strong>
                <?php echo form_input($login); ?>
                <strong><?php echo form_label('Пароль', $password['id']); ?></strong>
                <?php echo form_password($password); ?>
                <?php echo form_hidden('remember', 1); ?>
                <input type="submit" value="Вход" class="input"/>
                <input type="button" value="Напомнить" class="input" onclick="location.href='/auth/forgot_password/'"/>
                <?php echo form_close(); ?>
                <ul>
                    <li><a href="/instructions/">Инструкция</a></li>
                </ul>
                <?php
            } else {
                echo "			<form action=\"/auth/logout/\" method=\"post\">
				<strong><a href=\"/auth/user_edit/\">" . $name . "</a></strong><br/>
				<span style=\"font-size: 0.9em;\">" . $group_title . "</span><br/>
				<input type=\"submit\" value=\"Выход\" class=\"input\" />
			</form>";

                if ($group_id == 1 || $group_id == 6) {
                    ?>
                    <ul>
                        <li><span>Аукционы</span></li>
                        <li><a href="/tenders/previous/" title="Предстоящие">Предстоящие</a></li>
                        <li><a href="/tenders/current/" title="Текущие">Текущие</a></li>
                        <li><a href="/tenders/finished/" title="Завершённые">Завершённые</a></li>
                    </ul>
                    <ul>
                        <li><a href="/instructions/">Инструкция</a></li>
                        <li><a href="/responses/">Отзывы</a></li>
                    </ul>
                    <?php
                } elseif ($group_id == 2 || $group_id == 3 || $group_id == 5 || $group_id ==7) {
                    ?>
                    <ul>
                        <li><span>Инструкция</span></li>
                        <li><a href="/instructions/">Смотреть</a></li>
                        <li><a href="/instructions/edit/">Редактировать</a></li>
                        <li><span>Пользователи</span></li>
                        <li><a href="/auth/users/" title="Список пользователей">Список</a></li>
                        <?php
                        if ($group_id == 3) {
                            ?>
                            <li><a href="/auth/users_confirm/"
                                   title="Пользователи, ожидающие подтверждения">Ожидающие</a></li>
                            <li><a href="/auth/users_blacklist/" title="Черный список пользователей">Черный список</a>
                            </li>
                            <?php
                        }
                        ?>
                        <li><span>Аукционы</span></li>
                        <li><a href="/tenders/add/" title="Добавить">Добавить</a></li>
                        <li><a href="/tenders/previous/" title="Предстоящие">Предстоящие</a></li>
                        <li><a href="/tenders/current/" title="Текущие">Текущие</a></li>
                        <li><a href="/tenders/finished/" title="Завершённые">Завершённые</a></li>
                        <li><a href="/tenders/archive/" title="Архив">Архив</a></li>
                        <li><a href="/tenders/tags/" title="Категории">Категории</a></li>
                        <li><span>Комиссия</span></li>
                        <li><a href="/commission/add_user/" title="Добавить личность">Добавить</a></li>
                        <li><a href="/commission/" title="Состав комиссии">Состав</a></li>
                        <li><span>Администрирование</span></li>
                        <li><a href="/responses/">Отзывы</a></li>
                        <li><a href="/settings/" title="Настройки">Настройки</a></li>
                    </ul>
                    <?php
                } elseif ($group_id == 4) {
                    ?>
                    <ul>
                        <li><span>Пользователи</span></li>
                        <li><a href="/auth/users/" title="Список пользователей">Список</a></li>
                        <li><a href="/auth/users_confirm/" title="Пользователи, ожидающие подтверждения">Ожидающие</a>
                        </li>
                        <li><a href="/auth/users_blacklist/" title="Черный список пользователей">Черный список</a></li>
                    </ul>
                    <?php
                }
            }
            ?>
        </div>
    </div>
    <div id="content">
