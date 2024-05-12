<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BillController extends Controller
{
    public function index()
    {
        $facturas = Bill::all();
        return response()->json($facturas);
    }

    public function show($id)
    {
        $factura = Bill::find($id);
        if (!$factura) {
            return response()->json(['message' => 'Factura no encontrada'], 404);
        }
        return response()->json($factura);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombreVendedor' => 'required',
            'idUsuario' => 'required',
            'nomTienda' => 'required',
            'dirTienda' => 'required',
            'idContacto' => 'required',
            'idRes' => 'required',
            'fechaEmision' => 'required',
            'metodoPago' => 'required',
            'terYcon' => 'required',
            'costEnvio' => 'required',
            'totalPagar' => 'required',
        ]);

        $factura = Bill::create($request->all());

        return response()->json($factura, 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombreVendedor' => 'required',
            'idUsuario' => 'required',
            'nomTienda' => 'required',
            'dirTienda' => 'required',
            'idContacto' => 'required',
            'idRes' => 'required',
            'fechaEmision' => 'required',
            'metodoPago' => 'required',
            'terYcon' => 'required',
            'costEnvio' => 'required',
            'totalPagar' => 'required',
        ]);

        $factura = Bill::find($id);
        if (!$factura) {
            return response()->json(['message' => 'Factura no encontrada'], 404);
        }

        $factura->update($request->all());

        return response()->json($factura, 200);
    }

    public function destroy($id)
    {
        $factura = Bill::find($id);
        if (!$factura) {
            return response()->json(['message' => 'Factura no encontrada'], 404);
        }

        $factura->delete();

        return response()->json(['message' => 'Factura eliminada correctamente'], 200);
    }
}
