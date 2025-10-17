const db = require('../models');
const User = db.user;
const Role = db.role;
const bannerController = require('./banner.controller.js')
const newsController = require('./news.controller');


exports.home = async (req, res) => {
    try {
        // Obtener banners activos desde el controlador
        const activeBanners = await bannerController.getActiveBanners();
        activeNews = await newsController.getActiveNews();
        
        res.render('pages/home', {
            title: 'Inicio',
            user: req.session.userId ? {
                id: req.session.userId,
                username: req.session.username,
                roles: req.session.roles
            } : null,
            activeBanners: activeBanners // 👈 Pasar los banners al template
        });

    } catch (error) {
        console.error('Error en home:', error);
        res.render('pages/home', {
            title: 'Inicio',
            user: req.session.userId ? {
                id: req.session.userId,
                username: req.session.username,
                roles: req.session.roles
            } : null,
            activeBanners: [] // 👈 Array vacío en caso de error
        });
    }
};
// Para el user dashboard - mostrar formulario de scraping
exports.userBoard = async (req, res) => {
    try {
        // Obtener noticias existentes
        const news = await newsController.getAllNews();
        
        res.render('pages/user-dashboard', {
            title: 'Panel de Usuario - Noticias',
            news: news,
            user: {
                id: req.session.userId,
                username: req.session.username,
                email: req.session.email,
                roles: req.session.roles
            }
        });

    } catch (error) {
        console.error('Error en userBoard:', error);
        res.render('pages/user-dashboard', {
            title: 'Panel de Usuario - Noticias',
            news: [],
            user: {
                id: req.session.userId,
                username: req.session.username,
                email: req.session.email,
                roles: req.session.roles
            }
        });
    }
};

// Procesar scraping de noticias
exports.scrapeNews = async (req, res) => {
    try {
        const { newsUrl } = req.body;
        
        if (!newsUrl) {
            return res.redirect('/user-dashboard?error=Debe+ingresar+una+URL+válida');
        }

        console.log('🌐 Procesando URL:', newsUrl);
        
        // Realizar scraping
        const scrapedNews = await newsController.scrapeNews(newsUrl);
        
        if (scrapedNews.length === 0) {
            return res.redirect('/user-dashboard?error=No+se+encontraron+noticias+en+el+sitio+web');
        }

        // Guardar la primera noticia encontrada
        const savedNews = await newsController.saveNews(scrapedNews[0], req.session.userId);
        
        res.redirect('/user-dashboard?message=Noticia+obtenida+y+guardada+correctamente');

    } catch (error) {
        console.error('❌ Error en scrapeNews:', error);
        res.redirect('/user-dashboard?error=' + encodeURIComponent(error.message));
    }
};

exports.adminBoard = async (req, res) => {
    try {
        // Obtener todos los usuarios
        const users = await User.findAll({
            attributes: ['id', 'username', 'email', 'createdAt'],
            order: [['createdAt', 'DESC']]
        });

        // Obtener todos los roles disponibles
        const roles = await Role.findAll({
            attributes: ['id', 'name']
        });

        // Obtener los roles de cada usuario
        const usersWithRoles = await Promise.all(
            users.map(async (user) => {
                const userRoles = await user.getRoles({
                    attributes: ['id', 'name']
                });
                return {
                    id: user.id,
                    username: user.username,
                    email: user.email,
                    createdAt: user.createdAt,
                    Roles: userRoles
                };
            })
        );

        // Estadísticas simples
        const totalUsers = users.length;

        res.render('pages/admin-dashboard', {
            title: 'Panel de Administración',
            users: usersWithRoles,
            roles: roles,
            totalUsers: totalUsers,
            currentUserId: req.session.userId,
            user: {
                id: req.session.userId,
                username: req.session.username,
                roles: req.session.roles
            }
        });

    } catch (error) {
        console.error('Error en adminBoard:', error);
        res.status(500).render('error', {
            title: 'Error',
            message: 'Error al cargar el panel de administración: ' + error.message
        });
    }
};

exports.updateUserRoles = async (req, res) => {
    try {
        const userId = req.params.userId;
        const selectedRoles = req.body.roles || [];

        const user = await User.findByPk(userId);
        if (!user) {
            return res.redirect('/admin-dashboard?error=Usuario+no+encontrado');
        }

        const roles = await Role.findAll({
            where: {
                id: selectedRoles
            }
        });

        await user.setRoles(roles);
        res.redirect('/admin-dashboard?message=Roles+actualizados+correctamente');

    } catch (error) {
        console.error('Error en updateUserRoles:', error);
        res.redirect('/admin-dashboard?error=Error+al+actualizar+roles');
    }
};

exports.deleteUser = async (req, res) => {
    try {
        const userId = req.params.userId;
        
        if (userId == req.session.userId) {
            return res.redirect('/admin-dashboard?error=No+puedes+eliminarte+a+ti+mismo');
        }

        const user = await User.findByPk(userId);
        if (!user) {
            return res.redirect('/admin-dashboard?error=Usuario+no+encontrado');
        }

        await user.destroy();
        res.redirect('/admin-dashboard?message=Usuario+eliminado+correctamente');

    } catch (error) {
        console.error('Error en deleteUser:', error);
        res.redirect('/admin-dashboard?error=Error+al+eliminar+usuario');
    }
};

exports.moderatorBoard = (req, res) => {
  res.render('pages/moderator-dashboard', {
    title: 'Panel de Moderación - Banners',
    user: {
      id: req.session.userId,
      username: req.session.username,
      email: req.session.email,
      roles: req.session.roles
    }
  });
};

exports.profile = (req, res) => {
    res.render('pages/profile', {
        title: 'Mi Perfil',
        user: {
            id: req.session.userId,
            username: req.session.username,
            email: req.session.email,
            roles: req.session.roles
        }
    });
};


exports.updateUsername = async (req, res) => {
    try {
        const { newUsername } = req.body;
        
        if (!newUsername || newUsername.length < 3) {
            return res.redirect('/profile?error=Usuario+debe+tener+3+caracteres');
        }

        const user = await User.findByPk(req.session.userId);
        user.username = newUsername;
        await user.save();

        req.session.username = newUsername;
        res.redirect('/profile?message=Usuario+actualizado');

    } catch (error) {
        res.redirect('/profile?error=Error+actualizando+usuario');
    }
};


exports.updatePassword = async (req, res) => {
    try {
        const { currentPassword, newPassword } = req.body;
        
        const user = await User.findByPk(req.session.userId);
        const passwordValid = bcrypt.compareSync(currentPassword, user.password);

        if (!passwordValid) {
            return res.redirect('/profile?error=Contraseña+actual+incorrecta');
        }

        user.password = bcrypt.hashSync(newPassword, 8);
        await user.save();

        res.redirect('/profile?message=Contraseña+actualizada');

    } catch (error) {
        res.redirect('/profile?error=Error+actualizando+contraseña');
    }
};

