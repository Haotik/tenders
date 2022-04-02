<h4><?php echo $page_title; ?></h4>

<table class="reg reg-options">
    <tr bgcolor='#FF5E5E'>
        <th>id</th>
        <th>Название</th>
        <th>Действия</th>
    </tr>
    <?php if($all_tags != null):?>
        <?php foreach($all_tags as $tag):?>
            <tr>
                <td><?php echo $tag['id'];?></td>
                <td><?php echo $tag['caption'];?></td>
                <td>
                    <?php if($group_id == 3):?>
                        <a href="/tenders/tag_delete/<?php echo $tag['id'];?>/" class="button-delete" title="Удалить категорию" onclick="return confirm('Вы действительно хотите удалить категорию?');"></a>
                    <?php endif;?>
                </td>
            </tr>
        <?php endforeach;?>
    <?php endif;?>
</table>

<h4>Добавить категорию</h4>
<?php if(isset($post_error)):?>
    <div style="color:red;">
        <?php echo $post_error;?>
    </div>
<?php endif;?>
<form method="post">
    <label>Введите название категории:</label>
    <input type="text" name="caption" placeholder="Название категории" style="width: 100%;margin:10px 0;"><br>
    <input type="submit" name="sbmt" value="Добавить">
</form>
