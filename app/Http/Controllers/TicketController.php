<?php

namespace App\Http\Controllers;

use App\Models\Exhibition;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    private function generateTicketCode(): string
    {
        do {
            $code = 'TKT-' . strtoupper(Str::random(4)) . '-' . now()->format('Ymd');
            }
        while (Registration::where('ticket_code', $code)->exists());

        return $code;
    }



    // ═══════════════════════════════════════════
    // التسجيل في معرض
    // POST /api/visitor/exhibitions/{id}/register
    // ═══════════════════════════════════════════
    public function register($exhibitionId)
    {
        // التحقق من وجود المعرض وأنه منشور
        $exhibition = Exhibition::where('status', 'published')
            ->find($exhibitionId);

        if (!$exhibition) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Exhibition not found or not available for registration'
            ], 404);
        }

        // التحقق أن الزائر لم يسجّل مسبقاً
        $existing = Registration::where('exhibition_id', $exhibitionId)
            ->where('visitor_id', Auth::id())
            ->whereIn('status', ['confirmed', 'attended'])
            ->first();

        if ($existing) {
            return response()->json([
                'status'  => 'error',
                'message' => 'You are already registered for this exhibition',
                'ticket_code' => $existing->ticket_code
            ], 422);
        }

        // إنشاء التسجيل
        $registration = Registration::create([
            'exhibition_id' => $exhibitionId,
            'visitor_id'    => Auth::id(),
            'ticket_code'   => $this->generateTicketCode(),
            'status'        => 'confirmed',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Registration successful. Your ticket is ready',
            'data'    => [
                'ticket_code'    => $registration->ticket_code,
                'exhibition'     => $exhibition->title,
                'location'       => $exhibition->location,
                'start_date'     => $exhibition->start_date,
                'end_date'       => $exhibition->end_date,
                'status'         => $registration->status,
                'registered_at'  => $registration->created_at->format('Y-m-d H:i'),
            ]
        ], 201);
    }

    // عرض جميع تذاكر الزائر
    // GET /api/visitor/tickets
    // ═══════════════════════════════════════════
    public function myTickets()
    {
        $registrations = Registration::where('visitor_id', Auth::id())
            ->with(['exhibition:id,title,location,start_date,end_date,status'])
            ->latest()
            ->get()
            ->map(function ($reg) {
                return [
                    'ticket_id'     => $reg->id,
                    'ticket_code'   => $reg->ticket_code,
                    'status'        => $reg->status,
                    'registered_at' => $reg->created_at->format('Y-m-d H:i'),
                    'exhibition'    => [
                        'title'      => $reg->exhibition->title,
                        'location'   => $reg->exhibition->location,
                        'start_date' => $reg->exhibition->start_date,
                        'end_date'   => $reg->exhibition->end_date,
                        'status'     => $reg->exhibition->status,
                    ],
                ];
            });

        if ($registrations->isEmpty()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'You have no tickets yet',
                'data'    => []
            ]);
        }

        return response()->json([
            'status' => 'success',
            'total'  => $registrations->count(),
            'data'   => $registrations
        ]);
    }

    // عرض تفاصيل تذكرة محددة
    // GET /api/visitor/tickets/{id}
    // ═══════════════════════════════════════════
    public function showTicket($id)
    {
        $registration = Registration::where('visitor_id', Auth::id())
            ->with(['exhibition:id,title,location,start_date,end_date,status'])
            ->find($id);

        if (!$registration) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Ticket not found'
            ], 404);
        }

        return response()->json([
                'status' => 'success',
            'data'   => [
            'ticket_id'     => $registration->id,
                'ticket_code'   => $registration->ticket_code,
                'status'        => $registration->status,
                'registered_at' => $registration->created_at->format('Y-m-d H:i'),
                'exhibition'    => [
                'title'          => $registration->exhibition->title,
                    'location'   => $registration->exhibition->location,
                    'start_date' => $registration->exhibition->start_date,
                    'end_date'   => $registration->exhibition->end_date,
                    'status'     => $registration->exhibition->status,
                ],
            ]
        ]);
    }

    // إلغاء تذكرة
    // DELETE /api/visitor/tickets/{id}
    // ═══════════════════════════════════════════
    public function cancelTicket($id)
    {
        $registration = Registration::where('visitor_id', Auth::id())
            ->find($id);

        if (!$registration) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Ticket not found'
            ], 404);
        }

        if ($registration->status === 'cancelled') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Ticket is already cancelled'
            ], 422);
        }

        if ($registration->status === 'attended') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Cannot cancel a ticket that has already been used'
            ], 422);
        }

        $registration->update(['status' => 'cancelled']);

        return response()->json([
                'status'  => 'success',
            'message' => 'Ticket cancelled successfully'
        ]);
    }






}
