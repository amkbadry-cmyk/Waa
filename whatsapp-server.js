const { Client, LocalAuth } = require('whatsapp-web.js');
const express = require('express');
const app = express();
const qrcode = require('qrcode-terminal');

const PORT = 3001;
let qrCodeData = null;
let clientReady = false;

const client = new Client({
    authStrategy: new LocalAuth(),
    puppeteer: {
        headless: true,
        executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    }
});

client.on('qr', (qr) => {
    console.log('📱 QR Code تم توليده');
    qrCodeData = qr;
    qrcode.generate(qr, { small: true });
});

client.on('ready', () => {
    console.log('✅ واتساب جاهز!');
    clientReady = true;
    qrCodeData = null;
});

client.on('disconnected', (reason) => {
    console.log('❌ تم قطع الاتصال:', reason);
    clientReady = false;
    client.initialize();
});

client.initialize();

app.get('/qr', (req, res) => {
    if (qrCodeData) {
        res.json({
            success: true,
            qr_code: qrCodeData,
            message: 'امسح الكود من تطبيق واتساب'
        });
    } else if (clientReady) {
        res.json({
            success: true,
            message: '✅ تم تسجيل الدخول مسبقاً',
            ready: true
        });
    } else {
        res.json({
            success: false,
            message: '⏳ جاري توليد QR Code، حاول مرة أخرى'
        });
    }
});

app.post('/send', express.json(), async (req, res) => {
    const { phone, message } = req.body;
    
    if (!clientReady) {
        return res.json({
            success: false,
            message: '❌ واتساب غير جاهز، سجل الدخول أولاً'
        });
    }
    
    if (!phone || !message) {
        return res.json({
            success: false,
            message: '⚠️ يجب إرسال phone و message'
        });
    }
    
    try {
        const cleanPhone = phone.replace(/[^0-9]/g, '');
        const chatId = cleanPhone + '@c.us';
        await client.sendMessage(chatId, message);
        res.json({
            success: true,
            message: '✅ تم إرسال الرسالة',
            to: cleanPhone
        });
    } catch (error) {
        res.json({
            success: false,
            message: '❌ فشل الإرسال: ' + error.message
        });
    }
});

app.get('/status', (req, res) => {
    res.json({
        ready: clientReady,
        qr_available: !!qrCodeData,
        status: clientReady ? 'connected' : (qrCodeData ? 'waiting_qr' : 'initializing')
    });
});

app.listen(PORT, () => {
    console.log(`🚀 خادم واتساب يعمل على http://localhost:${PORT}`);
});