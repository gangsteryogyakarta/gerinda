/**
 * Gerindra EMS - WhatsApp Server (Baileys)
 * 
 * Simple HTTP API server for WhatsApp messaging
 * Using ES Modules for Baileys 6.x
 */

import express from 'express';
import cors from 'cors';
import QRCode from 'qrcode';
import pino from 'pino';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

import makeWASocket, {
    DisconnectReason,
    useMultiFileAuthState,
    fetchLatestBaileysVersion,
    makeCacheableSignalKeyStore
} from '@whiskeysockets/baileys';

// ESM dirname workaround
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
const PORT = process.env.PORT || 3001;

// Middleware
app.use(cors());
app.use(express.json());

// Logger
const logger = pino({ level: 'info' });

// Store
let sock = null;
let qrCode = null;
let connectionStatus = 'disconnected';

// Auth state directory
const AUTH_DIR = path.join(__dirname, 'auth_info');

/**
 * Format phone number to WhatsApp format
 */
function formatPhoneNumber(phone) {
    // Remove non-numeric characters
    phone = phone.replace(/\D/g, '');
    
    // Handle Indonesian numbers
    if (phone.startsWith('0')) {
        phone = '62' + phone.substring(1);
    }
    if (phone.startsWith('+62')) {
        phone = phone.substring(1);
    }
    if (!phone.startsWith('62')) {
        phone = '62' + phone;
    }
    
    return phone + '@s.whatsapp.net';
}

/**
 * Initialize WhatsApp connection
 */
async function connectToWhatsApp() {
    try {
        const { state, saveCreds } = await useMultiFileAuthState(AUTH_DIR);
        const { version } = await fetchLatestBaileysVersion();

        sock = makeWASocket({
            version,
            logger: pino({ level: 'silent' }),
            printQRInTerminal: true,
            auth: {
                creds: state.creds,
                keys: makeCacheableSignalKeyStore(state.keys, pino({ level: 'silent' }))
            },
            generateHighQualityLinkPreview: true,
        });

        // Handle connection events
        sock.ev.on('connection.update', async (update) => {
            const { connection, lastDisconnect, qr } = update;

            if (qr) {
                // Generate QR code as base64
                qrCode = await QRCode.toDataURL(qr);
                connectionStatus = 'qr';
                console.log('📱 Scan QR code to connect');
            }

            if (connection === 'close') {
                const shouldReconnect = lastDisconnect?.error?.output?.statusCode !== DisconnectReason.loggedOut;
                console.log('Connection closed. Reconnecting:', shouldReconnect);
                connectionStatus = 'disconnected';
                qrCode = null;
                
                if (shouldReconnect) {
                    setTimeout(connectToWhatsApp, 3000);
                }
            } else if (connection === 'open') {
                console.log('✅ WhatsApp connected!');
                connectionStatus = 'connected';
                qrCode = null;
            }
        });

        // Save credentials on update
        sock.ev.on('creds.update', saveCreds);

        // Handle incoming messages (optional)
        sock.ev.on('messages.upsert', async (m) => {
            const msg = m.messages[0];
            if (!msg.key.fromMe && m.type === 'notify') {
                console.log('📨 New message from:', msg.key.remoteJid);
            }
        });

    } catch (error) {
        console.error('Connection error:', error);
        connectionStatus = 'error';
        setTimeout(connectToWhatsApp, 5000);
    }
}

// ============ API ROUTES ============

/**
 * Health check
 */
app.get('/health', (req, res) => {
    res.json({
        status: 'ok',
        whatsapp: connectionStatus,
        timestamp: new Date().toISOString()
    });
});

/**
 * Get QR code
 */
app.get('/qr', (req, res) => {
    if (connectionStatus === 'connected') {
        return res.json({
            success: false,
            message: 'Already connected',
            status: connectionStatus
        });
    }

    if (!qrCode) {
        return res.json({
            success: false,
            message: 'QR code not available yet. Please wait...',
            status: connectionStatus
        });
    }

    res.json({
        success: true,
        qr: qrCode,
        status: connectionStatus
    });
});

/**
 * Get connection status
 */
app.get('/status', (req, res) => {
    res.json({
        connected: connectionStatus === 'connected',
        status: connectionStatus,
        user: sock?.user || null
    });
});

/**
 * Send text message
 */
app.post('/send', async (req, res) => {
    try {
        const { phone, message } = req.body;

        if (!phone || !message) {
            return res.status(400).json({
                success: false,
                error: 'Phone and message are required'
            });
        }

        if (connectionStatus !== 'connected') {
            return res.status(503).json({
                success: false,
                error: 'WhatsApp not connected',
                status: connectionStatus
            });
        }

        const jid = formatPhoneNumber(phone);
        
        await sock.sendMessage(jid, { text: message });

        console.log(`📤 Message sent to ${phone}`);

        res.json({
            success: true,
            phone: phone,
            message: 'Message sent successfully'
        });
    } catch (error) {
        console.error('Send error:', error);
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

/**
 * Send image with caption
 */
app.post('/send-image', async (req, res) => {
    try {
        const { phone, imageUrl, caption } = req.body;

        if (!phone || !imageUrl) {
            return res.status(400).json({
                success: false,
                error: 'Phone and imageUrl are required'
            });
        }

        if (connectionStatus !== 'connected') {
            return res.status(503).json({
                success: false,
                error: 'WhatsApp not connected'
            });
        }

        const jid = formatPhoneNumber(phone);

        await sock.sendMessage(jid, {
            image: { url: imageUrl },
            caption: caption || ''
        });

        res.json({
            success: true,
            phone: phone,
            message: 'Image sent successfully'
        });
    } catch (error) {
        console.error('Send image error:', error);
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

/**
 * Check if number exists on WhatsApp
 */
app.post('/check-number', async (req, res) => {
    try {
        const { phone } = req.body;

        if (!phone) {
            return res.status(400).json({
                success: false,
                error: 'Phone is required'
            });
        }

        if (connectionStatus !== 'connected') {
            return res.status(503).json({
                success: false,
                error: 'WhatsApp not connected'
            });
        }

        const jid = formatPhoneNumber(phone);
        const [result] = await sock.onWhatsApp(jid);

        res.json({
            success: true,
            phone: phone,
            exists: !!result?.exists,
            jid: result?.jid || null
        });
    } catch (error) {
        console.error('Check number error:', error);
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

/**
 * Bulk send messages
 */
app.post('/bulk-send', async (req, res) => {
    try {
        const { phones, message, delay = 2000 } = req.body;

        if (!phones || !Array.isArray(phones) || phones.length === 0) {
            return res.status(400).json({
                success: false,
                error: 'Phones array is required'
            });
        }

        if (!message) {
            return res.status(400).json({
                success: false,
                error: 'Message is required'
            });
        }

        if (connectionStatus !== 'connected') {
            return res.status(503).json({
                success: false,
                error: 'WhatsApp not connected'
            });
        }

        // Send in background
        const results = {
            total: phones.length,
            queued: phones.length,
            message: 'Bulk send started in background'
        };

        // Process in background
        (async () => {
            for (let i = 0; i < phones.length; i++) {
                try {
                    const jid = formatPhoneNumber(phones[i]);
                    await sock.sendMessage(jid, { text: message });
                    console.log(`📤 [${i + 1}/${phones.length}] Sent to ${phones[i]}`);
                } catch (err) {
                    console.error(`❌ Failed to send to ${phones[i]}:`, err.message);
                }

                // Delay between messages
                if (i < phones.length - 1) {
                    await new Promise(resolve => setTimeout(resolve, delay));
                }
            }
            console.log('✅ Bulk send completed');
        })();

        res.json({
            success: true,
            ...results
        });
    } catch (error) {
        console.error('Bulk send error:', error);
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

/**
 * Logout (clear session)
 */
app.post('/logout', async (req, res) => {
    try {
        if (sock) {
            await sock.logout();
        }

        // Clear auth directory
        if (fs.existsSync(AUTH_DIR)) {
            fs.rmSync(AUTH_DIR, { recursive: true });
        }

        connectionStatus = 'disconnected';
        qrCode = null;

        // Reconnect to get new QR
        setTimeout(connectToWhatsApp, 1000);

        res.json({
            success: true,
            message: 'Logged out successfully'
        });
    } catch (error) {
        console.error('Logout error:', error);
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// ============ START SERVER ============

app.listen(PORT, () => {
    console.log(`
╔═══════════════════════════════════════════════════════╗
║     🚀 Gerindra WhatsApp Server (Baileys)             ║
╠═══════════════════════════════════════════════════════╣
║  Server running on: http://localhost:${PORT}             ║
║                                                       ║
║  Endpoints:                                           ║
║  GET  /health      - Health check                     ║
║  GET  /status      - Connection status                ║
║  GET  /qr          - Get QR code                      ║
║  POST /send        - Send message                     ║
║  POST /send-image  - Send image                       ║
║  POST /check-number - Check if number exists          ║
║  POST /bulk-send   - Send bulk messages               ║
║  POST /logout      - Logout session                   ║
╚═══════════════════════════════════════════════════════╝
    `);

    // Start WhatsApp connection
    connectToWhatsApp();
});
