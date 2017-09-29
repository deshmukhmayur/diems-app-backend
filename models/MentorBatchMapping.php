<?php

class MentorBatchMapping extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = [
        'staff_detail_id',
        'class_mapping_id',
        'batch_name'
    ];

    public $timestamps = false;

    public function students() {
        return $this->hasMany('\StudentDetail');
    }
}

?>