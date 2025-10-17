const db = require('../models');
const Banner = db.banner;
const User = db.user;
const path = require('path');
const fs = require('fs');

exports.moderatorBoard = async (req, res) => {
    try {
        const banners = await Banner.findAll({
            include: [{
                model: User,
                as: 'user'
            }],
            order: [['idBanner', 'DESC']] // Ordenar por idBanner
        });

        res.render('pages/moderator-dashboard', {
            title: 'Gestión de Banners',
            banners: banners,
            user: {
                id: req.session.userId,
                username: req.session.username,
                roles: req.session.roles
            }
        });

    } catch (error) {
        console.error('Error en moderatorBoard:', error);
        res.status(500).render('error', {
            title: 'Error',
            message: 'Error al cargar el panel de banners'
        });
    }
};

exports.uploadBanner = async (req, res) => {
    try {
        if (!req.file) {
            return res.redirect('/moderator-dashboard?error=Debe+seleccionar+una+imagen');
        }

        const banner = await Banner.create({
            Titulo: req.body.title,
            Imagen: req.file.filename,
            Descripción: req.body.description,
            Enlace: req.body.link,
            estado: req.body.isActive === 'on',
            userId: req.session.userId
        });

        res.redirect('/moderator-dashboard?message=Banner+subido+correctamente');

    } catch (error) {
        res.redirect('/moderator-dashboard?error=Error+al+subir+banner');
    }
};

exports.deleteBanner = async (req, res) => {
    try {
        const bannerId = req.params.id;
        const banner = await Banner.findByPk(bannerId);

        if (!banner) {
            return res.redirect('/moderator-dashboard?error=Banner+no+encontrado');
        }

        // Eliminar archivo de imagen
        const imagePath = path.join(__dirname, '../public/banners/', banner.Imagen);
        if (fs.existsSync(imagePath)) {
            fs.unlinkSync(imagePath);
        }

        await banner.destroy();
        res.redirect('/moderator-dashboard?message=Banner+eliminado+correctamente');

    } catch (error) {
        console.error('Error al eliminar banner:', error);
        res.redirect('/moderator-dashboard?error=Error+al+eliminar+banner');
    }
};

exports.updateBanner = async (req, res) => {
    try {
        const { id } = req.params;
        const { titulo, descripcion, imagen } = req.body;
        
        // Validar título
        if (!titulo || titulo.length < 3) {
            return res.redirect('/moderator-dashboard?error=Titulo+debe+tener+al+menos+3+caracteres');
        }

        // Validar descripción
        if (!descripcion || descripcion.length < 3) {
            return res.redirect('/moderator-dashboard?error=Descripcion+debe+tener+al+menos+3+caracteres');
        }

        // Buscar el banner por ID
        const banner = await Banner.findByPk(id);
        if (!banner) {
            return res.redirect('/moderator-dashboard?error=Banner+no+encontrado');
        }

        // Actualizar los campos del banner
        banner.Titulo = titulo;
        banner.Descripción = descripcion;
        banner.Imagen = imagen;
        
        // Guardar los cambios
        await banner.save();

        res.redirect('/moderator-dashboard?message=Banner+actualizado+correctamente');

    } catch (error) {
        console.error('Error updating banner:', error);
        res.redirect('/moderator-dashboard?error=Error+actualizando+banner');
    }
};

exports.toggleBanner = async (req, res) => {
    try {
        const bannerId = req.params.id;
        const banner = await Banner.findByPk(bannerId);

        if (!banner) {
            return res.redirect('/moderator-dashboard?error=Banner+no+encontrado');
        }

        banner.estado = !banner.estado;
        await banner.save();

        const message = banner.estado ? 'Banner+activado' : 'Banner+desactivado';
        res.redirect('/moderator-dashboard?message=' + message);

    } catch (error) {
        console.error('Error al cambiar estado banner:', error);
        res.redirect('/moderator-dashboard?error=Error+al+cambiar+estado');
    }
};



// Para mostrar banners en el home
exports.getActiveBanners = async () => {
    try {
        const banners = await Banner.findAll({
            where: { estado: true },
            include: [{
                model: User,
                as: 'user'
            }],
            order: [['idBanner', 'DESC']]
        });
        return banners;
    } catch (error) {
        console.error('Error al obtener banners:', error);
        return [];
    }
};