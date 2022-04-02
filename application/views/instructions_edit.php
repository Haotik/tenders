<h4><?php echo $page_title; ?></h4>

<?php echo (!empty($message) ? '<p>' . $message . '</p>' : ''); ?>

<?php
	echo form_open_multipart('instructions/save');

	$data = array(
		'name'		=> 'page_content',
		'id'		=> 'page_content',
		'value'		=> ($page_content ? $page_content : ''),
		'cols'		=> 80,
		'rows'		=> 10
	);
	echo form_textarea($data);
	echo "<script type=\"text/javascript\">
var ckeditor = CKEDITOR.replace('page_content');
AjexFileManager.init({returnTo: 'ckeditor', editor: ckeditor, width: '100%', skin: 'light', lang: 'ru'});
</script>";

	echo "<p style=\"padding-top: 15px;\">" . form_submit('save', 'Сохранить', 'class="button"') . "</p>";

	echo form_close();
