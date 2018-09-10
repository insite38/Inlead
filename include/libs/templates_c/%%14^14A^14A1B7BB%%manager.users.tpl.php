<?php /* Smarty version 2.6.16, created on 2016-06-27 11:26:53
         compiled from ru/modules/users/admin/manager.users.tpl */ ?>
<a class="new_article" href="/admin/users/addUser.php">Добавить пользователя</a>
<table class="category_list" cellpadding="1" cellspacing="0" width="100%">
    <?php $_from = ($this->_tpl_vars['users']); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['item']):
?>
        <tr>
            <td>
                <b><?php echo $this->_tpl_vars['users'][$this->_tpl_vars['key']]['fio']; ?>
</b> (Login: <?php echo $this->_tpl_vars['users'][$this->_tpl_vars['key']]['login']; ?>
 <font color="grey">[ID:<?php echo $this->_tpl_vars['users'][$this->_tpl_vars['key']]['userId']; ?>
]</font>)
            <?php if ($this->_tpl_vars['users'][$this->_tpl_vars['key']]['accessRights'] == '1'): ?> <font color="red"><b>[администратор]</b></font><?php endif; ?>
        <?php if ($this->_tpl_vars['users'][$this->_tpl_vars['key']]['accessRights'] == '2'): ?> <font color="green"><b>[модератор]</b></font><?php endif; ?>
        <a href="/admin/users/editUser.php?id=<?php echo $this->_tpl_vars['users'][$this->_tpl_vars['key']]['userId']; ?>
" title="Редактировать">
            <img src="/templates/ru/images/icn_edit.png" alt="Редактировать" align="absmiddle" />
        </a>
        <a href="delete.php?id=<?php echo $this->_tpl_vars['users'][$this->_tpl_vars['key']]['userId']; ?>
" onclick="return confirm('Вы уверены?')" title="Удалить">
            <img src="/templates/ru/images/icn_trash.png" alt="Удалить" align="absmiddle" />
        </a>
    </td>
</tr>
<?php endforeach; endif; unset($_from); ?>
</table>