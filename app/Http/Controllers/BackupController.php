<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Storage;

class BackupController extends Controller
{
    public function createBackup()
{
    try {
        // Establecer la zona horaria
        date_default_timezone_set('America/Tegucigalpa');

        // Obtén las configuraciones de la base de datos desde el archivo de configuración
        $dbHost = env('DB_HOST');
        $dbPort = env('DB_PORT');
        $dbName = env('DB_DATABASE');
        $dbUser = env('DB_USERNAME');
        $dbPassword = env('DB_PASSWORD');

        // Define el nombre y la ruta del archivo de respaldo
        $backupPath = storage_path('app/laravel-backups/Acacias/');
        $timestamp = date('Y-m-d_H-i-s');
        $backupFile = $backupPath . $dbName . '_backup_' . $timestamp . '.sql';
        $zipFile = $backupPath . $dbName . '_backup_' . $timestamp . '.zip';

        // Asegúrate de que el directorio de respaldo exista
        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        // Ruta completa al binario de mysqldump
        $mysqldumpPath = 'C:\Program Files\MySQL\MySQL Server 8.0.6\bin\mysqldump.exe';

        // Crea el comando para realizar el respaldo y redirige el error estándar a null
        $command = "\"$mysqldumpPath\" --host=$dbHost --port=$dbPort --user=$dbUser --password=$dbPassword --no-tablespaces $dbName > \"$backupFile\" 2>nul";

        // Ejecuta el comando del sistema
        exec($command, $output, $result);

        if ($result !== 0) {
            Log::error('mysqldump error: ' . implode("\n", $output));
            throw new \Exception('Error al ejecutar el comando mysqldump.');
        }

        // Verifica que el archivo SQL se haya creado y tenga un tamaño mayor a 0 bytes
        if (filesize($backupFile) == 0) {
            throw new \Exception('El archivo SQL generado está vacío.');
        }

        // Crear un archivo ZIP del respaldo SQL
        $zip = new \ZipArchive();
        if ($zip->open($zipFile, \ZipArchive::CREATE) === TRUE) {
            $zip->addFile($backupFile, basename($backupFile));
            $zip->close();

            // Eliminar el archivo SQL después de comprimirlo
            unlink($backupFile);
        } else {
            throw new \Exception('No se pudo crear el archivo ZIP.');
        }

        Log::info('Backup created successfully: ' . $zipFile);
        session()->flash('success', 'Respaldo creado exitosamente');
        return redirect()->route('backup');
    } catch (\Exception $e) {
        Log::error('Error al realizar el respaldo: ' . $e->getMessage());
        return redirect()->back()->withErrors(['error' => 'Error al realizar el respaldo']);
    }
}


    public function listBackups()
    {
        $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
        $files = $disk->files(config('backup.backup.name'));
        $backups = [];
    
        foreach ($files as $file) {
            if ($disk->exists($file)) {
                $backups[] = [
                    'file_path' => $file,
                    'file_name' => str_replace(config('backup.backup.name') . '/', '', $file),
                    'file_size' => $disk->size($file),
                    'is_zip' => substr($file, -4) == '.zip'
                ];
            }
        }
    
        return view('backup', compact('backups'));
    }
    
    public function downloadBackup($file_name)
    {
        $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
        $file = config('backup.backup.name') . '/' . $file_name;

        if ($disk->exists($file)) {
            return response()->download($disk->path($file));
        } else {
            return redirect()->back()->with('error', 'El archivo no existe.');
        }
    }

    public function deleteBackup($file_name)
    {
        try {
            $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
            $file = config('backup.backup.name') . '/' . $file_name;

            if ($disk->exists($file)) {
                $disk->delete($file);
                return redirect()->back()->with('success', 'Respaldo eliminado exitosamente');
            } else {
                return redirect()->back()->with('error', 'El archivo no existe.');
            }
        } catch (\Exception $e) {
            Log::error('Error al eliminar el respaldo: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Error al eliminar el respaldo']);
        }
    }

    public function convertToZip($file_name)
    {
        try {
            $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
            $backupPath = config('backup.backup.name') . '/' . $file_name;
    
            if (!$disk->exists($backupPath)) {
                return redirect()->back()->with('error', 'El archivo no existe.');
            }
    
            // Verifica si el archivo ya está en formato ZIP
            if (substr($backupPath, -4) === '.zip') {
                return redirect()->back()->with('error', 'El archivo ya está en formato ZIP.');
            }
    
            $zipFile = str_replace('.sql', '.zip', $backupPath);
    
            // Crear un archivo ZIP
            $zip = new \ZipArchive();
            if ($zip->open($disk->path($zipFile), \ZipArchive::CREATE) === TRUE) {
                $zip->addFile($disk->path($backupPath), basename($backupPath));
                $zip->close();
    
                // Eliminar el archivo SQL después de comprimirlo
                $disk->delete($backupPath);
    
                return redirect()->route('backup')->with('success', 'El archivo se ha convertido a ZIP exitosamente.');
            } else {
                throw new \Exception('No se pudo crear el archivo ZIP.');
            }
        } catch (\Exception $e) {
            Log::error('Error al convertir el archivo a ZIP: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Error al convertir el archivo a ZIP']);
        }
    }
    

}
