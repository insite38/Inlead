<?php /* Smarty version 2.6.16, created on 2016-06-27 11:26:56
         compiled from ru/modules/users/admin/add.or.edit.user.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'ru/modules/users/admin/add.or.edit.user.tpl', 8, false),array('function', 'cycle', 'ru/modules/users/admin/add.or.edit.user.tpl', 45, false),)), $this); ?>
<a class="jump_back" href="/admin/users/">Назад</a>
<form action="<?php echo $this->_tpl_vars['actionType']; ?>
UserGo.php?id=<?php echo $_GET['id']; ?>
" method="POST">
    <table cellpadding="3" cellspacing="0" border="0" width="100%" class="category_list">
        <tr><td colspan="2" align="center"><?php echo $this->_tpl_vars['error']; ?>
</td></tr>

        <tr>
            <td align="right" width="30%"><b>Имя пользователя (login):</b></td>
            <td align="left"><input type="text" name="login" value="<?php if ($_POST['login']):  echo ((is_array($_tmp=$_POST['login'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp));  else:  echo $this->_tpl_vars['login'];  endif; ?>" maxlength="255" style="width: 90%;"></td>
        </tr>

        <tr>
            <td align="right" width="30%"><b>ФИО:</b></td>
            <td align="left"><input type="text" name="fio" value="<?php if ($_POST['fio']):  echo ((is_array($_tmp=$_POST['fio'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp));  else:  echo $this->_tpl_vars['fio'];  endif; ?>" maxlength="255" style="width: 90%;"></td>
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
            <td align="left"><input type="text" name="email" value="<?php if ($_POST['email']):  echo ((is_array($_tmp=$_POST['email'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp));  else:  echo $this->_tpl_vars['email'];  endif; ?>" maxlength="255" style="width: 90%;"></td>
        </tr>

        <tr>
            <td align="right" width="30%" valign="top">Права администратора<br>(доступ ко всем модулям системы):</td>
            <td align="left"><input type="checkbox" name="accessRights"<?php if (( $_POST['accessRights'] == 'on' || $this->_tpl_vars['accessRights'] == 1 )): ?> checked<?php endif; ?>></td>
        </tr>

        <tr>
            <th colspan="2">Доступные модули в системе для пользователя</th>
        </tr>

        <tr>
            <td align="center" colspan="2">

                <table border="0" align="center" cellpadding="10" width="100%">
                    <?php $_from = ($this->_tpl_vars['loadedModules']); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['mLang'] => $this->_tpl_vars['empty']):
?>
                        <?php echo smarty_function_cycle(array('name' => 'openTag','values' => "<tr>,,"), $this);?>

                        <td align="left">
                            <p><b>Язык/направление: <?php echo $this->_tpl_vars['mLang']; ?>
</b></p>
                            <?php $_from = $this->_tpl_vars['loadedModules'][$this->_tpl_vars['mLang']]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['mArray']):
?>
                                <input type="checkbox" name="accessModules[]" value="<?php echo $this->_tpl_vars['mArray']['dir']; ?>
:<?php echo $this->_tpl_vars['mLang']; ?>
"<?php if (( isset ( $this->_tpl_vars['accessModules'] ) && is_array ( $this->_tpl_vars['accessModules'] ) && array_search ( ($this->_tpl_vars['mArray']['dir']).":".($this->_tpl_vars['mLang']) , $this->_tpl_vars['accessModules'] ) !== false ) || ( isset ( $_POST['accessModules'] ) && is_array ( $_POST['accessModules'] ) && array_search ( ($this->_tpl_vars['mArray']['dir']).":".($this->_tpl_vars['mLang']) , $_POST['accessModules'] ) !== false )): ?> checked<?php endif; ?>> <?php echo $this->_tpl_vars['mArray']['title']; ?>
<br>
                            <?php endforeach; endif; unset($_from); ?>
                        </td>
                        <?php echo smarty_function_cycle(array('name' => 'closeTag','values' => ",,</tr>"), $this);?>

                    <?php endforeach; endif; unset($_from); ?>
                </table>
            </td>
        </tr>
    </table>

                <p align="center"><input type="submit" value="СОХРАНИТЬ"></p>
</form>