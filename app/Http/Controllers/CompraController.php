<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\DetalleCompra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{
    // Listar todas las compras
    public function index()
    {
        $compras = Compra::with('detalles.producto')->get();
        return response()->json($compras);
    }

    // Mostrar una compra específica
    public function show($id)
    {
        $compra = Compra::with('detalles.producto')->find($id);
        if (is_null($compra)) {
            return response()->json(['message' => 'Compra no encontrada'], 404);
        }
        return response()->json($compra);
    }

    // Crear una nueva compra y sus detalles
    public function store(Request $request)
    {
        \Log::info('Datos recibidos para crear compra:', $request->all());
        $data_input=$request->input('data',null);
        if ($data_input) {
            $data=json_decode($data_input,true);
            $data = array_map('trim', $data);
            $rules = [
            'idUsuario' => 'required|integer|exists:users,idUsuario',
            'idCarrito'=> 'required|integer', 
            'estadoCompra'=> 'required',
            'fecha'=> 'required|date'
            ];
            $isValid = \validator($data, $rules);
            if (!$isValid->fails()) {
                $compra = new Compra();
                $compra->idUsuario = $data['idUsuario'];
                $compra->idCarrito = $data['idCarrito'];
                $compra->estadoCompra = $data['estadoCompra'];
                $compra->fecha = $data['fecha'];
                $compra->save();
                $response = array(
                    'status' => 201,
                    'message' => 'Compra agregada',
                    'producto' => $compra
                );
            } else {
                $response = array(
                    'status' => 406,
                    'message' => 'Datos inválidos',
                    'errors' => $isValid->errors()
                );
            }
        } else {
            $response = array(
                'status' => 400,
                'message' => 'No se encontró el objeto data'
            );
        }
        return response()->json($response, $response['status']);
       
    }

    // Eliminar una compra
    public function destroy($id)
    {
        $compra = Compra::find($id);
        if (is_null($compra)) {
            return response()->json(['message' => 'Compra no encontrada'], 404);
        }

        try {
            DetalleCompra::where('idCompra', $compra->id)->delete();
            $compra->delete();

            DB::commit();

            return response()->json(['message' => 'Compra eliminada'], 204);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al eliminar la compra', 'error' => $e->getMessage()], 500);
        }
    }
}
