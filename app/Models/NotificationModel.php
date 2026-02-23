<?php
namespace App\Models;
use CodeIgniter\Model;

class NotificationModel extends Model
{
    // No table needed - generating notifications dynamically from events and event_history tables
    protected $table = 'events'; // Using events table as base
    protected $primaryKey = 'event_id';

    protected $eventsModel;
    protected $usersModel;
    protected $eventHistoryModel;

    public function __construct()
    {
        parent::__construct();
        $this->eventsModel = model("EventsModel");
        $this->usersModel = model("UserModel");
        $this->eventHistoryModel = model("EventsHistoryModel");
    }

    /**
     * Get all notifications for a user (generated dynamically from events and event_history)
     * Only shows notifications from events that had status changes in the last 7 days
     */
    public function getNotificationsByUserId($userId, $limit = null)
    {
        $user = $this->usersModel->find($userId);
        if (!$user) {
            return [];
        }

        $accessId = $user['access_id'];
        $orgId = $user['org_id'];
        $notifications = [];

        // Debug logging
        log_message('debug', "NotificationModel: User ID: {$userId}, Access ID: {$accessId}, Org ID: " . ($orgId ?? 'NULL'));

        // If org_id is NULL or 0, organization users won't get notifications
        if (empty($orgId) && $accessId == 0) {
            log_message('debug', "NotificationModel: WARNING - Organization user has no org_id! User ID: {$userId}");
            return [];
        }

        // Calculate 7 days ago timestamp
        $sevenDaysAgo = date('Y-m-d H:i:s', strtotime('-7 days'));

        // Get event IDs that had status changes in the last 7 days
        $recentEventHistory = $this->eventHistoryModel
            ->select('event_id')
            ->where('created_at >=', $sevenDaysAgo)
            ->groupBy('event_id')
            ->findAll();

        $recentEventIds = array_column($recentEventHistory, 'event_id');

        // Also include events created in the last 7 days (new submissions)
        $recentEvents = $this->eventsModel
            ->select('event_id')
            ->where('created_at >=', $sevenDaysAgo)
            ->findAll();

        $recentEventIds = array_unique(array_merge($recentEventIds, array_column($recentEvents, 'event_id')));

        // For organization users (students only, access_id = 0): Show events from their org that are:
        // - Pending approval (status_id = 1)
        // - Rejected (status_id = 6)
        // - Returned for revision (status_id = 7)
        // - Fully approved (status_id = 2)
        // Advisers (access_id > 0) should NOT see revision/rejection notifications - they only see pending approval notifications
        if ($orgId && $accessId == 0) {
            log_message('debug', "NotificationModel: Processing notifications for organization user - User ID: {$userId}, Org ID: {$orgId}, Access ID: {$accessId}");

            // DEBUG: Check ALL events for this org to see what statuses exist
            $allOrgEvents = $this->eventsModel
                ->select('events.event_id, events.event_name, events.status_id, events.current_access_id, events.org_id')
                ->where('events.org_id', $orgId)
                ->findAll();
            log_message('debug', "NotificationModel: Total events for org_id {$orgId}: " . count($allOrgEvents));
            if (count($allOrgEvents) > 0) {
                $statusCounts = [];
                foreach ($allOrgEvents as $evt) {
                    $status = $evt['status_id'] ?? 'NULL';
                    if (!isset($statusCounts[$status])) {
                        $statusCounts[$status] = 0;
                    }
                    $statusCounts[$status]++;
                }
                log_message('debug', "NotificationModel: Event status breakdown: " . json_encode($statusCounts));
            }

            // FIRST: Check for pending approval events (status_id = 1)
            $pendingEvents = $this->eventsModel
                ->select('events.*, organization.org_name, access_level.access_name')
                ->join('organization', 'organization.org_id = events.org_id', 'left')
                ->join('access_level', 'access_level.access_id = events.current_access_id')
                ->where('events.org_id', (int) $orgId)
                ->where('events.status_id', 1) // Pending approval
                ->findAll();

            log_message('debug', "NotificationModel: Found " . count($pendingEvents) . " pending events for org_id: {$orgId}");

            foreach ($pendingEvents as $event) {
                // Check if we already added this notification
                $notificationExists = false;
                foreach ($notifications as $notif) {
                    if ($notif['event_id'] == $event['event_id'] && $notif['type'] == 'event_pending') {
                        $notificationExists = true;
                        break;
                    }
                }

                if (!$notificationExists) {
                    // Use event creation time or updated time
                    $notificationTime = $event['created_at'] ?? $event['updated_at'] ?? date('Y-m-d H:i:s');

                    $notifications[] = [
                        'notification_id' => 'pend_org_' . $event['event_id'],
                        'user_id' => $userId,
                        'event_id' => $event['event_id'],
                        'message' => "Your event '{$event['event_name']}' is currently under review by {$event['access_name']}.",
                        'type' => 'event_pending',
                        'is_read' => 0,
                        'created_at' => $notificationTime,
                        'event_name' => $event['event_name'],
                        'org_id' => $event['org_id'],
                        'org_name' => $event['org_name'] ?? ''
                    ];
                }
            }

            // SECOND: Check events table directly for current rejected/revision status
            // This is the PRIMARY method - it ensures notifications are shown for ALL current rejected/revision events
            // regardless of when they were created or last updated
            // NOTE: We do NOT filter by recentEventIds here - we want ALL current rejected/revision events

            // Check for current revision events (status_id = 7)
            // We don't filter by current_access_id - if status is revision, show notification
            // Cast org_id to ensure proper comparison (handle both string and int)
            $currentRevisionEvents = $this->eventsModel
                ->select('events.*, organization.org_name')
                ->join('organization', 'organization.org_id = events.org_id', 'left')
                ->where('events.org_id', (int) $orgId)
                ->where('events.status_id', 7) // Returned for revision
                ->findAll();

            log_message('debug', "NotificationModel: Found " . count($currentRevisionEvents) . " revision events for org_id: {$orgId}");
            if (count($currentRevisionEvents) > 0) {
                $revEventIds = array_column($currentRevisionEvents, 'event_id');
                log_message('debug', "NotificationModel: Revision event IDs: " . implode(', ', $revEventIds));
            }

            foreach ($currentRevisionEvents as $event) {
                // Check if we already added this notification
                $notificationExists = false;
                foreach ($notifications as $notif) {
                    if ($notif['event_id'] == $event['event_id'] && $notif['type'] == 'event_revision') {
                        $notificationExists = true;
                        break;
                    }
                }

                if (!$notificationExists) {
                    // Get the most recent revision history entry for timestamp
                    $recentRevision = $this->eventHistoryModel
                        ->where('event_id', $event['event_id'])
                        ->where('status_id', 7)
                        ->orderBy('created_at', 'DESC')
                        ->first();

                    // Use history timestamp if available, otherwise use event updated_at or current time
                    $notificationTime = $recentRevision ? $recentRevision['created_at'] : ($event['updated_at'] ?? date('Y-m-d H:i:s'));
                    $historyId = ($recentRevision && isset($recentRevision['events_history_id'])) ? $recentRevision['events_history_id'] : 0;

                    $notifications[] = [
                        'notification_id' => 'rev_' . $event['event_id'] . '_' . $historyId,
                        'user_id' => $userId,
                        'event_id' => $event['event_id'],
                        'message' => "Your event '{$event['event_name']}' has been returned for revision. Please review the remarks and resubmit.",
                        'type' => 'event_revision',
                        'is_read' => 0,
                        'created_at' => $notificationTime,
                        'event_name' => $event['event_name'],
                        'org_id' => $event['org_id'],
                        'org_name' => $event['org_name'] ?? ''
                    ];
                }
            }

            // Check for current rejected events (status_id = 6)
            // IMPORTANT: This query should find ALL rejected events, regardless of when they were created
            // We don't filter by current_access_id - if status is rejected, show notification
            // Cast org_id to ensure proper comparison (handle both string and int)
            $currentRejectedEvents = $this->eventsModel
                ->select('events.*, organization.org_name')
                ->join('organization', 'organization.org_id = events.org_id', 'left')
                ->where('events.org_id', (int) $orgId)
                ->where('events.status_id', 6) // Rejected
                ->findAll();

            log_message('debug', "NotificationModel: Query for rejected events - org_id: {$orgId}, found: " . count($currentRejectedEvents));

            // Debug: Log the actual event IDs found
            if (count($currentRejectedEvents) > 0) {
                $eventIds = array_column($currentRejectedEvents, 'event_id');
                log_message('debug', "NotificationModel: Rejected event IDs: " . implode(', ', $eventIds));
                foreach ($currentRejectedEvents as $evt) {
                    log_message('debug', "NotificationModel: Rejected event - ID: {$evt['event_id']}, Name: {$evt['event_name']}, Status: {$evt['status_id']}, Access: " . ($evt['current_access_id'] ?? 'NULL'));
                }
            } else {
                log_message('debug', "NotificationModel: No rejected events found for org_id: {$orgId}");
            }

            foreach ($currentRejectedEvents as $event) {
                // Check if we already added this notification
                $notificationExists = false;
                foreach ($notifications as $notif) {
                    if ($notif['event_id'] == $event['event_id'] && $notif['type'] == 'event_rejected') {
                        $notificationExists = true;
                        break;
                    }
                }

                if (!$notificationExists) {
                    // Get the most recent rejection history entry for timestamp
                    $recentRejection = $this->eventHistoryModel
                        ->where('event_id', $event['event_id'])
                        ->where('status_id', 6)
                        ->orderBy('created_at', 'DESC')
                        ->first();

                    // Use history timestamp if available, otherwise use event updated_at or current time
                    $notificationTime = $recentRejection ? $recentRejection['created_at'] : ($event['updated_at'] ?? date('Y-m-d H:i:s'));
                    $historyId = ($recentRejection && isset($recentRejection['events_history_id'])) ? $recentRejection['events_history_id'] : 0;

                    $notifications[] = [
                        'notification_id' => 'rej_' . $event['event_id'] . '_' . $historyId,
                        'user_id' => $userId,
                        'event_id' => $event['event_id'],
                        'message' => "Your event '{$event['event_name']}' has been rejected. Please review the remarks.",
                        'type' => 'event_rejected',
                        'is_read' => 0,
                        'created_at' => $notificationTime,
                        'event_name' => $event['event_name'],
                        'org_id' => $event['org_id'],
                        'org_name' => $event['org_name'] ?? ''
                    ];
                }
            }

            // Fully approved events (status_id = 5) - only show if approved by all levels (President) in last 7 days
            // Check event_history for status_id = 8 (approved) that led to status_id = 5 (fully approved)
            // NOTE: Events have status_id = 2 while in approval workflow, and status_id = 5 only after President approves
            $approvedHistoryBuilder = $this->eventHistoryModel
                ->select('events_history.*, events.event_name, events.org_id, events.status_id, organization.org_name')
                ->join('events', 'events.event_id = events_history.event_id', 'left')
                ->join('organization', 'organization.org_id = events.org_id', 'left')
                ->where('events_history.status_id', 8) // Approved status
                ->where('events_history.created_at >=', $sevenDaysAgo)
                ->where('events.org_id', $orgId);

            // Only add whereIn if recentEventIds is not empty
            if (!empty($recentEventIds)) {
                $approvedHistoryBuilder->whereIn('events_history.event_id', $recentEventIds);
            }

            $approvedHistory = $approvedHistoryBuilder
                ->orderBy('events_history.created_at', 'DESC')
                ->findAll();

            foreach ($approvedHistory as $history) {
                $event = $this->eventsModel->find($history['event_id']);
                // Only show if event is FULLY approved (status_id = 5) - meaning all 5 approval levels have approved
                if ($event && $event['status_id'] == 5) {
                    $historyId = isset($history['events_history_id']) ? $history['events_history_id'] : 0;
                    $notifications[] = [
                        'notification_id' => 'appr_' . $history['event_id'] . '_' . $historyId,
                        'user_id' => $userId,
                        'event_id' => $history['event_id'],
                        'message' => "Your event '{$history['event_name']}' has been successfully approved, you may now proceed with the event.",
                        'type' => 'event_approved',
                        'is_read' => 0,
                        'created_at' => $history['created_at'],
                        'event_name' => $history['event_name'],
                        'org_id' => $history['org_id'],
                        'org_name' => $history['org_name'] ?? ''
                    ];
                }
            }

            // NEW: Include intermediate approval notifications for organization users
            // Find recent history entries where a level approved (status_id = 8) within the last 7 days
            // and create a notification for the organization user informing them that their event
            // was approved at a specific admin/access level (but not yet fully approved).
            $levelApprovals = $this->eventHistoryModel
                ->select('events_history.*, events.event_name, events.org_id, organization.org_name, users.access_id as approver_access_id, access_level.access_name as approver_access_name')
                ->join('events', 'events.event_id = events_history.event_id', 'left')
                ->join('organization', 'organization.org_id = events.org_id', 'left')
                ->join('users', 'users.user_id = events_history.user_id', 'left')
                ->join('access_level', 'access_level.access_id = users.access_id', 'left')
                ->where('events_history.status_id', 8)
                ->where('events_history.created_at >=', $sevenDaysAgo)
                ->where('events.org_id', $orgId)
                ->orderBy('events_history.created_at', 'DESC')
                ->findAll();

            if (!empty($levelApprovals)) {
                foreach ($levelApprovals as $history) {
                    $event = $this->eventsModel->find($history['event_id']);
                    // Skip if event already fully approved (status_id = 5) - handled above
                    if ($event && $event['status_id'] == 5)
                        continue;

                    $approverAccessId = isset($history['approver_access_id']) ? (int) $history['approver_access_id'] : 0;
                    $approverName = $history['approver_access_name'];

                    // Avoid duplicate notifications for same event/level
                    $notifKey = 'level_appr_' . $history['event_id'] . '_' . ($history['events_history_id'] ?? 0);
                    $alreadyExists = false;
                    foreach ($notifications as $n) {
                        if ($n['notification_id'] === $notifKey) {
                            $alreadyExists = true;
                            break;
                        }
                    }
                    if ($alreadyExists)
                        continue;

                    $notifications[] = [
                        'notification_id' => $notifKey,
                        'user_id' => $userId,
                        'event_id' => $history['event_id'],
                        'message' => "Your event '{$history['event_name']}' was approved by {$approverName}.",
                        'type' => 'event_level_approved',
                        'is_read' => 0,
                        'created_at' => $history['created_at'],
                        'event_name' => $history['event_name'],
                        'org_id' => $history['org_id'],
                        'org_name' => $history['org_name'] ?? ''
                    ];
                }
            }
        }

        // For advisers/admins (access_id > 0): Show events pending their approval
        // IMPORTANT: Admins should see ALL pending events that need their approval, not just recent ones
        // ACCESS LEVEL ITERATION: Show events where current_access_id matches this user's access_id
        // This ensures each admin level only sees events that need approval at their specific level
        // - access_id = 1: Events where current_access_id = 1 (newly submitted, needs Club Adviser approval)
        //   IMPORTANT: For advisers (access_id = 1), also filter by org_id so they only see events from their organization
        // - access_id = 2: Events where current_access_id = 2 (approved by level 1, needs level 2 approval)
        // - access_id = 3: Events where current_access_id = 3 (approved by level 2, needs level 3 approval)
        // - access_id = 4: Events where current_access_id = 4 (approved by level 3, needs level 4 approval)
        // - access_id = 5: Events where current_access_id = 5 (approved by level 4, needs level 5 approval)
        if ($accessId > 0) {
            log_message('debug', "NotificationModel: Processing notifications for admin/adviser - User ID: {$userId}, Access ID: {$accessId}, Org ID: " . ($orgId ?? 'NULL'));

            // ACCESS LEVEL ITERATION: Only show events where current_access_id matches this user's access_id
            // This ensures proper access level filtering
            $pendingEventsBuilder = $this->eventsModel
                ->select('events.*, organization.org_name, access_level.access_name')
                ->join('access_level', 'access_level.access_id = events.current_access_id')
                ->join('organization', 'organization.org_id = events.org_id', 'left')
                ->where('events.current_access_id', $accessId) // ACCESS LEVEL ITERATION: Match access level
                ->whereIn('events.status_id', [1, 2]); // Pending (1) and In-Progress (2) status

            // REMOVED: Don't filter by recentEventIds - admins should see ALL pending events regardless of age
            // This ensures admins see events that have been pending for more than 7 days

            // For advisers (access_id = 1), filter by their organization
            if ($accessId == 1 && $orgId) {
                $pendingEventsBuilder->where('events.org_id', $orgId);
                log_message('debug', "NotificationModel: Filtering by org_id for access_id = 1: {$orgId}");
            }

            $pendingEvents = $pendingEventsBuilder->findAll();

            log_message('debug', "NotificationModel: Found " . count($pendingEvents) . " pending events for access_id: {$accessId} (ACCESS LEVEL ITERATION)");

            // Debug: Log the current_access_id of found events to verify access level iteration
            if (count($pendingEvents) > 0) {
                $accessLevels = array_count_values(array_column($pendingEvents, 'current_access_id'));
                log_message('debug', "NotificationModel: Access level breakdown: " . json_encode($accessLevels));
            }

            foreach ($pendingEvents as $event) {
                // Verify access level iteration - event's current_access_id should match user's access_id
                if ($event['current_access_id'] != $accessId) {
                    log_message('warning', "NotificationModel: Access level mismatch! Event {$event['event_id']} has current_access_id={$event['current_access_id']}, but user has access_id={$accessId}");
                    continue; // Skip if access level doesn't match
                }

                // DEBUG: Check if this is event 96
                if ($event['event_id'] == 96) {
                    log_message('debug', "NotificationModel: DEBUG - Processing event 96 for notification creation!");
                }

                // Get the most recent history entry for this event to get the correct timestamp
                // Don't filter by date - we want the most recent history regardless of when it was created
                $recentHistory = $this->eventHistoryModel
                    ->where('event_id', $event['event_id'])
                    ->orderBy('created_at', 'DESC')
                    ->first();

                // REMOVED: Don't skip events older than 7 days - admins should see ALL pending events
                // The notification timestamp will be based on when the event was last updated or created

                // Determine message based on access level iteration
                if ($accessId == 1) {
                    // First level (Club Advisers) - newly submitted event
                    $message = "New event '{$event['event_name']}' has been submitted and requires your approval.";
                    $type = 'event_submission';
                } else {
                    // Higher levels - event was approved by previous level, now needs this level's approval
                    $prevLevel = $accessId - 1;
                    $message = "Event '{$event['event_name']}' has been approved by the {$event['access_name']} and now requires your approval.";
                    $type = 'event_approval';
                }

                // DEBUG: Check if notification is being created for event 96
                if ($event['event_id'] == 96) {
                    log_message('debug', "NotificationModel: DEBUG - Creating notification for event 96: {$message}");
                }

                // Use history timestamp if available, otherwise use event creation time
                // For old events, use the most recent history or event creation time
                $notificationTime = $recentHistory ? $recentHistory['created_at'] : ($event['created_at'] ?? date('Y-m-d H:i:s'));

                $notifications[] = [
                    'notification_id' => 'pend_' . $accessId . '_' . $event['event_id'],
                    'user_id' => $userId,
                    'event_id' => $event['event_id'],
                    'message' => $message,
                    'type' => $type,
                    'is_read' => 0,
                    'created_at' => $notificationTime,
                    'event_name' => $event['event_name'],
                    'org_id' => $event['org_id'],
                    'org_name' => $event['org_name'] ?? '',
                    'current_access_id' => $event['current_access_id'] // Include for debugging
                ];
            }

            log_message('debug', "NotificationModel: Added " . count($pendingEvents) . " admin notifications for access_id: {$accessId} (ACCESS LEVEL ITERATION)");
        }

        // Sort by created_at descending
        usort($notifications, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        // Remove duplicates based on notification_id
        $uniqueNotifications = [];
        $seenIds = [];
        foreach ($notifications as $notification) {
            if (!in_array($notification['notification_id'], $seenIds)) {
                $uniqueNotifications[] = $notification;
                $seenIds[] = $notification['notification_id'];
            }
        }

        log_message('debug', "NotificationModel: Total notifications before deduplication: " . count($notifications));
        log_message('debug', "NotificationModel: Total unique notifications before limit: " . count($uniqueNotifications));

        // Debug: Log notification types
        if (count($uniqueNotifications) > 0) {
            $types = array_count_values(array_column($uniqueNotifications, 'type'));
            log_message('debug', "NotificationModel: Notification types: " . json_encode($types));
        }

        if ($limit) {
            $uniqueNotifications = array_slice($uniqueNotifications, 0, $limit);
        }

        log_message('debug', "NotificationModel: Final notification count: " . count($uniqueNotifications));
        if (count($uniqueNotifications) > 0) {
            log_message('debug', "NotificationModel: Notification IDs: " . implode(', ', array_column($uniqueNotifications, 'notification_id')));
        }

        return $uniqueNotifications;
    }

    /**
     * Get unread notifications count for a user
     * Note: Read status will be tracked in localStorage on frontend
     */
    public function getUnreadCount($userId)
    {
        $notifications = $this->getNotificationsByUserId($userId);
        return count($notifications); // All are "unread" by default, frontend will track
    }

    /**
     * Mark notification as read (no-op, handled by frontend localStorage)
     */
    public function markAsRead($notificationId, $userId)
    {
        // Read status tracked in localStorage on frontend
        return true;
    }

    /**
     * Mark all notifications as read (no-op, handled by frontend localStorage)
     */
    public function markAllAsRead($userId)
    {
        // Read status tracked in localStorage on frontend
        return true;
    }

    /**
     * Create notification for multiple users (no-op, notifications generated dynamically)
     */
    public function createNotificationsForUsers($userIds, $eventId, $message, $type = 'event')
    {
        // Notifications are generated dynamically from events table
        // No need to store them
        return true;
    }

}
?>
