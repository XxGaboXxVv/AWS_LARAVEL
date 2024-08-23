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
            $this->authorize('insert', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error'=> 'No tienes permisos para poder Crear.']);
        }
    try {
        date_default_timezone_set('America/Tegucigalpa');

        $dbHost = env('DB_HOST');
        $dbPort = env('DB_PORT');
        $dbName = env('DB_DATABASE');
        $dbUser = env('DB_USERNAME');
        $dbPassword = env('DB_PASSWORD');

        $backupPath = storage_path('app/laravel-backups/Acacias/');
        $timestamp = date('Y-m-d_H-i-s');
        $backupFile = $backupPath . $dbName . '_backup_' . $timestamp . '.sql';
        $zipFile = $backupPath . $dbName . '_backup_' . $timestamp . '.zip';

        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $mysqldumpPath = '/usr/bin/mysqldump';

        // Captura tanto la salida como los errores
$command = "\"$mysqldumpPath\" --host=$dbHost --port=$dbPort --user=$dbUser --password=$dbPassword --no-tablespaces $dbName > \"$backupFile\" 2>/dev/null";
        exec($command, $output, $result);

        Log::info('mysqldump command: ' . $command);
        Log::info('mysqldump output: ' . implode("\n", $output));

        if ($result !== 0 || filesize($backupFile) == 0) {
            throw new \Exception('Error al ejecutar el comando mysqldump: ' . implode("\n", $output));
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipFile, \ZipArchive::CREATE) === TRUE) {
            $zip->addFile($backupFile, basename($backupFile));
            $zip->close();
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
    $backupPath = storage_path('app/laravel-backups/Acacias/');
    $files = array_merge(
        glob($backupPath . '*.zip'), // Filtra archivos ZIP
        glob($backupPath . '*.sql')  // Filtra archivos SQL
    );

    // Ordenar los archivos por fecha de modificación, más reciente primero
    usort($files, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });

    $backups = [];

    foreach ($files as $file) {
        $backups[] = [
            'file_path' => $file,
            'file_name' => basename($file),
            'file_size' => filesize($file),
            'is_zip' => substr($file, -4) === '.zip' // Verifica si es un archivo ZIP
        ];
    }

    return view('backup', compact('backups'));
}

    
    public function downloadBackup($file_name)
{
    $backupPath = storage_path('app/laravel-backups/Acacias/') . $file_name;

    if (file_exists($backupPath)) {
        return response()->download($backupPath);
    } else {
        return redirect()->back()->with('error', 'El archivo no existe.');
    }
}

public function deleteBackup($file_name)
{
    try {
            $this->authorize('delete', User::class); 
        } catch (AuthorizationException $e) {
            return response()->json(['error'=> 'No tienes permisos para poder Eliminar.']);
        }
    try {
        $backupPath = storage_path('app/laravel-backups/Acacias/') . $file_name;

        if (file_exists($backupPath)) {
            unlink($backupPath);
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
        $backupPath = storage_path('app/laravel-backups/Acacias/') . $file_name;

        if (!file_exists($backupPath)) {
            return redirect()->back()->with('error', 'El archivo no existe.');
        }

        // Verifica si el archivo ya está en formato ZIP
        if (substr($backupPath, -4) === '.zip') {
            return redirect()->back()->with('error', 'El archivo ya está en formato ZIP.');
        }

        $zipFile = str_replace('.sql', '.zip', $backupPath);

        // Crear un archivo ZIP
        $zip = new \ZipArchive();
        if ($zip->open($zipFile, \ZipArchive::CREATE) === TRUE) {
            $zip->addFile($backupPath, basename($backupPath));
            $zip->close();

            // Eliminar el archivo SQL después de comprimirlo
            unlink($backupPath);

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
