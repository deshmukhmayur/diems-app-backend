<?php

class ClassTeacherMapping extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = [
        'staff_detail_id',
        'class_mapping_id'
    ];

    public $timestamps = false;
}

?>