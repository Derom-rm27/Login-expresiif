const { authJwt } = require("../middleware");
const controller = require("../controllers/user.controller");
const reportController = require("../controllers/report.controller");
const bannerController = require("../controllers/banner.controller");
const newsController = require("../controllers/news.controller");
const upload = require("../middleware/upload");

module.exports = function(app){

    const Isauth = (req, res, next) => {
        if(req.session.userId){return next();}
        res.redirect('/login')
    };

    const hasrole = (roles) => {
        return (req, res, next) => {
            if (!req.session.roles) {
                return res.redirect('/login');
            }
            const userRoles = req.session.roles.map(role => 
                role.replace('ROLE_', '').toLowerCase()
            );

            const hasRequiredRole = roles.some(requiredRole =>
                userRoles.includes(requiredRole.toLowerCase())
            );

            if (hasRequiredRole) {
                return next();
            }

                return res.status(403).render('error', {
                title: 'Acceso Denegado',
                message: "Require Moderator or Admin Role!"
    });
        };
    };
    app.get('/', controller.home); 

    app.get('/login',(req, res) =>{
        if(req.session.userId){return res.redirect('/profile');}
        res.render('auth/login',{
            title: 'Iniciar Sesión',
            error: null
        });
    });

    app.get('/register',(req, res) =>{
        if(req.session.userId){return res.redirect('/profile');}
        res.render('auth/register',{
            title: 'Registrarse',
            error: null,
            formData: {},
        });
    });

    app.get('/profile', Isauth, controller.profile);

    app.post('/profile/update-username', Isauth, controller.updateUsername);

    app.post('/profile/update-password', Isauth, controller.updatePassword);

    app.get('/user-dashboard', Isauth, hasrole(['user', 'moderator', 'admin']), controller.userBoard);

    app.post('/scrape-news', Isauth, hasrole(['user', 'moderator', 'admin']), controller.scrapeNews);

    app.get('/profile',Isauth, controller.userBoard);

    app.get('/admin-dashboard',Isauth, hasrole(['admin']), controller.adminBoard);

    app.post('/manage-users/update-roles/:userId', Isauth, hasrole(['admin']), controller.updateUserRoles);

    app.get('/moderator-dashboard',Isauth, hasrole(['moderator']), bannerController.moderatorBoard);

    app.post('/update-banner/:id', Isauth, bannerController.updateBanner);


    app.post('/upload-banner', Isauth, hasrole(['moderator', 'admin']), upload.single('bannerImage'), bannerController.uploadBanner);

    app.get('/update-banner/:id', Isauth, hasrole(['moderator', 'admin']), bannerController.updateBanner);

    app.post('/update-banner/:id', Isauth, hasrole(['moderator', 'admin']), bannerController.updateBanner);

    app.get('/delete-banner/:id', Isauth, hasrole(['moderator', 'admin']), bannerController.deleteBanner);
    
    app.get('/toggle-banner/:id', Isauth, hasrole(['moderator', 'admin']), bannerController.toggleBanner);

    app.get('/visit-report', Isauth, hasrole(['admin']), reportController.visitReport);

    app.get('/logout', (req, res) => {
        req.session.destroy((err) => {
            if (err) {
                console.error('Error al cerrar sesión:', err);
                return res.redirect('/?error=Error+al+cerrar+sesión');
            }
            res.clearCookie('connect.sid');
            res.redirect('/?message=Sesión+cerrada+correctamente');
        });
    });
};  