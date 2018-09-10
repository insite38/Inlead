<?php

class plugin_photosMain
{

    protected $sql = '';

    function __construct($params)
    {
        global $API;
        global $sql;

        $this->sql = $sql;
    }

    function start(){
    	$this->sql->query('SELECT `filename` FROM `gallery_images` WHERE `owner` = 33 ORDER BY `id` DESC');

    	$result = '';
    	while ($row = $this->sql->next_row()) {
    		$result .= '
                    <div class="item">
                        <div class="item_lines"></div>
                        <div class="img">
                            <a href="/upload/images/gallery/preview/'.$row['filename'].'" title="" data-gallery="">
                                <img src="/upload/images/gallery/thumb/'.$row['filename'].'" alt="" />
                            </a>
                        </div>
                    </div>';
    	}

    	return $result;
    }
}