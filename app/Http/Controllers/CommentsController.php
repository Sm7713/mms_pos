<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\User; // Make sure you have a Comment model created
 // Make sure you have a Comment model created
use App\MyHelper\ApiResponce;
use Carbon\Carbon; // For handling date and time
use Illuminate\Support\Facades\Validator;

class CommentsController extends Controller
{
    // Function to add a comment
    public function addComment(Request $request)
    {
        // Validate the request data
        $request->validate([
            'message' => 'required|string',
        ]);

        // Create a new comment
        $comment = new Comment();
        $comment->message = $request->input('message');
        $comment->user_id = $request->user()->id;
        $comment->date = Carbon::now();
        $comment->save();

        return response()->json(['message' => 'Comment added successfully!'], 201);
    }

    public function assignComment(Request $request)
    {
        // Validate the request data
        $validator=Validator::make($request->all(),[
            'id'=>'required|integer|exists:'.User::class,
            'message' => 'required|string',
        ]);
        
        if($validator->fails()) return ApiResponce::sendResponce(404,null,$validator->errors());

        // Create a new comment
        $comment = new Comment();
        $comment->message = $request->input('message');
        $comment->user_id = $request->id;
        $comment->date = Carbon::now();
        $comment->save();

        return response()->json(['message' => 'Comment added successfully!'], 201);
    }

    public function replyComment(Request $request)
    {
        // Validate the request data
        $validator=Validator::make($request->all(),[
            'id'=>'required|exists:'.Comment::class,
            'reply_msg' => 'required|string',
        ]);

        if($validator->fails()) return ApiResponce::sendResponce(404,null,$validator->errors());

        // Create a new comment
        $comment = Comment::find($request->id);
        $comment->reply_msg = $request->input('reply_msg');
        $comment->reply_date=Carbon::now();
        $comment->read = true; // Assuming there's an 'is_read' field in the comments table
        $comment->save();

        return response()->json(['message' => 'Comment Reply sent successfully!'], 200);
    }

    //get all comments by user
    public function getAllCommentsByUser(Request $request){
        $comments=Comment::where('user_id',$request->user()->id)->latest()->get();
        return ApiResponce::sendResponce(200,"All Comments By User",$comments);
    }

    public function getAllComments(){
        $commentsSell=Comment::whereHas('User.sellPoint',function($q){
            $q->where('read',0);
        })->with('User')->latest()->get();// get name from user
        $commentSub=Comment::whereHas('User.Subscriber',function($q){
            $q->where('read',0);
        })->latest()->get();
        $commentMain=Comment::whereHas('User.Maintainer',function($q){
            $q->where('read',0);
        })->latest()->get();
        $data=['sellPoint'=>$commentsSell,'Subscriber'=>$commentSub,'Maintainer'=>$commentMain];
        // if(!$comments){
        //     return ApiResponce::sendResponce(200,"Not Any Comments Here");
        // }
        return ApiResponce::sendResponce(200,'All Comments',$data);
    }

    public function getAllCommentsRead(){
        $comments=Comment::where('read',1)->get();
        if(!$comments){
            return ApiResponce::sendResponce(200,"Not Any Comments Here");
        }
        return ApiResponce::sendResponce(200,'All Comments',$comments);
    }
    // Function to mark comments as read by the admin
    public function markAsRead($id)
    {
        // Find the comment by ID
        $comment = Comment::find($id);

        if ($comment) {
            $comment->read = true; // Assuming there's an 'is_read' field in the comments table
            $comment->save();

            return response()->json(['message' => 'Comment marked as read successfully!'], 200);
        } else {
            return response()->json(['message' => 'Comment not found!'], 404);
        }
    }

    // Function to delete a comment
    public function deleteComment($id)
    {
        // Find the comment by ID
        $comment = Comment::find($id);

        if ($comment) {
            $comment->delete();

            return response()->json(['message' => 'Comment deleted successfully!'], 200);
        } else {
            return response()->json(['message' => 'Comment not found!'], 404);
        }
    }

    public function deleteCommentByUser(Request $request)
    {
        
        $validator=Validator::make($request->all(),[
            'id'=>'required|exists:'.Comment::class,
        ]);

        if($validator->fails()) return ApiResponce::sendResponce(404,null,$validator->errors());
        // Find the comment by ID
        $comment = Comment::find($request->id);

        if ($comment) {
            $comment->delete();

            return response()->json(['message' => 'Comment deleted successfully!'], 200);
        } else {
            return response()->json(['message' => 'Comment not found!'], 404);
        }
    }
}
