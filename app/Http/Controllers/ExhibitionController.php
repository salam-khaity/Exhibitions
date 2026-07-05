<?php

namespace App\Http\Controllers;

use App\Helpers\ImageHelper;
use App\Models\Exhibition;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ExhibitionController extends Controller
{

    // دالة مساعدة لرفع الصور
    private function uploadImages(array $images, int $exhibitionId): void
    {
        foreach ($images as $image) {
            $path = ImageHelper::uploadAvatar($image, 'exhibitions');
            Image::create([
                'exhibition_id' => $exhibitionId,
                'image_path'    => $path,
            ]);
        }
    }


    // دالة مساعدة لحذف جميع صور معرض
    private function deleteImages(Exhibition $exhibition): void
    {
        foreach ($exhibition->images as $image) {
            if (file_exists(public_path($image->image_path))) {
                unlink(public_path($image->image_path));
            }
            $image->delete();
        }
    }
    // دالة مساعدة للتحقق من وجود المعرض وملكيته
    private function findExhibition($id)
    {
        $exhibition = Exhibition::find($id);
        if (!$exhibition) {
            return [
                'error'   => true,
                'code'    => 404,
                'message' => 'Exhibition not found'
            ];
        }
        if ($exhibition->organizer_id !== Auth::id()) {
            return [
                'error'   => true,
                'code'    => 403,
                'message' => 'You do not have permission to perform this action on this exhibition'
            ];
        }
        return [
            'error'      => false,
            'exhibition' => $exhibition
        ];
    }



    public function index()
    {
        $exhibitions = Exhibition::where('organizer_id', Auth::id())
            ->with(['images'])
            ->latest()
            ->get()
            ->map(function ($exhibition) {
                $exhibition->images->transform(function ($img) {
                    $img->image_url = asset($img->image_path);
                    return $img;
                });
                return $exhibition;
            });
        return response()->json([
            'status' => 'success',
            'data' => $exhibitions
        ]);
    }

    public function show($id)
    {
        $exhibition = Exhibition::where('organizer_id', Auth::id())
            ->with(['images'])
            ->find($id);
        if (!$exhibition) {
            return response()->json([
                'status' => 'error',
                'message' => 'No exhibitions has been created'
            ], 404);
        }

        $exhibition->images->transform(function ($img) {
            $img->image_url = asset($img->image_path);
            return $img;
        });

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
            'images'      => 'nullable|array|max:10',
            'images.*'    => 'image|mimes:jpg,jpeg,png,webp|max:2048',
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

        if ($request->hasFile('images')) {
            $this->uploadImages($request->file('images'), $exhibition->id);
        }

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

        $data = $request->only([
            'title',
            'description',
            'location',
            'start_date',
            'end_date'
        ]);

        if (empty($data)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No data provided to update'
            ], 422);
        }
        $exhibition->update($data);

        return response()->json([
            'status' => 'success',
            'data' => $exhibition->load('images')
        ]);
    }

    // إضافة صور لمعرض موجود
    public function addImages(Request $request, $id)
    {
        $exhibition = Exhibition::where('organizer_id', Auth::id())->find($id);

        if (!$exhibition) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Exhibition not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'images'   => 'required|array|max:10',
            'images.*' => 'image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $this->uploadImages($request->file('images'), $exhibition->id);

        return response()->json([
            'status'  => 'success',
            'message' => 'Images added successfully',
            'data'    => $exhibition->load('images')
        ]);
    }

    // حذف صورة واحدة محددة
    public function deleteImage($exhibitionId, $imageId)
    {
        $exhibition = Exhibition::where('organizer_id', Auth::id())
            ->find($exhibitionId);

        if (!$exhibition) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Exhibition not found'
            ], 404);
        }

        $image = Image::where('exhibition_id', $exhibitionId)
            ->find($imageId);

        if (!$image) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Image not found'
            ], 404);
        }

        if (file_exists(public_path($image->image_path))) {
            unlink(public_path($image->image_path));
        }

        $image->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'The image deleted successfully'
        ]);
    }

    // حذف معرض مع جميع صوره
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
// حالات المعرض




    // نشر المعرض — draft → published
    public function publish($id)
    {
        $result = $this->findExhibition($id);

        if ($result['error']) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message']
            ], $result['code']);
        }

        $exhibition = $result['exhibition'];
        if ($exhibition->status !== 'draft') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Exhibition cannot be published because its current status is: ' . $exhibition->status
            ], 422);
        }

        $exhibition->update(['status' => 'published']);
        return response()->json([
            'status'  => 'success',
            'message' => 'The exhibition has been successfully published',
            'data'    => $exhibition
        ]);
    }

    // بدء المعرض — published → ongoing
    public function start($id)
    {
        $result = $this->findExhibition($id);

        if ($result['error']) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message']
            ], $result['code']);
        }

        $exhibition = $result['exhibition'];

        if ($exhibition->status !== 'published') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Exhibition cannot be started because its current status is: ' . $exhibition->status
            ], 422);
        }
        $exhibition->update(['status' => 'ongoing']);

        return response()->json([
                'status'  => 'success',
            'message' => 'The exhibition has started successfully',
            'data'    => $exhibition
        ]);

    }

    // إنهاء المعرض — ongoing → completed
     public function complete($id)
    {

        $result = $this->findExhibition($id);

        if ($result['error']) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message']
            ], $result['code']);
        }

        $exhibition = $result['exhibition'];

        if ($exhibition->status !== 'ongoing') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Exhibition cannot be completed because its current status is: ' . $exhibition->status
            ], 422);
        }

        $exhibition->update(['status' => 'completed']);

        return response()->json([
            'status'  => 'success',
            'message' => 'The exhibition completed successfully',
            'data'    => $exhibition
        ]);
    }

    // إلغاء المعرض — draft أو published → cancelled
    public function cancel($id)
    {

        $result = $this->findExhibition($id);

        if ($result['error']) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message']
            ], $result['code']);
        }

        $exhibition = $result['exhibition'];

        if ($exhibition->status === 'ongoing') {
            return response()->json([
            'status'  => 'error',
            'message' => 'Exhibition cannot be cancelled because it is currently ongoing.'
        ], 422);
    }

        if ($exhibition->status === 'completed') {
            return response()->json([
            'status'  => 'error',
            'message' => 'Exhibition cannot be cancelled because it has already been completed.'
        ], 422);
    }

        if ($exhibition->status === 'cancelled') {
            return response()->json([
            'status'  => 'error',
            'message' => 'Exhibition is already cancelled.'
        ], 422);
    }

        $exhibition->update(['status' => 'cancelled']);

        return response()->json([
            'status'  => 'success',
            'message' => 'The exhibition has been successfully cancelled',
            'data'    => $exhibition
        ]);
    }

//    _________________________________________________________________________

// عمليات ال CRUD التابعة لقسم الادمن

    public function list()
    {
        $exhibitions = Exhibition::with('organizer:id,name,email', 'images')
            ->latest()
            ->get()
            ->map(function ($exhibition) {
                $exhibition->images->transform(function ($img) {
                    $img->image_url = asset($img->image_path);
                    return $img;
                });
                return $exhibition;
            });

        return response()->json([
            'status' => 'success',
            'data'   => $exhibitions
        ]);
    }


    public function read($id)
    {
        $exhibition = Exhibition::with('organizer:id,name,email', 'images')
            ->find($id);
        if (!$exhibition) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exhibition not found'
            ], 404);
        }

        $exhibition->images->transform(function ($img) {
            $img->image_url = asset($img->image_path);
            return $img;
        });

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

        $data = $request->only([
                'title',
                'description',
                'location',
                'start_date',
                'end_date',
        ]);

        if (empty($data)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No data provided to update'
            ], 422);
        }

        $exhibition->update($data);

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
        $this->deleteImages($exhibition);
        $exhibition->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Exhibition deleted successfully'
        ]);
    }
//    _________________________________________________________________________

}
