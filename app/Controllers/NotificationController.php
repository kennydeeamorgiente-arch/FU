<?php

namespace App\Controllers;

use App\Models\NotificationModel;

class NotificationController extends BaseController
{
    protected $notifications;

    public function __construct()
    {
        $this->notifications = model("NotificationModel");
    }

    /**
     * Get all notifications for the current user
     */
    public function getNotifications()
    {
        try {
            $session = session();
            $userId = $session->get('user_id');
            $orgId = $session->get('org_id');
            $accessId = $session->get('access_id');

            if (!$userId) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'User not logged in'
                ])->setStatusCode(401);
            }

            // Enhanced debug logging
            log_message('debug', "NotificationController: getNotifications called - User ID: {$userId}, Org ID: " . ($orgId ?? 'NULL') . ", Access ID: " . ($accessId ?? 'NULL'));

            $limit = $this->request->getGet('limit') ?? 50;
            $notifications = $this->notifications->getNotificationsByUserId($userId, $limit);

            // Debug logging
            log_message('debug', 'NotificationController: User ID: ' . $userId . ', Notifications count: ' . count($notifications));
            
            // Log notification details for debugging
            if (count($notifications) > 0) {
                $types = array_count_values(array_column($notifications, 'type'));
                log_message('debug', 'NotificationController: Notification types: ' . json_encode($types));
                log_message('debug', 'NotificationController: First notification: ' . json_encode($notifications[0] ?? []));
            } else {
                log_message('debug', 'NotificationController: No notifications found for user ID: ' . $userId);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $notifications ? $notifications : [],
                'debug' => [
                    'user_id' => $userId,
                    'org_id' => $orgId,
                    'access_id' => $accessId,
                    'count' => count($notifications)
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Notification error: ' . $e->getMessage());
            log_message('error', 'Notification stack trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage(),
                'data' => []
            ])->setStatusCode(500);
        }
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadCount()
    {
        $session = session();
        $userId = $session->get('user_id');

        if (!$userId) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User not logged in'
            ])->setStatusCode(401);
        }

        $count = $this->notifications->getUnreadCount($userId);

        return $this->response->setJSON([
            'status' => 'success',
            'count' => $count
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId)
    {
        $session = session();
        $userId = $session->get('user_id');

        if (!$userId) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User not logged in'
            ])->setStatusCode(401);
        }

        $result = $this->notifications->markAsRead($notificationId, $userId);

        if ($result) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Notification marked as read'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Failed to mark notification as read'
        ])->setStatusCode(400);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $session = session();
        $userId = $session->get('user_id');

        if (!$userId) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User not logged in'
            ])->setStatusCode(401);
        }

        $result = $this->notifications->markAllAsRead($userId);

        if ($result) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'All notifications marked as read'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Failed to mark notifications as read'
        ])->setStatusCode(400);
    }
}
?>

