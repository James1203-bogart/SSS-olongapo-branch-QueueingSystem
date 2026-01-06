<?php

namespace App\Services;

use Escpos\Printer;
use Escpos\CapabilityProfile;
use Escpos\PrintConnectors\NetworkPrintConnector;
use Escpos\PrintConnectors\WindowsPrintConnector;
use Escpos\PrintConnectors\CupsPrintConnector;
use Escpos\PrintConnectors\DummyPrintConnector;

class EscposPrinterService
{
    private function makeConnector()
    {
        $driver = config('printer.driver');
        try {
            switch ($driver) {
                case 'network':
                    $host = config('printer.host');
                    $port = (int) config('printer.port');
                    return new NetworkPrintConnector($host, $port);
                case 'windows':
                    $share = config('printer.windows_share');
                    return new WindowsPrintConnector($share);
                case 'cups':
                    $queue = config('printer.cups_printer');
                    return new CupsPrintConnector($queue);
                default:
                    return new DummyPrintConnector();
            }
        } catch (\Throwable $e) {
            // Fallback to dummy so we don't crash the app.
            return new DummyPrintConnector();
        }
    }

    /**
     * Print a simple ticket using ESC/POS commands.
     * Expects keys: number, category/transaction, priority, generated_at
     */
    public function printTicket(array $ticket): array
    {
        $connector = $this->makeConnector();
        $profile = CapabilityProfile::load("default");
        $printer = new Printer($connector, $profile);

        try {
            $number = (string) ($ticket['number'] ?? '---');
            $label  = (string) ($ticket['transaction'] ?? ($ticket['category'] ?? ''));
            $prio   = (string) ($ticket['priority'] ?? '');
            $time   = (string) ($ticket['generated_at'] ?? '');

            // Header
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->text("Queue Ticket\n");
            $printer->selectPrintMode();

            // Big number
            $printer->setTextSize(2, 2);
            $printer->text($number . "\n");
            $printer->setTextSize(1, 1);

            // Meta
            $meta = trim(($prio !== '' ? ucfirst($prio) . ' · ' : '') . $label);
            if ($meta !== '') {
                $printer->text($meta . "\n");
            }
            if ($time !== '') {
                $printer->text("Generated: " . $time . "\n");
            }

            $printer->feed(2);
            $printer->cut();
            $printer->close();

            return ['ok' => true];
        } catch (\Throwable $e) {
            try { $printer->close(); } catch (\Throwable $e2) {}
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
