<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Documents extends Model
{
    use SoftDeletes;

    protected $table = 'documents';

    public static $folderPath = "uploads/documents/";

    protected $fillable = [
        'document_type_id',
        'name',
        'folder_path',
        'file_mime_type',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public $appends = ['document_file_url'];

    public function document_type()
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id', 'id');
    }

    public function getDocumentFileUrlAttribute()
    {
        # document_file_url
        if ($this->folder_path) {
            if (file_exists(public_path($this->folder_path))) {
                return asset($this->folder_path);
            }
        }
        return "";
    }
}
