<?php
if (!defined("API")) {
	exit("Main include fail");
}

class mCfg extends cfg {
	public $cfg = array(
						array(
							"defaultTemplate",
							"Основной шаблон для модуля",
							"Оставьте на заполненым, если необходимо использовать основной шаблон системы",
							"text",
							"",
							"no"),

						array(
							"md",
							"Краткое описание (meta)",
							"Краткое описание всех страниц в модуле по умолчанию. Используется для SEO оптимизации.",
							"text",
							"",
							"no"),

						array(
							"mk",
							"Ключевые слова (meta)",
							"Ключевые слова всех страниц в модуле по умолчанию. Используется для SEO оптимизации",
							"text",
							"",
							"no"),

				);


	public function check() {
        return true;
	}
}
?>
