<?php

namespace App\Http\Controllers;

use App\Models\Label;
use Illuminate\Http\Request;
use JWTAuth;
use Auth;
use Exception;
use Validator;


class LabelController extends Controller
{
    public function createLabel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'note_id' => 'required',
            'labelname' => 'required|string|between:2,30',
        ]);

        if($validator->fails())
        {
            return response()->json($validator->errors()->toJson(), 400);
        }

        try
        {
            $currentUser = JWTAuth::parseToken()->authenticate();
            $label = new Label;
            $label->note_id = $request->get('note_id');
            $label->labelname = $request->get('labelname');

            if($currentUser->labels()->save($label))
            {
                return response()->json([
                    'message' => 'Label created Sucessfully'
                ], 201);
            } 
        }
        catch (Exception $e) 
		{
            return response()->json([
                'status' => 404, 
                'message' => 'Invalid authorization token'
            ], 404);
        }
        
    }

    public function displayLabelById(Request $request)
    {      
        try
        {
            $label = new Label;
            $label->user_id = auth()->id();
            $id = $request->input('id');
            $User = JWTAuth::parseToken()->authenticate();
            $labels = $User->labels()->find($id);
            if ($labels==''){
                return response()->json([
                    'message' => 'labels not found'
                ], 404);
            }
        }
        catch(Exception $e)
        {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }
        return $labels;
        
    }

    public function updateLabelById(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'labelname' => 'required|string|between:2,20',
        ]);
        if($validator->fails())
        {
            return response()->json($validator->errors()->toJson(), 400);
        }
        try
        {
            $id = $request->input('id');
            $currentUser = JWTAuth::parseToken()->authenticate();
            $label = $currentUser->labels()->find($id);
    
            if(!$label)
            {
                return response()->json([ 'message' => 'Label not Found'], 404);
            }
    
            $label->fill($request->all());
    
            if($label->save())
            {
                return response()->json(['message' => 'Label updated Sucessfully' ], 201);
            }      
        }
        catch(Exception $e)
        {
            return response()->json(['message' => 'Invalid authorization token' ], 404);
        }
        return $label;
    }

    public function deleteLabelById(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if($validator->fails())
        {
            return response()->json($validator->errors()->toJson(), 400);
        }
        try
        {
            $id = $request->input('id');
            $currentUser = JWTAuth::parseToken()->authenticate();
            $label = $currentUser->labels()->find($id);
    
            if(!$label)
            {
                return response()->json(['message' => 'Label not Found'], 404);
            }
    
            if($label->delete())
            {
                return response()->json(['message' => 'Label deleted Sucessfully'], 201);
            }   
        }
        catch(Exception $e)
        {
            return response()->json(['message' => 'Invalid authorization token' ], 404);
        }
        
    }

    public function getAllLabels()
    {
        //$labels = new Label();
        //$labels->user_id = auth()->id();
        $User = JWTAuth::parseToken()->authenticate();

        if ($User) 
        {
            $user = Label::select('id', 'labelname')
                ->where([
                    ['user_id', '=', $User->id],
                ])
                ->get();
            if ($user=='[]'){
                return response()->json([
                    'message' => 'Labels not found'
                ], 404);
            }
            return
            response()->json([
                'message' => 'Fetched Labels Successfully',
                'Labels' => $user
            ], 201);
            
        }
        return response()->json([
            'status' => 403, 
            'message' => 'Invalid token'
        ]);
    }

    public function getLabelsByNote(Request $request)
    {
        //$labels = new Label();
        //$labels->user_id = auth()->id();
        $User = JWTAuth::parseToken()->authenticate();

        //$note_id = $request->input('note_id');

        if ($User) 
        {
            $userLabels = Label::select('id', 'labelname')
                ->where([
                    ['note_id', '=', $request->input('note_id')]
                ])
                ->get();
            if ($userLabels=='[]'){
                return response()->json([
                    'message' => 'No Labels'
                ], 404);
            }
            return
            response()->json([
                'message' => 'Fetched Labels Successfully',
                'Labels' => $userLabels
            ], 201);
        }
        return response()->json([
            'status' => 403, 
            'message' => 'Invalid token'
        ]);
    }
}