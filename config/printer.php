<?php

return [
    // Choose how the app talks to the thermal printer.
    // 'network' -> TCP 9100 to a printer with IP address
    // 'windows' -> Windows shared printer name via spooler (server-side Windows only)
    // 'cups'    -> Use CUPS queue name (macOS/Linux; requires raw/escpos filter)
    'driver' => env('PRINTER_DRIVER', 'network'),

    // Network settings
    'host' => env('PRINTER_HOST', '127.0.0.1'),
    'port' => env('PRINTER_PORT', 9100),

    // Windows shared printer name, e.g. \\HOST\\PrinterName or local name
    'windows_share' => env('PRINTER_WINDOWS_SHARE', 'ThermalPrinter'),

    // CUPS queue name on macOS/Linux
    'cups_printer' => env('PRINTER_CUPS_QUEUE', 'Thermal_Printer'),

    // Optional: character encoding for text
    'encoding' => env('PRINTER_ENCODING', 'UTF-8'),
];
