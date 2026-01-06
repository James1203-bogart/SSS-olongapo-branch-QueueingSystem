/*
 * Client-side printing via QZ Tray (macOS & Windows).
 * Requires QZ Tray installed: https://qz.io/download/
 * In production, supply your own certificate and signature.
 */

import qz from 'qz-tray';

// Provide certificate and signature promises (placeholders).
qz.security.setCertificatePromise(function(resolve, reject) {
  // TODO: Replace with your signed certificate (PEM string)
  resolve("-----BEGIN CERTIFICATE-----\nMIIB...REPLACE_ME...\n-----END CERTIFICATE-----\n");
});
qz.security.setSignaturePromise(function(toSign) {
  // TODO: Replace with a call to your server to sign 'toSign' using your private key.
  // For demo purposes, we return null which works only if QZ Tray is in insecure mode.
  return Promise.resolve(null);
});

export async function initQZ() {
  if (!qz.websocket.isActive()) {
    await qz.websocket.connect();
  }
}

export async function printTicketWithQZ(printerName, ticket) {
  await initQZ();
  const cfg = qz.configs.create(printerName, { scaleContent: false });
  const lines = [];
  lines.push({ type: 'raw', format: 'utf8', data: '\u001B@' }); // init ESC/POS
  lines.push({ type: 'raw', format: 'utf8', data: '\u001B\u0061\u0001' }); // center
  lines.push({ type: 'raw', format: 'utf8', data: 'Queue Ticket\n' });
  lines.push({ type: 'raw', format: 'utf8', data: `\n${ticket.number}\n` });
  const meta = `${(ticket.priority||'').toUpperCase()} · ${(ticket.transaction||ticket.category||'')}`.trim();
  if (meta) lines.push({ type: 'raw', format: 'utf8', data: meta + '\n' });
  if (ticket.generated_at) lines.push({ type: 'raw', format: 'utf8', data: 'Generated: ' + ticket.generated_at + '\n' });
  lines.push({ type: 'raw', format: 'utf8', data: '\n\n' });
  lines.push({ type: 'raw', format: 'utf8', data: '\u001DVA\u0000' }); // partial cut
  return qz.print(cfg, lines);
}

// Also expose on window for easy use from Blade templates
if (typeof window !== 'undefined') {
  window.QZPrint = { initQZ, printTicketWithQZ, qz };
}
