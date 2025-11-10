<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'title',
        'slug',
        'description',
        'category_id',
        'tags',
        'price',
        'delivery_days',
        'sample_work_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tags'      => 'array',
            'price'     => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the student that owns the service.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the category that the service belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Check if sample work is an image.
     */
    public function sampleWorkIsImage(): bool
    {
        if (! $this->sample_work_path) {
            return false;
        }

        $extension = strtolower(pathinfo($this->sample_work_path, PATHINFO_EXTENSION));
        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    /**
     * Get the sample work URL.
     */
    public function getSampleWorkUrlAttribute(): ?string
    {
        if (! $this->sample_work_path) {
            return null;
        }

        // For images, we can generate a temporary URL
        if ($this->sampleWorkIsImage()) {
            return route('services.sample-work', $this->slug);
        }

        return null;
    }
}
