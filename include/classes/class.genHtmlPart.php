<?
class genHtmlPart { 
        function genCitySelect($valueName="selectCity") {
                global $cityIds;
                $return = "";
                $return = "<select name=\"".$valueName."\" size=\"1\"><option value=\"0\">Любой город</option>";
                foreach ($cityIds as $key=>$value) {
                        $return.= "<option value=\"".$key."\">".$value."</option>";
                        }
                return $return."</select>";
                }
        }
?>