<?php

namespace App\Http\Controllers;

use App\Models\Booth;
use App\Models\Exhibition;
use App\Models\Registration;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VisitorController extends Controller
{
    // ادارة الزوار

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
                'message' => 'You do not have permission to access this exhibition'
            ];
        }

        return [
            'error'      => false,
            'exhibition' => $exhibition
        ];
    }

    // عرض الزوار
    public function index($id)
    {
        $result = $this->findExhibition($id);

        if ($result['error']) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message']
            ], $result['code']);
        }

        $registrations = Registration::where('exhibition_id', $id)
            ->with([
                'visitor:id,name,email',
                'visitor.visitor:user_id,phone',
            ])
            ->latest()
            ->get()
            ->map(function ($reg) {
                return [
                    'registration_id' => $reg->id,
                    'ticket_code'     => $reg->ticket_code,
                    'status'          => $reg->status,
                    'registered_at'   => $reg->created_at->format('Y-m-d H:i'),
                    'visitor'         => [
                        'name'  => $reg->visitor->name,
                        'email' => $reg->visitor->email,
                        'phone' => $reg->visitor->visitor->phone ?? null,
                    ],
                ];
            });

        if ($registrations->isEmpty()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'No visitors registered for this exhibition yet',
                'data'    => []
            ]);
        }

        return response()->json([
            'status' => 'success',
            'total'  => $registrations->count(),
            'data'   => $registrations
        ]);
    }



    // تصدير PDF بدون Blade
    public function export($id)
    {
        $result = $this->findExhibition($id);

        if ($result['error']) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message']
            ], $result['code']);
        }

        $exhibition = $result['exhibition'];

        $registrations = Registration::where('exhibition_id', $id)
            ->with([
                'visitor:id,name,email',
                'visitor.visitor:user_id,phone',
            ])
            ->latest()
            ->get();

        if ($registrations->isEmpty()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No visitors to export'
            ], 404);
        }

        // بناء HTML مباشرة بدون Blade
        $html = $this->buildPdfHtml($exhibition, $registrations);

        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'portrait');

        $filename = 'visitors_exhibition_' . $exhibition->id . '_' . now()->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }


    // ═══════════════════════════════════════════
    // بناء HTML للـ PDF مباشرة في الكود
    // ═══════════════════════════════════════════
    private function buildPdfHtml($exhibition, $registrations): string
    {
        $rows = '';
        $i    = 1;

        foreach ($registrations as $reg) {
            $status = match($reg->status) {
                'confirmed' => 'Confirmed',
                'attended'  => 'Attended',
                'cancelled' => 'Cancelled',
                default     => $reg->status
            };

            $statusColor = match($reg->status) {
                'confirmed' => '#0F6E56',
                'attended'  => '#185FA5',
                'cancelled' => '#C62828',
                default     => '#333'
            };

            $phone = $reg->visitor->visitor->phone ?? 'N/A';
            $date  = $reg->created_at->format('Y-m-d');

            $rows .= "
                <tr>
                    <td>{$i}</td>
                    <td>{$reg->visitor->name}</td>
                    <td>{$reg->visitor->email}</td>
                    <td>{$phone}</td>
                    <td>{$reg->ticket_code}</td>
                    <td style='color:{$statusColor}; font-weight:bold;'>{$status}</td>
                    <td>{$date}</td>
                </tr>
            ";
            $i++;
        }

        $total      = $registrations->count();
        $exportedAt = now()->format('Y-m-d H:i');
        $startDate  = $exhibition->start_date;
        $endDate    = $exhibition->end_date;

        return "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'/>
            <style>
                body {
                    font-family: DejaVu Sans, sans-serif;
                    font-size: 12px;
                    color: #333;
                }
                h2 {
                    text-align: center;
                    color: #1F4E79;
                    margin-bottom: 4px;
                }
                .subtitle {
                    text-align: center;
                    color: #555;
                    margin: 2px 0 20px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 10px;
                }
                th {
                    background-color: #1F4E79;
                    color: white;
                    padding: 8px;
                    text-align: left;
                    font-size: 12px;
                }
                td {
                    padding: 7px 8px;
                    border-bottom: 1px solid #ddd;
                    font-size: 11px;
                }
                tr:nth-child(even) {
                    background-color: #f9f9f9;
                }
                .footer {
                    margin-top: 20px;
                    text-align: center;
                    font-size: 10px;
                    color: #888;
                }
            </style>
        </head>
        <body>
            <h2>Exhibition Visitors List</h2>
            <p class='subtitle'>{$exhibition->title} — {$exhibition->location}</p>
            <p class='subtitle'>{$startDate} to {$endDate}</p>

            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Ticket Code</th>
                        <th>Status</th>
                        <th>Registered At</th>
                    </tr>
                </thead>
                <tbody>
                    {$rows}
                </tbody>
            </table>

            <div class='footer'>
                Total Visitors: {$total} — Exported At: {$exportedAt}
            </div>
        </body>
        </html>
        ";

    }

    // إحصائيات جميع المعارض
    public function statistics()
    {
        $organizerId   = Auth::id();
        $exhibitions   = Exhibition::where('organizer_id', $organizerId)->get();

        if ($exhibitions->isEmpty()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'No exhibitions found',
                'data'    => []
            ]);
        }

        $exhibitionIds = $exhibitions->pluck('id');

        return response()->json([
            'status' => 'success',
            'data'   => [
            'exhibitions' => [
                    'total'     => $exhibitions->count(),
                    'draft'     => $exhibitions->where('status', 'draft')->count(),
                    'published' => $exhibitions->where('status', 'published')->count(),
                    'ongoing'   => $exhibitions->where('status', 'ongoing')->count(),
                    'completed' => $exhibitions->where('status', 'completed')->count(),
                    'cancelled' => $exhibitions->where('status', 'cancelled')->count(),
                ],
            'booths' => [
                    'total'     => Booth::whereIn('exhibition_id', $exhibitionIds)->count(),
                    'available' => Booth::whereIn('exhibition_id', $exhibitionIds)->where('status', 'available')->count(),
                    'pending'   => Booth::whereIn('exhibition_id', $exhibitionIds)->where('status', 'pending')->count(),
                    'reserved'  => Booth::whereIn('exhibition_id', $exhibitionIds)->where('status', 'reserved')->count(),
                ],
            'visitors' => [
                    'total'     => Registration::whereIn('exhibition_id', $exhibitionIds)->count(),
                    'confirmed' => Registration::whereIn('exhibition_id', $exhibitionIds)->where('status', 'confirmed')->count(),
                    'attended'  => Registration::whereIn('exhibition_id', $exhibitionIds)->where('status', 'attended')->count(),
                    'cancelled' => Registration::whereIn('exhibition_id', $exhibitionIds)->where('status', 'cancelled')->count(),
                ],
            ]
        ]);
    }

    // إحصائيات معرض واحد
    public function show($id)
    {
        $exhibition = Exhibition::where('organizer_id', Auth::id())->find($id);

        if (!$exhibition) {
            return response()->json([
                    'status'  => 'error',
                'message' => 'Exhibition not found or does not belong to you'
            ], 404);
        }

        $booths        = Booth::where('exhibition_id', $id);
        $registrations = Registration::where('exhibition_id', $id);

        $totalBooths      = (clone $booths)->count();
        $reservedBooths   = (clone $booths)->where('status', 'reserved')->count();
        $totalVisitors    = (clone $registrations)->count();
        $attendedVisitors = (clone $registrations)->where('status', 'attended')->count();

        return response()->json([
                'status' => 'success',
            'data'   => [
            'exhibition' => [
            'title'      => $exhibition->title,
                    'location'   => $exhibition->location,
                    'start_date' => $exhibition->start_date,
                    'end_date'   => $exhibition->end_date,
                    'status'     => $exhibition->status,
                ],
                'booths' => [
                'total'            => $totalBooths,
                    'available'        => (clone $booths)->where('status', 'available')->count(),
                    'pending'          => (clone $booths)->where('status', 'pending')->count(),
                    'reserved'         => $reservedBooths,
                    'occupancy_rate'   => $this->calcRate($totalBooths, $reservedBooths),
                ],
                'visitors' => [
                'total'            => $totalVisitors,
                    'confirmed'        => (clone $registrations)->where('status', 'confirmed')->count(),
                    'attended'         => $attendedVisitors,
                    'cancelled'        => (clone $registrations)->where('status', 'cancelled')->count(),
                    'attendance_rate'  => $this->calcRate($totalVisitors, $attendedVisitors),
                ],
            ]
        ]);
    }

    private function calcRate(int $total, int $part): string
    {
        if ($total === 0) return '0%';
        return round(($part / $total) * 100, 1) . '%';
    }


}
