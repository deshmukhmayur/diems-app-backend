<?php

class AdminUser extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = [
        'id',
        'username',
        'password',
        'u_type'
    ];

    public $timestamps = false;
}

?>