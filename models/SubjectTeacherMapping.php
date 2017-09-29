<?php

class SubjectTeacherMapping extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = [
        'subject_detail_id',
        'staff_detail_id',
        'class_mapping_id'
    ];

    public $timestamps = false;

    public function subject() {
        return $this->belongsTo('\SubjectDetail', 'subject_detail_id');
    }

    public function staff() {
        return $this->belongsTo('\StaffDetail', 'staff_mapping_id');
    }

    public function class() {
        return $this->belongsTo('\ClassMapping', 'class_mapping_id');
    }
}

?>