<?php

class ClassTestDetail extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = [
        'subject_detail_id',
        'total_marks',
        'passing',
        'obt_marks',
        'student_detail_id',
        'ct_no',
        'created_at',
        'updated_at',
        'expired'
    ];

    public function subject() {
        return $this->belongsTo('\SubjectDetail', 'subject_detail_id');
    }

    public function student() {
        return $this->belongsTo('\StudentDetail', 'student_detail_id');
    }
}

?>