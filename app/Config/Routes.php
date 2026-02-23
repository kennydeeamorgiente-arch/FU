<?php

use App\Controllers\UserController;
use CodeIgniter\Router\RouteCollection;
use App\Controllers\Pages;
use App\Controllers\Organization\OrganizationController;
use App\Controllers\Admin\AdminController;
use App\Controllers\EventsController;
use App\Controllers\NotificationController;

/**
 * @var RouteCollection $routes
 */
$routes->get('/register', 'User::register');
$routes->get('/login', [UserController::class, 'login']);
$routes->get('/', [UserController::class, 'index']);

// $routes->post('/store', 'User::stpre');
$routes->post('verifyLogin', [UserController::class, 'verifyLogin']);
$routes->get('user/logout', [UserController::class, 'logout']);
//for Google ni sya
$routes->get('google/login', 'GoogleAuthController::login');
$routes->get('google/callback', 'GoogleAuthController::callback');

$routes->get('search-org', [OrganizationController::class, 'searchOrg']);
$routes->group('organization', function ($routes) {
  $routes->get('login', [OrganizationController::class, 'login']);
  $routes->get('homepage', [OrganizationController::class, 'homepage']);
  $routes->get('leaderboards', [OrganizationController::class, 'leaderboards']);
  $routes->get('get-organizations', [OrganizationController::class, 'getOrganizations']);
  $routes->get('get-current-ranking', [OrganizationController::class, 'getCurrentRanking']);
  $routes->get('search-organizations', [OrganizationController::class, 'searchOrganizations']);

  $routes->get('host-event', [OrganizationController::class, 'hostEvent']);
  $routes->get('host-event/edit/(:num)', [OrganizationController::class, 'editEventForm']);
  $routes->post('host-event/add-event', [EventsController::class, 'addEvent']);
  $routes->post('host-event/edit-event', [EventsController::class, 'editEvent']);
  $routes->post('delete-event', [EventsController::class, 'deleteEvent']);

  $routes->get('files/event/(.*)', 'EventsController::download/$1');

  $routes->get('log-event', [OrganizationController::class, 'logEvent']);
  $routes->get('log-event/get-pending-events', [OrganizationController::class, 'getPendingLogEvents']);
  $routes->post('log-event/submit', [OrganizationController::class, 'submitLogEvent']);

  $routes->get('track-event', [OrganizationController::class, 'trackEvent']);
  $routes->get('get-org-events', [OrganizationController::class, 'getEvents']);
  $routes->get('get-events-by-date', [EventsController::class, 'getEventsByDateRange']);
  $routes->get('manage-event/(:segment)/(:any)', [EventsController::class, 'manageEventById']);

  $routes->get('profile/(:any)', [OrganizationController::class, 'profile']);
  $routes->get('settings', [OrganizationController::class, 'settings']);

  // Notifications
  $routes->get('notifications', [NotificationController::class, 'getNotifications']);
  $routes->get('notifications/unread-count', [NotificationController::class, 'getUnreadCount']);
  $routes->post('notifications/mark-read/(:num)', [NotificationController::class, 'markAsRead']);
  $routes->post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
});

$routes->group('admin', ['filter' => 'access:1,2,3,4,5'], function ($routes) {
  $routes->get('login', [AdminController::class, 'login']);
  $routes->get('dashboard', [AdminController::class, 'dashboard']);
  $routes->get('leaderboards', [AdminController::class, 'leaderboards']);
  $routes->get('manage-events', [AdminController::class, 'manageEvents']);
  $routes->get('pending-proposals', [AdminController::class, 'pendingProposals']);
  $routes->get('in-progress-events', [AdminController::class, 'inProgressEvents']);
  $routes->get('completed-events', [AdminController::class, 'completedEvents']);
  $routes->get('organizations', [AdminController::class, 'organizations']);

  //USERS----------------
  $routes->get('users', [AdminController::class, 'users']);
  $routes->post('get-users', [AdminController::class, 'usersAjax']);
  $routes->get('modify-user/(:segment)/(:any)?', [AdminController::class, 'modifyUser']);
  $routes->post('create-user', [AdminController::class, 'createUser']);
  $routes->post('edit-user', [AdminController::class, 'updateUser']);
  $routes->post('delete-user', [AdminController::class, 'deleteUser']);

  $routes->get('settings', [AdminController::class, 'settings']);

  $routes->get('get-organizations', [OrganizationController::class, 'getOrganizations']);
  $routes->get('modify-organization/(:segment)/(:any)?', [AdminController::class, 'addOrg']);
  $routes->post('create-organization', [OrganizationController::class, 'createOrganization']);
  $routes->post('edit-organization', [OrganizationController::class, 'updateOrganization']);
  $routes->post('delete-organization', [OrganizationController::class, 'deleteOrganization']);

  $routes->get('manage-events/view/(:any)?', [EventsController::class, 'viewEvent']);
  $routes->get('manage-events/view-partial/(:any)?', [EventsController::class, 'viewEventPartial']);

  $routes->get('get-events', [EventsController::class, 'getEvents']);
  $routes->post('update-events', [EventsController::class, 'updateEvent']);
  $routes->post('approve-event', [EventsController::class, 'approveEvent']);

  // Notifications
  $routes->get('notifications', [NotificationController::class, 'getNotifications']);
  $routes->get('notifications/unread-count', [NotificationController::class, 'getUnreadCount']);
  $routes->post('notifications/mark-read/(:num)', [NotificationController::class, 'markAsRead']);
  $routes->post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
});
