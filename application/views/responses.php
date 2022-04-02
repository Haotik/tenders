<?php
    $responses = array(
        'name'  => 'responses',
        'id'    => 'responses',
        'value' => (!empty($_POST['responses']) ? $_POST['responses'] : set_value('responses')),
        'rows'  => 5
    );
?>
				<h4>Написать отзыв</h4>
                <?php echo form_open("responses/save", array('id' => 'settings-form')); ?>
                    <table class="reg">
                        <tr>
                            <td class="td_left"><?php echo form_label('Комментарий', $responses['id']); ?><span class="red">*</span>:</td>
                            <td>
                                <?php echo form_textarea($responses); ?>
                                <?php echo form_error($responses['name']); ?><?php echo isset($errors[$responses['name']])?$errors[$responses['name']]:''; ?>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
                                <span class="red">* обязательное поля</span>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
                                <?php echo form_submit('save', 'Отправить отзыв', 'class="button"'); ?>&nbsp;<?php echo form_reset('cancel', 'Отмена', 'class="button"'); ?>
                            </td>
                        </tr>
                    </table>
                <?php echo form_close(); ?>

				<h4><?php echo $page_title; ?></h4>
<?php
	if (!empty($comments))
	{
		foreach( $comments as $key => $value )
		{
?>
				<div class="comment-item" id="comment_<?php echo $value['id']; ?>">
                    <div class="detail-item">
<?php

    if ($group_id == 2 || $group_id == 3) {
        echo "                        <p class=\"fio\"><a href=\"/auth/user_edit/" . $value['user_id'] . "\" target=\"_blank\">" . $value['name'] . "</a></p>";
    }
    else
        echo "                        <p class=\"fio\"><strong>" . $value['name'] . "</strong></p>";

?>
                        <p class="date"><?php echo date("d.m.Y", $value['date_publish']); ?> <?php echo date("H:i", $value['date_publish']); ?></p>
                    </div>
                    <div class="comment">
                        <?php echo str_replace("
", "<br/>", $value['comment']); ?>
                    </div>
<?php if ( !empty($value['answer']) ) : ?>
                    <div class="clear"></div>
                    <div class="answer">
                        <p class="fio"><strong>Администратор</strong> отвечает</p>
                        <?php echo str_replace("
", "<br/>", $value['answer']); ?>
                    </div>
<?php endif; ?>
<?php

    if ( $group_id == 2 || $group_id == 3 ) {
        echo "<p class=\"links-answer\"><a href=\"/responses/answer/" . $value['id'] . "\">Ответить на отзыв</a> | <a href=\"\" onclick=\"noty({ animateOpen: {opacity: 'show'}, animateClose: {opacity: 'hide'}, layout: 'center', text: 'Вы действительно хотите удалить отзыв?', buttons: [ {type: 'btn btn-mini btn-primary', text: 'Удалить', click: function(\$noty) { \$noty.close(); $.DeleteComment(" . $value['id'] . "); } }, {type: 'btn btn-mini btn-danger', text: 'Отмена', click: function(\$noty) { \$noty.close(); } } ], closable: false, timeout: false }); return false;\">Удалить отзыв</a></p>";
    }

?>
                </div>
				<div class="clear"></div>
<?php
		}
		echo $paginate;
    }
    else
        echo "<p>Отзывов пока нет.</p>";
?>
