<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBell extends Component
{
    public function markAsRead(string $id): void
    {
        $notification = Auth::user()?->notifications()->where('id', $id)->first();
        if ($notification && is_null($notification->read_at)) {
            $notification->markAsRead();
        }
    }

    public function markAllAsRead(): void
    {
        Auth::user()?->unreadNotifications->markAsRead();
    }

    public function render()
    {
        $user = Auth::user();
        $unreadCount = $user ? $user->unreadNotifications()->count() : 0;
        $notifications = $user ? $user->notifications()->latest()->take(10)->get() : collect();

        return view('livewire.notification-bell', [
            'unreadCount' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }
}
