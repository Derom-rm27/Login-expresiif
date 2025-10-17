const axios = require('axios');
const cheerio = require('cheerio');
const db = require('../models');
const News = db.news;
const User = db.user;

// Función principal de scraping
exports.scrapeNews = async (url) => {
    try {
        console.log('🔍 Scrapeando:', url);
        
        const { data } = await axios.get(url, {
            timeout: 10000,
            headers: {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            }
        });

        const $ = cheerio.load(data);
        const news = [];

        // Scraper genérico para la mayoría de sitios de noticias

            
            // Extraer título
            let title = $('.col-md-12').find('h1').text().trim();
            
            // Extraer resumen/descripción
            let summary = $('.bajada').text().trim();
            
            // Extraer imagen
            let image = $('.img-note-alt').find('img').attr('src');
            
            // Extraer autor/fuente
            let author = "TVPerúNoticas";
            
            // Extraer enlace
            let link = url;

            // Solo agregar si tiene título y resumen mínimos
                news.push({
                    Titulo: title,
                    Resumen: summary.length > 200 ? summary.substring(0, 200) + '...' : summary,
                    Imagen: image || '',
                    Autor: author || 'Desconocido',
                    Fuente: new URL(url).hostname,
                    Enlace: link || url
            });

        return news;

    } catch (error) {
        throw new Error('No se pudo obtener noticias del sitio web');
    }
};

// Guardar noticias en la base de datos
exports.saveNews = async (newsData, userId) => {
    try {
        const news = await News.create({
            Titulo: newsData.Titulo,
            Resumen: newsData.Resumen,
            Imagen: newsData.Imagen,
            Autor: newsData.Autor,
            Fuente: newsData.Fuente,
            Enlace: newsData.Enlace,
            estado: true,
            userId: userId
        });

        return news;

    } catch (error) {
        throw error;
    }
};

// Obtener todas las noticias
exports.getAllNews = async () => {
    try {
        const news = await News.findAll({
            include: [{
                model: User,
                as: 'user'
            }],
            order: [['idNews', 'DESC']]
        });
        return news;
    } catch (error) {
        console.error('Error obteniendo noticias:', error);
        return [];
    }
};

// Obtener noticias activas para el home
exports.getActiveNews = async () => {
    try {
        const news = await News.findAll({
            where: { estado: true },
            include: [{
                model: User,
                as: 'user'
            }],
            order: [['idNews', 'DESC']],
            limit: 6 // Máximo 6 noticias en el home
        });
        return news;
    } catch (error) {
        console.error('Error obteniendo noticias activas:', error);
        return [];
    }
};