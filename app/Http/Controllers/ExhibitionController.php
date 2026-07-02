<?php

namespace App\Http\Controllers;

use App\Models\Exhibition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ExhibitionController extends Controller
{

    public function index()
    {
        $exhibitions = Exhibition::where('organizer_id', Auth::id())
            ->latest()
            ->get();
        return response()->json([
            'status' => 'success',
            'data' => $exhibitions
        ]);
    }

    public function show($id)
    {
        $exhibition = Exhibition::where('organizer_id', Auth::id())
            ->find($id);
        if (!$exhibition) {
            return response()->json([
                'status' => 'error',
                'message' => 'No exhibitions has been created'
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'data' => $exhibition
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:150',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $exhibition = Exhibition::create([
            'organizer_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'location' => $request->location,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $exhibition
        ], 201);
    }

    public function update(Request $request, $id)
    {

        $exhibition = Exhibition::where('organizer_id', Auth::id())
            ->find($id);

        if (!$exhibition) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exhibition not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:150',
            'description' => 'sometimes|string',
            'location' => 'sometimes|string|max:255',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $exhibition->update($request->only([
            'title',
            'description',
            'location',
            'start_date',
            'end_date'
        ]));

        return response()->json([
            'status' => 'success',
            'data' => $exhibition
        ]);
    }

    public function destroy($id)
    {
        $exhibition = Exhibition::where('organizer_id', Auth::id())
            ->find($id);
        if (!$exhibition) {
            return response()->json([
                'status' => 'error',
                'message' => 'The exhibition does not exist'
            ], 404);
        }
        $exhibition->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Exhibition deleted successfully'
            ]);
    }

//    _________________________________________________________________________

// قسم الادمن

    public function list()
    {
        $exhibitions = Exhibition::with('organizer:id,name,email')
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $exhibitions
        ]);
    }


    public function read($id)
    {
        $exhibition = Exhibition::with('organizer:id,name,email')
            ->find($id);
        if (!$exhibition) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exhibition not found'
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'data'   => $exhibition
        ]);
    }

    public function edit(Request $request, $id)
    {
        $exhibition = Exhibition::find($id);

        if (!$exhibition) {
            return response()->json([
                'status' => 'error',
                'message' => 'The specific exhibition does not exist'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title'       => 'sometimes|string|max:150',
            'description' => 'sometimes|string',
            'location'    => 'sometimes|string|max:255',
            'start_date'  => 'sometimes|date',
            'end_date'    => 'sometimes|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                    'status'  => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $exhibition->update($request->only([
                'title',
                'description',
                'location',
                'start_date',
                'end_date',
            ]));

        return response()->json([
                'status' => 'success',
            'data'   => $exhibition
        ]);
    }

    public function delete($id)
    {
        $exhibition = Exhibition::find($id);
        if (!$exhibition) {
            return response()->json([
                'status' => 'error',
                'message' => 'The exhibition does not exist'
            ], 404);
        }
        $exhibition->delete();

        return response()->json([
                'status'  => 'success',
            'message' => 'Exhibition deleted successfully'
        ]);
    }

}
