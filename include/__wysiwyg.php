<?php
class FCKeditor
{

    public $InstanceName;
    public $BasePath;
    public $Width;
    public $Height;
    public $ToolbarSet;
    public $Value;
    public $Config;

    public function __construct($instanceName)
    {
        $this->InstanceName = $instanceName;
        $this->BasePath = '';
        $this->Width = '100%';
        $this->Height = '200';
        $this->ToolbarSet = 'Default';
        $this->Value = '';

        $this->Config = array();
    }

    public function Create()
    {
        echo $this->CreateHtml();
    }

    /**
     * TODO: preview
     */

    public function CreateHtml()
    {/*contextmenu,*/
        $script_init = '
            <script type="text/javascript">
			tinymce.init({
				language: "ru",
				mode : "exact",
				elements : "' . $this->InstanceName . '",
    			theme: "modern",
				relative_urls: false,
				plugins: [
        			"improvedcode advlist autolink lists link image charmap print preview hr anchor pagebreak",
        			"searchreplace wordcount visualblocks visualchars code fullscreen",
        			"insertdatetime media nonbreaking save table contextmenu directionality",
        			"paste textcolor colorpicker textpattern"
    			],
    			toolbar1: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | code improvedcode | media | forecolor", file_browser_callback: RoxyFileBrowser,
			//HTML ImprovedCode
			improvedcode_options : {
                height: 580,
                indentUnit: 4,
                tabSize: 4,
                lineNumbers: true
            },
				content_css : "/css/style.css?ver="+new Date().getTime()+", /css/adaptive.css?ver="+new Date().getTime()
			});
			
			function RoxyFileBrowser(field_name, url, type, win) {
				var roxyFileman = \'/include/tinymce/plugins/fileman/index.html\';
				if (roxyFileman.indexOf("?") < 0) {     
					roxyFileman += "?type=" + type;   
				}
				else {
					roxyFileman += "&type=" + type;
				}
				roxyFileman += \'&input=\' + field_name + \'&value=\' + document.getElementById(field_name).value;
				if(tinyMCE.activeEditor.settings.language){
					roxyFileman += \'&langCode=\' + tinyMCE.activeEditor.settings.language;
				}
				tinyMCE.activeEditor.windowManager.open({
					file: roxyFileman,
					title: \'Менеджер файлов\',
					width: 960, 
					height: 650,
					resizable: "yes",
					plugins: "media",
					inline: "yes",
					close_previous: "no"  
				}, {     window: win,     input: field_name    });
				return false; 
			}
		</script>';
        $area = '<div style="text-align: left; font-size: 0.6em;"><a href="#" class="tinymce_toggle">вкл/выкл редактор</a></div>
		    <textarea
		        name="' . $this->InstanceName . '"
		        width="' . $this->Width . '"
		        height="' . $this->Height . '"
		        id="' . $this->InstanceName . '"
		        style="width: 100%; height: 300px; "
		    >
		    ' . $this->Value . '
		    </textarea>';

        return $script_init . $area;
    }
}

?>