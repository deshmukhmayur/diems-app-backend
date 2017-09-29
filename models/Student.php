<?php

class StudentDetail extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = [
        'name',
        'prn_no',
        'roll_no',
        'class_mapping_id',
        'mentor_batch_mapping_id',
        'dob',
        'mob_no',
        'email',
        'password',
        'attendance',
        'ct_avg'
    ];

    public function marks() {
        return $this->hasMany('\ClassTestDetail');
    }

    public function getAvg() {
        return $this->marks()->where('student_detail_id', $this['id'])->avg('obt_marks');
    }

    public function getSubAvg($sub_id) {
        return $this->marks()->where('subject_detail_id', $sub_id)->avg('obt_marks');
    }

    public function class() {
        return $this->belongsTo('\ClassMapping', 'class_mapping_id');
    }

    public function branch() {
        return $this->class->dept;
    }

    public function batch() {
        return $this->belongsTo('\MentorBatchMapping', 'mentor_batch_mapping_id');
    }
}

?>