<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Charge le dump de contenu migré depuis WordPress (database/seed.sql).
 *
 * Le dump est importé via le client natif (mariadb/mysql) car il contient des
 * directives transactionnelles (AUTOCOMMIT, DROP/CREATE) que PHP/PDO gère mal :
 * c'est rapide (~1s) et fiable. Bascule sur DB::unprepared() si le binaire manque.
 */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seed.sql');

        if (! File::exists($path)) {
            throw new RuntimeException("Fichier de seed introuvable : {$path}");
        }

        if (! $this->importViaClient($path)) {
            DB::unprepared(File::get($path));
        }
    }

    /**
     * Importe le dump via le client mariadb/mysql en ligne de commande.
     *
     * @return bool true si l'import a réussi via le client natif
     */
    private function importViaClient(string $path): bool
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if (($config['driver'] ?? null) !== 'mysql' && ($config['driver'] ?? null) !== 'mariadb') {
            return false;
        }

        $binary = $this->resolveBinary();

        if ($binary === null) {
            return false;
        }

        $process = new Process([
            $binary,
            '--host='.$config['host'],
            '--port='.$config['port'],
            '--user='.$config['username'],
            '--password='.$config['password'],
            '--default-character-set=utf8mb4',
            $config['database'],
        ]);

        $process->setInput(File::get($path));
        $process->setTimeout(120);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->command?->warn('Import natif échoué, bascule sur PHP : '.trim($process->getErrorOutput()));

            return false;
        }

        return true;
    }

    private function resolveBinary(): ?string
    {
        foreach (['mariadb', 'mysql'] as $candidate) {
            $which = new Process(['which', $candidate]);
            $which->run();

            if ($which->isSuccessful()) {
                return trim($which->getOutput());
            }
        }

        return null;
    }
}
