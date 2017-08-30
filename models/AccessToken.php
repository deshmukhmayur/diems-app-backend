<?php

class AccessToken extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = [
        'id',
        'token',
        'u_id',
        'u_type',
    ];

    public $timestamps = false;
}

?>