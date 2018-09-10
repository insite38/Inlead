<a class="jump_back" href="/admin/users/">Назад</a>
<form action="{$actionType}UserGo.php?id={$smarty.get.id}" method="POST">
    <table cellpadding="3" cellspacing="0" border="0" width="100%" class="category_list">
        <tr><td colspan="2" align="center">{$error}</td></tr>

        <tr>
            <td align="right" width="30%"><b>Имя пользователя (login):</b></td>
            <td align="left"><input type="text" name="login" value="{if $smarty.post.login}{$smarty.post.login|escape}{else}{$login}{/if}" maxlength="255" style="width: 90%;"></td>
        </tr>

        <tr>
            <td align="right" width="30%"><b>ФИО:</b></td>
            <td align="left"><input type="text" name="fio" value="{if $smarty.post.fio}{$smarty.post.fio|escape}{else}{$fio}{/if}" maxlength="255" style="width: 90%;"></td>
        </tr>

        <tr>
            <td align="right" width="30%">Пароль:</td>
            <td align="left"><input type="text" name="password_1" maxlength="255" style="width: 90%;"></td>
        </tr>

        <tr>
            <td align="right" width="30%">Пароль (еще раз):</td>
            <td align="left"><input type="text" name="password_2" maxlength="255" style="width: 90%;"></td>
        </tr>

        <tr>
            <td align="right" width="30%">E-mail адрес:</td>
            <td align="left"><input type="text" name="email" value="{if $smarty.post.email}{$smarty.post.email|escape}{else}{$email}{/if}" maxlength="255" style="width: 90%;"></td>
        </tr>

        <tr>
            <td align="right" width="30%" valign="top">Права администратора<br>(доступ ко всем модулям системы):</td>
            <td align="left"><input type="checkbox" name="accessRights"{if ($smarty.post.accessRights == "on" || $accessRights == 1)} checked{/if}></td>
        </tr>

        <tr>
            <th colspan="2">Доступные модули в системе для пользователя</th>
        </tr>

        <tr>
            <td align="center" colspan="2">

                <table border="0" align="center" cellpadding="10" width="100%">
                    {foreach from="$loadedModules" key="mLang" item="empty"}
                        {cycle name="openTag" values="<tr>,,"}
                        <td align="left">
                            <p><b>Язык/направление: {$mLang}</b></p>
                            {foreach from=$loadedModules.$mLang item="mArray"}
                                <input type="checkbox" name="accessModules[]" value="{$mArray.dir}:{$mLang}"{if (isset($accessModules) && is_array($accessModules) && array_search("`$mArray.dir`:$mLang", $accessModules) !== false) || (isset($smarty.post.accessModules) && is_array($smarty.post.accessModules) && array_search("`$mArray.dir`:$mLang", $smarty.post.accessModules) !== false)} checked{/if}> {$mArray.title}<br>
                            {/foreach}
                        </td>
                        {cycle name="closeTag" values=",,</tr>"}
                    {/foreach}
                </table>
            </td>
        </tr>
    </table>

                <p align="center"><input type="submit" value="СОХРАНИТЬ"></p>
</form>