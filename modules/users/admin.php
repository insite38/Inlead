<?php
if (!defined("API")) {
	exit("Main include fail");
}

$module = new usersModule();
$module->lang 	= @$admLng;
$module->smarty =&$smarty;
$module->exel 	= &$exel;

switch ($rFile) {
	// Вывод страницы отображения странцы "Управление пользователями)
	case "managerUsers.php":
	case "index.php":
                $module->page_title = $module->lang['users_list'];
		$API['content'] = $module->getManagerUsersForm();
	break;

	// Добовление пользователя
	case "addUser.php":
		$API['content'] = $module->getAddOrEditUserForm("add");
	break;

	case "addUserGo.php":
		$error = $module->getPostDataError("add", $_POST);

		// Устанавливаем права пользователя
		if ($error === false) {
			if (isset($_POST['accessRights']) && $_POST['accessRights'] === "on") {
				$accessRights = 1; // права администратора
			} elseif (isset($_POST['accessModules']) && is_array($_POST['accessModules']) && sizeof(array_filter($_POST['accessModules'])) > 0) {
					$accessRights = 2;
				} else {
					$accessRights = 0;
				}



			// add
			$sql->query("
						INSERT INTO
									`#__#users_v2`
													(
														`fio`,
														`login`,
														`password`,
														`email`,
														`accessRights`,
														`accessModules`,
														`lastLogin`,
														`regTime`,
														`addedBy`
														)
						VALUES
													(
													'".$sql->escape($_POST['fio'])."',
													'".$sql->escape($_POST['login'])."',
													'".crypt($sql->escape($_POST['password_1']))."',
													'".$sql->escape($_POST['email'])."',
													'".$accessRights."',
													'".$sql->escape(implode(",", (isset($_POST['accessModules']) && is_array($_POST['accessModules']) ? $_POST['accessModules'] : array())))."',
													'0000-00-00 00:00:00',
													NOW(),
													'".$sql->escape("")."'
													)


						");

			message("OK", null, "/admin/users/");

		} else {
			//error
			$API['content'] = $module->getAddOrEditUserForm("add", $error);
		}
	break;

	case "editUser.php":
		if (!isset($_GET['id']) || empty($_GET['id'])) page404();

		$sql->query("
						SELECT
								*,
								`accessModules`
						FROM
								`#__#users_v2`
						WHERE
								`userId` = '".(int)$_GET['id']."'
								",
								true);

		if ($sql->num_rows() !== 1) page404();

		$accessModules = explode(",", $sql->result['accessModules']);

		$API['content'] = $module->getAddOrEditUserForm("edit", null, (array('accessModules' => $accessModules) + $sql->result));
	break;

	case "delete.php":
		if (!isset($_GET['id']) || empty($_GET['id'])) {
			page404();
		}

		$sql->query("
							DELETE
						 	FROM
						 			`#__#users_v2`

						 	WHERE
						 			`userId` = '".$sql->escape((int)$_GET['id'])."'
						 			");
		message("OK", "", "/admin/".$uri[2]."/managerUsers.php");
	break;


	case "editUserGo.php":
		$error = $module->getPostDataError("edit", $_POST);

		// Устанавливаем права пользователя
		if ($error === false) {
			if (isset($_POST['accessRights']) && $_POST['accessRights'] === "on") {
				$accessRights = 1; // права администратора
			} elseif (isset($_POST['accessModules']) && is_array($_POST['accessModules']) && sizeof(array_filter($_POST['accessModules'])) > 0) {
					$accessRights = 2;
				} else {
					$accessRights = 0;
				}

			$sql->query("
							UPDATE
									`#__#users_v2`
							SET
									`fio` = '".$sql->escape($_POST['fio'])."',
									`login` = '".$sql->escape($_POST['login'])."',
									".(!empty($_POST['password_1'])  ? "`password` = '".$sql->escape(crypt($_POST['password_1']))."'," : "")."
									`email` = '".$sql->escape($_POST['email'])."',
									`accessRights` = '".$accessRights."',
									`accessModules` = '".$sql->escape(implode(",", (isset($_POST['accessModules']) && is_array($_POST['accessModules']) ? $_POST['accessModules'] : array())))."'
							WHERE
									`userId` = '".(int)@$_GET['id']."'");
			message("OK", null, "admin/".$uri[2]."/index.php");
		} else {
			$API['content'] = $module->getAddOrEditUserForm("edit", $error);
		}



	break;

	default:
		page404();
	break;


}
$API['pageTitle'] = $module->lang['users'] . ' / ' . $module->page_title;
?>
