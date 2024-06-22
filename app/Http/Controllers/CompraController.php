<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\DetalleCompra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\JwtAuth;
use App\Models\Carrito;
use App\Models\User;
use Illuminate\Support\Facades\Log;

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
        \Log::info('Encabezados recibidos:', $request->headers->all());
        \Log::info('Cuerpo de la solicitud:', $request->all());
        $bearerToken = $request->header('bearertoken');
        \Log::info('Datos recibidos para crear compra:', $request->all());
        if (!$bearerToken) {
            return response()->json([
                'status' => 401,
                'message' => 'Token inválido o no proporcionado.'
            ], 401);
        }
        $jwt = new JwtAuth();
        $decodedToken = $jwt->checkToken($bearerToken, true);

        if (!$decodedToken || !isset($decodedToken->iss)) {
            return response()->json(['status' => 400, 'message' => 'Token inválido'], 400);
        }
        $userId = $decodedToken->iss;
       
      
        $carrito = Carrito::where('user_id', $userId)->first();
        if (!$carrito) {
            return response()->json([
                'status' => 404,
                'message' => 'Carrito no encontrado para el usuario.'
            ], 404);
        }
    
        $data_input = $request->input('data', null);
        if ($data_input) {
            $data = json_decode($data_input, true);
            $data = array_map('trim', $data);
            $rules = [
                'estadoCompra'=> 'required',
                'fecha'=> 'required|date'
            ];
            $isValid = \Validator::make($data, $rules);
            if (!$isValid->fails()) {
                $compra = new Compra();
                $compra->idUsuario = $userId;
                $compra->idCarrito = $carrito->id;
                $compra->estadoCompra = $data['estadoCompra'];
                $compra->fecha = $data['fecha'];
                $compra->save();
                $this->eliminarProductosComprados($carrito->id);
                $response = array(
                    'status' => 201,
                    'message' => 'Compra agregada',
                    'compra' => $compra
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
