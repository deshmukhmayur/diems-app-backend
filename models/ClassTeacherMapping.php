<?php

class ClassTeacherMapping extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = [
        'staff_detail_id',
        'class_mapping_id'
    ];

    public $timestamps = false;

    public function class() {
        return $this->belongsTo('\ClassMapping', 'class_mapping_id');
    }

    public function staff() {
        return $this->belongsTo('\StaffDetail', 'staff_detail_id');
    }
}

?>