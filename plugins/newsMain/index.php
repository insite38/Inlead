<?php

class plugin_newsMain
{

    protected $sql = '';

    function __construct($params)
    {
        global $API;
        global $sql;

        $this->sql = $sql;
    }

    function start(){
    	$this->sql->query('SELECT `title`,`smallText`,`image` FROM `news` WHERE `ownerId` = 3 ORDER BY `id` DESC');

    	$result = '
                    <div class="item">';
        $n=0;
    	while ($row = $this->sql->next_row()) {
    		$result .= '
                        <div class="row carusel_item">
                            <div class="carusel_item_l">
                                <img src="/userfiles/news/'.$row['image'].'" alt="" />
                            </div>
                            <div class="carusel_item_r">
                                <strong>'.$row['title'].'</strong>
                                '.$row['smallText'].'
                            </div>
                        </div>';

            if($n >= 2){
                $result .= '
                    </div>
                    <div class="item">';
                $n = 0;
            }else{
                $n++;
            }
    	}

                $result .= '
                    </div>';

    	return $result;
    }
}