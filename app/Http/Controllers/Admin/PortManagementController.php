<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePortRequest;
use App\Http\Requests\Admin\UpdatePortRequest;
use App\Models\Country;
use App\Models\Port;
use Illuminate\Http\Request;

class PortManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Port::with('country')->orderBy('name');

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('unlocode', 'like', "%{$search}%");
            });
        }

        $ports = $query->paginate(15)->withQueryString();

        $stats = [
            'total_ports'             => Port::count(),
            'total_countries_covered' => Port::distinct('country_id')->count('country_id'),
        ];

        $countries = Country::orderBy('name')->get(['id', 'name', 'iso_code']);

        return view('admin.ports', compact('ports', 'stats', 'countries'));
    }

    public function store(StorePortRequest $request)
    {
        Port::create($request->validated());

        return back()->with('success', 'Pelabuhan baru berhasil ditambahkan.');
    }

    public function update(UpdatePortRequest $request, Port $port)
    {
        $port->update($request->validated());

        return back()->with('success', "Pelabuhan {$port->name} berhasil diperbarui.");
    }

    public function destroy(Port $port)
    {
        $name = $port->name;
        $port->delete();

        return back()->with('success', "Pelabuhan {$name} berhasil dihapus.");
    }
}