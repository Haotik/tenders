     $.show_popup_date = function (){
        $("#new_date_picker").css('display', 'flex');
        $("#new_end_date").datepicker($.datepicker.regional["ru"]);
    }

    $('#change_date_cancel').on('click', function(event) {
        $("#new_date_picker").css('display', 'none');
    });

    $('#winner_request').on('click', function(event) {
        var $reason = $('#winner_comment').val();
        var $winner = $('input[name=victory_member]').val();
        if ($reason.trim() == '') {
            $('.erore_place').html('Комментарий не может быть пустым');
        } else {
            var $tender_id = $(this).val();
            $._SelectWinner($winner, $tender_id, $reason);
            $("#winner_reason").css('display', 'none');
        }
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
    $('#winner_cancel').on('click', function(event) {
        $("#winner_reason").css('display', 'none');
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
$(document).ready(function($) {
    $('#load_lots').change(function(e) {
        
        // Получить загруженный объект файла
        const { files } = e.target;
        const fileReader = new FileReader();
        
        fileReader.addEventListener('load', event => {
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

                data.map((item, i) => {
                    const [ new_lot_name, new_lot_unit, new_lot_need, new_lot_product_link, new_lot_product_name, new_lot_price ] = item;
                    //разобрали строку xcel
                    //теперь надо найти соответсвие имени - строке 
                    $('#lots_show').find('tr').each(function(){
                      var $name = $(this).find('.lot_name').text();
                      if ($name == new_lot_name){
                        $(this).find('input[name="product_name"]').val(new_lot_product_name);
                        $(this).find('.middle').val(new_lot_price);
                      }
                    })

                })
                
                } catch (err) {
                    // Соответствующие запросы о неправильном типе ошибки файла могут быть брошены сюда
                    console.log ('Неверный тип файла');
                    return;
                }
        });
        fileReader.readAsBinaryString(files[0]);
    });

});