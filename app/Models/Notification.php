<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'notification_channel',
        'severity_level',
        'notification_status',
        'sent_at',
        'read_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Notification belongs to User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    /**
     * Check if notification has been read
     */
    public function isRead()
    {
        return $this->read_at !== null;
    }

    /**
     * Get notifications for a specific user
     */
    public static function forUser($userId)
    {
        return self::where('user_id', $userId)
            ->latest()
            ->get();
    }

    /**
     * Get unread notifications
     */
    public static function unread()
    {
        return self::whereNull('read_at')
            ->latest()
            ->get();
    }
}
