<?php

class UsersSiteModel extends BaseModel
{
    public function __construct($db)
    {
        parent::__construct($db, 'users_site');
    }
}