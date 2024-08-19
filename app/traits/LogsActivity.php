<?php
namespace App\Traits;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;
    use DateTime;
    use DateTimeZone;

    trait LogsActivity
    {
        protected function logActivity($description, $action, $data = [], $oldData = [], $additionalContext = [])
        {
            $date = new DateTime('now', new DateTimeZone('UTC'));
            $date->setTimezone(new DateTimeZone('America/Tegucigalpa'));

            if (Auth::check()) {
                \Log::info('Datos anteriores (oldData): ', $oldData);
                \Log::info('Datos nuevos (data): ', $data);
                DB::table('TBL_MS_BITACORA')->insert([
                    'ID_USUARIO' => Auth::user()->ID_USUARIO,
                    'ID_OBJETO' => $this->getObjectId($action),
                    'FECHA' => $date->format('Y-m-d H:i:s'),
                    'ACCION' => $this->getActionDescription($action, $description, $additionalContext),
                    'DESCRIPCION' => $this->getDetailedDescription($action, $description, $data, $oldData, $additionalContext),
                ]);
            }
        }

        private function getActionDescription($action, $description, $additionalContext)
        {
            switch (strtolower($action)) {
                case 'get':
                    return 'Consulta de ' . $description;
                case 'post':
                    return 'Creación de ' . $description;
                case 'put':
                    return 'Actualización de ' . $description;
                case 'delete':
                    return 'Eliminación de ' . $description;
                default:
                    return ucfirst($action) . ' de ' . $description;
            }
        }

        private function getDetailedDescription($action, $description, $data, $oldData, $additionalContext)
        {
            switch (strtolower($action)) {
                case 'post':
                    $dataDetails = [];
                    foreach ($data as $key => $value) {
                        $dataDetails[] = "$key: '$value'";
                    }
                    return 'Creación de ' . $description . ' con los siguientes datos: ' . implode(', ', $dataDetails);
                case 'put':
                    $changes = [];
                    foreach ($data as $key => $newValue) {
                        $oldValue = $oldData[$key] ?? '(no definido)';
                        if ($newValue !== $oldValue) {
                            $changes[] = "$key de '$oldValue' a '$newValue'";
                        }
                    }
                    return 'Actualización de ' . $description . ' con los cambios: ' . implode(', ', $changes);
                case 'delete':
                    $oldValues = [];
                    foreach ($oldData as $key => $value) {
                        $oldValues[] = "$key: '$value'";
                    }
                    return 'Eliminación de ' . $description . ' con los valores: ' . implode(', ', $oldValues);
                default:
                    return ucfirst($action) . ' de ' . $description;
            }
        }
        private function getObjectId($action)
        {
            // Mapeo de las acciones a los nombres de los objetos
            $actionToObject = [
                'get' => 'OBTENCION DE DATOS',
                'post' => 'INSERCION DE DATOS',
                'put' => 'ACTUALIZACION DE DATOS',
                'delete' => 'ELIMINACION DE DATOS',
                'login' => 'INICIO DE SESION',
                'logout' => 'CIERRE DE SESION',
                'visit' => 'CAMBIO DE PAGINA',
            ];
    
            $objectName = $actionToObject[strtolower($action)] ?? null;
    
            if (!$objectName) {
                \Log::info('Objeto no encontrado para la acción:', [$action]);
                return null;
            }
    
            // Busca en la tabla TBL_OBJETOS el ID_OBJETO para la acción específica
            $object = DB::table('TBL_OBJETOS')
                        ->where('OBJETO', '=', $objectName)
                        ->first();
            
            \Log::info('Objeto encontrado en TBL_OBJETOS:', [$object]);
    
            return $object ? $object->ID_OBJETO : null;
        }
    }
