<?php

namespace App\Http\Controllers;

use App\Models\Booth;
use App\Models\Exhibition;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    // دالة مساعدة للبحث عن مستخدم بدور محدد
    private function findUser($id, string $role)
    {
        $user = User::where('role', $role)->find($id);

        if (!$user) {
            return [
                'error'   => true,
                'message' => ucfirst($role) . ' not found'
            ];
        }

        return ['error' => false, 'user' => $user];
    }
    private function deactivateUser($id, string $role)
    {
        $result = $this->findUser($id, $role);

        if ($result['error']) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message']
            ], 404);
        }

        $user = $result['user'];

        if (!$user->is_active) {
            return response()->json([
                'status'  => 'error',
                'message' => ucfirst($role) . ' account is already deactivated'
            ], 422);
        }

        $user->update(['is_active' => false]);
        $user->tokens()->delete();

        return response()->json([
            'status'  => 'success',
            'message' => ucfirst($role) . ' account deactivated successfully'
        ]);
    }

    // دالة مساعدة لتفعيل الحساب
      private function activateUser($id, string $role)
    {
        $result = $this->findUser($id, $role);

        if ($result['error']) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message']
            ], 404);
        }

        $user = $result['user'];

        if ($user->is_active) {
            return response()->json([
                'status'  => 'error',
                'message' => ucfirst($role) . ' account is already activated'
            ], 422);
        }

        $user->update(['is_active' => true]);

        return response()->json([
            'status'  => 'success',
            'message' => ucfirst($role) . ' account activated successfully'
        ]);
    }

    // دالة مساعدة لحذف المستخدم
    // ═══════════════════════════════════════════
    private function deleteUser($id, string $role)
    {
        $result = $this->findUser($id, $role);

        if ($result['error']) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message']
            ], 404);
        }

        $user = $result['user'];
        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'status'  => 'success',
            'message' => ucfirst($role) . ' deleted successfully'
        ]);
    }

//ــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــ

// المنظمون

    public function indexOrganizers()
    {
        $users = User::where('role', 'organizer')
            ->with('organizer')
            ->latest()
            ->get();

        if ($users->isEmpty()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'No organizers found',
                'data'    => []
            ]);
        }

        return response()->json(['status' => 'success', 'data' => $users]);
    }

    public function showOrganizer($id)
    {
        $user = User::where('role', 'organizer')
            ->with('organizer')
            ->find($id);

        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Organizer not found'
            ], 404);
        }

        return response()->json(['status' => 'success', 'data' => $user]);
    }

    public function updateOrganizer(Request $request, $id)
    {
        $result = $this->findUser($id, 'organizer');

        if ($result['error']) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message']
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'  => 'sometimes|string|max:100',
            'email' => 'sometimes|email|unique:users,email,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $data = $request->only(['name', 'email']);

        if (empty($data)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No data provided to update'
            ], 422);
        }

        $result['user']->update($data);

        return response()->json([
            'status'  => 'success',
            'message' => 'Organizer updated successfully',
            'data'    => $result['user']
        ]);
    }

    public function deactivateOrganizer($id)
    {
        return $this->deactivateUser($id, 'organizer');
    }
    public function activateOrganizer($id)
    {
        return $this->activateUser($id, 'organizer');
    }
    public function deleteOrganizer($id)
    {
        return $this->deleteUser($id, 'organizer');
    }

//ــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــ

//العارضون

    public function indexExhibitors()
    {
        $users = User::where('role', 'exhibitor')
            ->with('exhibitor')
            ->latest()
            ->get();

        if ($users->isEmpty()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'No exhibitors found',
                'data'    => []
            ]);
        }

        return response()->json(['status' => 'success', 'data' => $users]);
    }

    public function showExhibitor($id)
    {
        $user = User::where('role', 'exhibitor')
            ->with('exhibitor')
            ->find($id);

        if (!$user) {
            return response()->json([
                    'status'  => 'error',
                'message' => 'Exhibitor not found'
            ], 404);
        }

        return response()->json(['status' => 'success', 'data' => $user]);
    }


    public function updateExhibitor(Request $request, $id)
    {
        $result = $this->findUser($id, 'exhibitor');

        if ($result['error']) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message']
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'  => 'sometimes|string|max:100',
            'email' => 'sometimes|email|unique:users,email,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $data = $request->only(['name', 'email']);

        if (empty($data)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No data provided to update'
            ], 422);
        }

        $result['user']->update($data);

        return response()->json([
            'status'  => 'success',
            'message' => 'Exhibitor updated successfully',
            'data'    => $result['user']
        ]);
    }

    public function deactivateExhibitor($id)
    {
        return $this->deactivateUser($id, 'exhibitor');
    }
    public function activateExhibitor($id)
    {
        return $this->activateUser($id, 'exhibitor');
    }
    public function deleteExhibitor($id)
    {
        return $this->deleteUser($id, 'exhibitor');
    }


//ــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــ
    // الزوار
    public function indexVisitors()
    {
        $users = User::where('role', 'visitor')
            ->with('visitor')
            ->latest()
            ->get();

        if ($users->isEmpty()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'No visitors found',
                'data'    => []
            ]);
        }

        return response()->json(['status' => 'success', 'data' => $users]);
    }

    public function showVisitor($id)
    {
        $user = User::where('role', 'visitor')
            ->with('visitor')
            ->find($id);

        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Visitor not found'
            ], 404);
        }

        return response()->json(['status' => 'success', 'data' => $user]);
    }

    public function deactivateVisitor($id)
    {
        return $this->deactivateUser($id, 'visitor');
    }
    public function activateVisitor($id)
    {
        return $this->activateUser($id, 'visitor');
    }
    public function deleteVisitor($id)
    {
        return $this->deleteUser($id, 'visitor');
    }

//ــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــ

// المعارض

    private function findExhibition($id)
    {
        $exhibition = Exhibition::find($id);

        if (!$exhibition) {
            return ['error' => true, 'message' => 'Exhibition not found'];
        }

        return ['error' => false, 'exhibition' => $exhibition];
    }

    public function index()
    {
        $exhibitions = Exhibition::with([
                'organizer:id,name,email',
                'images'
            ])->latest()->get();
        if ($exhibitions->isEmpty()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'No exhibitions found',
                'data'    => []
            ]);
        }
        return response()->json(['status' => 'success', 'data' => $exhibitions]);
    }

    public function show($id)
    {
        $result = $this->findExhibition($id);

        if ($result['error']) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message']
            ], 404);
        }

        $exhibition = Exhibition::with([
                'organizer:id,name,email',
                'images'
            ])->find($id);

        return response()->json(['status' => 'success', 'data' => $exhibition]);
    }

    public function update(Request $request, $id)
    {
        $result = $this->findExhibition($id);

        if ($result['error']) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message']
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
                'title', 'description', 'location', 'start_date', 'end_date'
            ]);

        if (empty($data)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No data provided to update'
            ], 422);
        }

        $result['exhibition']->update($data);

        return response()->json([
            'status'  => 'success',
            'message' => 'Exhibition updated successfully',
            'data'    => $result['exhibition']
        ]);
    }

    public function cancel($id)
    {
        $result = $this->findExhibition($id);

        if ($result['error']) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message']
            ], 404);
        }

        $exhibition = $result['exhibition'];

        if ($exhibition->status === 'cancelled') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Exhibition is already cancelled'
            ], 422);
        }

        if ($exhibition->status === 'completed') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Cannot cancel a completed exhibition'
            ], 422);
        }

        $exhibition->update(['status' => 'cancelled']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Exhibition cancelled successfully'
        ]);
    }

    public function destroy($id)
    {
        $result = $this->findExhibition($id);

        if ($result['error']) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message']
            ], 404);
        }

        $result['exhibition']->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Exhibition deleted successfully'
        ]);
    }


//ــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــ

// الاحصائيات

    public function statistics()
    {
        return response()->json([
                'status' => 'success',
            'data'   => [
            'users' => [
            'total'      => User::whereNot('role', 'admin')->count(),
                    'organizers' => User::where('role', 'organizer')->count(),
                    'exhibitors' => User::where('role', 'exhibitor')->count(),
                    'visitors'   => User::where('role', 'visitor')->count(),
                    'active'     => User::whereNot('role', 'admin')->where('is_active', true)->count(),
                    'inactive'   => User::whereNot('role', 'admin')->where('is_active', false)->count(),
                ],
                'exhibitions' => [
                'total'     => Exhibition::count(),
                    'draft'     => Exhibition::where('status', 'draft')->count(),
                    'published' => Exhibition::where('status', 'published')->count(),
                    'ongoing'   => Exhibition::where('status', 'ongoing')->count(),
                    'completed' => Exhibition::where('status', 'completed')->count(),
                    'cancelled' => Exhibition::where('status', 'cancelled')->count(),
                ],
                'booths' => [
                'total'     => Booth::count(),
                    'available' => Booth::where('status', 'available')->count(),
                    'pending'   => Booth::where('status', 'pending')->count(),
                    'reserved'  => Booth::where('status', 'reserved')->count(),
                ],
                'registrations' => [
                'total'     => Registration::count(),
                    'confirmed' => Registration::where('status', 'confirmed')->count(),
                    'attended'  => Registration::where('status', 'attended')->count(),
                    'cancelled' => Registration::where('status', 'cancelled')->count(),
                ],
            ]
        ]);
    }

}
