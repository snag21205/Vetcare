<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePetRequest;
use App\Http\Requests\UpdatePetRequest;
use Illuminate\Http\Request;
use Inertia\Response;
use App\Models\Customer;
use App\Models\Pet;
use Inertia\Inertia;

class PetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $pets = Pet::with('customer')->latest()->paginate(15);
        return Inertia::render('Pets/Index', [
            'pets' => $pets
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $customers = Customer::orderBy('name')->get(['id', 'name', 'phone']);
        return Inertia::render('Pets/Create', ['customers' => $customers]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePetRequest $request)
    {
        Pet::create($request->validated());
        return redirect()->route('pets.index')->with('success', 'Đã thêm vật nuôi thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pet $pet): Response
    {
        $customers = Customer::orderBy('name')->get(['id', 'name', 'phone']);

        return Inertia::render('Pets/Edit', ['pet' => $pet->load('customer'), 'customers' => $customers,]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePetRequest $request, Pet $pet)
    {
        $pet->update($request->validated());
        return redirect()->route('pets.index')->with('success', 'Đã cập nhập thông tin vật nuôi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pet $pet)
    {
        $pet->delete();
        return redirect()->route('pets.index')->with('success', 'Đã xóa vật nuôi thành công.');
    }

    /**
     * Lấy lịch sử khám bệnh của vật nuôi (JSON API).
     */
    public function history(Pet $pet)
    {
        $history = $pet->medicalRecords()
            ->with(['doctor', 'prescriptions.medicine', 'services'])
            ->latest()
            ->get();

        return response()->json($history);
    }
}
