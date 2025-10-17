const db = require('../models');
const PageVisit = db.pagevisit;
const userAgentParser = require('../utils/userAgentParser');

const countVisit = async (req, res, next) => {
    // SOLO contar la página principal (/) y SOLO si es HTML
    if (req.path === '/' && req.method === 'GET' && req.headers.accept.includes('text/html')) {
        try {          
            const userAgent = req.headers['user-agent'] || 'Unknown';
            const { os, browser } = userAgentParser(userAgent);
            
            // Usar IP real del cliente
            const clientIP = req.ip || 
                            req.connection.remoteAddress || 
                            req.socket.remoteAddress ||
                            (req.connection.socket ? req.connection.socket.remoteAddress : null) ||
                            '127.0.0.1';    
            const visit = await PageVisit.create({
                ip_address: clientIP,
                user_agent: userAgent,
                operating_system: os,
                browser: browser
            });           
        } catch (error) {
            next();
        }
    } 
    next();
};

module.exports = countVisit;