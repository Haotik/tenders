<?php
    $answer = array(
        'name'  => 'answer',
        'id'    => 'answer',
        'value' => (!empty($_POST['answer']) ? $_POST['answer'] : set_value('answer')),
        'rows'  => 5
    );
?>
				<h4>Написать ответ на отзыв</h4>
                <?php echo form_open("responses/saveanswer", array('id' => 'answer-form')); ?>
                    <?php echo form_hidden('comment_id', $comment[0]['id']); ?>
                    <table class="reg">
                        <tr>
                            <td class="td_left">Отзыв:</td>
                            <td>
                                <?php echo str_replace("
", "<br/>", $comment[0]['comment']); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="td_left"><?php echo form_label('Ответ на отзыв', $answer['id']); ?><span class="red">*</span>:</td>
                            <td>
                                <?php echo form_textarea($answer); ?>
                                <?php echo form_error($answer['name']); ?><?php echo isset($errors[$answer['name']])?$errors[$answer['name']]:''; ?>
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
                                <?php echo form_submit('save', 'Отправить ответ', 'class="button"'); ?>&nbsp;<?php echo form_reset('cancel', 'Отмена', 'class="button"'); ?>
                            </td>
                        </tr>
                    </table>
                <?php echo form_close(); ?>
