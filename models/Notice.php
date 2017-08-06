<?php

class NoticeDetail extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = [
        'title',
        'body',
        'img_url',
        'end_date',
        'branch',
        'class',
        'divsion',
        'user_type'
    ];
}

?>