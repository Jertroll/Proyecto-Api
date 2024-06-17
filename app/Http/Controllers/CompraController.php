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
        \Log::info('Datos recibidos para crear reserva:', $request->all());
        $validatedData = $request->validate([
            'cliente_id' => 'required|integer|exists:clientes,id',
            'idCarrito'=> 'required|integer', 
            'estadoCompra'=> 'required',
            'fecha'=> 'required|date',
            'detalles' => 'required|array',
            'detalles.*.idProducto' => 'required|integer|exists:productos,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precioUnitario' => 'required|numeric|min:0',
            'detalles.*.subTotal' => 'required|numeric|min:0',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 406,
                'message' => 'Datos de la compra inválidos',
                'errors' => $validator->errors()
            ], 406);
        }

        try {
            $compra = Compra::create([
                'cliente_id' =>$request->input ('cliente_id'),
                'idCarrito' =>$request->input ('idCarrito'),
                'estadoCompra' =>$request->input ('estadoCompra'),
                'fecha'=>$request->input ('fecha'),
                ]);

                if ($request->has('detalles')) {
                    foreach ($request->input('detalles') as $detalle) {
                        
                        $producto = Produto::findOrFail($detalle['idProducto']);
                        $precioUnitario = $producto->precio;// Asumo que este dato se obtendrá de algún lugar (ej. tabla Tour)
                        $subTotal = $precioUnitario * $detalle['cantidad']; // Calcular el subtotal
    
                        DetalleCompra::create([
                            'idCompra' => $compra->idCompra,
                            'idProducto' => $detalle['idProducto'],
                            'cantidad' => $detalle['cantidad'],
                            'precioUnitario' => $precioUnitario,
                            'subTotal' => $subTotal,
                        ]);
                    }
                }

            return response()->json([
                'status' => 201,
                'message' => 'Reserva y detalles de reserva creados exitosamente',
                'reserva' => $compra->load('detalles.producto'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Ocurrió un error al procesar la solicitud',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Actualizar una compra existente
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'cliente_id' => 'integer|exists:clientes,id',
            'detalles' => 'array',
            'detalles.*.idProducto' => 'integer|exists:productos,id',
            'detalles.*.cantidad' => 'integer|min:1',
            'detalles.*.precioUnitario' => 'numeric|min:0',
            'detalles.*.subTotal' => 'numeric|min:0',
        ]);

        $compra = Compra::find($id);
        if (is_null($compra)) {
            return response()->json(['message' => 'Compra no encontrada'], 404);
        }

        DB::beginTransaction();

        try {
            if (isset($validatedData['cliente_id'])) {
                $compra->update(['cliente_id' => $validatedData['cliente_id']]);
            }

            if (isset($validatedData['detalles'])) {
                DetalleCompra::where('idCompra', $compra->id)->delete();

                foreach ($validatedData['detalles'] as $detalle) {
                    $detalle['idCompra'] = $compra->id;
                    DetalleCompra::create($detalle);
                }
            }

            DB::commit();

            return response()->json($compra->load('detalles.producto'));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al actualizar la compra', 'error' => $e->getMessage()], 500);
        }
    }

    // Eliminar una compra
    public function destroy($id)
    {
        $compra = Compra::find($id);
        if (is_null($compra)) {
            return response()->json(['message' => 'Compra no encontrada'], 404);
        }

        DB::beginTransaction();

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
