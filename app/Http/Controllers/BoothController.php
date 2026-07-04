<?php

namespace App\Http\Controllers;

use App\Models\Booth;
use App\Models\Exhibition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BoothController extends Controller
{
    private function findExhibition($exhibitionId)
    {
        $exhibition = Exhibition::find($exhibitionId);

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
                'message' => 'You do not have permission to access this exhibition'
            ];
        }

        return [
            'error'      => false,
            'exhibition' => $exhibition
        ];
    }

    private function findBooth($boothId)
    {
        $booth = Booth::find($boothId);

        if (!$booth) {
            return [
                'error'   => true,
                'code'    => 404,
                'message' => 'Booth not found'
            ];
        }

        if ($booth->exhibition->organizer_id !== Auth::id()) {
            return [
                'error'   => true,
                'code'    => 403,
                'message' => 'You do not have permission to access this booth'
            ];
        }

        return [
            'error' => false,
            'booth' => $booth
        ];
    }

    public function store(Request $request, $exhibitionId)
    {
        $result = $this->findExhibition($exhibitionId);

        if ($result['error']) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message']
            ], $result['code']);
        }

        $validator = Validator::make($request->all(), [
            'booth_number' => 'required|string|max:20',
            'size'         => 'required|string|max:50',
            'price'        => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $booth = Booth::create([
            'exhibition_id' => $exhibitionId,
            'booth_number'  => $request->booth_number,
            'size'          => $request->size,
            'price'         => $request->price,
        ]);

        return response()->json([
            'status' => 'success',
            'data'   => $booth
        ], 201);
    }

    public function index($exhibitionId)
    {
        $result = $this->findExhibition($exhibitionId);

        if ($result['error']) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message']
            ], $result['code']);
        }

        $booths = Booth::where('exhibition_id', $exhibitionId)->get();

        return response()->json([
            'status' => 'success',
            'data'   => $booths
        ]);
    }

    public function show($boothId)
    {
        $result = $this->findBooth($boothId);

        if ($result['error']) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $result['booth']
        ]);
    }

    public function update(Request $request, $boothId)
    {
        $result = $this->findBooth($boothId);

        if ($result['error']) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message']
            ], $result['code']);
        }

        $validator = Validator::make($request->all(), [
            'booth_number' => 'sometimes|string|max:20',
            'size'         => 'sometimes|string|max:50',
            'price'        => 'sometimes|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $result['booth']->update($request->only([
                'booth_number',
                'size',
                'price',
            ]));

        return response()->json([
            'status' => 'success',
            'data'   => $result['booth']
        ]);
    }

    public function destroy($boothId)
    {
        $result = $this->findBooth($boothId);

        if ($result['error']) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message']
            ], $result['code']);
        }

        $result['booth']->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'The booth has been successfully deleted.'
        ]);
    }

//ـــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــ
// إدارة طلبات الحجز للمنظم
//ـــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــ

    private function findBooths($boothId)
    {
        $booth = Booth::with('exhibitor')->find($boothId);

        if (!$booth) {
            return [
                'error'   => true,
                'code'    => 404,
                'message' => 'Booth not found'
            ];
        }

        if ($booth->exhibition->organizer_id !== Auth::id()) {
            return [
                'error'   => true,
                'code'    => 403,
                'message' => 'You do not have permission to access this booth'
            ];
        }

        return [
            'error' => false,
            'booth' => $booth
        ];
    }

    // عرض جميع الطلبات المعلقة لجميع معارض المنظم
    public function indexRequest()
    {
        $pendingBooths = Booth::where('status', 'pending')
            ->whereHas('exhibition', function ($query) {
                $query->where('organizer_id', Auth::id());
            })
            ->with([
                'exhibition:id,title,location,start_date,end_date',
                'exhibitor:id,name,email',
                'exhibitor.exhibitor:user_id,brand_name,phone',
            ])
            ->get();

        if ($pendingBooths->isEmpty()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'لا توجد طلبات معلقة حالياً',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $pendingBooths
        ]);

    }

    // قبول طلب حجز
    public function approve($boothId)
    {
        $result = $this->findBooths($boothId);

        if ($result['error']) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message']
            ], $result['code']);
        }

        $booth = $result['booth'];

        if ($booth->status !== 'pending') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Cannot approve booth because its current status is: ' . $booth->status
            ], 422);
        }

        $booth->update(['status' => 'reserved']);

        return response()->json([
            'status'  => 'success',
            'message' => 'تم قبول طلب الحجز بنجاح',
            'data'    => $booth
        ]);
    }
    // رفض طلب حجز
    public function reject($boothId)
    {

        $result = $this->findBooths($boothId);

        if ($result['error']) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message']
            ], $result['code']);
        }

        $booth = $result['booth'];

        if ($booth->status !== 'pending') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Cannot reject booth because its current status is: ' . $booth->status
            ], 422);
        }

        // إعادة الجناح للحالة متاح وحذف العارض منه
        $booth->update([
            'status'       => 'available',
            'exhibitor_id' => null,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'تم رفض طلب الحجز',
            'data'    => $booth
        ]);
    }


}
