

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DetalleFactura;
use App\Models\Bill;
use Carbon\Carbon;

class DetalleFacturaController extends Controller
{
    public function index()
    {
        $detalles = DetalleFactura::all();
        
        return response()->json(['status' => 200, 'message' => 'Detalles de factura obtenidos con éxito', 'data' => $detalles], 200);
    }
    
    public function show($id)
    {
        $detalle = DetalleFactura::find($id);
        
        if (!$detalle) {
            return response()->json(['status' => 404, 'message' => 'Detalle de factura no encontrado'], 404);
        }
        
        return response()->json(['status' => 200, 'message' => 'Detalle de factura obtenido con éxito', 'data' => $detalle], 200);
    }
    public function store(Request $request){
        {
            // Obtener los datos de la solicitud
            $data = $request->input('data', null);
            
            // Verificar si se proporcionaron datos
            if (!$data) {
                return response()->json(['status' => 400, 'message' => 'No se encontró el objeto data'], 400);
            }
            
            // Decodificar los datos JSON
            $decodedData = json_decode($data, true);
            
            // Verificar si la decodificación fue exitosa
            if ($decodedData === null || !is_array($decodedData)) {
                return response()->json(['status' => 400, 'message' => 'Los datos proporcionados no son válidos'], 400);
            }
            
            // Validar los datos
            $validator = \Validator::make($decodedData, [
                'idFactura' => 'required',
                'fecha' => 'required',
                'total' => 'required',
                'impuesto' => 'required',
                'totalPagar' => 'required',
            ]);
            
            // Verificar si la validación falla
            if ($validator->fails()) {
                return response()->json(['status' => 406, 'message' => 'Datos inválidos', 'errors' => $validator->errors()], 406);
            }
            
            try {
                 $detalleFactura = new DetalleFactura();
                 $detalleFactura->fill($decodedData);
                
                 $bill->idFactura = $data['idFactura'];
                 $bill = Bill::findOrFail($data['idFactura']);

                 $detalleFactura->fecha = date('Y-m-d H:i:s');
                 $fechaHoraActual = Carbon::now();
                 $detalleFactura->fechaHora = $fechaHoraActual;
                $detalleFactura->save();
                
                // Devolver la respuesta JSON con el detalle de la factura
                return response()->json(['status' => 201, 'message' => 'Detalle de factura creado', 'detalleFactura' => $detalleFactura], 201);
            } catch (\Exception $e) {
                // Manejar cualquier excepción y devolver una respuesta de error
                return response()->json(['status' => 500, 'message' => 'Error al crear el detalle de factura: ' . $e->getMessage()], 500);
            }
        }
    }    

    public function destroy($id)
    {
        $detalle = DetalleFactura::find($id);
        
        if (!$detalle) {
            return response()->json(['status' => 404, 'message' => 'Detalle de factura no encontrado'], 404);
        }
        
        try {
            $detalle->delete();
            
            return response()->json(['status' => 200, 'message' => 'Detalle de factura eliminado con éxito'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => 'Error al eliminar el detalle de factura: ' . $e->getMessage()], 500);
        }
    }
    
    public function update(Request $request, $id)
{
    // Obtener los datos de la solicitud
    $data = $request->only(['impuesto', 'totalPagar']);

    // Verificar si el detalle de factura existe
    $detalleFactura = DetalleFactura::find($id);
    if (!$detalleFactura) {
        return response()->json(['status' => 404, 'message' => 'Detalle de factura no encontrado'], 404);
    }

    // Actualizar el detalle de la factura con los nuevos datos
    $detalleFactura->update($data);

    // Devolver la respuesta JSON con los datos actualizados del detalle de la factura
    return response()->json(['status' => 200, 'message' => 'Detalle de factura actualizado', 'detalleFactura' => $detalleFactura], 200);
}

}
