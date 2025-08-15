<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
     // Function to get all unread notifications for the authenticated user
     public function getUnreadNotifications(Request $request)
     {
         $user = $request->user();
         $unreadNotifications = $user->unreadNotifications;
 
         return response()->json($unreadNotifications);
     }
 
     // Function to mark a specific notification as read
     public function markAsRead(Request $request, $notificationId)
     {
         $user = $request->user();
         $notification = $user->notifications()->find($notificationId);
 
         if ($notification) {
             $notification->markAsRead();
             return response()->json(['message' => 'Notification marked as read.']);
         }
 
         return response()->json(['message' => 'Notification not found.'], 404);
     }
 
     // Function to get all notifications (read and unread) for the authenticated user
     public function getAllNotifications(Request $request)
     {
         $user = $request->user();
         $notifications = $user->notifications;
 
         return response()->json($notifications);
     }
 
     // Function to mark all notifications as read for the authenticated user
     public function markAllAsRead(Request $request)
     {
         $user = $request->user();
         $user->unreadNotifications->markAsRead();
 
         return response()->json(['message' => 'All notifications marked as read.']);
     }
}
