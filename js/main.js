$(document).ready(function() {
    $('#filter_tags').select2({
        allowClear: true,
        placeholder: "Выберите категорию",
    });

    $('#filter_tags').on('change',function(){
        $('#filter_form').submit();
    });

    $('#reg_tags').select2({
        placeholder: "Категории",
    });
    $('#add_form_tags').select2({
        placeholder: "Выберите категории для аукциона",
    });
    $('#add_tender_users').select2({
        placeholder: "Выберите участников для аукциона",
    });
    $('#notice_disable').on('click',function(){
       $('#notice_block').toggle();
    });

    $('#reg_tags').on('change',function(){
        $('#select_all_tags').attr('checked',false);
    });

    $('#select_all_tags').on('click',function(){
        if($("#select_all_tags").is(':checked') ){
            $("#reg_tags > option").prop("selected","selected");
            $("#reg_tags").trigger("change");
            $('#select_all_tags').attr('checked',true);
        }else{
            $("#reg_tags > option").removeAttr("selected");
            $("#reg_tags").trigger("change");
        }
    });
});
