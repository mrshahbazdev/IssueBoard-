<?php

namespace Modules\IssueBoard\Support;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\IssueBoard\Models\Attachment;

trait HasAttachments
{
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function images(): MorphMany
    {
        return $this->attachments()->where('mime', 'like', 'image/%');
    }

    public function attachFile(UploadedFile $file, ?int $userId = null): Attachment
    {
        $disk = config('issueboard.disk');
        $path = $file->store(config('issueboard.directory'), $disk);

        return $this->attachments()->create([
            'disk' => $disk,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType() ?: 'application/octet-stream',
            'size' => $file->getSize(),
            'uploaded_by' => $userId ?? auth()->id(),
        ]);
    }

    public function deleteAttachments(): void
    {
        $this->attachments->each(function (Attachment $attachment) {
            Storage::disk($attachment->disk)->delete($attachment->path);
            $attachment->delete();
        });
    }
}
