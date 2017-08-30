<?php

class SubjectTeacherMapping extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = [
        'subject_detail_id',
        'staff_detail_id',
        'class_mapping_id'
    ];

    public $timestamps = false;
}

?>