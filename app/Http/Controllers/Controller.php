<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;

abstract class Controller
{
    /**
     * Push an event to the Socket.io server so it can broadcast in real-time.
     */
    protected function emitSocketEvent(string $event, array $data): void
    {
        try {
            $port = env('SOCKET_IO_PORT', 6001);
            $ch = curl_init("http://localhost:{$port}/emit");
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode(['event' => $event, 'data' => $data]),
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 2,
            ]);
            curl_exec($ch);
            curl_close($ch);
        } catch (\Exception $e) {
            // Socket server might not be running — fail silently
        }
    }

    /**
     * Create a notification record for a specific user and push it via Socket.io.
     */
    protected function notifyUser(int $userId, string $title, string $message, string $type): void
    {
        Notification::create(compact('title', 'message', 'type') + ['user_id' => $userId]);

        $this->emitSocketEvent('user-notification', [
            'user_id' => $userId,
            'title'   => $title,
            'message' => $message,
            'type'    => $type,
        ]);
    }

    /**
     * Notify every admin user.
     */
    protected function notifyAdmins(string $title, string $message, string $type): void
    {
        User::where('role', 'admin')->each(function (User $admin) use ($title, $message, $type) {
            Notification::create([
                'user_id' => $admin->id,
                'title'   => $title,
                'message' => $message,
                'type'    => $type,
            ]);
        });

        $this->emitSocketEvent('admin-notification', compact('title', 'message', 'type'));
    }
}
