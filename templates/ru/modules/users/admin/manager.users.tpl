{*
manager.users.tpl
*}
<a class="new_article" href="/admin/users/addUser.php">Добавить пользователя</a>
<table class="category_list" cellpadding="1" cellspacing="0" width="100%">
    {foreach from="$users" item="item" key="key"}
        <tr>
            <td>
                <b>{$users.$key.fio}</b> (Login: {$users.$key.login} <font color="grey">[ID:{$users.$key.userId}]</font>)
            {if $users.$key.accessRights == "1"} <font color="red"><b>[администратор]</b></font>{/if}
        {if $users.$key.accessRights == "2"} <font color="green"><b>[модератор]</b></font>{/if}
        <a href="/admin/users/editUser.php?id={$users.$key.userId}" title="Редактировать">
            <img src="/templates/ru/images/icn_edit.png" alt="Редактировать" align="absmiddle" />
        </a>
        <a href="delete.php?id={$users.$key.userId}" onclick="return confirm('Вы уверены?')" title="Удалить">
            <img src="/templates/ru/images/icn_trash.png" alt="Удалить" align="absmiddle" />
        </a>
    </td>
</tr>
{/foreach}
</table>