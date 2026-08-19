<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $date = $request->date ?? today()->format('Y-m-d');
        $status = $request->status ?? '';

        $appointments = Appointment::with(['pet.customer', 'doctor'])
            ->whereDate('scheduled_at', $date)
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy('scheduled_at')
            ->get();

        return Inertia::render('Appointments/Index', [
            'appointments' => $appointments,
            'filters'      => compact('date', 'status'),
            // Truyền danh sách status để render dropdown filter
            'statuses'     => [
                'pending'    => 'Chờ xác nhận',
                'confirmed'  => 'Đã xác nhận',
                'checked_in' => 'Đã check-in',
                'completed'  => 'Hoàn thành',
                'cancelled'  => 'Đã hủy',
                'no_show'    => 'Không đến',
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $pets = Pet::with('customer')
            ->orderBy('name')
            ->get(['id', 'name', 'species', 'customer_id']);
        $doctors = User::role('vet')->get(['id', 'name']);
        return Inertia::render('Appointments/Create', [
            'pets'    => $pets,
            'doctors' => $doctors,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAppointmentRequest $request)
    {
        $validated = $request->validated();
        if (isset($validated['notes'])) {
            $validated['note'] = $validated['notes'];
        }

        $data = array_merge([
            'status'     => 'pending',
            'created_by' => $request->user()->id,
        ], $validated);

        $appointment = Appointment::create($data);

        $targetDate = $appointment->scheduled_at 
            ? $appointment->scheduled_at->format('Y-m-d') 
            : today()->format('Y-m-d');

        return redirect()
            ->route('appointments.index', ['date' => $targetDate])
            ->with('success', 'Đã đặt lịch hẹn thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Appointment $appointment): Response
    {
        $pets    = Pet::with('customer')->orderBy('name')->get(['id', 'name', 'species', 'customer_id']);
        $doctors = User::role('vet')->get(['id', 'name']);
        return Inertia::render('Appointments/Edit', [
            'appointment' => $appointment->load(['pet.customer', 'doctor']),
            'pets'        => $pets,
            'doctors'     => $doctors,
            'statuses'    => [
                'pending'    => 'Chờ xác nhận',
                'confirmed'  => 'Đã xác nhận',
                'checked_in' => 'Đã check-in',
                'completed'  => 'Hoàn thành',
                'cancelled'  => 'Đã hủy',
                'no_show'    => 'Không đến',
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        $validated = $request->validated();
        if (isset($validated['notes'])) {
            $validated['note'] = $validated['notes'];
        }

        $appointment->update($validated);
        return redirect()
            ->route('appointments.index', ['date' => $appointment->scheduled_at->format('Y-m-d')])
            ->with('success', 'Đã cập nhật lịch hẹn!');
    }

    public function checkIn(Appointment $appointment)
    {
        // Chỉ cho phép check-in nếu đang ở trạng thái confirmed
        if ($appointment->status !== 'confirmed') {
            return back()->with('error', 'Chỉ có thể check-in lịch đã xác nhận!');
        }
        $appointment->update(['status' => 'checked_in']);
        return back()->with('success', "Đã check-in cho {$appointment->pet->name}!");
    }

    public function confirm(Appointment $appointment)
    {
        if ($appointment->status !== 'pending') {
            return back()->with('error', 'Chỉ xác nhận được lịch đang chờ!');
        }
        $appointment->update(['status' => 'confirmed']);
        return back()->with('success', 'Đã xác nhận lịch hẹn!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Appointment $appointment)
    {
        $appointment->update(['status' => 'cancelled']);
        return back()->with('success', 'Đã hủy lịch hẹn!');
    }
}
